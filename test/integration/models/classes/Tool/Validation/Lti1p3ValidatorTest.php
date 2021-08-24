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

use Carbon\Carbon;
use Nyholm\Psr7\Factory\HttplugFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Message\Launch\Builder\PlatformOriginatingLaunchBuilder;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Result\LaunchValidationResultInterface;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\ResourceLinkClaim;
use OAT\Library\Lti1p3Core\Message\Payload\MessagePayloadInterface;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Platform\PlatformInterface;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Jwt\Builder\Builder;
use OAT\Library\Lti1p3Core\Security\Jwt\Parser\Parser;
use OAT\Library\Lti1p3Core\Security\Jwt\TokenInterface;
use OAT\Library\Lti1p3Core\Security\Jwt\Validator\Validator;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainFactory;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyInterface;
use OAT\Library\Lti1p3Core\Security\Nonce\Nonce;
use OAT\Library\Lti1p3Core\Security\Nonce\NonceInterface;
use OAT\Library\Lti1p3Core\Security\Nonce\NonceRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcAuthenticator;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcInitiator;
use OAT\Library\Lti1p3Core\Security\User\Result\UserAuthenticationResult;
use OAT\Library\Lti1p3Core\Security\User\Result\UserAuthenticationResultInterface;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticatorInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\OidcTestingTrait;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3Core\Tool\ToolInterface;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use OAT\Library\Lti1p3Core\Util\Generator\IdGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
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

    protected function setUp(): void
    {
        $this->registrationRepository = $this->createTestRegistrationRepository();
        $this->nonceRepository = $this->createTestNonceRepository();

        $this->registration = $this->createTestRegistration();

        $this->builder = new PlatformOriginatingLaunchBuilder();
        $this->oidcInitiator = new OidcInitiator($this->registrationRepository);
        $this->oidcAuthenticator = new OidcAuthenticator($this->registrationRepository, $this->createTestUserAuthenticator());

        $this->subject = new ToolLaunchValidator($this->registrationRepository, $this->nonceRepository);
    }
    public function testValidatePlatformOriginatingLaunchForLtiResourceLinkSuccess(): void
    {
        die;
        $message = $this->builder->buildPlatformOriginatingLaunch(
            $this->registration,
            LtiMessageInterface::LTI_MESSAGE_TYPE_RESOURCE_LINK_REQUEST,
            $this->registration->getTool()->getLaunchUrl(),
            'loginHint',
            null,
            [],
            [
                new ResourceLinkClaim('identifier')
            ]
        );

        $res = $this->buildOidcFlowRequest($message);

        var_dump($res);

        /*        $result = $this->subject->validatePlatformOriginatingLaunch($this->buildOidcFlowRequest($message));

                $this->assertInstanceOf(LaunchValidationResultInterface::class, $result);
                $this->assertFalse($result->hasError());

                $this->verifyJwt($result->getPayload()->getToken(), $this->registration->getPlatformKeyChain()->getPublicKey());
                $this->verifyJwt($result->getState()->getToken(), $this->registration->getToolKeyChain()->getPublicKey());

                $this->assertEquals(
                    [
                        'ID token kid header is provided',
                        'ID token validation success',
                        'ID token version claim is valid',
                        'ID token message_type claim is valid',
                        'ID token roles claim is valid',
                        'ID token user identifier (sub) claim is valid',
                        'ID token nonce claim is valid',
                        'ID token deployment_id claim valid for this registration',
                        'ID token message type claim LtiResourceLinkRequest requirements are valid',
                        'State validation success',
                    ],
                    $result->getSuccesses()
                );

                $this->assertEquals('identifier', $result->getPayload()->getResourceLink()->getIdentifier());*/
    }


    private function buildOidcFlowRequest(LtiMessageInterface $message): ServerRequestInterface
    {
        return $this->createServerRequest('GET', $this->performOidcFlow($message)->toUrl());
    }

    private function performOidcFlow(
        LtiMessageInterface $message,
        RegistrationRepositoryInterface $repository = null,
        UserAuthenticatorInterface $authenticator = null
    ): LtiMessageInterface {
        $repository = $repository ?? $this->createTestRegistrationRepository();
        $authenticator = $authenticator ?? $this->createTestUserAuthenticator();

        $oidcInitiator = new OidcInitiator($repository);
        $oidcAuthenticator = new OidcAuthenticator($repository, $authenticator);

        $oidcInitMessage = $oidcInitiator->initiate($this->createServerRequest('GET', $message->toUrl()));

        return $oidcAuthenticator->authenticate($this->createServerRequest('GET', $oidcInitMessage->toUrl()));
    }

    private function createServerRequest(
        string $method,
        string $uri,
        array $params = [],
        array $headers = []
    ): ServerRequestInterface {
        $serverRequest =  (new Psr17Factory())->createServerRequest($method, $uri);

        foreach ($headers as $headerName => $headerValue) {
            $serverRequest = $serverRequest->withAddedHeader($headerName, $headerValue);
        }

        $method = strtoupper($method);

        if ($method === 'GET') {
            return $serverRequest->withQueryParams($params);
        }

        if ($method === 'POST') {
            return $serverRequest->withParsedBody($params);
        }

        return $serverRequest;
    }

    private function createResponse($content = null, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        return (new HttplugFactory())->createResponse($statusCode, null, $headers, $content);
    }

    private function createTestRegistration(
        string $identifier = 'registrationIdentifier',
        string $clientId = 'registrationClientId',
        PlatformInterface $platform = null,
        ToolInterface $tool = null,
        array $deploymentIds = ['deploymentIdentifier'],
        KeyChainInterface $platformKeyChain = null,
        KeyChainInterface $toolKeyChain = null,
        string $platformJwksUrl = null,
        string $toolJwksUrl = null
    ): Registration {
        return new Registration(
            $identifier,
            $clientId,
            $platform ?? $this->createTestPlatform(),
            $tool ?? $this->createTestTool(),
            $deploymentIds,
            $platformKeyChain ?? $this->createTestKeyChain('platformKeyChain'),
            $toolKeyChain ?? $this->createTestKeyChain('toolKeyChain'),
            $platformJwksUrl,
            $toolJwksUrl
        );
    }

    private function createTestRegistrationRepository(array $registrations = []): RegistrationRepositoryInterface
    {
        $registrations = !empty($registrations)
            ? $registrations
            : [$this->createTestRegistration()];

        return new class ($registrations) implements RegistrationRepositoryInterface
        {
            /** @var RegistrationInterface[] */
            private $registrations;

            /** @param RegistrationInterface[] $registrations */
            public function __construct(array $registrations)
            {
                foreach ($registrations as $registration) {
                    $this->registrations[$registration->getIdentifier()] = $registration;
                }
            }

            public function find(string $identifier): ?RegistrationInterface
            {
                return $this->registrations[$identifier] ?? null;
            }

            public function findAll(): array
            {
                return $this->registrations;
            }

            public function findByClientId(string $clientId): ?RegistrationInterface
            {
                foreach ($this->registrations as $registration) {
                    if ($registration->getClientId() === $clientId) {
                        return $registration;
                    }
                }

                return null;
            }

            public function findByPlatformIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
            {
                foreach ($this->registrations as $registration) {
                    if ($registration->getPlatform()->getAudience() === $issuer) {
                        if (null !== $clientId) {
                            if ($registration->getClientId() === $clientId) {
                                return $registration;
                            }
                        } else {
                            return $registration;
                        }
                    }
                }

                return null;
            }

            public function findByToolIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
            {
                foreach ($this->registrations as $registration) {
                    if ($registration->getTool()->getAudience() === $issuer) {
                        if (null !== $clientId) {
                            if ($registration->getClientId() === $clientId) {
                                return $registration;
                            }
                        } else {
                            return $registration;
                        }
                    }
                }

                return null;
            }
        };
    }

    private function createTestKeyChain(
        string $identifier = 'keyChainIdentifier',
        string $keySetName = 'keySetName',
        string $publicKey = null,
        string $privateKey = null,
        string $privateKeyPassPhrase = null,
        string $algorithm = KeyInterface::ALG_RS256
    ): KeyChainInterface {
        return (new KeyChainFactory)->create(
            $identifier,
            $keySetName,
            $publicKey ?? '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAscIMHj3PYVhSYoj9+8ZO
/MzX06SQmqbCguT9OFlmsDSngV8Cxbgnb6U4Jrfz76gfX99Ohbdl8+qTz+bid7Mm
UVxMYf1nNs7l74TBVsFrKQLlEbtf+h4/CtA6NvKLE9Wbh0KvIL/1LzNLvb8LmTIe
PZ1n8IKq/983qHPua3fIVxOFW9iYzbUdKpPHNmvgrsSkyqrVq3cuJMW0ZSszRiVP
5BKev8YYt6SnJrVE5GYg6X32rVCpdrNIuluLF+uPBs/Ed0x33or0e590HwZxYPgQ
3/1SsKtfxvLlNydBgbi7RNjVw1fNju9dSfQr9Ximac02/7yiw5Kv9zWiDrk4Sib4
CwIDAQAB
-----END PUBLIC KEY-----',
            $privateKey ?? '-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----
',
            $privateKeyPassPhrase,
            $algorithm
        );
    }

    private function buildJwt(
        array $headers = [],
        array $claims = [],
        KeyInterface $key = null
    ): TokenInterface {
        return (new Builder(null, $this->createTestIdGenerator()))->build(
            $headers,
            $claims,
            $key ?? $this->createTestKeyChain()->getPrivateKey()
        );
    }

    private function parseJwt(string $tokenString): TokenInterface
    {
        return (new Parser())->parse($tokenString);
    }

    private function verifyJwt(TokenInterface $token, KeyInterface $key): bool
    {
        return (new Validator())->validate($token, $key);
    }

    private function createTestClientAssertion(RegistrationInterface $registration): string
    {
        $assertion = $this->buildJwt(
            [
                MessagePayloadInterface::HEADER_KID => $registration->getToolKeyChain()->getIdentifier()
            ],
            [
                MessagePayloadInterface::CLAIM_ISS => $registration->getTool()->getAudience(),
                MessagePayloadInterface::CLAIM_SUB => $registration->getClientId(),
                MessagePayloadInterface::CLAIM_AUD => [
                    $registration->getPlatform()->getAudience(),
                    $registration->getPlatform()->getOAuth2AccessTokenUrl(),
                ]
            ],
            $registration->getToolKeyChain()->getPrivateKey()
        );

        return $assertion->toString();
    }

    private function createTestClientAccessToken(RegistrationInterface $registration, array $scopes = []): string
    {
        $accessToken = $this->buildJwt(
            [],
            [
                MessagePayloadInterface::CLAIM_AUD => $registration->getClientId(),
                'scopes' => $scopes
            ],
            $registration->getPlatformKeyChain()->getPrivateKey()
        );

        return $accessToken->toString();
    }

    private function createTestIdGenerator(string $generatedId = null): IdGeneratorInterface
    {
        return new class ($generatedId) implements IdGeneratorInterface
        {
            /** @var string */
            private $generatedId;

            public function __construct(string $generatedId = null)
            {
                $this->generatedId = $generatedId ?? 'id';
            }

            public function generate(): string
            {
                return $this->generatedId;
            }
        };
    }

    private function createTestUserAuthenticator(
        bool $withAuthenticationSuccess = true,
        bool $withAnonymous = false
    ): UserAuthenticatorInterface {
        return new class ($withAuthenticationSuccess, $withAnonymous) implements UserAuthenticatorInterface
        {
            /** @var bool */
            private $withAuthenticationSuccess;

            /** @var bool */
            private $withAnonymous;

            public function __construct(bool $withAuthenticationSuccess, bool $withAnonymous)
            {
                $this->withAuthenticationSuccess = $withAuthenticationSuccess;
                $this->withAnonymous = $withAnonymous;
            }

            public function authenticate(
                RegistrationInterface $registration,
                string $loginHint
            ): UserAuthenticationResultInterface {
                return new UserAuthenticationResult(
                    $this->withAuthenticationSuccess,
                    $this->withAnonymous ? null : $this->createTestUserIdentity()
                );
            }

            private function createTestUserIdentity(
                string $identifier = 'userIdentifier',
                string $name = 'userName',
                string $email = 'userEmail',
                string $givenName = 'userGivenName',
                string $familyName = 'userFamilyName',
                string $middleName = 'userMiddleName',
                string $locale = 'userLocale',
                string $picture = 'userPicture',
                array $additionalProperties = []
            ): UserIdentity {
                return new UserIdentity($identifier, $name, $email, $givenName, $familyName, $middleName, $locale, $picture, $additionalProperties);
            }
        };
    }

    private function createTestNonceRepository(array $nonces = [], bool $withAutomaticFind = false): NonceRepositoryInterface
    {
        $nonces = !empty($nonces) ? $nonces : [
            new Nonce('existing'),
            new Nonce('expired', Carbon::now()->subDay()),
        ];

        return new class ($nonces, $withAutomaticFind) implements NonceRepositoryInterface
        {
            /** @var NonceInterface[] */
            private $nonces;

            /** @var bool */
            private $withAutomaticFind;

            public function __construct(array $nonces, bool $withAutomaticFind)
            {
                foreach ($nonces as $nonce) {
                    $this->add($nonce);
                }

                $this->withAutomaticFind = $withAutomaticFind;
            }

            public function add(NonceInterface $nonce): self
            {
                $this->nonces[$nonce->getValue()] = $nonce;

                return $this;
            }

            public function find(string $value): ?NonceInterface
            {
                if ($this->withAutomaticFind) {
                    return current($this->nonces);
                }

                return $this->nonces[$value] ?? null;
            }

            public function save(NonceInterface $nonce): void
            {
                return;
            }
        };
    }

    private function createTestPlatform(
        string $identifier = 'platformIdentifier',
        string $name = 'platformName',
        string $audience = 'platformAudience',
        string $oidcAuthenticationUrl = 'http://platform.com/oidc-auth',
        string $oauth2AccessTokenUrl = 'http://platform.com/access-token'
    ): Platform {
        return new Platform($identifier, $name, $audience, $oidcAuthenticationUrl, $oauth2AccessTokenUrl);
    }

    private function createTestTool(
        string $identifier = 'toolIdentifier',
        string $name = 'toolName',
        string $audience = 'toolAudience',
        string $oidcInitiationUrl = 'http://tool.com/oidc-init',
        string $launchUrl = 'http://tool.com/launch',
        string $deepLinkingUrl = 'http://tool.com/deep-launch'
    ): Tool {
        return new Tool($identifier, $name, $audience, $oidcInitiationUrl, $launchUrl, $deepLinkingUrl);
    }
}