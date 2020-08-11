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

namespace oat\taoLti\test\unit\models\classes\Platform\Service;

use oat\generis\test\TestCase;
use oat\taoLti\models\classes\Platform\Service\KeyChainGenerator;

class KeyChainGeneratorTest extends TestCase
{
    /** @var KeyChainGenerator */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new KeyChainGenerator();
    }

    public function testGetPublicKey(): void
    {
        $result = $this->subject->getKeyChain();

        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', $result->getPrivateKey()->getValue());
        $this->assertStringContainsString('-----END PRIVATE KEY-----', $result->getPrivateKey()->getValue());
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $result->getPublicKey()->getValue());
        $this->assertStringContainsString('-----END PUBLIC KEY-----', $result->getPublicKey()->getValue());
    }
}
