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
        // Unused
    }

    protected function getClassService()
    {
        // Unused
    }

    /**
     * Get common user uri associated to Lti user id
     *
     * @param $id
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_NoContent
     */
    public function get($id)
    {
        $class = new \core_kernel_classes_Class(CLASS_LTI_USER);
        $instances = $class->searchInstances(array(
            PROPERTY_USER_LTIKEY => $id,
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
            'userUri' => $ltiUser->getUri()
        );
    }
}