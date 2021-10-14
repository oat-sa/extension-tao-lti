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

use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use oat\oatbox\service\ConfigurableService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class LtiAgsScoreService extends ConfigurableService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public const SERVICE_ID = 'taoLti/LtiAgsScoreService';

    public const OPTION_SCORE_SERVICE_CLIENT = 'score_service_client';
    public const OPTION_SCORE_FACTORY = 'score_factory';

    /**
     * @throws LtiAgsException
     */
    public function send(RegistrationInterface $registration, AgsClaim $ags, array $data): void
    {
        $scoreClient = $this->getOption(self::OPTION_SCORE_SERVICE_CLIENT) ?? new ScoreServiceClient();
        $scoreFactory = $this->getOption(self::OPTION_SCORE_FACTORY) ?? new ScoreFactory();

        if (!$scoreClient instanceof ScoreServiceInterface) {
            throw new LtiAgsException(
                sprintf('%s option should implement %s', self::OPTION_SCORE_SERVICE_CLIENT, ScoreServiceInterface::class)
            );
        }

        if (!$scoreFactory instanceof ScoreFactoryInterface) {
            throw new LtiAgsException(
                sprintf('%s option should implement %s', self::OPTION_SCORE_FACTORY, ScoreServiceInterface::class)
            );
        }

        $score = $scoreFactory->create($data);

        try {
            $result = $scoreClient->publishScoreForClaim($registration, $score, $ags);

            if (false === $result) {
                throw new LtiException('Failed status has benn received during AGS sending');
            }

        } catch (LtiExceptionInterface $e) {
            $exception = new LtiAgsException('AGS score send failed', 1, $e);

            throw $exception
                ->setAgsClaim($ags)
                ->setRegistration($registration)
                ->setScore($score);
        }
    }
}
