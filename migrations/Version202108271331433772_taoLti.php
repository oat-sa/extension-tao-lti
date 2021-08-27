<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Security\Nonce\NonceRepository;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\oatbox\reporting\Report;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Tool\Validation\Lti1p3Validator;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202108271331433772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register ' . Lti1p3Validator::SERVICE_ID;
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            Lti1p3Validator::SERVICE_ID,
            new Lti1p3Validator(
                new ToolLaunchValidator(
                    $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class),
                    new NonceRepository($this->getServiceLocator()->get(ItemPoolSimpleCacheAdapter::class))
                )
            )
        );

        $this->addReport(Report::createSuccess('Registered ' . Lti1p3Validator::SERVICE_ID));
    }

    public function down(Schema $schema): void
    {
        $this->addReport(
            $this->getServiceManager()->unregister(Lti1p3Validator::SERVICE_ID)
                ? Report::createSuccess('Registered ' . Lti1p3Validator::SERVICE_ID)
                : Report::createError(Lti1p3Validator::SERVICE_ID . ' was not found.')
        );
    }
}
