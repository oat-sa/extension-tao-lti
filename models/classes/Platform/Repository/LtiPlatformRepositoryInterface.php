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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\Platform\Repository;

use Countable;
use oat\taoLti\models\classes\Platform\LtiPlatform;

/**
 * Service methods to manage the LTI platform business objects.
 */
interface LtiPlatformRepositoryInterface extends Countable
{
    const SERVICE_ID = 'taoLti/LtiPlatformRepository';

    /**
     * Returns all providers.
     *
     * @return LtiPlatform[]
     */
    public function findAll(): array;

    /**
     * Search all LTI providers with label property containing the given label.
     *
     * @return LtiPlatform[]
     */
    public function searchByLabel(string $label): array;

    public function searchById(string $id): ?LtiPlatform;

    public function searchByIssuer(string $issuer, string $clientId = null): ?LtiPlatform;
}
