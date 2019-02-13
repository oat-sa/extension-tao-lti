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

use Renderer;
use HTTPToolkit;
use common_http_Request;
use oat\tao\helpers\Template;
use oat\tao\model\mvc\error\ResponseAbstract;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;

/**
 * Send LTI error response.
 *
 * @package oat\taoLti\models\classes
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class LtiReturnResponse extends ResponseAbstract
{
    /**
     * @var LtiException
     */
    protected $exception;

    protected $requestParams;

    /**
     * @var LtiLaunchData
     */
    protected $launchData;

    /**
     * @var Renderer
     */
    private $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param int $code
     * @return ResponseAbstract
     */
    public function setHttpCode($code)
    {
        $this->httpCode = 302;
        return $this;
    }

    /**
     * Send LTI error response.
     */
    public function send()
    {
        try {
            $this->requestParams = common_http_Request::currentRequest()->getParams();
            $this->launchData = LtiLaunchData::fromRequest(common_http_Request::currentRequest());
            $baseUrl = null;

            if ($this->requiresRedirect() && !empty($this->getReturnBaseUrl())) {
                $this->errorRedirectResponse();
            } else {
                echo $this->showLtiErrorPage();
            }
        } catch (\Exception $e) {
            $this->renderer->setTemplate(Template::getTemplate('error/error500.tpl', 'tao'));
            echo $this->renderer->render();
        }
    }

    /**
     * Check if redirect error response is required.
     *
     * @return bool
     */
    protected function requiresRedirect() {
        return $this->exception instanceof LtiClientException;
    }

    /**
     * Generate LtiErrorMessage based on exception
     *
     * @return LtiErrorMessage
     */
    protected function getLtiErrorMessage() {
        $message = __('Error: ') . $this->exception->getMessage();
        $log = __('Error: [key %s] "%s"', $this->exception->getKey(), $this->exception->getMessage());
        return new LtiErrorMessage($message, $log);
    }

    /**
     * Show error page
     *
     * @return string
     * 
     * @throws LtiVariableMissingException
     * @throws \common_Exception
     */
    protected function showLtiErrorPage() {
        if (isset($this->requestParams[LtiLaunchData::TOOL_CONSUMER_INSTANCE_NAME])) {
            $this->renderer->setData(
                'consumerLabel',
                $this->requestParams[LtiLaunchData::TOOL_CONSUMER_INSTANCE_NAME]
            );
        } elseif (isset($this->requestParams[LtiLaunchData::TOOL_CONSUMER_INSTANCE_DESCRIPTION])) {
            $this->renderer->setData(
                'consumerLabel',
                $this->requestParams[LtiLaunchData::TOOL_CONSUMER_INSTANCE_DESCRIPTION]
            );
        }

        if (isset($this->requestParams[LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL])) {
            $returnUrl = $this->requestParams[LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL];
            $serverName = $_SERVER['SERVER_NAME'];
            $pieces = parse_url($returnUrl);
            $domain = isset($pieces['host']) ? $pieces['host'] : '';
            if ($serverName == $domain) {
                $this->renderer->setData('returnUrl', $returnUrl);
            }
        }

        return $this->renderLtiErrorPage($this->exception, false);
    }

    /**
     * Render an error page.
     *
     * Ignore the parameter returnLink as LTI session always
     * require a way for the consumer to return to his platform
     *
     * @param LtiException $error
     * @param bool $returnLink
     *
     * @return string
     *
     * @throws LtiVariableMissingException
     * @throws \common_Exception
     */
    protected function renderLtiErrorPage(LtiException $error, $returnLink = true)
    {
        // In regard of the IMS LTI standard, we have to show a back button that refer to the
        // launch_presentation_return_url url param. So we have to retrieve this parameter before trying to start
        // te session
        $consumerLabel = $this->launchData->getToolConsumerName();
        if (!is_null($consumerLabel)) {
            $this->renderer->setData('consumerLabel', $consumerLabel);
        }

        $this->renderer->setData('message', $error->getMessage());
        $this->renderer->setTemplate(Template::getTemplate('error.tpl', 'taoLti'));

        return $this->renderer->render();
    }

    /**
     * Send LTI error redirect response.
     *
     * @throws LtiException
     * @throws \common_exception_Error
     */
    private function errorRedirectResponse()
    {
        $queryParams = $this->getLtiErrorMessage()->getUrlParams();
        $url = $this->getRedirectUrl($queryParams);

        $this->ltiRedirect($url);
    }

    /**
     * Build LTI return url with query parameters.
     *
     * @param array $queryParams
     * @return string
     * @throws LtiException
     * @throws \common_exception_Error
     */
    private function getRedirectUrl(array $queryParams) {
        $baseUrl = $this->getReturnBaseUrl();

        if (!empty($baseUrl)) {
            return $baseUrl . (parse_url($baseUrl, PHP_URL_QUERY) ? '&' : '?') . http_build_query($queryParams);
        } else {
            throw new LtiException('Invalid LTI return url.');
        }
    }

    /**
     * Get lti return url from LTI session or from request data.
     *
     * @return string
     * @throws LtiException
     * @throws \common_exception_Error
     */
    private function getReturnBaseUrl()
    {
        $baseUrl = '';

        /** @var TaoLtiSession $session */
        $session = \common_session_SessionManager::getSession();
        if ($session instanceof TaoLtiSession) {
            $launchData = $session->getLaunchData();
            if ($launchData->hasReturnUrl()) {
                $baseUrl = $launchData->getReturnUrl();
            }
        } else {
            if ($this->launchData->hasVariable(LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL)) {
                $baseUrl = $this->launchData->getVariable(LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL);
            }
        }

        return $baseUrl;
    }

    /**
     * @param $url
     * @param int $statusCode
     */
    private function ltiRedirect($url, $statusCode = 302)
    {
        header(HTTPToolkit::statusCodeHeader($statusCode));
        header(HTTPToolkit::locationHeader($url));
    }
}
