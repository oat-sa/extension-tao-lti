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

namespace oat\taoLti\test\unit\models\classes\Security\DataAccess\Repository;

use oat\generis\test\TestCase;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;
use PHPUnit\Framework\MockObject\MockObject;

class ToolKeyChainRepositoryTest extends TestCase
{
    /** @var ToolKeyChainRepository */
    private $subject;

    /** @var LtiProviderService|MockObject */
    private $ltiProviderService;

    public function setUp(): void
    {
        $this->ltiProviderService = $this->createMock(LtiProviderService::class);
        $this->subject = new ToolKeyChainRepository();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    LtiProviderService::SERVICE_ID => $this->ltiProviderService
                ]
            )
        );
    }

    public function testFindAll(): void
    {
        $ltiProvider = $this->createMock(LtiProvider::class);

        $ltiProvider->method('getId')
            ->willReturn('ltiId');

        $ltiProvider->method('getToolPublicKey')
            ->willReturn('key');

        $this->ltiProviderService
            ->method('searchById')
            ->willReturn($ltiProvider);

        $keyChain = new KeyChain(
            'ltiId',
            'ltiId',
            new Key('key'),
            new Key('')
        );

        $this->assertEquals(new KeyChainCollection(...[$keyChain]), $this->subject->findAll(new KeyChainQuery()));
    }
}
