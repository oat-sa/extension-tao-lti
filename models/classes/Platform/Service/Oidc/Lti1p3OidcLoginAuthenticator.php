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

namespace oat\taoLti\models\classes\Platform\Service\Oidc;

use OAT\Library\Lti1p3Core\Security\Oidc\OidcAuthenticator;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Lti1p3OidcLoginAuthenticator extends ConfigurableService implements OidcLoginAuthenticatorInterface
{
    /** @var OidcAuthenticator */
    private $loginAuthenticator;

    public function withLoginAuthenticator(OidcAuthenticator $loginAuthenticator): void
    {
        $this->loginAuthenticator = $loginAuthenticator;
    }

    public function authenticate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $launchRequest = $this->getExternalOidcLoginAuthenticator()
            ->authenticate($request);

        $response->getBody()
            ->write($launchRequest->toHtmlRedirectForm());

        return $response;
    }

    private function getExternalOidcLoginAuthenticator(): OidcAuthenticator
    {
        if (!$this->loginAuthenticator) {
            $this->loginAuthenticator = new OidcAuthenticator(
                $this->getRegistrationRepository(),
                $this->getUserAuthenticator()
            );
        }

        return $this->loginAuthenticator;
    }

    private function getRegistrationRepository(): Lti1p3RegistrationRepository
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::SERVICE_ID);
    }

    private function getUserAuthenticator(): Lti1p3UserAuthenticator
    {
        return $this->getServiceLocator()->get(Lti1p3UserAuthenticator::class);
    }
}
