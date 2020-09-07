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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\test\unit\models\classes\LtiOutcome;

use PHPUnit\Framework\TestCase;
use oat\taoLti\models\classes\LtiOutcome\LtiOutcomeXmlFactory;

class LtiOutcomeXmlFactoryTest extends TestCase
{
    /** @var LtiOutcomeXmlFactory */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new LtiOutcomeXmlFactory();
    }

    /**
     * @dataProvider provideOutcomeXML
     */
    public function testBuild(string $expected, array $data): void
    {
        $this->assertEquals(
            $expected,
            $this->subject->build(
                $data['sourcedId'],
                $data['grade'],
                $data['messageIdentifier'],
                $data['operation'],
                $data['language']
            )
        );
    }

    /**
     * @dataProvider provideOutcomeXML
     */
    public function testBuildWithDefault(string $expected, array $data): void
    {
        $this->assertEquals(
            $expected,
            $this->subject->build(
                $data['sourcedId'],
                $data['grade'],
                $data['messageIdentifier']
            )
        );
    }

    public function provideOutcomeXML(): array
    {
        $sourcedId = 'sourcedId';
        $messageIdentifier = uniqid('', true);
        $operation = 'replaceResultRequest';
        $grade = 0.1;
        $language = 'en-us';
        $expected = '<?xml version = "1.0" encoding = "UTF-8"?>
                <imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
                    <imsx_POXHeader>
                        <imsx_POXRequestHeaderInfo>
                            <imsx_version>V1.0</imsx_version>
                            <imsx_messageIdentifier>' . $messageIdentifier . '</imsx_messageIdentifier>
                        </imsx_POXRequestHeaderInfo>
                    </imsx_POXHeader>
                    <imsx_POXBody>
                        <' . $operation . '>
                            <resultRecord>
                                <sourcedGUID>
                                    <sourcedId>' . $sourcedId . '</sourcedId>
                                </sourcedGUID>
                                <result>
                                    <resultScore>
                                        <language>' . $language . '</language>
                                        <textString>' . $grade . '</textString>
                                    </resultScore>
                                </result>
                            </resultRecord>
                        </' . $operation . '>
                    </imsx_POXBody>
                </imsx_POXEnvelopeRequest>';
        return [
            [
                'expected' => $expected,
                [
                    'sourcedId' => $sourcedId,
                    'grade' => $grade,
                    'messageIdentifier' => $messageIdentifier,
                    'operation' => $operation,
                    'language' => $language
                ]
            ],
        ];
    }
}
