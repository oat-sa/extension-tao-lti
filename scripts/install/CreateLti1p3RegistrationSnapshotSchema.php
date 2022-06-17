<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\taoLti\scripts\install;

use oat\generis\persistence\PersistenceManager;
use oat\generis\persistence\sql\SchemaProviderInterface;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationSnapshotSchemaProvider;

class CreateLti1p3RegistrationSnapshotSchema extends ScriptAction
{
    public const OPTION_PERSISTENCE = 'persistence';

    public function __invoke($params = [])
    {
        return parent::__invoke($params);
    }

    protected function provideUsage(): array
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Shows this message'
        ];
    }

    protected function provideOptions(): array
    {
        return [
            static::OPTION_PERSISTENCE => [
                'prefix' => 'p',
                'longPrefix' => static::OPTION_PERSISTENCE,
                'defaultValue' => 'default',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Create `lti_platform_registration` table.';
    }

    protected function run(): void
    {
        $this->getPersistenceManager()->applySchemaProvider(
            $this->getSchemaProvider()
        );
    }

    private function getPersistenceManager(): PersistenceManager
    {
        return $this->getServiceLocator()->get(PersistenceManager::class);
    }

    private function getSchemaProvider(): SchemaProviderInterface
    {
        return new Lti1p3RegistrationSnapshotSchemaProvider(
            $this->getOption(static::OPTION_PERSISTENCE)
        );
    }
}
