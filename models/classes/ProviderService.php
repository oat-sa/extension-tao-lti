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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes;

use core_kernel_classes_Class;
use oat\tao\model\OntologyClassService;

/**
 * Service methods to manage the LTI provider business objects using the RDF API.
 *
 * @package taoLti
 */
class ProviderService extends OntologyClassService
{
    const SERVICE_ID = 'taoLti/ProviderService';
    const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIProvider';

    /**
     * return the group top level class
     *
     * @return core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return $this->getClass(self::CLASS_URI);
    }
}
