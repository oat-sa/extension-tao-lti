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
 * Copyright (c) 2023-2024 (original work) Open Assessment Technologies SA;
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
     * @deprecated LTI requests to open Authoring should come through launch().
     *
     * @throws LtiException
     * @throws InterruptedActionException
     * @throws common_exception_Error
     */
    public function run(): void
    {
        if ($this->hasAccess(tao_actions_Main::class, 'entry')) {
            // Using a 301 Moved Permanently redirect prevents getting the request recorded in the browser's
            // history, avoiding going back to the token validation when the user clicks the "Back" button
            $this->redirect(_url('entry', 'Main', 'tao', $_GET), 301);
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
     * @throws core_kernel_users_Exception
     *
     * @return never
     */
    public function launch(): void
    {
        try {
            $ltiMessage = $this->getValidatedLtiMessagePayload();

            $user = $this->getUserService()->addUser(
                $ltiMessage->getUserIdentity()->getIdentifier(),
                helpers_Random::generateString(UserService::PASSWORD_LENGTH),
                new core_kernel_classes_Resource(
                    $this->getAuthoringRoleService()->getValidRole($ltiMessage->getRoles())
                )
            );

            $this->getLtiService()->startLti1p3Session($ltiMessage, $user);
        } catch (LtiException $exception) {
            $this->handleLtiException($exception);
        }

        // Using a 301 Moved Permanently redirect prevents getting the request recorded in the browser's
        // history, avoiding going back to the token validation when the user clicks the "Back" button
        $this->redirect(_url('entry', 'Main', 'tao', $_GET), 301);
    }

    /**
     * Handles an exception, either by rethrowing it or by throwing an instance of
     * InterruptedActionException in order to redirect the user to the login page.
     *
     * @throws InterruptedActionException
     * @throws LtiException
     * @return never
     */
    private function handleLtiException(LtiException $exception): void
    {
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

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getAuthoringRoleService(): AuthoringLtiRoleService
    {
        return $this->getPsrContainer()->get(AuthoringLtiRoleService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getLtiService(): LtiService
    {
        return $this->getPsrContainer()->get(LtiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getUserService(): tao_models_classes_UserService
    {
        return $this->getPsrContainer()->get(tao_models_classes_UserService::class);
    }
}
