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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 */

namespace oat\taoLti\scripts\tools;


use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use \common_report_Report as Report;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;

/**
 * Class SetupLtiConsumer
 *
 * usage `sudo -u www-data php index.php 'oat\taoLti\scripts\tools\SetupLtiConsumer' -k 123456 -s 123456`
 *
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 * @package oat\taoLti\scripts\tools
 */
class SetupLtiConsumer extends ScriptAction
{
    use OntologyAwareTrait;

    /**
     * Provides option of script
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'key' => array(
                'prefix' => 'k',
                'longPrefix' => 'key',
                'description' => 'Consumer key',
                'required' => true,
                'cast' => 'string'
            ),
            'secret' => array(
                'prefix' => 's',
                'longPrefix' => 'secret',
                'description' => 'Consumer secret',
                'required' => true,
                'cast' => 'string'
            ),
            'label' => array(
                'prefix' => 'l',
                'longPrefix' => 'label',
                'description' => 'Consumer label',
                'required' => false,
                'cast' => 'string'
            ),
            'callbackUrl' => [
                'prefix' => 'cu',
                'longPrefix' => 'callbackUrl',
                'description' => 'Callback url for consumer',
                'required' => false
            ]
        ];
    }

    /**
     * Provides description of the script
     *
     * @return string
     */
    protected function provideDescription()
    {
        return 'Script to create a sample consumer';
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

    /**
     * Run the script
     */
    protected function run()
    {
        if (empty($this->getOption('key')) || empty($this->getOption('secret'))) {
            return new Report(Report::TYPE_ERROR, 'Not all arguments were provided. Try to run the script with -h option');
        }

        $consumerService = $this->getClassService();
        $clazz = $this->getClass(ConsumerService::CLASS_URI);

        $label = $this->hasOption('label') ? $this->getOption('label') : $consumerService->createUniqueLabel($clazz);

        try {
            $consumer = $consumerService->createInstance($clazz, $label);
        } catch (\Exception $e) {
            return new Report(Report::TYPE_ERROR, 'Error while creating consumer. Actual message is: ' . $e->getMessage());
        }

        $consumer->setPropertyValue($this->getProperty(DataStore::PROPERTY_OAUTH_KEY), $this->getOption('key'));
        $consumer->setPropertyValue($this->getProperty(DataStore::PROPERTY_OAUTH_SECRET), $this->getOption('secret'));

        if ($this->hasOption('callbackUrl')) {
            $consumer->setPropertyValue($this->getProperty(DataStore::PROPERTY_OAUTH_CALLBACK), $this->getOption('callbackUrl'));
        }

        return new Report(Report::TYPE_SUCCESS, sprintf('Lti consumer "%s" was created. Check GUI to edit its properties', $consumer->getLabel()));
    }

    /**
     * get the consumer service
     *
     * @return ConsumerService
     */
    private function getClassService()
    {
        return ConsumerService::singleton();
    }
}