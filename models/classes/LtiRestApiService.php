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

class LtiRestApiService extends \tao_models_classes_Service
{
    protected function getRootClass()
    {
        throw new \common_exception_NoImplementation();
    }

    protected function getClassService()
    {
        throw new \common_exception_NoImplementation();
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
        $class = new \core_kernel_classes_Class(CLASS_LTI_USER);

        $dataStore = new \tao_models_classes_oauth_DataStore();
        try {
            /** @var \core_kernel_classes_Resource $consumerResource */
            $consumerResource = $dataStore->findOauthConsumerResource($key);
        } catch (\tao_models_classes_oauth_Exception $e) {
            throw new \common_exception_NotFound($e->getMessage());
        }

        $instances = $class->searchInstances(array(
            PROPERTY_USER_LTIKEY => $id,
            PROPERTY_USER_LTICONSUMER => $consumerResource
        ), array(
            'like'	=> false
        ));

        if (count($instances) > 1) {
            throw new \common_Exception('Multiple user accounts found for user key: ' . $id);
        }

        /** @var \core_kernel_classes_Resource $ltiUser */
        $ltiUser = count($instances) == 1 ? current($instances) : null;
        if (!$ltiUser) {
            return null;
        }

        return array (
            'id' => $ltiUser->getUri()
        );
    }
}