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
        /** @var \taoLti_models_classes_TaoLtiSession $session */
        $session = \common_session_SessionManager::getSession();
        $launchData = $session->getLaunchData();
        $params = $this->exception->getLtiMessage()->getUrlParams();
        $baseUrl = $launchData->getReturnUrl();
        $url = $baseUrl . (parse_url($baseUrl, PHP_URL_QUERY) ? '&' : '?') . http_build_query($params);
        header(\HTTPToolkit::locationHeader($url));
        return;
    }
    
}
