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
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;

class ToolKeyChainRepositoryTest extends TestCase
{
    /** @var ToolKeyChainRepository */
    private $subject;

    public function setUp(): void
    {
        #
        # @TODO Remove after get info from provider
        #
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', '');
        }

        #
        # @TODO Remove after get info from provider
        #
        if (!defined('ROOT_URL')) {
            define('ROOT_URL', '');
        }

        $this->subject = new ToolKeyChainRepository();
    }

    public function testFindAll(): void
    {
        #
        # @TODO Assert as soon as we have these values coming from provider
        #
        $publicKey = file_get_contents(ROOT_PATH . 'tool.key') ?? '';

        #
        # @TODO Assert as soon as we have these values coming from provider
        #
        $keyChain = new KeyChain(
            '1',
            'myToolKeySetName',
            new Key($publicKey),
            new Key('')
        );

        $this->assertEquals(new KeyChainCollection(...[$keyChain]), $this->subject->findAll(new KeyChainQuery()));
    }
}
