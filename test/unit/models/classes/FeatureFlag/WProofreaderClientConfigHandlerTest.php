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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\FeatureFlag;

use oat\oatbox\session\SessionService;
use oat\taoLti\models\classes\FeatureFlag\WProofreaderClientConfigHandler;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\TaoLtiSession;
use PHPUnit\Framework\TestCase;

class WProofreaderClientConfigHandlerTest extends TestCase
{
    private const ORIGINAL_CONFIG = [
        'tao/ckeditor/wproofreaderBootstrap' => [
            'spellCheckConfig' => [
                'enabled' => true,
                'providerId' => 'native',
                'providerConfig' => []
            ],
            'otherOption' => true
        ]
    ];

    public function testLeavesConfigUnchangedOutsideLtiSession(): void
    {
        $sessionService = $this->createMock(SessionService::class);
        $sessionService->method('getCurrentSession')->willReturn(null);

        $subject = new WProofreaderClientConfigHandler($sessionService);

        self::assertSame(self::ORIGINAL_CONFIG, $subject(self::ORIGINAL_CONFIG));
    }

    /**
     * @dataProvider invalidClaimProvider
     */
    public function testLeavesConfigUnchangedForInvalidClaim(?string $claim): void
    {
        $subject = new WProofreaderClientConfigHandler($this->createSessionService($claim));

        self::assertSame(self::ORIGINAL_CONFIG, $subject(self::ORIGINAL_CONFIG));
    }

    public function invalidClaimProvider(): array
    {
        return [
            'missing' => [null],
            'malformed JSON' => ['{'],
            'non-object JSON' => ['1'],
            'disabled' => [json_encode([
                'enabled' => false,
                'providerId' => 'wproofreader',
                'providerConfig' => ['serviceId' => 'key']
            ])],
            'blank key' => [json_encode([
                'enabled' => true,
                'providerId' => 'wproofreader',
                'providerConfig' => ['serviceId' => ' ']
            ])],
            'unsafe URL' => [json_encode([
                'enabled' => true,
                'providerId' => 'wproofreader',
                'providerConfig' => ['serviceId' => 'key', 'srcUrl' => 'http://example.com/wscbundle.js']
            ])]
        ];
    }

    public function testAppliesValidatedClaimAndPreservesOtherModuleConfig(): void
    {
        $claim = json_encode([
            'enabled' => true,
            'providerId' => 'wproofreader',
            'providerConfig' => [
                'serviceId' => ' key ',
                'srcUrl' => 'https://example.com/wscbundle.js',
                'ignored' => true
            ],
            'ignored' => true
        ]);
        $subject = new WProofreaderClientConfigHandler($this->createSessionService($claim));

        self::assertSame(
            [
                'tao/ckeditor/wproofreaderBootstrap' => [
                    'spellCheckConfig' => [
                        'enabled' => true,
                        'providerId' => 'wproofreader',
                        'providerConfig' => [
                            'serviceId' => 'key',
                            'srcUrl' => 'https://example.com/wscbundle.js'
                        ]
                    ],
                    'otherOption' => true
                ]
            ],
            $subject(self::ORIGINAL_CONFIG)
        );
    }

    public function testAppliesValidatedClaimWithoutOptionalEndpoint(): void
    {
        $claim = json_encode([
            'enabled' => true,
            'providerId' => 'wproofreader',
            'providerConfig' => ['serviceId' => 'key']
        ]);
        $subject = new WProofreaderClientConfigHandler($this->createSessionService($claim));

        $result = $subject(self::ORIGINAL_CONFIG);

        self::assertSame(
            ['serviceId' => 'key'],
            $result['tao/ckeditor/wproofreaderBootstrap']['spellCheckConfig']['providerConfig']
        );
    }

    private function createSessionService(?string $claim): SessionService
    {
        $launchData = $this->createMock(LtiLaunchData::class);
        $launchData->method('getCustomParameter')
            ->with(LtiLaunchData::LTI_SPELL_CHECK_CONFIG)
            ->willReturn($claim);

        $session = $this->createMock(TaoLtiSession::class);
        $session->method('getLaunchData')->willReturn($launchData);

        $sessionService = $this->createMock(SessionService::class);
        $sessionService->method('getCurrentSession')->willReturn($session);

        return $sessionService;
    }
}
