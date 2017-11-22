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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
namespace oat\taoLti\models\classes\ResourceLink;

use oat\oatbox\service\ConfigurableService;
/**
 * Service to generate unique ids for consumers resource links,
 * and storing this link in a keyvalue store
 *
 * @author joel bout (joel@taotesting.com)
 */
class KeyValueLink extends ConfigurableService implements LinkService
{
    const OPTION_PERSISTENCE = 'persistence';

    const PREFIX = 'lti_link_';

    /**
     * (non-PHPdoc)
     * @see \oat\taoLti\models\classes\ResourceLink\LinkService::getLinkId()
     */
    public function getLinkId($consumerId, $resourceLink)
    {
        $id = $this->getPersistence()->get(self::PREFIX.$consumerId.$resourceLink);
        if ($id == false) {
            $id = \core_kernel_uri_UriService::singleton()->generateUri();
            $this->getPersistence()->set(self::PREFIX.$consumerId.$resourceLink, $id);
        }
        return $id;
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        return $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)
        ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }
}
