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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\controller;

use common_exception_BadRequest as BadRequestException;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Resource as KernelResource;
use OAT\Library\Lti1p3Core\Launch\Builder\LtiLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Link\ResourceLink\ResourceLink;
use OAT\Library\Lti1p3Core\Message\Claim\ContextClaim;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Security\Jwks\Exporter\JwksExporter;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepository;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\tao\model\controller\SignedFormInstance;
use oat\tao\model\http\Controller;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Security\Business\Contract\SecretKeyServiceInterface;
use oat\taoLti\models\classes\Security\Business\Domain\Exception\SecretKeyGenerationException;
use oat\taoLti\models\platform\builder\Lti1p3LaunchBuilder;
use oat\taoLti\models\platform\builder\LtiLaunchBuilderInterface;
use oat\taoLti\models\platform\service\LtiPlatformJwkProvider;
use oat\taoLti\models\platform\service\LtiPlatformJwksProvider;
use tao_actions_form_CreateInstance as CreateInstanceContainer;
use tao_actions_SaSModule;
use tao_helpers_form_Form as Form;
use tao_helpers_form_FormFactory as FormFactory;
use tao_helpers_Uri as UriHelper;
use tao_models_classes_dataBinding_GenerisFormDataBinder as FormDataBinder;
use tao_models_classes_dataBinding_GenerisFormDataBindingException as FormDataBindingException;
use OAT\Library\Lti1p3Core\Security\Jwks\Exporter\Jwk\JwkRS256Exporter;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Oidc\Endpoint\OidcLoginAuthenticator;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class LtiPlatform extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use HttpJsonResponseTrait;

    public function jwks(): void
    {
        /** @var LtiPlatformJwksProvider $provider */
        $provider = $this->getServiceLocator()
            ->get(LtiPlatformJwksProvider::class);

        $this->getPsrResponse()
            ->getBody()
            ->write(json_encode($provider->getKeySet()));
    }

    public function oidcAuth(): void
    {
        /*
         * //@TODO Make open ID work
         *
         * Tutorial: https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/message/oidc-resource-link-launch.md#platform-side-oidc-authentication
         * Repository implementation example: https://github.com/oat-sa/lib-lti1p3-core/blob/master/tests/Traits/DomainTestingTrait.php#L158
         * User authenticator example: https://github.com/oat-sa/lib-lti1p3-core/blob/master/tests/Traits/SecurityTestingTrait.php#L66
         */

        $platformkeyChain = $this->getPlatformKeyChain();
        $toolKeyChain = $this->getToolKeyChain();

        $platform = new Platform(
            'tao',
            'tao',
            'https://test-tao-deploy.docker.localhost'
        );

        //
        // @TODO Configure the tool to be used
        //
        // Using this tools as example: https://lti-ri.imsglobal.org/lti/tools/728
        //
        $tool = new Tool(
            'local_demo',               // [required] identifier
            'local_demo',                     // [required] name
            'http://localhost:8888/tool',             // [required] audience
            'http://localhost:8888/lti1p3/oidc/login-initiation',   // [optional] OIDC login initiation url
            'http://localhost:8888/tool/launch'      // [optional] LTI default ResourceLink launch url
        );

        $deploymentIds = ['1']; //@TODO Must come from configuration

        $registration = new Registration(
            'registrationIdentifier',
            'client_id',
            $platform,
            $tool,
            $deploymentIds,
            $platformkeyChain,
            $toolKeyChain,
            'https://test-tao-deploy.docker.localhost/taoLti/LtiPlatform/jwks'
        );

//        /** @var RegistrationRepositoryInterface $registrationRepository */
//        $registrationRepository = ...
//
//        /** @var UserAuthenticatorInterface $userAuthenticator */
//        $userAuthenticator = ...
//
//        /** @var ServerRequestInterface $request */
//        $request = ...
//
//        // Create the OIDC login initiator
//        $authenticator = new OidcLoginAuthenticator($registrationRepository, $userAuthenticator);
//
//        // Perform the login authentication (delegating to the $userAuthenticator with the hint 'loginHint')
//        $launchRequest = $authenticator->authenticate($request);
//
//        // Auto redirection to the tool via the  user's browser
//        echo $launchRequest->toHtmlRedirectForm();
    }

    public function launch(): void
    {
        /** @var LtiLaunchBuilderInterface $builder */
        $builder = $this->getServiceLocator()->get(Lti1p3LaunchBuilder::class);

        $providerId = $_GET['provider'] ?? 'https://test-tao-deploy.docker.localhost/ontologies/tao.rdf#i5f1e85088826d7d0e37a39af157828';

        /** @var LtiProvider $ltiProvider */
        $ltiProvider = $this->getServiceLocator()->get(LtiProviderService::class)->searchById($providerId);

        /** @var User $user */
        $user = $this->getServiceLocator()
            ->get(SessionService::SERVICE_ID)
            ->getCurrentUser();

        $ltiLaunch = $builder->withProvider($ltiProvider)
            ->withUser($user)
            ->withClaims(
                [
                    new ContextClaim('contextId'),  // LTI claim representing the context
                    'myCustomClaim' => 'myCustomValue' // custom claim
                ]
            )->withRoles(
                [
                    'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner'
                ]
            )->build();

        $this->getPsrResponse()
            ->getBody()
            ->write('<a href="' . $ltiLaunch->getToolLaunchUrlWithParams() . '" target="_blank">Click me</a>');
    }

    public function oauth(): void
    {
    }

    private function getPlatformKeyChain(): KeyChain
    {
        /** @var LtiPlatformJwkProvider $provider */
        $provider = $this->getServiceLocator()
            ->get(LtiPlatformJwkProvider::class);

        return new KeyChain(
            LtiPlatformJwksProvider::LTI_PLATFORM_KEY_SET_NAME,
            LtiPlatformJwksProvider::LTI_PLATFORM_KEY_SET_NAME,
            $provider->getPublicKey(),
            $provider->getPrivateKey()
        );
    }

    private function getToolKeyChain(): KeyChain
    {
        $keySetName = 'myToolKeySetName';

        $publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyZXlfd5yqChtTH91N76V
okquRu2r1EwNDUjA0GAygrPzCpPbYokasxzs+60Do/lyTIgd7nRzudAzHnujIPr8
GOPIlPlOKT8HuL7xQEN6gmUtz33iDhK97zK7zOFEmvS8kYPwFAjQ03YKv+3T9b/D
brBZWy2Vx4Wuxf6mZBggKQfwHUuJxXDv79NenZarUtC5iFEhJ85ovwjW7yMkcflh
Ugkf1o/GIR5RKoNPttMXhKYZ4hTlLglMm1FgRR63pvYoy9Eq644a9x2mbGelO3Hn
GbkaFo0HxiKbFW1vplHzixYCyjc15pvtBxw/x26p8+lNthuxzaX5HaFMPGs10rRP
LwIDAQAB
-----END PUBLIC KEY-----';

        return new KeyChain(
            '1',
            $keySetName, //@TODO Important
            $publicKey
        );
    }
}
