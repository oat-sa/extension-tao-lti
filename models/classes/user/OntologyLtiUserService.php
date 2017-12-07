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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\models\classes\user;

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use oat\generis\model\data\ModelManager;

/**
 * Ontology implementation of the lti user service
 *
 * @access public
 * @author Antoine Robin, <antoine@taotesting.com>
 * @package taoLti
 */
class OntologyLtiUserService extends LtiUserService
{

    const PROPERTY_USER_LTICONSUMER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#UserConsumer';

    const PROPERTY_USER_LTIKEY = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#UserKey';

    const CLASS_LTI_USER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIUser';

    const PROPERTY_USER_LAUNCHDATA = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LaunchData';
    
    const OPTION_TRANSACTION_SAFE = 'transaction-safe';
    
    const OPTION_TRANSACTION_SAFE_RETRY = 'transaction-safe-retry';

    /**
     * Returns the existing tao User that corresponds to
     * the LTI request or spawns it. Overriden to implement
     * transaction safe implementation for Ontology Storage.
     *
     * @param \taoLti_models_classes_LtiLaunchData $launchData
     * @throws \taoLti_models_classes_LtiException
     * @return LtiUser
     */
    public function findOrSpawnUser(\taoLti_models_classes_LtiLaunchData $launchData)
    {
        $dataModel = ModelManager::getModel();
        $transactionSafe = $this->getOption(self::OPTION_TRANSACTION_SAFE);
        
        if (!$dataModel instanceof \core_kernel_persistence_smoothsql_SmoothModel || !$transactionSafe) {
            // Non-transaction safe approach (default).
            $taoUser = $this->findUser($launchData);
            if (is_null($taoUser)) {
                $taoUser = $this->spawnUser($launchData);
            }
            
            return $taoUser;
        } else {
            // Transaction safe approach.
            $platform = $dataModel->getPersistence()->getPlatform();            
            $retry = 0;
            $maxRetry = $this->getRetryOption();
            $previousIsolationLevel = $platform->getTransactionIsolation();
            
            while ($retry <= $maxRetry) {
                // As the following instructions produce a Critical Section, we need SERIALIZABLE SQL Isolation Level.
                $platform->setTransactionIsolation(\common_persistence_sql_Platform::TRANSACTION_SERIALIZABLE);
                $platform->beginTransaction();
                
                try {
                    $taoUser = $this->findUser($launchData);
                    
                    if (is_null($taoUser)) {
                        $taoUser = $this->spawnUser($launchData);
                    }
                    
                    $platform->commit();
                    
                    return $taoUser;
                } catch (\common_persistence_sql_SerializationException $e) {
                    // Serialization failures may occur. Useful reading below:
                    // - https://www.postgresql.org/docs/9.5/static/transaction-iso.html
                    // - https://dev.mysql.com/doc/refman/5.7/en/innodb-deadlocks.html
                    
                    // Will be a WARNING for Sprint-64 only. After this point, will go to DEBUG level.
                    \common_Logger::w('SQL Serialization Failure occured in ' . __CLASS__ . '::' . __LINE__ . ' while finding or spawing LTI Ontology user. Retried ' . $retry . ' times.');
                    $retry++;
                } catch (\Exception $e) {
                    if ($platform->isTransactionActive()) {
                        \common_Logger::d('Rollbacking LTI Ontology user transaction.');
                        $platform->rollback();
                    }
                    
                    // Log original exception.
                    \common_Logger::e('[' . get_class($e) . '] ' . $e->getMessage());
                    
                    throw new \taoLti_models_classes_LtiException('LTI Ontology user could not be created. Process had to be rolled back.', 0, $e);
                } finally {
                    // Reset isolation level to previous one!
                    $platform->setTransactionIsolation($previousIsolationLevel);
                }
            }
        }
    }

    /**
     * @TODO TT-273 split method in separate action (create and update)
     * @param LtiUser $user
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @return mixed|void
     * @throws \common_exception_InvalidArgumentType
     * @throws \tao_models_classes_oauth_Exception
     */
    protected function updateUser(LtiUser $user, \taoLti_models_classes_LtiLaunchData $ltiContext)
    {

        $userResource = new \core_kernel_classes_Resource($user->getIdentifier());

        if($userResource->exists()){

            $properties = $userResource->getPropertiesValues([
                PROPERTY_USER_UILG,
                PROPERTY_USER_FIRSTNAME,
                PROPERTY_USER_LASTNAME,
                PROPERTY_USER_MAIL,
                PROPERTY_USER_ROLES,
            ]);

            foreach ($properties as $key => $values){
                if($values != $user->getPropertyValues($key)){
                    $userResource->editPropertyValues(new \core_kernel_classes_Property($key), $user->getPropertyValues($key));
                }
            }
        } else {
            $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);

            $props = array(
                self::PROPERTY_USER_LTIKEY => $ltiContext->getUserID(),
                self::PROPERTY_USER_LTICONSUMER => $ltiContext->getLtiConsumer(),
                PROPERTY_USER_UILG => $user->getPropertyValues(PROPERTY_USER_UILG),
                RDFS_LABEL => $user->getPropertyValues(RDFS_LABEL),
                PROPERTY_USER_FIRSTNAME => $user->getPropertyValues(PROPERTY_USER_FIRSTNAME),
                PROPERTY_USER_LASTNAME => $user->getPropertyValues(PROPERTY_USER_LASTNAME),
                PROPERTY_USER_MAIL => $user->getPropertyValues(PROPERTY_USER_MAIL),
                PROPERTY_USER_ROLES => $user->getPropertyValues(PROPERTY_USER_ROLES),
            );

            $userResource = $class->createInstanceWithProperties($props);
            \common_Logger::i('added User ' . $userResource->getLabel());
        }
        $user->setIdentifier($userResource->getUri());
    }


    /**
     * @inheritdoc
     */
    public function getUserIdentifier($userId, $ltiConsumer)
    {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        $instances = $class->searchInstances(array(
            self::PROPERTY_USER_LTIKEY => $userId,
            self::PROPERTY_USER_LTICONSUMER => $ltiConsumer
        ), array(
            'like' => false
        ));
        if (count($instances) > 1) {
            throw new \taoLti_models_classes_LtiException(
                'Multiple user accounts found for user key \'' . $userId . '\'',
                LtiErrorMessage::ERROR_SYSTEM_ERROR
            );
        }
        /** @var \core_kernel_classes_Resource $instance */
        if (count($instances) == 1) {
            $instance = current($instances);
            return $instance->getUri();
        } else {
            return null;
        }

    }

    
    private function getRetryOption()
    {
        $retryOption = $this->getOption(self::OPTION_TRANSACTION_SAFE_RETRY);
        
        // Arbitrary default is 1.
        return ($retryOption) ? $retryOption : 1;
    }
}
