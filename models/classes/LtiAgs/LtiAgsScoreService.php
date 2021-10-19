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

namespace oat\taoLti\models\classes\LtiAgs;

use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;

class LtiAgsScoreService implements LtiAgsScoreServiceInterface
{
    /** @var ScoreServiceInterface  */
    private $scoreServiceClient;

    /** @var ScoreFactoryInterface  */
    private $scoreFactory;

    public function __construct(ScoreServiceInterface $scoreServiceClient, ScoreFactoryInterface $scoreFactory)
    {
        $this->scoreServiceClient = $scoreServiceClient;
        $this->scoreFactory = $scoreFactory;
    }

    /**
     * @throws LtiAgsException
     */
    public function send(RegistrationInterface $registration, AgsClaim $ags, array $data): void
    {
        $score = $this->scoreFactory->create($data);

        try {
            $result = $this->scoreServiceClient ->publishScoreForClaim($registration, $score, $ags);

            if (false === $result) {
                throw new LtiException('Failed status has been received during AGS sending');
            }

        } catch (LtiExceptionInterface $e) {
            $exception = new LtiAgsException('AGS score send failed.', 1, $e);

            throw $exception
                ->setAgsClaim($ags)
                ->setRegistration($registration)
                ->setScore($score);
        }
    }
}
