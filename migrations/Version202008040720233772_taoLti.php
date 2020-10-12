<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\controller\Security;

final class Version202008040720233772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new public route to provide JWKS';
    }

    public function up(Schema $schema): void
    {
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
}
