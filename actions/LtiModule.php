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

namespace oat\taoLti\actions;

use \tao_actions_CommonModule;
use oat\taoLti\actions\traits\LtiModuleTrait;

/**
 * An abstract lti controller
 * 
 * @package taoLti
 */
abstract class LtiModule extends tao_actions_CommonModule
{
    use LtiModuleTrait;

    /**
     * Returns an error page
     *
     * Ignore the parameter returnLink as LTI session always
     * require a way for the consumer to return to his platform
     *
     * @param string $error error to handle
     * @param boolean $returnLink
     * @param int $httpStatus
     */
    protected function returnError($error, $returnLink = true, $httpStatus = null)
    {
        $error = new \taoLti_models_classes_LtiException($error);
        $this->returnLtiError($error, $returnLink);
    }
}