<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use core_kernel_classes_Resource;
use core_kernel_users_Cache;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\LtiRoles;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202505171522423888_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply launch permissions for ContentDeveloper role';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->getRule());
        $this->updateCache();

        $this->addReport(Report::createInfo('Applied permission for AuthoringTool'));
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getRule());
        $this->updateCache();

        $this->addReport(Report::createInfo('Revoked permission for AuthoringTool'));
    }

    private function getRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER,
            ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'run']
        );
    }

    private function updateCache(): void
    {
        $this->addReport(
            Report::createInfo(
                sprintf(
                    'Clearing the Generis cache for roles %s',
                    LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER
                )
            )
        );

        core_kernel_users_Cache::removeIncludedRoles(
            new core_kernel_classes_Resource(LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER)
        );

        OntologyUpdater::syncModels();
    }
}
