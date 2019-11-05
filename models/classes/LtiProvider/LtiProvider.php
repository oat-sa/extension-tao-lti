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
 */

namespace oat\taoLti\models\classes\LtiProvider;

/**
 * LTI provider business object.
 */
class LtiProvider implements \JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $key;

    /** @var string */
    private $secret;

    /** @var string */
    private $callbackUrl;

    /**
     * Optional field for LtiProviders retrieved from
     * @see \oat\taoDeliverConnect\model\TenantLtiProviderRepository
     * @var string|null
     */
    private $tenantId;

    /** @var string[] */
    private $roles;

    /**
     * @param string $id
     * @param string $label
     * @param string $key
     * @param string $secret
     * @param string $callbackUrl
     * @param string|null $tenantId
     * @param string[] $roles
     */
    public function __construct($id, $label, $key, $secret, $callbackUrl, $tenantId, $roles)
    {
        $this->id = $id;
        $this->label = $label;
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackUrl = $callbackUrl;
        $this->tenantId = $tenantId;
        $this->roles = $roles;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * @return string|null
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'uri' => $this->getId(),
            'text' => $this->getLabel(),
            'key' => $this->getKey(),
            'secret' => $this->getSecret(),
            'callback' => $this->getCallbackUrl(),
            'roles' => $this->getRoles()
        ];
    }
}
