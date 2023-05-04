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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\Platform\Service;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
// phpcs:disable Generic.Files.LineLength
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface as AccessTokenRequestValidationResultInterface;
// phpcs:enable Generic.Files.LineLength
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator as Lti1p3AccessTokenRequestValidator;
use oat\taoLti\models\classes\LtiProvider\InvalidLtiProviderException;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Security\AccessTokenRequestValidator;
use Psr\Http\Message\ServerRequestInterface;
use tao_models_classes_UserException;

class AccessTokenRequestValidatorTest extends TestCase
{
    /** @var AccessTokenRequestValidator */
    private $subject;

    /** @var Lti1p3AccessTokenRequestValidator|MockObject */
    private $validator;

    /** @var AccessTokenRequestValidationResultInterface|MockObject */
    private $validatorResult;

    /** @var MockObject|ServerRequestInterface  */
    private $request;

    public function setUp(): void
    {
        $this->validator = $this->createMock(Lti1p3AccessTokenRequestValidator::class);
        $this->validatorResult = $this->createMock(AccessTokenRequestValidationResultInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);

        $this->validatorResult
            ->method('getScopes')
            ->willReturn(['learner']);

        $this->validator
            ->method('validate')
            ->willReturn($this->validatorResult);

        $this->subject = new AccessTokenRequestValidator();
        $this->subject->withValidator($this->validator);
    }

    public function testResultHasErrors(): void
    {
        $this->expectException(tao_models_classes_UserException::class);
        $this->expectExceptionMessage('Access Token Validation failed. error');

        $this->validatorResult
            ->expects($this->once())
            ->method('hasError')
            ->willReturn(true);

        $this->validatorResult
            ->expects($this->once())
            ->method('getError')
            ->willReturn('error');

        $this->subject
            ->withRole('learner')
            ->validate($this->request);
    }

    public function testResultRegistrationEmpty(): void
    {
        $this->expectException(tao_models_classes_UserException::class);
        $this->expectExceptionMessage('Access Token Validation failed. ');

        $this->validatorResult
            ->expects($this->once())
            ->method('getError');

        $this->validatorResult
            ->expects($this->once())
            ->method('getRegistration')
            ->willReturn(null);

        $this->subject->validate($this->request);
    }

    public function testRegistrationClientNotMatchingDelivery(): void
    {
        $this->expectException(InvalidLtiProviderException::class);

        $registration = $this->createMock(RegistrationInterface::class);
        $ltiProviderService = $this->createMock(LtiProviderService::class);
        $requestLtiProvider = $this->createMock(LtiProvider::class);
        $ltiProvider = $this->createMock(LtiProvider::class);

        $ltiProviderService
            ->expects($this->once())
            ->method('searchByToolClientId')
            ->willReturn($requestLtiProvider);

        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    LtiProviderService::SERVICE_ID => $ltiProviderService,
                ]
            )
        );

        $this->validatorResult
            ->expects($this->exactly(2))
            ->method('getRegistration')
            ->willReturn($registration);

        $registration
            ->expects($this->once())
            ->method('getClientId')
            ->willReturn('client_id');


        $ltiProvider
            ->expects($this->once())
            ->method('getId')
            ->willReturn('id');

        $this->subject
            ->withRole('learner')
            ->withLtiProvider($ltiProvider)
            ->validate($this->request);
    }
}
