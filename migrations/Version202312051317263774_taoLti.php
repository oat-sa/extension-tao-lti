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
final class Version202312051317263774_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply new http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor and http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator permission for AuthoringTool';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->getLaunchActionRule());
        AclProxy::applyRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_INSTRUCTOR));
        AclProxy::applyRule($this->getRunActionRule(LtiRoles::CONTEXT_INSTITUTION_LTI1P3_ADMINISTRATOR));

        $this->addReport(
            Report::createInfo(
                sprintf(
                    'Clearing the Generis cache for roles %s',
                    LtiRoles::CONTEXT_LTI1P3_INSTRUCTOR,
                )
            )
        );
        core_kernel_users_Cache::removeIncludedRoles(
            new core_kernel_classes_Resource(LtiRoles::CONTEXT_LTI1P3_INSTRUCTOR)
        );
        core_kernel_users_Cache::removeIncludedRoles(
            new core_kernel_classes_Resource(LtiRoles::CONTEXT_INSTITUTION_LTI1P3_ADMINISTRATOR)
        );

        OntologyUpdater::syncModels();

        $this->addReport(Report::createInfo('Apply new permission for AuthoringTool'));
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getLaunchActionRule());
        AclProxy::revokeRule($this->getRunActionRule(LtiRoles::CONTEXT_LTI1P3_INSTRUCTOR));
        AclProxy::revokeRule($this->getRunActionRule(LtiRoles::CONTEXT_INSTITUTION_LTI1P3_ADMINISTRATOR));

        $this->addReport(Report::createInfo('Revoke CONTEXT_INSTRUCTOR, CONTEXT_INSTITUTION_LTI1P3_ADMINISTRATOR permission for AuthoringTool'));
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
