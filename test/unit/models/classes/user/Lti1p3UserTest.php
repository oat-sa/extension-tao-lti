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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\models\classes\user;

use core_kernel_classes_Resource;
use oat\generis\test\ServiceManagerMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\Lti1p3User;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use tao_models_classes_LanguageService;
use tao_models_classes_Service;

class Lti1p3UserTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const TEST_LANGUAGES = ['en_GB', 'ja_JP', 'fr_FR'];

    /** @var UserLanguageServiceInterface|MockObject */
    private $userLanguageServiceMock;
    /** @var MockObject|tao_models_classes_LanguageService| */
    private $languageServiceMock;

    protected function setUp(): void
    {
        if (!defined('DEFAULT_LANG')) {
            define('DEFAULT_LANG', 'en-US');
        }

        $this->userLanguageServiceMock = $this->createMock(UserLanguageServiceInterface::class);
        $class = new ReflectionClass(tao_models_classes_Service::class);
        $this->languageServiceMock = $this->createMock(tao_models_classes_LanguageService::class);


        $class->setStaticPropertyValue(
            'instances',
            [tao_models_classes_LanguageService::class => $this->languageServiceMock]
        );
    }

    public function testSuccessCase(): void
    {
        $data = new LtiLaunchData([
                                      'oauth_consumer_key' => '',
                                      'resource_link_id' => 'b449a7b1-e040-4b5b-bc5a-14f667560bb0',
                                      'resource_link_title' => null,
                                      'context_id' => null,
                                      'context_label' => null,
                                      'context_title' => null,
                                      'user_id' => null,
                                      'roles' => 'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner',
                                      'lis_person_name_given' => null,
                                      'lis_person_name_family' => null,
                                      'lis_person_name_full' => null,
                                      'lis_person_contact_email_primary' => null,
                                      'launch_presentation_locale' => null,
                                      'launch_presentation_return_url' => null,
                                      'tool_consumer_instance_name' => 'TAO',
                                      'tool_consumer_instance_description' => 'TAO',
                                      'lti_version' => '1.3.0',
                                      'lti_message_type' => 'LtiResourceLinkRequest',
                                      'lis_result_sourcedid' => null,
                                      'lis_outcome_service_url' => null
                                  ], []);

        $subject = new Lti1p3User($data);

        self::assertEquals($data, $subject->getLaunchData());
    }

    public function testSetGetRegistrationId(): void
    {
        $subject = new Lti1p3User(new LtiLaunchData([], []));

        $subject->setRegistrationId('registration-id');

        self::assertEquals('registration-id', $subject->getRegistrationId());
    }

    public function testDefaultAnonymousUserLanguage(): void
    {
        $subject = new Lti1p3User(new LtiLaunchData([], []));
        $this->propagateUser($subject);

        if (defined('DEFAULT_ANONYMOUS_INTERFACE_LANG')) {
            self::assertEquals(DEFAULT_ANONYMOUS_INTERFACE_LANG, $subject->getLanguage());
        } else {
            $this->userLanguageServiceMock
                ->expects(self::once())
                ->method('getDefaultLanguage')
                ->willReturn(DEFAULT_LANG);
            self::assertEquals(DEFAULT_LANG, $subject->getLanguage());
        }
    }

    public function testDefaultNotAnonymousUserLanguage(): void
    {
        $subject = new Lti1p3User(new LtiLaunchData([
            'user_id' => 'notAnonymous'
        ], []));
        $this->propagateUser($subject);
        $this->userLanguageServiceMock->expects(self::once())->method('getDefaultLanguage')->willReturn(DEFAULT_LANG);
        self::assertEquals(DEFAULT_LANG, $subject->getLanguage());
    }

    public function testNotDefaultLanguageForAnonymousUser(): void
    {
        $languageIndex = array_rand(self::TEST_LANGUAGES);
        $language = self::TEST_LANGUAGES[$languageIndex];
        $subject = new Lti1p3User(new LtiLaunchData([
            'launch_presentation_locale' => $language
        ], []));
        $this->propagateUser($subject);
        $this->languageServiceMock->expects(self::once())
            ->method('isLanguageAvailable')
            ->withConsecutive([
                $language,
                new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI)
            ])
            ->willReturn(true);

        self::assertEquals($language, $subject->getLanguage());
    }

    public function testNotDefaultLanguageForNotAnonymousUser(): void
    {
        $languageIndex = array_rand(self::TEST_LANGUAGES);
        $language = self::TEST_LANGUAGES[$languageIndex];
        $subject = new Lti1p3User(new LtiLaunchData([
            'user_id' => 'notAnonymous',
            'launch_presentation_locale' => $language
        ], []));
        $this->propagateUser($subject);
        $this->languageServiceMock->expects(self::once())
            ->method('isLanguageAvailable')
            ->withConsecutive([
                $language,
                new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI)
            ])
            ->willReturn(true);

        self::assertEquals($language, $subject->getLanguage());
    }

    private function propagateUser(Lti1p3User $user): void
    {
        $user->setServiceLocator($this->getServiceManagerMock([
            UserLanguageServiceInterface::SERVICE_ID => $this->userLanguageServiceMock,
        ]));
    }
}
