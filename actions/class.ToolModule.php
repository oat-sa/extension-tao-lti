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
 * An abstract tool controller to be extended by the concrete tools
 * 
 * @package taoLti
 */
abstract class taoLti_actions_ToolModule extends tao_actions_CommonModule
{

    /**
     * Entrypoint of every tool
     */
    public function launch() {
		try {
		    taoLti_models_classes_LtiService::singleton()->startLtiSession(common_http_Request::currentRequest());
		    // check if cookie has been set
		    $this->redirect(_url('verifyCookie', 'CookieUtils', 'taoLti', array('session' => session_id(),'redirect' => _url('run'))));
        } catch (common_user_auth_AuthFailedException $e) {
            $this->returnError(__('The LTI connection could not be established'), false);
        } catch (taoLti_models_classes_LtiException $e) {
			$this->returnError(__('The LTI connection could not be established'), false);
        } catch (tao_models_classes_oauth_Exception $e) {
			$this->returnError(__('The LTI connection could not be established'), false);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tao_actions_CommonModule::returnError()
	 */
	protected function returnError($description, $returnLink = false) {
        if (tao_helpers_Request::isAjax()) {
            common_Logger::w('Called '.__FUNCTION__.' in an unsupported AJAX context');
            throw new common_Exception($description); 
        } else {
            if (!empty($description)) {
                $this->setData('message', $description);
            }
            if ($returnLink !== false) {
                $this->setData('returnLink', $returnLink);
            }
            $this->setView('error.tpl', 'taoLti');
        }
	}
	
	/**
	 * run() contains the actual tool's controller
	 */
	abstract public function run();
	
	/**
	 * Returns the lti tool of this controller
	 * 
	 * @return taoLti_models_classes_LtiTool
	 */
	abstract protected function getTool();
	
}