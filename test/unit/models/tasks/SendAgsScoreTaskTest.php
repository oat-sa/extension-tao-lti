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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use oat\oatbox\reporting\ReportInterface;
use oat\taoLti\models\classes\LtiAgs\LtiAgsException;
use oat\taoLti\models\classes\LtiAgs\LtiAgsScoreService;
use oat\taoLti\models\tasks\SendAgsScoreTask;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;

class SendAgsScoreTaskTest extends TestCase
{
    /** @var SendAgsScoreTask $subject */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new SendAgsScoreTask();
    }

    public function testItReturnsSuccessReport(): void
    {
        $parameters = $this->getValidParameters();

        $this->setupServiceLocator($parameters, false);

        $report = $this->subject->__invoke($parameters);

        $this->assertEquals(ReportInterface::TYPE_SUCCESS, $report->getType());
        $this->assertEquals('AGS score has been sent successfully', $report->getMessage());
    }

    public function testItReturnsErrorReportIfSendReturnsFalsyResult(): void
    {
        $parameters = $this->getValidParameters();

        $this->setupServiceLocator($parameters, true);

        $report = $this->subject->__invoke($parameters);

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals('exception error message', $report->getMessage());
    }

    public function testItReturnsErrorReportIfExceptionWasThrownOnSend(): void
    {
        $parameters = $this->getValidParameters();

        $this->setupServiceLocator($parameters, true);

        $report = $this->subject->__invoke($parameters);

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals('exception error message', $report->getMessage());
    }

    public function testItReturnsErrorReportIfRegistrationNotFound(): void
    {
        $registrationRepository = $this->createMock(RegistrationRepositoryInterface::class);
        $registrationRepository
            ->method('find')
            ->with('invalid')
            ->willReturn(null);

        $serviceLocatorMock = $this->getServiceLocatorMock(
            [
                Lti1p3RegistrationRepository::SERVICE_ID => $registrationRepository
            ]
        );
        $this->subject->setServiceLocator($serviceLocatorMock);

        $report = $this->subject->__invoke(
            array_merge(
                $this->getValidParameters(),
                ['registrationId' => 'invalid']
            )
        );

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals('Registration with identifier "invalid" not found', $report->getMessage());
    }

    /**
     * @dataProvider getInvalidParameters
     */
    public function testReturnsErrorReportWhenInvalidParametersAreProvided(array $data, string $errorMessage): void
    {
        $report = $this->subject->__invoke($data);

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals($errorMessage, $report->getMessage());
    }

    public function getInvalidParameters(): array
    {
        return [
            [
                [
                    'registrationId' => 1, // invalid
                    'agsClaim' => [],
                    'data' => [],
                ],
                'Parameter "registrationId" must be a string'
            ],
            [
                [
                    'registrationId' => 'valid',
                    'agsClaim' => null, // invalid
                    'data' => [],
                ],
                'Parameter "agsClaim" must be an array and include "scope" as an array'
            ],
            [
                [
                    'registrationId' => 'valid',
                    'agsClaim' => [], // scope missing
                    'data' => [],
                ],
                'Parameter "agsClaim" must be an array and include "scope" as an array'
            ],
            [
                [
                    'registrationId' => 'valid',
                    'agsClaim' => [
                        'scope' => [
                            "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem",
                            "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
                            "https://purl.imsglobal.org/spec/lti-ags/scope/score"
                        ]
                    ],
                    'data' => 1, // invalid
                ],
                'Parameter "data" must be an array'
            ]
        ];
    }

    private function getValidParameters(): array
    {
        return [
            'registrationId' => 'id',
            'agsClaim' => [
                'scope' => [
                    "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem",
                    "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
                    "https://purl.imsglobal.org/spec/lti-ags/scope/score"
                ]
            ],
            'data' => [
                'userId' => 'anonymous'
            ]
        ];
    }

    private function setupServiceLocator(array $parameters, bool $throwException): void
    {
        $registration = $this->createMock(RegistrationInterface::class);

        $registrationRepository = $this->createMock(RegistrationRepositoryInterface::class);
        $registrationRepository
            ->method('find')
            ->with('id')
            ->willReturn($registration);

        $ltiAgsScoreService = $this->createMock(LtiAgsScoreService::class);

        if ($throwException) {
            $ltiAgsScoreService
                ->method('send')
                ->with($registration, AgsClaim::denormalize($parameters['agsClaim']), $parameters['data'])
                ->willThrowException(new LtiAgsException('exception error message'));
        } else {
            $ltiAgsScoreService
                ->method('send')
                ->with($registration, AgsClaim::denormalize($parameters['agsClaim']), $parameters['data']);
        }

        $serviceLocatorMock = $this->getServiceLocatorMock(
            [
                Lti1p3RegistrationRepository::SERVICE_ID => $registrationRepository,
                LtiAgsScoreService::class => $ltiAgsScoreService
            ]
        );
        $this->subject->setServiceLocator($serviceLocatorMock);
    }
}