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

use oat\generis\model\GenerisRdf;
use OAT\Library\Lti1p3Core\Launch\Builder\LtiLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Launch\Builder\OidcLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Launch\LaunchRequestInterface;
use OAT\Library\Lti1p3Core\Link\ResourceLink\ResourceLink;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use OAT\Library\Lti1p3Core\User\UserIdentityInterface;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Tool\LtiLaunchCommandInterface;

class Lti1p3LaunchRequestFactory extends ConfigurableService
{
    /** @var LtiLaunchRequestBuilder */
    private $ltiLaunchRequestBuilder;

    /** @var OidcLaunchRequestBuilder */
    private $oidcLaunchRequestBuilder;

    public function withLtiLaunchRequestBuilder(LtiLaunchRequestBuilder $ltiLaunchRequestBuilder): self
    {
        $this->ltiLaunchRequestBuilder = $ltiLaunchRequestBuilder;

        return $this;
    }

    public function withOidcLaunchRequestBuilder(OidcLaunchRequestBuilder $oidcLaunchRequestBuilder): self
    {
        $this->oidcLaunchRequestBuilder = $oidcLaunchRequestBuilder;

        return $this;
    }

    public function create(LtiLaunchCommandInterface $command): LaunchRequestInterface
    {
        $registration = $this->getRegistrationRepository()
            ->find($command->getLtiProvider()->getId());

        if ($command->isAnonymousLaunch()) {
            return $this->getLtiLaunchRequestBuilder()->buildResourceLinkLtiLaunchRequest(
                new ResourceLink($command->getResourceIdentifier(), $command->getLaunchUrl()),
                $registration,
                $registration->getDefaultDeploymentId(),
                $command->getRoles(),
                $command->getClaims()
            );
        }

        if ($command->isOpenIdConnectLaunch()) {
            return $this->getOidcLaunchRequestBuilder()->buildResourceLinkOidcLaunchRequest(
                new ResourceLink($command->getResourceIdentifier(), $command->getLaunchUrl()),
                $registration,
                $command->getOpenIdLoginHint(),
                $registration->getDefaultDeploymentId(),
                $command->getRoles(),
                $command->getClaims()
            );
        }

        return $this->getLtiLaunchRequestBuilder()->buildUserResourceLinkLtiLaunchRequest(
            new ResourceLink($command->getResourceIdentifier(), $command->getLaunchUrl()),
            $registration,
            $this->getUserIdentity($command),
            $registration->getDefaultDeploymentId(),
            $command->getRoles(),
            $command->getClaims()
        );
    }

    private function getLtiLaunchRequestBuilder(): LtiLaunchRequestBuilder
    {
        return $this->ltiLaunchRequestBuilder ?? new LtiLaunchRequestBuilder();
    }

    private function getOidcLaunchRequestBuilder(): OidcLaunchRequestBuilder
    {
        return $this->oidcLaunchRequestBuilder ?? new OidcLaunchRequestBuilder();
    }

    private function getRegistrationRepository(): RegistrationRepositoryInterface
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getUserIdentity(LtiLaunchCommandInterface $command): UserIdentityInterface
    {
        $user = $command->getUser();

        $fullName = (string)($user->getPropertyValues(GenerisRdf::PROPERTY_USER_FIRSTNAME)[0] ?? null)
            . ' ' . (string)($user->getPropertyValues(GenerisRdf::PROPERTY_USER_LASTNAME)[0] ?? null);

        $email = (string)($user->getPropertyValues(GenerisRdf::PROPERTY_USER_MAIL)[0] ?? null);

        return new UserIdentity(
            $user->getIdentifier(),
            $fullName,
            $email
        );
    }
}
