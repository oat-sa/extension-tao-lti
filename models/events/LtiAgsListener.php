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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoLti\models\events;

use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionStateContext;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\Lti1p3User;
use oat\taoLti\models\tasks\SendAgsScoreTask;
use oat\taoQtiTest\models\TestSessionService;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiScalar;
use qtism\runtime\common\OutcomeVariable;

class LtiAgsListener extends ConfigurableService
{
    public function onDeliveryExecutionStart(DeliveryExecutionCreated $event)
    {
        $user = $event->getUser();

        if ($user instanceof Lti1p3User && $user->getLaunchData()->hasVariable(LtiLaunchData::AGS_CLAIMS))
        {
            /** @var AgsClaim $agsClaim */
            $agsClaim = $user->getLaunchData()->getVariable(LtiLaunchData::AGS_CLAIMS);

            /** @var QueueDispatcherInterface $taskQueue */
            $taskQueue = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
            $taskQueue->createTask(new SendAgsScoreTask(), [
                'registrationId' => $user->getRegistrationId(),
                'agsClaim' => $agsClaim->normalize(),
                'data' => [
                    'userId' => $user->getIdentifier(),
                    'activityProgress' => ScoreInterface::ACTIVITY_PROGRESS_STATUS_STARTED
                ]
            ], 'AGS score send on test launch');
        }
    }

    public function onDeliveryExecutionStateUpdate(DeliveryExecutionState $event)
    {
        if ($event->getPreviousState() === DeliveryExecutionInterface::STATE_ACTIVE
            && $event->getState() === DeliveryExecutionInterface::STATE_FINISHED
            && null !== $event->getContext()
        ) {
            $this->onDeliveryExecutionFinish($event);
        }
    }

    protected function onDeliveryExecutionFinish(DeliveryExecutionState $event)
    {
        /** @var User $user */
        $user = $event->getContext()->getParameter(DeliveryExecutionStateContext::PARAM_USER);

        if ($user instanceof Lti1p3User && $user->getLaunchData()->hasVariable(LtiLaunchData::AGS_CLAIMS))
        {
            /** @var AgsClaim $agsClaim */
            $agsClaim = $user->getLaunchData()->getVariable(LtiLaunchData::AGS_CLAIMS);

            /** @var TestSessionService $testSessionService */
            $testSessionService = $this->getServiceManager()->get(TestSessionService::SERVICE_ID);
            $session = $testSessionService->getTestSession($event->getDeliveryExecution());

            $scoreTotal = null;
            $scoreTotalMax = null;

            foreach ($session->getAllVariables()->getArrayCopy() as $variable)
            {
                if ($variable instanceof OutcomeVariable) {
                    $value = $variable->getValue();

                    if ($variable->getIdentifier() === 'SCORE_TOTAL' && $value instanceof QtiScalar) {
                        $scoreTotal = $value->getValue();
                    }

                    if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX' && $value instanceof QtiScalar) {
                        $scoreTotalMax = $value->getValue();
                    }
                }
            }

            /** @var QueueDispatcherInterface $taskQueue */
            $taskQueue = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
            $taskQueue->createTask(new SendAgsScoreTask(), [
                'registrationId' => $user->getRegistrationId(),
                'agsClaim' => $agsClaim->normalize(),
                'data' => [
                    'userId' => $user->getIdentifier(),
                    'activityProgress' => ScoreInterface::ACTIVITY_PROGRESS_STATUS_COMPLETED,
                    'gradingProgress' => ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED,
                    'scoreGiven' => $scoreTotal,
                    'scoreMaximum' => $scoreTotalMax,
                ]
            ], 'AGS score send on test launch');
        }
    }
}
