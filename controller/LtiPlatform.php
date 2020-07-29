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
use oat\tao\model\controller\SignedFormInstance;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\Security\Business\Contract\SecretKeyServiceInterface;
use oat\taoLti\models\classes\Security\Business\Domain\Exception\SecretKeyGenerationException;
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

class LtiPlatform extends tao_actions_SaSModule
{
    public function jwks(): void
    {
        /** @var LtiPlatformJwksProvider $provider */
        $provider = $this->getServiceLocator()
            ->get(LtiPlatformJwksProvider::class);

        $this->getPsrResponse()
            ->getBody()
            ->write(json_encode($provider->getKeySet()));
    }

    public function oidc_auth(): void
    {
        $platformkeyChain = $this->getKeyChain();
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
        /*
        TOOL: https://lti-ri.imsglobal.org/lti/tools/1149
        LAUNCH URL: https://lti-ri.imsglobal.org/lti/tools/1149/launches
        */

        $platformkeyChain = $this->getKeyChain();
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

        $user = new UserIdentity('gabriel', 'gabriel', 'gabriel@gabriel.com');

        $builder = new LtiLaunchRequestBuilder();

        $ltiLaunchRequest = $builder->buildUserResourceLinkLtiLaunchRequest(
            new ResourceLink('identifier'),
            $registration, // $this->repository->find('local'),
            $user,
            null,
            [
                'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner' // role
            ],
            [
                new ContextClaim('contextId'),  // LTI claim representing the context
                'myCustomClaim' => 'myCustomValue' // custom claim
            ]
        );

//        $ltiLaunchRequest = $builder->buildResourceLinkLtiLaunchRequest(
//            new ResourceLink('identifier'),
//            $registration, // $this->repository->find('local'),
//            null,
//            [
//                'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner' // role
//            ],
//            [
//                new ContextClaim('contextId'),  // LTI claim representing the context
//                'myCustomClaim' => 'myCustomValue' // custom claim
//            ]
//        );

        $this->getPsrResponse()->getBody()->write($ltiLaunchRequest->toHtmlLink('Click me!!', ['target' => '_blank']));
    }

    public function oauth(): void
    {
    }

    private function getKeyChain(): KeyChain
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
