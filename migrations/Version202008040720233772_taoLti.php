<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use common_Exception;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\controller\Security;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

final class Version202008040720233772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new public route to provide JWKS';
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     */
    public function up(Schema $schema): void
    {
        $this->createFileSystem();

        AclProxy::applyRule($this->getRule());
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getRule());
    }

    private function getRule(): AccessRule
    {
        return new AccessRule(AccessRule::GRANT, TaoRoles::ANONYMOUS, Security::class);
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
