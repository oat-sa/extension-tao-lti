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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\integration\models\classes\Tool\Validation;

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Message\Launch\Builder\PlatformOriginatingLaunchBuilder;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Result\LaunchValidationResultInterface;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\ResourceLinkClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Nonce\NonceRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcAuthenticator;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcInitiator;
use OAT\Library\Lti1p3Core\Tests\Traits\OidcTestingTrait;
use oat\oatbox\cache\CacheItem;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Tool\Validation\Lti1p3Validator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;

class Lti1p3ValidatorTest extends TestCase
{
    use OidcTestingTrait;

    /** @var RegistrationRepositoryInterface */
    private $registrationRepository;

    /** @var NonceRepositoryInterface */
    private $nonceRepository;

    /** @var RegistrationInterface */
    private $registration;

    /** @var PlatformOriginatingLaunchBuilder */
    private $builder;

    /** @var OidcInitiator */
    private $oidcInitiator;

    /** @var OidcAuthenticator */
    private $oidcAuthenticator;

    /** @var ToolLaunchValidator */
    private $subject;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once ROOT_PATH . 'vendor/oat-sa/lib-lti1p3-core/tests/Traits/OidcTestingTrait.php';
    }

    protected function setUp(): void
    {
        $this->registrationRepository = $this->createTestRegistrationRepository();
        $this->nonceRepository = $this->createTestNonceRepository();

        $publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAscIMHj3PYVhSYoj9+8ZO
/MzX06SQmqbCguT9OFlmsDSngV8Cxbgnb6U4Jrfz76gfX99Ohbdl8+qTz+bid7Mm
UVxMYf1nNs7l74TBVsFrKQLlEbtf+h4/CtA6NvKLE9Wbh0KvIL/1LzNLvb8LmTIe
PZ1n8IKq/983qHPua3fIVxOFW9iYzbUdKpPHNmvgrsSkyqrVq3cuJMW0ZSszRiVP
5BKev8YYt6SnJrVE5GYg6X32rVCpdrNIuluLF+uPBs/Ed0x33or0e590HwZxYPgQ
3/1SsKtfxvLlNydBgbi7RNjVw1fNju9dSfQr9Ximac02/7yiw5Kv9zWiDrk4Sib4
CwIDAQAB
-----END PUBLIC KEY-----';

        $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAscIMHj3PYVhSYoj9+8ZO/MzX06SQmqbCguT9OFlmsDSngV8C
xbgnb6U4Jrfz76gfX99Ohbdl8+qTz+bid7MmUVxMYf1nNs7l74TBVsFrKQLlEbtf
+h4/CtA6NvKLE9Wbh0KvIL/1LzNLvb8LmTIePZ1n8IKq/983qHPua3fIVxOFW9iY
zbUdKpPHNmvgrsSkyqrVq3cuJMW0ZSszRiVP5BKev8YYt6SnJrVE5GYg6X32rVCp
drNIuluLF+uPBs/Ed0x33or0e590HwZxYPgQ3/1SsKtfxvLlNydBgbi7RNjVw1fN
ju9dSfQr9Ximac02/7yiw5Kv9zWiDrk4Sib4CwIDAQABAoIBADTY1/l1rt3mADhD
Oh9MSddmnxPQ7RzNTy7THWVPTvQ780DHGm/l2/OZTyRTtDYf6ZP7M8EVUT4/E0rP
/axQmqe9pQfM6o6k3D9lXIWKY22B6tBmwJX/wAZa+bO0UBzJeL+x15cI+r/ZpD75
OV2GRO9UiL48WtJPbqCqNsvEhM8+A2qPBA7srH1yxn1Y0ESiHDyoNLdyrPMmnLcw
M1TBMg4UxiUqWn0q6+fKuY8S35hYsM5AbSkmOXP3A6+yr/UyYb9NvkVxYJ2UW9PI
Lf38BzI1QVy7eje1CoMpw3JRN2i/4Vu7QLuUDK2Me7guFKdAn+JZt2UWa0snz3cP
RWs0+6ECgYEA4m33BqcBszmT4BzTEZCpMAOv4bxQk7ljez0fd/ydu1Yd7tuajHoc
QmTBpPiHalObvlD/nzbQwEXc57M+OwmBYAD1Q2svOx8Tn4T1Zy4N1WYTG8/hEQBz
ZqmIzLui1XnnO9KC+oST25grUwFPh7lk3zZ8aoYPhIoAC7/pxxnjm9UCgYEAyPji
zECnYt77ipmUbPoFyqt82mWDx5f09hJGj6c63T+3JB6bE3kc2Zs8ZZz6UpTvFMtM
bD7k+A4cDEJhIGGNXkqEA7B8SB396TOv/43c3huEvAda9Jmf/1AFEEkmvtKtxsas
ZxsczdO4bvCQK8+BAMRXlYr0vO7t3cx8+b+1lF8CgYAexuuoz9J/VfgvojteS9dz
W0zw1fPt4GkRO0GnwYJ/EDmJWfgr1/03WRKpJc7iOPMWb1QPhBfjyps4MzjmNWiM
cBTmUQ9ebd7w89WXbL8cnn9CbIMfGHyXG7wod+iuM5+mlfqPqq2eT5Sz952jySNY
48MNh6NcVJWlAzT3hyFU8QKBgHEommMRgG5OSWoIAae+u6YbGujJwgKPUDGBptNa
AO3040TmKsEzL4hjPQWl9tiq3VdjBPvqCfiV0Tsh4RhfdT8DTAPbyo68vGwjW1TU
Zul0qy9IIPGa0pjqUH+UAMnvTEOhOA+yF2zZan6k2zif1O4+n2YnYJhFHBAIBNKH
HFGXAoGAAIP89G0qmsgpYFV3xkiOSw9NA5W8kFY4hLj1e+SbqaBELGxbI419nvYS
01OjK+G+5jbuGOidVcJ1SVn/2hKvvCfiTJovU9x8iIvr0ke61rsHGXfUJ3eWLnXa
uRQa1b83fSwj0MKYiZAHQ2xAInIWpK4bPyLOgRNKtUsNsT1HQQk=
-----END RSA PRIVATE KEY-----';

        $this->registration = $this->createTestRegistration(
            'registrationIdentifier',
            'registrationClientId',
            null,
            null,
            ['deploymentIdentifier'],
            $this->createTestKeyChain(
                'platformKeyChain',
                'keySetName',
                $publicKey,
                $privateKey
            ),
            $this->createTestKeyChain(
                'toolKeyChain',
                'keySetName',
                $publicKey,
                $privateKey
            )
        );

        $this->builder = new PlatformOriginatingLaunchBuilder();
        $this->oidcInitiator = new OidcInitiator($this->registrationRepository);
        $this->oidcAuthenticator = new OidcAuthenticator($this->registrationRepository, $this->createTestUserAuthenticator());


        $this->subject = new Lti1p3Validator();

        $mockRegistrationRepository = $this->getMockBuilder(Lti1p3RegistrationRepository::class)->getMock();
        $mockRegistrationRepository
            ->method('findByPlatformIssuer')
            ->willReturn($this->registration);


        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Lti1p3RegistrationRepository::SERVICE_ID => $mockRegistrationRepository,
                    ItemPoolSimpleCacheAdapter::class => $this->createArrayCache()
                ]
            )
        );
    }

    public function testItGetsValidatedPayload(): void
    {
        $message = $this->builder->buildPlatformOriginatingLaunch(
            $this->registration,
            LtiMessageInterface::LTI_MESSAGE_TYPE_RESOURCE_LINK_REQUEST,
            $this->registration->getTool()->getLaunchUrl(),
            'loginHint',
            null,
            ['http://purl.imsglobal.org/vocab/lis/v2/membership#Learner'],
            [
                new ResourceLinkClaim('identifier')
            ]
        );

        $messagePayload = $this->subject->getValidatedPayload($this->buildOidcFlowRequest($message));

        self::assertEquals('deploymentIdentifier', $messagePayload->getDeploymentId());
        self::assertEquals('1.3.0', $messagePayload->getVersion());
        self::assertEquals('userName', $messagePayload->getUserIdentity()->getName());
        self::assertEquals(new ResourceLinkClaim('identifier'), $messagePayload->getResourceLink());
    }

    /**
     * @dataProvider providerItControlRoles
     */
    public function testItControlsRoles(array $roles, string $expectedMessage): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage($expectedMessage);

        $message = $this->builder->buildPlatformOriginatingLaunch(
            $this->registration,
            LtiMessageInterface::LTI_MESSAGE_TYPE_RESOURCE_LINK_REQUEST,
            $this->registration->getTool()->getLaunchUrl(),
            'loginHint',
            null,
            $roles,
            [
                new ResourceLinkClaim('identifier')
            ]
        );

        $messagePayload = $this->subject->getValidatedPayload($this->buildOidcFlowRequest($message));
    }

    public function providerItControlRoles(): array
    {
        return [
            'No role provided' => [[], 'No valid IMS context role has been provided'],
            'Invalid role provided' => [
                ['http://purl.imsglobal.org/vocab/lis/v2/membership#Wrong'],
                'Role http://purl.imsglobal.org/vocab/lis/v2/membership#Wrong is invalid for type context'
            ]
        ];
    }

    private function buildOidcFlowRequest(LtiMessageInterface $message): ServerRequestInterface
    {
        return $this->createServerRequest(
            'GET',
            $this->performOidcFlow(
                $message,
                $this->createTestRegistrationRepository([$this->registration])
            )->toUrl()
        );
    }

    private function createArrayCache(): CacheItemPoolInterface
    {
        return new class() implements CacheItemPoolInterface {
            private $cache = [];

            public function getItem($key)
            {
                return $this->cache[$key] ?? new CacheItem($key);
            }

            public function getItems(array $keys = array())
            {
                return $this->cache;
            }

            public function hasItem($key)
            {
                return !empty($this->cache[$key]);
            }

            public function clear()
            {
                $this->cache = [];
            }

            public function deleteItem($key)
            {
                unset($this->cache[$key]);
            }

            public function deleteItems(array $keys)
            {
                $this->clear();
            }

            public function save(CacheItemInterface $item)
            {
                $this->cache[$item->getKey()] = $item;
            }

            public function saveDeferred(CacheItemInterface $item) {}

            public function commit() {}
        };
    }
}
