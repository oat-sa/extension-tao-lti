<?php declare(strict_types=1);

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

namespace oat\taoLti\test\unit\models\classes\Security\DataAccess\Repository;

use oat\generis\test\TestCase;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

class PlatformKeyChainRepositoryTest extends TestCase
{
    /** @var PlatformKeyChainRepository */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new PlatformKeyChainRepository();
    }

    public function testFindAll(): void
    {
        //@TODO Improve test after refactor
        $this->assertInstanceOf(KeyChainCollection::class, $this->subject->findAll(new KeyChainQuery()));
    }

    public function testSave(): void
    {
        //@TODO Improve test after refactor
        $this->assertNull(
            $this->subject->save(
                new KeyChain('', '', new Key(''), new Key(''))
            )
        );
    }
}
