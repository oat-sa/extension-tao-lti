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

use oat\generis\model\OntologyRdfs;
use oat\taoLti\models\classes\LtiRestApiService;
use oat\tao\model\oauth\DataStore;

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
            'oauth_consumer_key' => self::LTI_CONSUMER_KEY,
            'label' => OntologyRdfs::RDFS_LABEL,
            'oauth-key' => DataStore::PROPERTY_OAUTH_KEY,
            'oauth-secret' => DataStore::PROPERTY_OAUTH_SECRET,
            'oauth-callback-url' => DataStore::PROPERTY_OAUTH_CALLBACK,
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