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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\FeatureFlag;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\menu\ExcludedSectionListInterface;

class ExcludedSectionList extends ConfigurableService implements ExcludedSectionListInterface
{
    public function getExcludedSections(): array
    {
        if (!$this->getFeatureFlagChecker()->isEnabled(LtiFeatures::LTI_1P3)) {
            return LtiSectionsList::LTI_1P3_SECTIONS;
        }

        return [];
    }

    private function getFeatureFlagChecker(): FeatureFlagChecker
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::SERVICE_ID);
    }
}
