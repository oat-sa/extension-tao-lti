<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoLti\scripts\tools;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\log\LoggerAggregator;
use oat\oatbox\log\VerboseLoggerFactory;
use oat\taoLti\models\classes\ResourceLink\KeyValueLink;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\ResourceLink\OntologyLink;

/**
 * Class OntologyLtiResourceLinksToKvMigration
 *
 * Script to migrate Ontology Lti links from ontology to key value persistence
 * - Must have `--kv-persistence` option as KV persistence destination of migration
 * - Overrides the LinkService config with KeyValue implementation if `--no-migrate-service` is not set
 * - Delete Ontology data after migration if `--no-delete` is not set
 *
 * @package oat\ltiDeliveryProvider\scripts\dbMigrations
 */
class OntologyLtiResourceLinksToKvMigration extends ScriptAction
{
    use OntologyAwareTrait;

    /**
     * Run the migration
     */
    protected function run()
    {
        try {
            $this->setVerbosity();

            /** @var LinkService  $ontologyLinkService */
            $ontologyLinkService = $this->getServiceLocator()->get(LinkService::SERVICE_ID);

            if (!$ontologyLinkService instanceof OntologyLink) {
                return new \common_report_Report(\common_report_Report::TYPE_ERROR, ' LtiLinks migration must be done on a Ontology Service e.q. LtiDeliveryExecutionService.');
            }

            $kvLinkService = new KeyValueLink(array(
                KeyValueLink::OPTION_PERSISTENCE => $this->getKeyValuePersistenceName()
            ));
            if ($this->getOption('no-migrate-service') !== true) {
                $this->registerService(LinkService::SERVICE_ID, $kvLinkService);
                $this->logNotice('Link service was set to KeyValue implementation.');
            }

            $class = $this->getClass(OntologyLink::CLASS_LTI_INCOMINGLINK);
            $iterator = new \core_kernel_classes_ResourceIterator($class);
            $i = 0;
            foreach ($iterator as $instance) {

                $properties = $instance->getPropertiesValues(array(
                    OntologyLink::PROPERTY_LINK_ID,
                    OntologyLink::PROPERTY_CONSUMER,
                ));

                $consumerId = $this->getPropertyValue($properties, OntologyLink::PROPERTY_CONSUMER);
                $resourceLink = $this->getPropertyValue($properties, OntologyLink::PROPERTY_LINK_ID);

                if ($this->getKeyValuePersistence()->set(KeyValueLink::PREFIX . $consumerId . $resourceLink, $instance->getUri())) {
                    if ($this->getOption('no-delete') !== true) {
                        $instance->delete();
                        $this->logInfo('Link "' . $instance->getUri() .'" deleted from ontology storage.');
                    }
                    $this->logNotice('Link "' . $instance->getUri() .'" successfully migrated.');
                    $i++;
                } else {
                    $this->logError('Link "' . $instance->getUri() .'" cannot be migrated.');
                }
            }
            $this->logNotice('LTI links migrated: ' . $i);
        } catch (\Exception $e) {
            return \common_report_Report::createFailure('LtiLinks migration has failed with error message : ' . $e->getMessage());

        }

        return \common_report_Report::createSuccess('LtiLinks successfully has been migrated from Ontology to KV value. Count of LtiLinks migrated: ' . $i);
    }

    /**
     * Extract a property value from $properties array
     *
     * @param array $properties
     * @param $propertyName
     * @return null|string
     */
    protected function getPropertyValue(array $properties, $propertyName)
    {
        if (!isset($properties[$propertyName])) {
            return null;
        }
        $value = reset($properties[$propertyName]);
        return $value instanceof \core_kernel_classes_Resource ? $value->getUri() : (string) $value;
    }

    /**
     * Get the persistence name from option
     *
     * @return string
     * @throws \common_Exception
     */
    protected function getKeyValuePersistenceName()
    {
        $this->getKeyValuePersistence();
        return $this->getOption('kv-persistence');
    }

    /**
     * Create the persistence from option and validate as KeyValue persistence
     *
     * @return \common_persistence_KeyValuePersistence
     * @throws \common_Exception
     */
    protected function getKeyValuePersistence()
    {
        $persistenceName = $this->getOption('kv-persistence');
        /** @var \common_persistence_Manager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID);
        $persistence = $persistenceManager->getPersistenceById($persistenceName);
        if (!$persistence instanceof \common_persistence_KeyValuePersistence) {
            throw new \common_Exception('Given persistence is not a key value');
        }
        return $persistence;
    }

    /**
     * If verbose option is set, set the appropriate logger
     */
    protected function setVerbosity()
    {
        if ($this->getOption('verbose') === true) {
            $verboseLogger = VerboseLoggerFactory::getInstance(['-nc', '-vv']);
            $this->setLogger(new LoggerAggregator(array(
                $this->getLogger(),
                $verboseLogger
            )));
        }
    }

    /**
     * Provides option of script
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'kv-persistence' => array(
                'prefix' => 'kv',
                'longPrefix' => 'kv-persistence',
                'required' => true,
                'description' => 'The KeyValue persistence where you want to migrate to.',
            ),
            'no-migrate-service' => array(
                'prefix' => 'nms',
                'longPrefix' => 'no-migrate-service',
                'flag' => true,
                'description' => 'Don\'t migrate the OntologyLink from ontology to key value.',
            ),
            'no-delete' => array(
                'prefix' => 'nd',
                'longPrefix' => 'no-delete',
                'flag' => true,
                'description' => 'Don\'t delete ontology LTI links after migration.',
            ),
            'verbose' => array(
                'prefix' => 'v',
                'longPrefix' => 'verbose',
                'flag' => true,
                'description' => 'Output the log as command output.',
            ),
        ];
    }

    /**
     * Provides description of the script
     *
     * @return string
     */
    protected function provideDescription()
    {
        return 'Migration script to migrate LTI Links from Ontology to KeyValue persistence.';
    }

    /**
     * Provides help of this script
     *
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints the help.'
        ];
    }
}