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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\platform\builder;

use OAT\Library\Lti1p3Core\Launch\Builder\LtiLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Link\ResourceLink\ResourceLink;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\taoLti\models\platform\service\LtiPlatformJwkProvider;
use oat\taoLti\models\platform\service\LtiPlatformJwksProvider;
use oat\taoLti\models\tool\launch\LtiLaunchInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\tool\service\LtiToolJwkProvider;
use oat\taoLtiConsumer\model\delivery\container\Lti1p1DeliveryLaunch;

class Lti1p3LaunchBuilder extends ConfigurableService implements LtiLaunchBuilderInterface
{
    /** @var LtiProvider */
    private $ltiProvider;

    /** @var User */
    private $user;

    /** @var array */
    private $roles;

    /** @var array */
    private $claims;

    public function withProvider(LtiProvider $ltiProvider): LtiLaunchBuilderInterface
    {
        $this->ltiProvider = $ltiProvider;

        return $this;
    }

    public function withUser(User $user): LtiLaunchBuilderInterface
    {
        $this->user = $user;

        return $this;
    }

    public function withRoles(array $roles): LtiLaunchBuilderInterface
    {
        $this->roles = $roles;

        return $this;
    }

    public function withClaims(array $claims): LtiLaunchBuilderInterface
    {
        $this->claims = $claims;

        return $this;
    }

    public function build(): LtiLaunchInterface
    {
        $platformKeyChain = $this->getPlatformKeyChain();

        $toolKeyChain = $this->getToolKeyChain($this->ltiProvider);
        $platform = $this->getPlatform();
        $tool = $this->getTool();

        $deploymentIds = ['1']; //@TODO Must come from Provider configuration

        // @TODO Must find registration from repository
        $registration = new Registration(
            'registrationIdentifier',
            'client_id', //@TODO Comes from provider config
            $platform,
            $tool,
            $deploymentIds,
            $platformKeyChain,
            $toolKeyChain,
            $this->getAudience() . '/taoLti/LtiPlatform/jwks'
        );

        $builder = new LtiLaunchRequestBuilder();

        if ($this->user) {
            $ltiLaunchRequest = $builder->buildUserResourceLinkLtiLaunchRequest(
                new ResourceLink('identifier'),
                $registration,
                $this->getUserIdentity(),
                null,
                $this->roles,
                $this->claims
            );
        }

        if (!$this->user) {
            $builder = new LtiLaunchRequestBuilder();
            $ltiLaunchRequest = $builder->buildResourceLinkLtiLaunchRequest(
                new ResourceLink('identifier'),
                $registration,
                null,
                $this->roles,
                $this->claims
            );
        }

        return new Lti1p1DeliveryLaunch($ltiLaunchRequest->getUrl(), $ltiLaunchRequest->getParameters());
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

    private function getUserIdentity()
    {
        // @TODO Get other user data
        return new UserIdentity($this->user->getIdentifier(), 'Test', 'test@test.com');
    }

    private function getPlatform(): Platform
    {
        // @TODO Get it from proper place...
        return new Platform(
            'tao',
            'tao',
            $this->getAudience()
        );
    }

    private function getAudience(): string
    {
        return rtrim(ROOT_URL, '/'); // @TODO GEt it from proper config
    }

    private function getTool(): Tool
    {
        // @TODO This should be build based on Provider config
        return new Tool(
            'local_demo',
            'local_demo',
            'http://localhost:8888/tool',
            'http://localhost:8888/lti1p3/oidc/login-initiation',
            'http://localhost:8888/tool/launch'
        );
    }

    private function getToolKeyChain(LtiProvider $ltiProvider): KeyChain
    {
        /** @var LtiToolJwkProvider $provider */
        $provider = $this->getServiceLocator()->get(LtiToolJwkProvider::class);

        $publicKey = $provider->getPublicKey($ltiProvider);

        return new KeyChain(
            '1', // @TODO This should be build based on Provider config
            'myToolKeySetName', // @TODO This should be build based on Provider config
            $publicKey
        );
    }
}
