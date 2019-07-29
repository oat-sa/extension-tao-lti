<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 29/07/19
 * Time: 11:47
 */

namespace oat\taoLti\models\classes\LtiProvider;

use core_kernel_classes_Resource as RdfResource;

class LtiProviderResource extends RdfResource
{
    /** @var string */
    private $key = '';

    /** @var string */
    private $secret = '';

    /** @var string */
    private $callbackUrl = '';

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
     * @return LtiProviderResource
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
     * @return LtiProviderResource
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
     * @return LtiProviderResource
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }
}
