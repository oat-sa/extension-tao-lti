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

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package core
 * @subpackage kernel_auth_adapter
 */
class taoLti_models_classes_LtiAuthAdapter
	implements common_user_auth_Adapter
{
    /**
     * 
     * @var tao_models_classes_oauth_Request
     */
	private $request;
	
	/**
	 * Creates an Authentication adapter from an OAuth Request
	 * 
	 * @param tao_models_classes_oauth_Request $request
	 */
	public function __construct(tao_models_classes_oauth_Request $request) {
	    $this->request = $request;
	}
	
	/**
     * (non-PHPdoc)
     * @see common_user_auth_Adapter::authenticate()
     */
    public function authenticate() {
    	
        $oauthRequest = tao_models_classes_oauth_Request::fromRequest();
        if (!$oauthRequest->isValid()) {
    	    throw new taoLti_models_classes_LtiException('Invalid LTI signature');
    	}
    	$ltiLaunchData = new taoLti_models_classes_LtiLaunchData($oauthRequest->getParamters());
    	
    	return new taoLti_models_classes_LtiUser($ltiLaunchData);
    }
}