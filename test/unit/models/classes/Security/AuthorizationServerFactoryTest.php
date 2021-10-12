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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace unit\models\classes\Security;

use oat\generis\test\TestCase;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\oatbox\log\LoggerService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;

class AuthorizationServerFactoryTest extends TestCase
{
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AuthorizationServerFactory([
            AuthorizationServerFactory::OPTION_ENCRYPTION_KEY => 'toto',
        ]);

        $this->subject->setServiceLocator($this->getServiceLocatorMock([
            Lti1p3RegistrationRepository::class => $this->createMock(Lti1p3RegistrationRepository::class),
            ItemPoolSimpleCacheAdapter::class => $this->createMock(ItemPoolSimpleCacheAdapter::class),
            LoggerService::SERVICE_ID => $this->createMock(LoggerService::class),
        ]));
    }

    public function testImplementation(): void
    {
        $implementation = $this->subject->getImplementation();
        $this->assertInstanceOf(\OAT\Library\Lti1p3Core\Security\OAuth2\Factory\AuthorizationServerFactory::class, $implementation);
    }
}
