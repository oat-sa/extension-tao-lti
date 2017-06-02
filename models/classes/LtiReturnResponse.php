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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */
namespace oat\taoLti\models\classes;

use oat\tao\model\mvc\error\ResponseAbstract;
use oat\tao\helpers\Template;

/**
 * Class LtiReturnResponse
 *
 * Redirect to lti return url
 *
 * @package oat\taoLti\models\classes
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @property \taoLti_models_classes_LtiException $exception
 */
class LtiReturnResponse extends ResponseAbstract
{

    public function setHttpCode($code) {
        $this->httpCode = 302;
        return $this;
    }

    public function send()
    {
        $baseUrl = null;
        /** @var \taoLti_models_classes_TaoLtiSession $session */
        $session = \common_session_SessionManager::getSession();
        if ($session instanceof \taoLti_models_classes_TaoLtiSession) {
            $launchData = $session->getLaunchData();
            if($launchData->hasReturnUrl()){
                $baseUrl = $launchData->getReturnUrl();
            }
        } else {
            $request = \common_http_Request::currentRequest();
            $params = $request->getParams();
            if(isset($params[\taoLti_models_classes_LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL])){
                $baseUrl = $params[\taoLti_models_classes_LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL];
            }
        }

        if ($baseUrl !== null) {
            $params = $this->exception->getLtiMessage()->getUrlParams();
            $url = $baseUrl . (parse_url($baseUrl, PHP_URL_QUERY) ? '&' : '?') . http_build_query($params);
            header(\HTTPToolkit::locationHeader($url));
        } else {
            require Template::getTemplate('error/error500.tpl', 'tao');
        }
        return;
    }
    
}
