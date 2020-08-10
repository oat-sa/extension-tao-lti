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

namespace oat\taoLti\test\unit\models\classes\Platform\Repository;

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Registration\Registration;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3RegistrationRepositoryTest extends TestCase
{
    /** @var Lti1p3RegistrationRepository */
    private $subject;

    /** @var KeyChainRepositoryInterface|MockObject */
    private $toolKeyChainRepository;

    /** @var KeyChainRepositoryInterface|MockObject */
    private $platformKeyChainRepository;

    public function setUp(): void
    {
        $this->toolKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);
        $this->platformKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);

        $this->subject = new Lti1p3RegistrationRepository();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    ToolKeyChainRepository::class => $this->toolKeyChainRepository,
                    PlatformKeyChainRepository::SERVICE_ID => $this->platformKeyChainRepository,
                ]
            )
        );
    }

    public function testFindWillReturnRegistrationForTool(): void
    {
        $this->expectToolAndPlatformKeys();

        $registration = $this->subject->find('registrationId');

        $this->assertInstanceOf(Registration::class, $registration);
    }

    private function expectToolAndPlatformKeys(): void
    {
        $key = new KeyChain(
            'id',
            'name',
            new Key(''),
            new Key('')
        );

        $this->toolKeyChainRepository
            ->method('findAll')
            ->willReturn(new KeyChainCollection(...[$key]));

        $this->platformKeyChainRepository
            ->method('findAll')
            ->willReturn(new KeyChainCollection(...[$key]));
    }
}
