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
    public function create(LtiLaunchCommandInterface $command): LaunchRequestInterface
    {
        #
        # @TODO We need to build the identifier as a combination of ids and so on, so we can build proper launch URL
        #
        $registration = $this->getRegistrationRepository()->find('TODO - Not mapped in the provider yet');

        if ($command->isAnonymousLaunch()) {
            $builder = new LtiLaunchRequestBuilder();

            return $builder->buildResourceLinkLtiLaunchRequest(
                new ResourceLink('identifier'),
                $registration,
                $command->getLtiProvider()->getDeploymentId(),
                $command->getRoles(),
                $command->getClaims()
            );
        }

        if ($command->isOpenIdConnectLaunch()) {
            $builder = new OidcLaunchRequestBuilder();

            return $builder->buildResourceLinkOidcLaunchRequest(
                new ResourceLink('identifier'),
                $registration,
                $command->getOpenIdLoginHint(),
                $command->getLtiProvider()->getDeploymentId(),
                $command->getRoles(),
                $command->getClaims()
            );
        }

        $builder = new LtiLaunchRequestBuilder();

        return $builder->buildUserResourceLinkLtiLaunchRequest(
            new ResourceLink('identifier'),
            $registration,
            $this->getUserIdentity($command),
            $command->getLtiProvider()->getDeploymentId(),
            $command->getRoles(),
            $command->getClaims()
        );
    }

    private function getRegistrationRepository(): RegistrationRepositoryInterface
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getUserIdentity(LtiLaunchCommandInterface $command): UserIdentityInterface
    {
        //@TODO Get proper user identity...
        return new UserIdentity(
            $command->getUser()->getIdentifier(),
            'Test',
            'test@test.com'
        );
    }
}
