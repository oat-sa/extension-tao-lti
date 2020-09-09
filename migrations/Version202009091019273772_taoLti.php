<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009091019273772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return sprintf('Register %s class', PlatformKeyChainRepository::class);
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            PlatformKeyChainRepository::SERVICE_ID,
            new PlatformKeyChainRepository(
                [
                    PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID => 'defaultPlatformKeyId',
                    PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME => 'defaultPlatformKeyName',
                    PlatformKeyChainRepository::OPTION_DEFAULT_PUBLIC_KEY_PATH => '/platform/default/public.key',
                    PlatformKeyChainRepository::OPTION_DEFAULT_PRIVATE_KEY_PATH => '/platform/default/private.key',
                ]
            )
        );

        $this->getServiceManager()->register(
            AuthorizationServerFactory::SERVICE_ID,
            new AuthorizationServerFactory(
                [
                    AuthorizationServerFactory::OPTION_ENCRYPTION_KEY => bin2hex(openssl_random_pseudo_bytes(16 ))
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(PlatformKeyChainRepository::SERVICE_ID);
        $this->getServiceManager()->unregister(AuthorizationServerFactory::SERVICE_ID);
    }
}
