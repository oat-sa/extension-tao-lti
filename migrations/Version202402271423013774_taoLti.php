<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

final class Version202402271423013774_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update PlatformKeyChain config format';
    }

    public function up(Schema $schema): void
    {
        $platformKeyChainRepository = $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
        $options = $platformKeyChainRepository->getOptions();
        $platformKeyChainRepository->setOptions([$options]);
        $this->getServiceLocator()->register(PlatformKeyChainRepository::SERVICE_ID, $platformKeyChainRepository);
    }

    public function down(Schema $schema): void
    {
        $platformKeyChainRepository = $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
        $options = $platformKeyChainRepository->getOptions();
        $platformKeyChainRepository->setOptions(reset($options));
        $this->getServiceLocator()->register(PlatformKeyChainRepository::SERVICE_ID, $platformKeyChainRepository);
    }
}
