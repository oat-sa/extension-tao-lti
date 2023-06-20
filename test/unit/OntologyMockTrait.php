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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit;

use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;

trait OntologyMockTrait
{
    use ServiceManagerMockTrait;

    /**
     * @return core_kernel_persistence_smoothsql_SmoothModel
     */
    protected function getOntologyMock(): core_kernel_persistence_smoothsql_SmoothModel
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
