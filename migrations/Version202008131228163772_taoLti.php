<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Platform\Service\KeyChainGenerator;
use oat\taoLti\models\classes\Platform\Service\KeyChainGeneratorInterface;

final class Version202008131228163772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Will generate keyChain and store in persistence';
    }

    public function up(Schema $schema): void
    {
        $this->getKeyChainGenerator()->generate();
    }

    public function down(Schema $schema): void
    {
    }

    /**
     * @return KeyChainGeneratorInterface
     */
    private function getKeyChainGenerator(): KeyChainGeneratorInterface
    {
        return $this->getServiceLocator()->get(KeyChainGenerator::class);
    }
}
