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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool\Validation;

use OAT\Library\Lti1p3Core\Exception\LtiException as Lti1p3Exception;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidatorInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Role\RoleInterface;
use oat\tao\model\service\InjectionAwareService;
use oat\taoLti\models\classes\LtiException;
use Psr\Http\Message\ServerRequestInterface;

class Lti1p3Validator extends InjectionAwareService
{
    public const SERVICE_ID = 'taoLti/Lti1p3Validator';

    /** @var ToolLaunchValidatorInterface */
    private $toolLaunchValidator;

    public function __construct(ToolLaunchValidatorInterface $toolLaunchValidator)
    {
        parent::__construct();

        $this->toolLaunchValidator = $toolLaunchValidator;
    }

    public function getValidatedPayload(ServerRequestInterface $request): LtiMessagePayloadInterface
    {
        try {
            $ltiMessagePayload = $this->validateRequest($request);

            if ($ltiMessagePayload === null) {
                throw new LtiException('No LTI message payload received.');
            }

            $this->validateRole($ltiMessagePayload);
        } catch (Lti1p3Exception $exception) {
            throw new LtiException($exception->getMessage());
        }

        return $ltiMessagePayload;
    }

    public function validateRequest(ServerRequestInterface $request): LtiMessagePayloadInterface
    {
        return $this->toolLaunchValidator->validatePlatformOriginatingLaunch($request)->getPayload();
    }

    public function validateRole(LtiMessagePayloadInterface $ltiMessagePayload): void
    {
        $roles = $ltiMessagePayload->getValidatedRoleCollection();

        if (!$roles->canFindBy(RoleInterface::TYPE_CONTEXT)) {
            throw new LtiException('No valid IMS context role has been provided.');
        }
    }
}
