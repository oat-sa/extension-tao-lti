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

namespace oat\taoLti\models\classes;

use common_exception_NoImplementation;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use oat\oatbox\service\ConfigurableService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class LtiAgsScoreService extends ConfigurableService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const SERVICE_ID = 'taoLti/LtiAgsScoreService';

    const OPTION_SCORE_SERVICE_CLIENT = 'score_service_client';
    const OPTION_SCORE_FACTORY = 'score_factory';

    /**
     * @throws common_exception_NoImplementation
     */
    public function send(RegistrationInterface $registration, AgsClaim $ags, array $data): bool
    {
        $scoreClient = $this->getOption(self::OPTION_SCORE_SERVICE_CLIENT) ?? new ScoreServiceClient();
        $scoreFactory = $this->getOption(self::OPTION_SCORE_FACTORY) ?? new ScoreFactory();

        if (!$scoreClient instanceof ScoreServiceInterface) {
            throw new common_exception_NoImplementation(sprintf('%s option should implement ScoreServiceInterface', self::OPTION_SCORE_SERVICE_CLIENT));
        }

        if (!$scoreFactory instanceof ScoreFactoryInterface) {
            throw new common_exception_NoImplementation(sprintf('%s option should implement ScoreFactoryInterface', self::OPTION_SCORE_FACTORY));
        }

        return $scoreClient->publishScoreForClaim(
            $registration,
            $scoreFactory->create($data),
            $ags
        );
    }
}
