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
 */

use oat\taoLti\models\classes\LtiRestApiService;

class taoLti_actions_LtiRestApi extends \tao_actions_CommonRestModule
{
    const LTI_USER_ID = 'lti_user_id';

    /**
     * taoResultServer_actions_QtiRestResults constructor.
     * Pass model service to handle http call business
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = LtiRestApiService::singleton();
    }

    /**
     * Entry point of API, only get($id) is available
     * @throws \oat\taoLti\models\classes\taoLti_models_classes_LtiException
     */
    public function index()
    {
        try {
            if (strtolower($this->getRequestMethod())!=='get') {
                throw new \common_exception_NoImplementation();
            }

            $parameters = $this->getParameters();
            $id = (int) $parameters[self::LTI_USER_ID];
            if ($id==0) {
                throw new \common_exception_InvalidArgumentType('LtiRestApi', 'get', '', 'id', $id);
            }

            $data = $this->service->get($id);
            if (!$data) {
                common_Logger::i('Id ' . $id . ' is not found.');
                throw new common_exception_NoContent('No id found for the given id.');
            }

            $this->returnSuccess($data);
        } catch (Exception $e) {
            common_Logger::w($e->getMessage());
            $this->returnFailure($e);
        }
    }

    /**
     * Optionnaly a specific rest controller may declare
     * aliases for parameters used for the rest communication
     */
    protected function getParametersAliases()
    {
        return array(
            "user" => self::LTI_USER_ID
        );
    }

    /**
     * Optionnal Requirements for parameters to be sent on every service
     */
    protected function getParametersRequirements()
    {
        return array(
            "get" => array(
                self::LTI_USER_ID,
            )
        );
    }

    /**
     * Return success response
     * Override of CommonRestModule::returnSuccesss
     * @param array $data
     * @throws common_exception_NotImplemented
     */
    protected function returnSuccess($data = array())
    {
        echo $this->encode($data);
        exit(0);
    }
}