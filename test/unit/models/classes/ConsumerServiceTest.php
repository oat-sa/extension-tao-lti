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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\unit\models\classes;

use common_session_Session;
use core_kernel_classes_Class;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\uri\Bin2HexUriProvider;
use oat\generis\model\kernel\uri\UriProvider;
use oat\generis\persistence\DriverConfigurationFeeder;
use oat\generis\persistence\PersistenceManager;
use oat\generis\persistence\sql\SchemaProviderInterface;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\cache\NoCache;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\event\EventAggregator;
use oat\oatbox\event\EventManager;
use oat\oatbox\log\LoggerService;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\taoLti\models\classes\ConsumerService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConsumerServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    public function testGetRootClass()
    {
        $subject = new ConsumerService();
        $subject->setModel($this->getOntologyMock());

        $rootClass = $subject->getRootClass();

        $this->assertInstanceOf(core_kernel_classes_Class::class, $rootClass);
        $this->assertEquals(ConsumerService::CLASS_URI, $rootClass->getUri());
    }

    /**
     * @return core_kernel_persistence_smoothsql_SmoothModel
     */
    protected function getOntologyMock()
    {
        $model = new core_kernel_persistence_smoothsql_SmoothModel([
            core_kernel_persistence_smoothsql_SmoothModel::OPTION_PERSISTENCE => 'mockSql',
            core_kernel_persistence_smoothsql_SmoothModel::OPTION_READABLE_MODELS => [2,3],
            core_kernel_persistence_smoothsql_SmoothModel::OPTION_WRITEABLE_MODELS => [2],
            core_kernel_persistence_smoothsql_SmoothModel::OPTION_NEW_TRIPLE_MODEL => 2,
        ]);
        $this->getServiceManagerMock([
            Ontology::SERVICE_ID => $model,
        ]);

        return $model;
    }
}
