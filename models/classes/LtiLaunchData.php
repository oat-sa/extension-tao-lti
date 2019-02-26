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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\models\classes;

use common_http_Request;
use common_Logger;
use core_kernel_classes_Resource;
use tao_helpers_Request;
use tao_models_classes_oauth_DataStore;
use oat\oatbox\log\LoggerAwareTrait;

class LtiLaunchData implements \JsonSerializable
{
    use LoggerAwareTrait;

    const OAUTH_CONSUMER_KEY  = 'oauth_consumer_key';
    const RESOURCE_LINK_ID    = 'resource_link_id';
    const RESOURCE_LINK_TITLE = 'resource_link_title';
    const CONTEXT_ID          = 'context_id';
    const CONTEXT_LABEL       = 'context_label';
    const CONTEXT_TITLE       = 'context_title';

    const USER_ID                          = 'user_id';
    const ROLES                            = 'roles';
    const LIS_PERSON_NAME_GIVEN            = 'lis_person_name_given';
    const LIS_PERSON_NAME_FAMILY           = 'lis_person_name_family';
    const LIS_PERSON_NAME_FULL             = 'lis_person_name_full';
    const LIS_PERSON_CONTACT_EMAIL_PRIMARY = 'lis_person_contact_email_primary';

    const LAUNCH_PRESENTATION_LOCALE     = 'launch_presentation_locale';
    const LAUNCH_PRESENTATION_RETURN_URL = 'launch_presentation_return_url';

    const TOOL_CONSUMER_INSTANCE_NAME        = 'tool_consumer_instance_name';
    const TOOL_CONSUMER_INSTANCE_DESCRIPTION = 'tool_consumer_instance_description';

    const LTI_VERSION = 'lti_version';
    const LTI_MESSAGE_TYPE = 'lti_message_type';

    /**
     * LTI variables
     *
     * @var array
     */
    private $variables;

    /**
     * Custom parameters of the LTI call
     *
     * @var array
     */
    private $customParams;

    /**
     * @var core_kernel_classes_Resource
     */
    private $ltiConsumer;

    /**
     * Spawns an LtiSession
     *
     * @param array $ltiVariables
     * @param array $customParameters
     */
    public function __construct($ltiVariables, $customParameters)
    {
        $this->variables = $ltiVariables;
        $this->customParams = $customParameters;
    }

    /**
     *
     * @param common_http_Request $request
     * @return LtiLaunchData
     * @throws \ResolverException
     */
    public static function fromRequest(common_http_Request $request)
    {
        $extra = self::getParametersFromUrl($request->getUrl());

        return new static($request->getParams(), $extra);
    }

