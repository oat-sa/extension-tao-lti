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
 * Copyright (c) 2021-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool\Validation;

use OAT\Library\Lti1p3Core\Exception\LtiException as Lti1p3Exception;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\AbstractLaunchValidator;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Role\RoleInterface;
use OAT\Library\Lti1p3Core\Security\Nonce\NonceRepository;
use oat\taoLti\models\classes\LtiException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;

class Lti1p3Validator
{
    private ?AbstractLaunchValidator $tooLaunchValidator;

    private RegistrationRepositoryInterface $registrationRepository;
    private CacheItemPoolInterface $cacheAdapter;

    public function __construct(
        RegistrationRepositoryInterface $registrationRepository,
        CacheItemPoolInterface $cacheAdapter,
        AbstractLaunchValidator $tooLaunchValidator = null,
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->cacheAdapter = $cacheAdapter;
        $this->tooLaunchValidator = $tooLaunchValidator;
    }

    /**
     * @throws LtiException
     */
    public function getValidatedPayload(ServerRequestInterface $request): LtiMessagePayloadInterface
    {
        try {
            $ltiMessagePayload = $this->validateRequest($request);

            $this->validateRole($ltiMessagePayload);
        } catch (Lti1p3Exception $exception) {
            throw new LtiException($exception->getMessage());
        }

        return $ltiMessagePayload;
    }

    /**
     * @throws Lti1p3Exception
     */
    public function validateRequest(ServerRequestInterface $request): LtiMessagePayloadInterface
    {
        $validator = $this->getToolLaunchValidator();

        $result = $validator->validatePlatformOriginatingLaunch($request);

        if ($result->hasError()) {
            throw new Lti1p3Exception($result->getError());
        }

        $ltiMessagePayload = $result->getPayload();

        if ($ltiMessagePayload === null) {
            throw new Lti1p3Exception('No LTI message payload received.');
        }

        return $ltiMessagePayload;
    }

    /**
     * @throws LtiException
     */
    public function validateRole(LtiMessagePayloadInterface $ltiMessagePayload): void
    {
        $roles = $ltiMessagePayload->getValidatedRoleCollection();

        if (!$roles->canFindBy(RoleInterface::TYPE_CONTEXT)) {
            throw new LtiException('No valid IMS context role has been provided.');
        }
    }

    private function getRegistrationRepository(): RegistrationRepositoryInterface
    {
        return $this->registrationRepository;
    }

    /**
     * @return ToolLaunchValidator
     */
    public function getToolLaunchValidator(): AbstractLaunchValidator
    {
        return $this->tooLaunchValidator ?? new ToolLaunchValidator(
            $this->getRegistrationRepository(),
            new NonceRepository($this->cacheAdapter)
        );
    }
}
