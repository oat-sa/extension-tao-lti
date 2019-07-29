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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\models\classes\LtiProvider;

/**
 * LTI provider business object.
 */
class LtiProvider implements \JsonSerializable
{
    /** @var string */
    private $uri;

    /** @var string */
    private $label;

    /** @var string */
    private $key;

    /** @var string */
    private $secret;

    /** @var string */
    private $callbackUrl;

    /**
     * LtiProvider constructor.
     *
     * @param string $uri
     * @param string $label
     * @param string $key
     * @param string $secret
     * @param string $callbackUrl
     */
    public function __construct($uri = '', $label = '', $key = '', $secret = '', $callbackUrl = '')
    {
        $this->uri = $uri;
        $this->label = $label;
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @return LtiProvider
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return LtiProvider
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return LtiProvider
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return LtiProvider
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     *
     * @return LtiProvider
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getUri(),
            'uri' => $this->getUri(),
            'text' => $this->getLabel(),
            'key' => $this->getKey(),
            'secret' => $this->getSecret(),
            'callback' => $this->getCallbackUrl(),
        ];
    }
}
