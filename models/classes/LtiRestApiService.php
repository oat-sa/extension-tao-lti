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

use oat\oatbox\service\ServiceManager;

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

    public function get($id)
    {
        $class = new \core_kernel_classes_Class(CLASS_LTI_USER);
        $instances = $class->searchInstances(array(
            PROPERTY_USER_LTIKEY		=> $id,
            PROPERTY_USER_LTICONSUMER	=> $this->getLtiConsumerResource($ltiContext)
        ), array(
            'like'	=> false
        ));
        if (count($instances) > 1) {
            throw new taoLti_models_classes_LtiException('Multiple user accounts found for user key \''.$ltiContext->getUserID().'\'');
        }
        return count($instances) == 1 ? current($instances) : null;
    }
}