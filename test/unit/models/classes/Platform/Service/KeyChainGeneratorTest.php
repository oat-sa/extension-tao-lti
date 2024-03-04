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
use oat\taoLti\models\classes\Platform\Service\OpenSslKeyChainGenerator;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

class KeyChainGeneratorTest extends TestCase
{
    /** @var OpenSslKeyChainGenerator */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new OpenSslKeyChainGenerator();
    }

    public function testGenerate(): void
    {
        $result = $this->subject->generate();

        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', $result->getPrivateKey()->getContent());
        $this->assertStringContainsString('-----END PRIVATE KEY-----', $result->getPrivateKey()->getContent());
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $result->getPublicKey()->getContent());
        $this->assertStringContainsString('-----END PUBLIC KEY-----', $result->getPublicKey()->getContent());
    }

    public function testGenerateWithPassphrase(): void
    {
        $result = $this->subject->generate(
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID,
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME,
            'pass'
        );

        $this->assertEquals('pass', $result->getPrivateKey()->getPassPhrase());
        $this->assertEmpty($result->getPublicKey()->getPassPhrase());
        $this->assertStringContainsString('-----BEGIN ENCRYPTED PRIVATE KEY-----', $result->getPrivateKey()->getContent());
        $this->assertStringContainsString('-----END ENCRYPTED PRIVATE KEY-----', $result->getPrivateKey()->getContent());
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $result->getPublicKey()->getContent());
        $this->assertStringContainsString('-----END PUBLIC KEY-----', $result->getPublicKey()->getContent());
    }
}
