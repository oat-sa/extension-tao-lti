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
 */
namespace oat\taoLti\models\classes\Lis;

use common_user_auth_Adapter;
use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthRequest;
use oat\oatbox\log\LoggerAwareTrait;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\LtiUser;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class LisAuthAdapter implements common_user_auth_Adapter, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use LoggerAwareTrait;
    const OAUTH_CONSUMER_KEY = 'oauth_consumer_key';

    /** @var ServerRequestInterface */
    protected $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function authenticate()
    {
        try {
            $authorization = $this->request->getHeader('Authorization');
            if (empty($authorization)) {
                throw new LisAuthAdapterException('Header auth missing, header received.');
            }

            $oauthRequest = OAuthRequest::from_request(
                $this->request->getMethod(),
                $this->request->getUri()
            );

            $validator = new LisSignatureValidator();

            $consumerSecret = $this->getConsumerSecret($oauthRequest->get_parameter(self::OAUTH_CONSUMER_KEY));

            $oauthConsumer = $this->getOAuthConsumer($oauthRequest, $consumerSecret);

            $params = $validator->validate(
                $oauthRequest,
                $oauthConsumer,
                $this->request->getMethod(),
                $this->request->getUri()
            );

            $ltiLaunchData = $this->getLaunchData($params);

            return new LtiUser($ltiLaunchData, $oauthRequest->get_parameter(self::OAUTH_CONSUMER_KEY));
        } catch (Throwable $exception) {
            throw new LisAuthAdapterException('Authentication failed');
        }
    }

    private function getConsumerSecret($oauthConsumerKey = null)
    {
        // @todo retrieve secret for key -- ?
        return 'secret';
    }

    /**
     * @param OAuthRequest $oauthRequest
     * @param string       $consumerSecret
     *
     * @return OAuthConsumer
     */
    private function getOAuthConsumer(OAuthRequest $oauthRequest, string $consumerSecret)
    {
        return new OAuthConsumer(
            $oauthRequest->get_parameter(self::OAUTH_CONSUMER_KEY),
            $consumerSecret,
            null
        );
    }

    /**
     * @param array $ltiVariables
     * @param array $customParameters
     *
     * @return LtiLaunchData
     */
    private function getLaunchData(array $ltiVariables, array $customParameters = [])
    {
        return new LtiLaunchData($ltiVariables, $customParameters);
    }
}
