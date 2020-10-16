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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool\Factory;

use ErrorException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Launch\Builder\LtiResourceLinkLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Tool\LtiLaunchCommandInterface;

class Lti1p3LaunchRequestFactory extends ConfigurableService
{
    /** @var LtiResourceLinkLaunchRequestBuilder */
    private $ltiLaunchRequestBuilder;

    public function withLtiLaunchRequestBuilder(LtiResourceLinkLaunchRequestBuilder $ltiLaunchRequestBuilder): self
    {
        $this->ltiLaunchRequestBuilder = $ltiLaunchRequestBuilder;

        return $this;
    }

    /**
     * @throws ErrorException
     * @throws LtiExceptionInterface
     */
    public function create(LtiLaunchCommandInterface $command): LtiMessageInterface
    {
        $registration = $this->getRegistrationRepository()
            ->find($command->getLtiProvider()->getId());

        if (!$registration) {
            throw new ErrorException(
                sprintf(
                    'Registration for provider %s not found',
                    $command->getLtiProvider()->getId()
                )
            );
        }

        return $this->getLaunchRequestBuilder()->buildLtiResourceLinkLaunchRequest(
            new LtiResourceLink(
                $command->getResourceIdentifier(),
                [
                    'url' => $command->getLaunchUrl(),
                ]
            ),
            $registration,
            $command->getOpenIdLoginHint(),
            $registration->getDefaultDeploymentId(),
            $command->getRoles(),
            $command->getClaims()
        );
    }

    private function getLaunchRequestBuilder(): LtiResourceLinkLaunchRequestBuilder
    {
        return $this->ltiLaunchRequestBuilder ?? new LtiResourceLinkLaunchRequestBuilder();
    }

    private function getRegistrationRepository(): RegistrationRepositoryInterface
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::SERVICE_ID);
    }
}
