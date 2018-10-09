<?php

namespace oat\taoLti\test\integration;

use oat\tao\model\oauth\DataStore;
use oat\tao\test\integration\RestTestRunner;
use oat\taoLti\models\classes\ConsumerService;

class RestServiceTest extends RestTestRunner
{
    /**
     * @var \core_kernel_classes_Class
     */
    private $consumerClass;

    /**
     * @throws \common_exception_Error
     */
    public function setUp()
    {
        parent::setUp();
        $this->consumerClass = new \core_kernel_classes_Class(ConsumerService::CLASS_URI);
    }

    /**
     * @return string created URI
     *
     * @throws \core_kernel_persistence_Exception
     */
    public function testCreate()
    {
        $url = $this->host . 'taoLti/RestService/index';

        $label = uniqid('RestServiceTest', true);

        $instances = $this->consumerClass->getInstances(true);
        $countBefore = count($instances);

        $postData = [
            'label' => $label,
            'oauth-key' => 'oauth key value',
            'oauth-secret' => 'oauth secret value',
            'oauth-callback-url' => 'https://oauth-callback-url/',
        ];

        $responseBody = $this->curl($url, CURLOPT_POST, 'data', array(CURLOPT_POSTFIELDS => $postData));
        // here should be an assertion of status 200, but we can only curl() has a bad interface

        $instances = $this->consumerClass->getInstances(true);
        $this->assertEquals($countBefore, count($instances) - 1);

        $addedInstance = end($instances);

        $this->assertEquals($postData['oauth-key'], $addedInstance->getOnePropertyValue($addedInstance->getProperty(DataStore::PROPERTY_OAUTH_KEY)));
        $this->assertEquals($postData['oauth-secret'], $addedInstance->getOnePropertyValue($addedInstance->getProperty(DataStore::PROPERTY_OAUTH_SECRET)));
        $this->assertEquals($postData['oauth-callback-url'], $addedInstance->getOnePropertyValue($addedInstance->getProperty(DataStore::PROPERTY_OAUTH_CALLBACK)));

        return $addedInstance->getUri();
    }

    /**
     * @depends testCreate
     * @param $uri
     */
    public function testDelete($uri)
    {
        $url = $this->host . 'taoLti/RestService/index?uri=' . urlencode($uri);

        $instances = $this->consumerClass->getInstances(true);
        $countBefore = count($instances);

        $responseBody = $this->curl($url, 'DELETE', 'data');
        // here should be an assertion of status 200, but we can only curl() has a bad interface

        $instances = $this->consumerClass->getInstances(true);
        $this->assertEquals($countBefore, count($instances) + 1);
    }
}
