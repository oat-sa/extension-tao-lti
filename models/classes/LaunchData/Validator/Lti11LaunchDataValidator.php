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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\LaunchData\Validator;


use oat\oatbox\Configurable;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiInvalidLaunchDataException;
use oat\taoLti\models\classes\LtiVariableMissingException;
use oat\taoLti\models\classes\LtiLaunchData;

/**
 * Class LaunchDataValidator
 * @package oat\taoLti\models\classes\LaunchData\validator
 */
class Lti11LaunchDataValidator extends Configurable implements LtiValidatorInterface
{
    const LTI_VERSION_PATTERN = '/^(LTI-)[0-9]+(p)[0-9]+$/';
    const LTI_VERSION_1_PATTERN = '/^(LTI-1p)[0-9]+$/';
    const LTI_MESSAGE_TYPE = 'basic-lti-launch-request';

    /**
     * Check if provides launch data object is valid.
     *
     * @param LtiLaunchData $data
     * @return bool
     * @throws LtiException
     * @throws LtiInvalidLaunchDataException
     * @throws LtiVariableMissingException
     */
    public function validate(LtiLaunchData $data) {
        try {
            if (!$this->isValidLinkId($this->getLaunchDataParameter($data, LtiLaunchData::RESOURCE_LINK_ID))) {
                throw new LtiInvalidLaunchDataException("Required parameter resource_link_id can not be empty.");
            }

            $ltiVersion = $this->getLaunchDataParameter($data, LtiLaunchData::LTI_VERSION);
            if (!$this->isValidLtiVersion($ltiVersion)) {
                throw new LtiInvalidLaunchDataException("Invalid LTI version provided.");
            }

            if (!$this->isCorrectLtiVersion($ltiVersion)) {
                throw new LtiInvalidLaunchDataException("Wrong LTI version provided.");
            }

            if (!$this->isValidLtiMessageType($this->getLaunchDataParameter($data, LtiLaunchData::LTI_MESSAGE_TYPE))) {
                throw new LtiInvalidLaunchDataException('Invalid LTI message type provided.');
            }

        } catch (LtiException $e) {
            $e->setLaunchData($data);
            throw $e;
        }

        return true;
    }

    /**
     * Verify if link id has valid value.
     *
     * @param $linkId
     * @return bool
     */
    private function isValidLinkId($linkId) {
        return !empty($linkId);
    }

    /**
     * Verify if LTI version value has correct format.
     *
     * @param $ltiVersion
     * @return bool
     */
    private function isValidLtiVersion($ltiVersion) {
        return (bool) preg_match(self::LTI_VERSION_PATTERN, $ltiVersion);
    }

    /**
     * Verify if LTI version has correct value.
     *
     * @param $ltiVersion
     * @return bool
     */
    private function isCorrectLtiVersion($ltiVersion) {
        return (bool) preg_match(self::LTI_VERSION_1_PATTERN, $ltiVersion);
    }

    private function isValidLtiMessageType($ltiMessageType) {
        return $ltiMessageType == self::LTI_MESSAGE_TYPE;
    }

    /**
     * Get launch data parameter by name.
     *
     * @param LtiLaunchData $data
     * @param $name
     * @return mixed
     * @throws LtiVariableMissingException
     */
    private function getLaunchDataParameter(LtiLaunchData $data, $name) {
        if ($data->hasVariable($name)) {
            return $data->getVariable($name);
        }

        throw new LtiVariableMissingException($name);
    }
}
