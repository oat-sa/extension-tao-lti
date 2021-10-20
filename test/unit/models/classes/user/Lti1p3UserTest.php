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

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\Lti1p3User;

class Lti1p3UserTest extends TestCase
{
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
}
