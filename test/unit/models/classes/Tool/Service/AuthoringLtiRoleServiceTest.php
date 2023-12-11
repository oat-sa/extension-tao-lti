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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\Tool\Service;

use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\Tool\Exception\WrongLtiRolesException;
use oat\taoLti\models\classes\Tool\Service\AuthoringLtiRoleService;
use PHPUnit\Framework\TestCase;

class AuthoringLtiRoleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->subject = new AuthoringLtiRoleService(
            [
                LtiRoles::CONTEXT_LTI1P3_ADMINISTRATOR_SUB_DEVELOPER,
                LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_DEVELOPER,
                LTIRoles::CONTEXT_INSTITUTION_LTI1P3_ADMINISTRATOR,
                LtiRoles::CONTEXT_LTI1P3_INSTRUCTOR
            ]
        );
    }

    /**
     * @dataProvider ltiMessageRolesProvider
     */
    public function testValidRole(array $rolesProvided, string $expected): void
    {
        self::assertEquals($expected, $this->subject->getValidRole($rolesProvided));
    }

    /**
     * @dataProvider invalidRolesProvider
     * @throws WrongLtiRolesException
     */
    public function testExpectException(array $roles): void
    {
        $this->expectException(WrongLtiRolesException::class);
        $this->subject->getValidRole($roles);
    }

    protected function invalidRolesProvider(): array
    {
        return [
            'Empty array' => [
                'roles' => [],
            ],
            'UnsupportedRole' => [
                'roles' => ['http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Support']
            ]
        ];
    }

    protected function ltiMessageRolesProvider(): array
    {
        return [
            'When one valid roles' => [
                'rolesProvided' => [
                    'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator',
                    'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Support'
                ],
                'expected' => 'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator'
            ],
            'When more then one valid roles' => [
                'rolesProvided' => [
                    'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator',
                    'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Support',
                    'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ContentDeveloper'
                ],
                'expected' => 'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator'
            ]
        ];
    }
}
