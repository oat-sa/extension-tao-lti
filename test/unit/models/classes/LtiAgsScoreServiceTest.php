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

namespace oat\taoLti\models\classes;

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use stdClass;

class LtiAgsScoreServiceTest extends TestCase
{
    public function testItSendsAgsClaim(): void
    {
        $scoreServerClient = $this->createMock(ScoreServiceClient::class);
        $scoreFactory = $this->createMock(ScoreFactory::class);
        $registration = $this->createMock(RegistrationInterface::class);
        $agsClaim = $this->createMock(AgsClaim::class);

        $ltiAgsService = new LtiAgsScoreService(
            [
                LtiAgsScoreService::OPTION_SCORE_SERVICE_CLIENT => $scoreServerClient,
                LtiAgsScoreService::OPTION_SCORE_FACTORY => $scoreFactory,
            ]
        );

        $data = (new ScoreFactory())->create(['userId' => '1']);

        $scoreFactory
            ->method('create')
            ->with(['userId' => '1'])
            ->willReturn($data);

        $scoreServerClient
            ->expects($this->once())
            ->method('publishScoreForClaim')
            ->with($registration, $data, $agsClaim);

        $ltiAgsService->send($registration, $agsClaim, ['userId' => '1']);
    }

    public function testItThrowsWhenServiceClientOptionIsInvalid(): void
    {
            $ltiAgsService = new LtiAgsScoreService(
                [
                    LtiAgsScoreService::OPTION_SCORE_SERVICE_CLIENT => new stdClass(),
                    LtiAgsScoreService::OPTION_SCORE_FACTORY => new ScoreFactory(),
                ]
            );

            $this->expectException(LtiAgsException::class);
            $this->expectExceptionMessage('score_service_client option should implement ScoreServiceInterface');

            $ltiAgsService->send(
                $this->createMock(RegistrationInterface::class),
                $this->createMock(AgsClaim::class),
                []
            );
    }

    public function testItThrowsWhenScoreFactoryOptionIsInvalid(): void
    {
        $ltiAgsService = new LtiAgsScoreService(
            [
                LtiAgsScoreService::OPTION_SCORE_SERVICE_CLIENT => new ScoreServiceClient(),
                LtiAgsScoreService::OPTION_SCORE_FACTORY => new stdClass(),
            ]
        );

        $this->expectException(LtiAgsException::class);
        $this->expectExceptionMessage('score_factory option should implement ScoreFactoryInterface');

        $ltiAgsService->send(
            $this->createMock(RegistrationInterface::class),
            $this->createMock(AgsClaim::class),
            []
        );
    }
}