    /**
     * @param string $url
     * @return array
     * @throws \ResolverException
     */
    private static function getParametersFromUrl($url)
    {
        $returnValue = array();

        // get parameters
        parse_str(parse_url($url, PHP_URL_QUERY), $returnValue);

        // encoded in url
        $parts = explode('/', tao_helpers_Request::getRelativeUrl($url), 4);
        if (count($parts) == 4) {
            list ($extension, $module, $action, $codedUri) = $parts;
            $base64String = base64_decode($codedUri);
            if ($base64String !== false) {
                // old serialised url
                if (substr($base64String, 0, strlen('a:')) == 'a:') {
                    $additionalParams = unserialize($base64String);
                } else {
                    $additionalParams = json_decode($base64String, true);
                }
                if ($additionalParams !== false && is_array($additionalParams)) {
                    foreach ($additionalParams as $key => $value) {
                        $returnValue[$key] = $value;
                    }
                }
            }
        }

        return $returnValue;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getCustomParameter($key)
    {
        return isset($this->customParams[$key]) ? $this->customParams[$key] : null;
    }

    /**
     * Get all custom parameters provided during launch.
     *
     * @return array
     */
    public function getCustomParameters()
    {
        return $this->customParams;
    }

    /**
     * Get all lti variables provided during launch.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getResourceLinkID()
    {
        return $this->getVariable(self::RESOURCE_LINK_ID);
    }

    /**
     * @param $key
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getVariable($key)
    {
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        } else {
            throw new LtiVariableMissingException($key);
        }
    }

    // simpler access

    /**
     * @return mixed|string
     * @throws LtiVariableMissingException
     */
    public function getResourceLinkTitle()
    {
        if ($this->hasVariable(self::RESOURCE_LINK_TITLE)) {
            return $this->getVariable(self::RESOURCE_LINK_TITLE);
        } else {
            return __('link');
        }
    }

    public function hasVariable($key)
    {
        return isset($this->variables[$key]);
    }

    /**
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getUserID()
    {
        return $this->getVariable(self::USER_ID);
    }

    /**
     * @return mixed
     */
    public function getUserGivenName()
    {
        if ($this->hasVariable(static::LIS_PERSON_NAME_GIVEN)) {
            return $this->getVariable(static::LIS_PERSON_NAME_GIVEN);
        }
    }

    /**
     * @return mixed
     */
    public function getUserFamilyName()
    {
        if ($this->hasVariable(static::LIS_PERSON_NAME_FAMILY)) {
            return $this->getVariable(static::LIS_PERSON_NAME_FAMILY);
        }
    }

    /**
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getUserFullName()
    {
        if ($this->hasVariable(self::LIS_PERSON_NAME_FULL)) {
            return $this->getVariable(self::LIS_PERSON_NAME_FULL);
        }
    }

    /**
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getUserEmail()
    {
        return $this->getVariable(self::LIS_PERSON_CONTACT_EMAIL_PRIMARY);
    }

    /**
     * @return array
     * @throws LtiVariableMissingException
     */
    public function getUserRoles()
    {
        return explode(',', $this->getVariable(self::ROLES));
    }

    public function hasLaunchLanguage()
    {
        return $this->hasVariable(self::LAUNCH_PRESENTATION_LOCALE);
    }

    /**
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getLaunchLanguage()
    {
        return $this->getVariable(self::LAUNCH_PRESENTATION_LOCALE);
    }

    /**
     * Tries to return the tool consumer name
     *
     * Returns null if no name found
     *
     * @return string
     * @throws LtiVariableMissingException
     */
    public function getToolConsumerName()
    {
        return $this->hasVariable(self::TOOL_CONSUMER_INSTANCE_NAME)
            ? $this->getVariable(self::TOOL_CONSUMER_INSTANCE_NAME)
            : $this->hasVariable(self::TOOL_CONSUMER_INSTANCE_DESCRIPTION)
                ? $this->getVariable(self::TOOL_CONSUMER_INSTANCE_DESCRIPTION)
                : null;
    }

    /**
     * @return core_kernel_classes_Resource
     * @throws LtiVariableMissingException
     */
    public function getLtiConsumer()
    {
        if (is_null($this->ltiConsumer)) {
            $dataStore = new tao_models_classes_oauth_DataStore();
            $this->ltiConsumer = $dataStore->findOauthConsumerResource($this->getOauthKey());
        }

        return $this->ltiConsumer;
    }

    /**
     * @return mixed
     * @throws LtiVariableMissingException
     */
    public function getOauthKey()
    {
        return $this->getVariable(self::OAUTH_CONSUMER_KEY);
    }

    /**
     * @return bool
     * @throws LtiException
     */
    public function hasReturnUrl()
    {
        if ($this->hasVariable(self::LAUNCH_PRESENTATION_RETURN_URL)) {
            $returnUrl = $this->getReturnUrl();

            if (!empty($returnUrl)) {
                if (filter_var($returnUrl, FILTER_VALIDATE_URL)) {
                    return true;
                } else {
                    $this->logWarning("Invalid LTI Return URL '${returnUrl}'.");
                }
            }
        }

        return false;
    }

    /**
     * Return the returnUrl to the tool consumer
     *
     * @return string
     * @throws LtiException
     */
    public function getReturnUrl()
    {
        return $this->getVariable(self::LAUNCH_PRESENTATION_RETURN_URL);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
              'variables' => $this->variables,
              'customParams' => $this->customParams,
        ];
    }
}
