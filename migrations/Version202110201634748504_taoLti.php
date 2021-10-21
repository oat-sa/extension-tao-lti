<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\event\EventManager;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoLti\models\events\LtiAgsListener;

final class Version202110201634748504_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register LtiAgsListener::onDeliveryExecutionStart';
    }

    public function up(Schema $schema): void
    {
        $eventManager = $this->getServiceLocator()->get(EventManager::CONFIG_ID);

        $eventManager->attach(
            DeliveryExecutionCreated::class,
            [LtiAgsListener::class, 'onDeliveryExecutionStart']
        );

        $eventManager->attach(
            DeliveryExecutionState::class,
            [LtiAgsListener::class, 'onDeliveryExecutionStateUpdate']
        );

        $this->getServiceManager()->register(EventManager::CONFIG_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        $eventManager = $this->getServiceLocator()->get(EventManager::CONFIG_ID);

        $eventManager->detach(
            DeliveryExecutionCreated::class,
            [LtiAgsListener::class, 'onDeliveryExecutionStart']
        );

        $eventManager->detach(
            DeliveryExecutionState::class,
            [LtiAgsListener::class, 'onDeliveryExecutionStateUpdate']
        );

        $this->getServiceManager()->register(EventManager::CONFIG_ID, $eventManager);
    }
}
