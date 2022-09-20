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
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use oat\taoLti\models\classes\LtiAgs\LtiAgsException;
use oat\taoLti\models\classes\LtiAgs\LtiAgsScoreService;

class LtiAgsScoreServiceTest extends TestCase
{
    public function testItSendsAgsClaim(): void
    {
        $scoreServerClient = $this->createMock(ScoreServiceClient::class);
        $scoreFactory = $this->createMock(ScoreFactoryInterface::class);
        $registration = $this->createMock(RegistrationInterface::class);
        $agsClaim = $this->createMock(AgsClaim::class);

        $ltiAgsService = new LtiAgsScoreService($scoreServerClient, $scoreFactory);

        $data = (new ScoreFactory())->create(['userId' => '1']);

        $scoreFactory
            ->method('create')
            ->with(['userId' => '1'])
            ->willReturn($data);

        $scoreServerClient
            ->expects($this->once())
            ->method('publishScoreForClaim')
            ->with($registration, $data, $agsClaim)
            ->willReturn(true);

        $ltiAgsService->send($registration, $agsClaim, ['userId' => '1']);
    }

    public function testItThrowsWhenPublishMethodReturnsFalse(): void
    {
        $scoreServerClient = $this->createMock(ScoreServiceClient::class);
        $scoreFactory = $this->createMock(ScoreFactoryInterface::class);
        $registration = $this->createMock(RegistrationInterface::class);
        $agsClaim = $this->createMock(AgsClaim::class);

        $ltiAgsService = new LtiAgsScoreService($scoreServerClient, $scoreFactory);

        $data = (new ScoreFactory())->create(['userId' => '1']);

        $scoreFactory
            ->method('create')
            ->with(['userId' => '1'])
            ->willReturn($data);

        $scoreServerClient
            ->expects($this->once())
            ->method('publishScoreForClaim')
            ->with($registration, $data, $agsClaim)
            ->willReturn(false);

        $this->expectException(LtiAgsException::class);
        $this->expectExceptionMessage('AGS score send failed. Failed status has been received during AGS sending');

        $ltiAgsService->send($registration, $agsClaim, ['userId' => '1']);
    }
}
