<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use common_Exception;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202008061124043772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     */
    public function up(Schema $schema): void
    {
        $this->createFileSystem();
    }

    public function down(Schema $schema): void
    {
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     */
    private function createFileSystem(): void
    {
        /** @var FileSystemService $fsService */
        $fsService = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
        $fsService->createFileSystem(PlatformKeyChainRepository::FILE_SYSTEM_ID);

        $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $fsService);
    }
}
