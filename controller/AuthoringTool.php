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

use common_exception_Error;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use InterruptedActionException;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use oat\oatbox\user\User;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use oat\taoLti\models\classes\LtiService;
use oat\taoLti\models\classes\TaoLtiSession;
use oat\taoLti\models\classes\Tool\Validation\Lti1p3Validator;
use oat\taoLti\models\classes\user\LtiUser;
use oat\taoLti\models\classes\user\OntologyLtiUserService;
use tao_actions_Main;
use tao_models_classes_UserService;

class AuthoringTool extends ToolModule
{
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
                'You are not authorized to access this resource',
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    protected function getValidatedLtiMessagePayload(): LtiMessagePayloadInterface
    {
        return $this->getServiceLocator()
            ->getContainer()
            ->get(Lti1p3Validator::class . 'Authoring')
            ->getValidatedPayload($this->getPsrRequest());
    }

    /**
     * @throws common_exception_Error
     * @throws ActionEnforcingException
     * @throws InterruptedActionException
     */
    public function launch(): void
    {
        $message = $this->getValidatedLtiMessagePayload();

        $user = $this->getServiceLocator()
            ->getContainer()
            ->get(tao_models_classes_UserService::class)
            ->addUser(
                $message->getUserIdentity()->getIdentifier(),
                'this-is-fake',
                new core_kernel_classes_Resource(current($message->getRoles()))
            );

        LtiService::singleton()->startLti1p3Session($message, $user->getUri());

        $this->forward('run', null, null, $_GET);
    }
}