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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\controller;

use ActionEnforcingException;
use common_exception_Error;
use core_kernel_classes_Resource;
use helpers_Random;
use InterruptedActionException;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use oat\taoLti\models\classes\LtiService;
use oat\taoLti\models\classes\Tool\Exception\WrongLtiRolesException;
use oat\taoLti\models\classes\Tool\Service\AuthoringLtiRoleService;
use oat\taoLti\models\classes\Tool\Validation\Lti1p3Validator;
use oat\taoLti\models\classes\user\UserService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use tao_actions_Main;
use tao_models_classes_UserService;

class AuthoringTool extends ToolModule
{
    private const LTI_NO_MATCHING_REGISTRATION_FOUND_MESSAGE = 'No matching registration found tool side';

    /**
     * @throws LtiException
     * @throws InterruptedActionException
     * @throws common_exception_Error
     */
    public function run(): void
    {
        if ($this->hasAccess(tao_actions_Main::class, 'entry')) {
            $this->redirect(_url('entry', 'Main', 'tao', $_GET));
        } else {
            throw new LtiException(
                __('You are not authorized to access this resource'),
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    /**
     * @throws LtiException
     */
    protected function getValidatedLtiMessagePayload(): LtiMessagePayloadInterface
    {
        return $this->getServiceLocator()
            ->getContainer()
            ->get(Lti1p3Validator::class . 'Authoring')
            ->getValidatedPayload($this->getPsrRequest());
    }

    /**
     * @throws ActionEnforcingException
     * @throws InterruptedActionException
     * @throws LtiException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws common_exception_Error
     * @throws WrongLtiRolesException
     */
    public function launch(): void
    {
        $ltiMessage = $this->getLtiMessageOrRedirectToLogin();

        $user = $this->getServiceLocator()
            ->getContainer()
            ->get(tao_models_classes_UserService::class)
            ->addUser(
                $ltiMessage->getUserIdentity()->getIdentifier(),
                helpers_Random::generateString(UserService::PASSWORD_LENGTH),
                new core_kernel_classes_Resource(
                    $this->getAuthoringRoleService()->getValidRole($ltiMessage->getRoles())
                )
            );

        $this->getServiceLocator()
            ->getContainer()
            ->get(LtiService::class)
            ->startLti1p3Session($ltiMessage, $user);

        $this->forward('run', null, null, $_GET);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InterruptedActionException
     * @throws LtiException
     * @throws NotFoundExceptionInterface
     */
    private function getLtiMessageOrRedirectToLogin(): LtiMessagePayloadInterface
    {
        try {
            $message = $this->getValidatedLtiMessagePayload();
        } catch (LtiException $exception) {
            if ($exception->getMessage() !== self::LTI_NO_MATCHING_REGISTRATION_FOUND_MESSAGE) {
                throw $exception;
            }

            $this->getLogger()->warning(
                sprintf(
                    'Missing registration for current audience. Redirecting to the login page. Exception: %s',
                    $exception
                )
            );
            $this->redirect(_url('login', 'Main', 'tao'));
        }

        return $message;
    }

    private function getAuthoringRoleService(): AuthoringLtiRoleService
    {
        return $this->getPsrContainer()->get(AuthoringLtiRoleService::class);
    }
}
