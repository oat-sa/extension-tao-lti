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

namespace oat\taoLti\models\classes\Security;

use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator as Lti1p3AccessTokenRequestValidator;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiProvider\InvalidLtiProviderException;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Service\AccessTokenRequestValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use tao_models_classes_UserException;

class AccessTokenRequestValidator extends ConfigurableService implements AccessTokenRequestValidatorInterface
{
    /** @var Lti1p3AccessTokenRequestValidator */
    private $validator;

    /** @var LtiProvider */
    private $ltiProvider;

    /** @var string */
    private $role;

    public function withLtiProvider(LtiProvider $ltiProvider): AccessTokenRequestValidatorInterface
    {
        $this->ltiProvider = $ltiProvider;

        return $this;
    }

    public function withRole(string $role): AccessTokenRequestValidatorInterface
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @throws InvalidLtiProviderException
     * @throws MissingScopeException
     * @throws tao_models_classes_UserException
     */
    public function validate(ServerRequestInterface $request): void
    {
        $result = $this->getAccessTokenRequestValidator()->validate($request);

        if ($this->role !== null && !in_array($this->role, $result->getScopes(), true)) {
            throw new MissingScopeException(sprintf('Scope %s is not allowed', $this->role));
        }

        if ($result->hasError() || $result->getRegistration() === null) {
            throw new tao_models_classes_UserException(
                sprintf('Access Token Validation failed. %s', $result->getError())
            );
        }

        if ($this->ltiProvider !== null) {
            $ltiProvider = $this->getLtiProviderService()->searchByToolClientId(
                $result->getRegistration()->getClientId()
            );

            if (!$this->isSameLtiProvider($ltiProvider)) {
                throw new InvalidLtiProviderException('Lti provider from registration is not matching delivery');
            }
        }
    }

    public function withValidator(Lti1p3AccessTokenRequestValidator $validator): void
    {
        $this->validator = $validator;
    }

    private function getAccessTokenRequestValidator(): Lti1p3AccessTokenRequestValidator
    {
        if (!$this->validator) {
            $this->validator = new Lti1p3AccessTokenRequestValidator($this->getRegistrationRepository());
        }

        return $this->validator;
    }

    private function getRegistrationRepository(): RegistrationRepositoryInterface
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getLtiProviderService(): LtiProviderService
    {
        return $this->getServiceLocator()->get(LtiProviderService::SERVICE_ID);
    }

    private function isSameLtiProvider(LtiProvider $ltiProvider): bool
    {
        return $this->ltiProvider->getId() === $ltiProvider->getId();
    }
}
