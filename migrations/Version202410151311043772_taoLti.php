<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use core_kernel_classes_Resource;
use core_kernel_users_Cache;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\LtiRoles;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202410151311043772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->getLaunchActionRule());
        AclProxy::applyRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_CREATOR));

        $this->addReport(
            Report::createInfo(
                sprintf(
                    'Clearing the Generis cache for roles %s',
                    LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_CREATOR,
                )
            )
        );
        core_kernel_users_Cache::removeIncludedRoles(
            new core_kernel_classes_Resource(LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_CREATOR)
        );

        OntologyUpdater::syncModels();

        $this->addReport(Report::createInfo('Apply permission for AuthoringTool'));

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    private function getLaunchActionRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::ANONYMOUS,
            ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'launch']
        );
    }

    private function getRunActionRule(string $role): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            $role,
            ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'run']
        );
    }
}
