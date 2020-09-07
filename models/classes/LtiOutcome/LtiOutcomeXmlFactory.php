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

namespace oat\taoLti\models\classes\LtiOutcome;

use oat\oatbox\service\ConfigurableService;

class LtiOutcomeXmlFactory extends ConfigurableService
{

    private const REPLACE_RESULT_REQUEST = 'replaceResultRequest';
    private const OUTCOME_DEFAULT_LANG = 'en-us';

    public function buildReplaceResultRequest(
        string $sourcedId,
        string $grade,
        string $messageIdentifier,
        string $language = self::OUTCOME_DEFAULT_LANG): string
    {
        $language = $language ?? $this->getDefaultLang();
        return '<?xml version = "1.0" encoding = "UTF-8"?>
                <imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
                    <imsx_POXHeader>
                        <imsx_POXRequestHeaderInfo>
                            <imsx_version>V1.0</imsx_version>
                            <imsx_messageIdentifier>' . $messageIdentifier . '</imsx_messageIdentifier>
                        </imsx_POXRequestHeaderInfo>
                    </imsx_POXHeader>
                    <imsx_POXBody>
                        <' . self::REPLACE_RESULT_REQUEST . '>
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
                        </' . self::REPLACE_RESULT_REQUEST . '>
                    </imsx_POXBody>
                </imsx_POXEnvelopeRequest>';
    }

    private function getDefaultLang(): string
    {
        return defined(DEFAULT_LANG) ? DEFAULT_LANG : self::OUTCOME_DEFAULT_LANG;
    }
}
