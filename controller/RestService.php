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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\controller;

use oat\taoLti\models\classes\LtiRestApiService;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;

class RestService extends \tao_actions_CommonRestModule
{
    const LTI_USER_ID = 'lti_user_id';
    const LTI_CONSUMER_KEY = 'lti_consumer_key';

    /**
     * taoLti_actions_RestService constructor.
     * Pass model service to handle http call business
     * @throws \common_exception_NoImplementation
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = LtiRestApiService::singleton();
    }

    /**
     * @param null $uri
     * @return mixed|void
     * @throws \common_exception_NoImplementation
     */
    public function get($uri = null)
    {
        throw new \common_exception_NoImplementation();
    }

    /**
     * Creates a consumer
     *
     * @throws \common_exception_NotImplemented
     */
    public function create()
    {
        try {
            if ($this->getRequestMethod() != \Request::HTTP_PUT) {
                throw new \common_exception_NotImplemented('Only PUT method is accepted to request this service.');
            }

            $class = new \core_kernel_classes_Class(ConsumerService::CLASS_URI);

            if (!$this->hasRequestParameter('label')) {
                throw new \common_exception_MissingParameter('label', $this->getRequestURI());
            }

            $label = new \core_kernel_classes_Class($this->getRequestParameter('label'));
            $consumer = $class->createInstance($label);
            $consumer->setPropertyValue(new \core_kernel_classes_Property('classUri'), ConsumerService::CLASS_URI);

            if ($this->hasRequestParameter('oath-key')) {
                $consumer->setPropertyValue(new \core_kernel_classes_Property(DataStore::PROPERTY_OAUTH_KEY), $this->getRequestParameter('oath-key'));
            }
            if ($this->hasRequestParameter('oath-secret')) {
                $consumer->setPropertyValue(new \core_kernel_classes_Property(DataStore::PROPERTY_OAUTH_SECRET), $this->getRequestParameter('oath-secret'));
            }
            if ($this->hasRequestParameter('oath-callback-url')) {
                $consumer->setPropertyValue(new \core_kernel_classes_Property(DataStore::PROPERTY_OAUTH_CALLBACK), $this->getRequestParameter('oath-callback-url'));
            }

            $this->returnSuccess([
                'data' => $consumer->getUri(),
            ]);
        } catch (\Exception $e) {
            \common_Logger::w($e->getMessage());
            $this->returnFailure($e);
        }
    }

    /**
     * Deletes given consumer
     *
     * @param null $uri
     * @return mixed|void
     * @throws \common_exception_NotImplemented
     */
    public function delete($uri = null)
    {
        try {
            if (!$uri) {
                throw new \common_exception_MissingParameter('uri', $this->getRequestURI());
            }

            $consumer = new \core_kernel_classes_Resource($uri);

            if (!$consumer->exists()) {
                $this->returnFailure(new \common_exception_NotFound('Consumer has not been found'));
            }

            $consumer->delete(true);

            $this->returnSuccess();
        } catch (\Exception $e) {
            \common_Logger::w($e->getMessage());
            $this->returnFailure($e);
        }
    }

    /**
     * End point to get common user uri by lti user id
     * @throws \common_exception_NotImplemented
     */
    public function getUserId()
    {
        try {
            $parameters = $this->getParameters();
            if (!isset($parameters[self::LTI_USER_ID])) {
                throw new \common_exception_MissingParameter(self::LTI_USER_ID, __FUNCTION__);
            }
            if (!isset($parameters[self::LTI_CONSUMER_KEY])) {
                throw new \common_exception_MissingParameter(self::LTI_CONSUMER_KEY, __FUNCTION__);
            }

            $id = $parameters[self::LTI_USER_ID];
            $key = $parameters[self::LTI_CONSUMER_KEY];

            $data = $this->service->getUserId($id, $key);
            if (!$data) {
                \common_Logger::i('Id ' . $id . ' is not found.');
                throw new \common_exception_NotFound('No data found for the given id.');
            }

            $this->returnSuccess($data);
        } catch (\Exception $e) {
            \common_Logger::w($e->getMessage());
            $this->returnFailure($e);
        }
    }

    /**
     * Optionally a specific rest controller may declare
     * aliases for parameters used for the rest communication
     */
    protected function getParametersAliases()
    {
        return array(
            'user_id' => self::LTI_USER_ID,
            'oauth_consumer_key' => self::LTI_CONSUMER_KEY
        );
    }

    /**
     * Return array of required parameters sorted by http method
     * @return array
     */
    protected function getParametersRequirements()
    {
        return array();
    }
}