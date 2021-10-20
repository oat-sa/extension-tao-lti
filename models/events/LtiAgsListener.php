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
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\Lti1p3User;
use oat\taoLti\models\tasks\SendAgsScoreTask;

class LtiAgsListener
{
    public static function sendAgsOnStart(DeliveryExecutionCreated $event)
    {
        $user = $event->getUser();

        if ($user instanceof Lti1p3User && $user->getLaunchData()->hasVariable(LtiLaunchData::AGS_CLAIMS)
        ) {
            /** @var AgsClaim $agsClaim */
            $agsClaim = $user->getLaunchData()->getVariable(LtiLaunchData::AGS_CLAIMS);

            /** @var QueueDispatcherInterface $taskQueue */
            $taskQueue = ServiceManager::getServiceManager()->get(QueueDispatcherInterface::SERVICE_ID);
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
}
