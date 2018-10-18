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

namespace oat\taoLti\models\classes;

use oat\generis\model\OntologyRdfs;
use oat\taoLti\models\classes\user\LtiUserService;

class LtiRestApiService extends \tao_models_classes_CrudService
{
    protected function getClassService()
    {
        return ConsumerService::singleton();
    }

    /**
     * Get common user uri associated to Lti user id
     *
     * @param $id string Identifier of LTI user
     * @param $key string Oauth LTI consumer key
     * @return array|null
     * @throws \common_Exception
     * @throws \tao_models_classes_oauth_Exception
     */
    public function getUserId($id, $key)
    {
        $dataStore = new \tao_models_classes_oauth_DataStore();
        try {
            /** @var \core_kernel_classes_Resource $consumerResource */
            $consumerResource = $dataStore->findOauthConsumerResource($key);
        } catch (\tao_models_classes_oauth_Exception $e) {
            throw new \common_exception_NotFound($e->getMessage());
        }

        /** @var LtiUserService $service */
        $service = $this->getServiceLocator()->get(LtiUserService::SERVICE_ID);
        $userIdentifier = $service->getUserIdentifier($id, $consumerResource);

        if (is_null($userIdentifier)) {
            return null;
        }

        return array (
            'id' => $userIdentifier
        );
    }

    public function createFromArray(array $propertiesValues)
    {
        if (array_key_exists(OntologyRdfs::RDFS_LABEL, $propertiesValues)) {
            $label = $propertiesValues[OntologyRdfs::RDFS_LABEL];
        } else {
            $label = '';
        }
        unset($propertiesValues[OntologyRdfs::RDFS_LABEL]);

        $propertiesValues['classUri'] = ConsumerService::CLASS_URI;

        $resource = parent::create($label, $this->getRootClass(), $propertiesValues);
        return $resource;
    }
}