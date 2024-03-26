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
 * Copyright (c) 2013-2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\models\classes;

use common_http_Request;
use core_kernel_classes_Resource;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Platform\PlatformInterface;
use oat\oatbox\log\LoggerAwareTrait;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use Psr\Http\Message\ServerRequestInterface;
use tao_helpers_Request;

class LtiLaunchData implements \JsonSerializable
{
    use LoggerAwareTrait;

    public const OAUTH_CONSUMER_KEY  = 'oauth_consumer_key';
    public const RESOURCE_LINK_ID    = 'resource_link_id';
    public const RESOURCE_LINK_TITLE = 'resource_link_title';
    public const CONTEXT_ID          = 'context_id';
    public const CONTEXT_LABEL       = 'context_label';
    public const CONTEXT_TITLE       = 'context_title';

    public const USER_ID                          = 'user_id';
    public const ROLES                            = 'roles';
    public const LIS_PERSON_NAME_GIVEN            = 'lis_person_name_given';
    public const LIS_PERSON_NAME_FAMILY           = 'lis_person_name_family';
    public const LIS_PERSON_NAME_FULL             = 'lis_person_name_full';
    public const LIS_PERSON_CONTACT_EMAIL_PRIMARY = 'lis_person_contact_email_primary';

    public const LAUNCH_PRESENTATION_LOCALE     = 'launch_presentation_locale';
    public const LAUNCH_PRESENTATION_RETURN_URL = 'launch_presentation_return_url';

    public const TOOL_CONSUMER_INSTANCE_ID          = 'tool_consumer_instance_id';
    public const TOOL_CONSUMER_INSTANCE_NAME        = 'tool_consumer_instance_name';
    public const TOOL_CONSUMER_INSTANCE_DESCRIPTION = 'tool_consumer_instance_description';

    public const LTI_VERSION = 'lti_version';
    public const LTI_MESSAGE_TYPE = 'lti_message_type';

    public const LIS_RESULT_SOURCEDID = 'lis_result_sourcedid';
    public const LIS_OUTCOME_SERVICE_URL = 'lis_outcome_service_url';

    // review mode
    public const LTI_SHOW_SCORE = 'custom_show_score';
    public const LTI_SHOW_CORRECT = 'custom_show_correct';

    public const LTI_REDIRECT_AFTER_LOGOUT_URL = 'authoringSettings.redirectAfterLogoutUrl';

    public const LTI_TAO_LOGIN_URL = 'authoringSettings.taoLoginUrl';

    // for user claim
    private const LTI_FOR_USER_ID = 'lti_for_user_id';
    private const LTI_FOR_USER_EMAIL = 'lti_for_user_email';
    private const LTI_FOR_USER_FAMILY_NAME = 'lti_for_user_family_name';
    private const LTI_FOR_USER_GIVEN_NAME = 'lti_for_user_given_name';
    private const LTI_FOR_USER_NAME = 'lti_for_user_name';
    private const LTI_FOR_USER_PERSON_SOURCED_ID = 'lti_for_user_person_sourced_id';
    private const LTI_FOR_USER_ROLES = 'lti_for_user_roles';

    // AGS
    public const AGS_CLAIMS = 'ags_claims';

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
    public function __construct(array $ltiVariables, array $customParameters)
    {
        $this->variables = $ltiVariables;
        $this->customParams = $customParameters;
    }

    public static function fromJsonArray(array $json): LtiLaunchData
    {
        static::unserializeAgsClaims($json);
        return new static($json['variables'], $json['customParams']);
    }

    /**
     *
     * @param common_http_Request $request
     * @return LtiLaunchData
     * @throws \ResolverException
     */
    public static function fromPsrRequest(ServerRequestInterface $request)
    {
        $extra = self::getParametersFromUrl($request->getUri()->__toString());
        $combined = array_merge($request->getQueryParams(), $request->getParsedBody());
        return new static($combined, $extra);
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

    public static function fromLti1p3MessagePayload(
        LtiMessagePayloadInterface $payload,
        PlatformInterface $platform = null
    ): self {
        $variables[self::OAUTH_CONSUMER_KEY] = '';
        $variables[self::RESOURCE_LINK_ID] =
            $payload->getResourceLink() ? $payload->getResourceLink()->getIdentifier() : null;
        $variables[self::RESOURCE_LINK_TITLE] =
            $payload->getResourceLink() ? $payload->getResourceLink()->getTitle() : null;

        $variables[self::CONTEXT_ID] = $payload->getContext() ? $payload->getContext()->getIdentifier() : null;
        $variables[self::CONTEXT_LABEL] = $payload->getContext() ? $payload->getContext()->getLabel() : null;
        $variables[self::CONTEXT_TITLE] = $payload->getContext() ? $payload->getContext()->getTitle() : null;

        $variables[self::USER_ID] =
            $payload->getUserIdentity() ? $payload->getUserIdentity()->getIdentifier() : null;

        $variables[self::ROLES] = implode(',', $payload->getRoles());
        $variables[self::LIS_PERSON_NAME_GIVEN] =
            $payload->getUserIdentity() ? $payload->getUserIdentity()->getGivenName() : null;
        $variables[self::LIS_PERSON_NAME_FAMILY] =
            $payload->getUserIdentity() ? $payload->getUserIdentity()->getFamilyName() : null;
        $variables[self::LIS_PERSON_NAME_FULL] =
            $payload->getUserIdentity() ? $payload->getUserIdentity()->getName() : null;
        $variables[self::LIS_PERSON_CONTACT_EMAIL_PRIMARY] =
            $payload->getUserIdentity() ? $payload->getUserIdentity()->getEmail() : null;

        $variables[self::LAUNCH_PRESENTATION_LOCALE] =
            $payload->getLaunchPresentation() ? $payload->getLaunchPresentation()->getLocale() : null;
        $variables[self::LAUNCH_PRESENTATION_RETURN_URL] =
            $payload->getLaunchPresentation() ? $payload->getLaunchPresentation()->getReturnUrl() : null;

        $variables[self::LTI_VERSION] = $payload->getVersion();
        $variables[self::LTI_MESSAGE_TYPE] = $payload->getMessageType();
        $variables[self::LIS_RESULT_SOURCEDID] =
            $payload->getBasicOutcome() ? $payload->getBasicOutcome()->getLisResultSourcedId() : null;
        $variables[self::LIS_OUTCOME_SERVICE_URL] =
            $payload->getBasicOutcome() ? $payload->getBasicOutcome()->getLisOutcomeServiceUrl() : null;
        $variables[self::LTI_FOR_USER_ID] =
            $payload->getForUser() ? $payload->getForUser()->getIdentifier() : null;
        $variables[self::LTI_FOR_USER_EMAIL] =
            $payload->getForUser() ? $payload->getForUser()->getEmail() : null;
        $variables[self::LTI_FOR_USER_FAMILY_NAME] =
            $payload->getForUser() ? $payload->getForUser()->getFamilyName() : null;
        $variables[self::LTI_FOR_USER_GIVEN_NAME] =
            $payload->getForUser() ? $payload->getForUser()->getGivenName() : null;
        $variables[self::LTI_FOR_USER_NAME] =
            $payload->getForUser() ? $payload->getForUser()->getName() : null;
        $variables[self::LTI_FOR_USER_PERSON_SOURCED_ID] =
            $payload->getForUser() ? $payload->getForUser()->getPersonSourcedId() : null;
        $variables[self::LTI_FOR_USER_ROLES] =
            $payload->getForUser() ? $payload->getForUser()->getRoles() : null;

        if ($platform) {
            // we need to have inner platform ID
            $variables[self::TOOL_CONSUMER_INSTANCE_ID] = $platform->getIdentifier();

            if ($platformFromClaim = $payload->getPlatformInstance()) {
                $variables[self::TOOL_CONSUMER_INSTANCE_NAME] = $platformFromClaim->getName();
                $variables[self::TOOL_CONSUMER_INSTANCE_DESCRIPTION] = $platformFromClaim->getDescription();
            } else {
                $variables[self::TOOL_CONSUMER_INSTANCE_NAME] = $platform->getName();
                $variables[self::TOOL_CONSUMER_INSTANCE_DESCRIPTION] = $platform->getName();
            }
        }

        if ($ags = $payload->getAgs()) {
            $variables[self::AGS_CLAIMS] = $ags;
        }

        $customParams = $payload->getCustom();

        // review mode
        if (isset($customParams[self::LTI_SHOW_SCORE])) {
            $variables[self::LTI_SHOW_SCORE] = filter_var(
                $customParams[self::LTI_SHOW_SCORE],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        if (isset($customParams[self::LTI_SHOW_CORRECT])) {
            $variables[self::LTI_SHOW_CORRECT] = filter_var(
                $customParams[self::LTI_SHOW_CORRECT],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return new static($variables, $customParams);
    }

    /**
     * @throws \ResolverException
     */
    private static function getParametersFromUrl(string $url): array
    {
        $returnValue = [];

        parse_str(parse_url($url, PHP_URL_QUERY), $returnValue);

        // encoded in url
        $parts = explode('/', tao_helpers_Request::getRelativeUrl($url), 4);
        if (count($parts) == 4) {
            [$extension, $module, $action, $codedUri] = $parts;
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

    private static function unserializeAgsClaims(array &$json): void
    {
        if (isset($json['variables'][self::AGS_CLAIMS])) {
            $json['variables'][self::AGS_CLAIMS] = AgsClaim::denormalize($json['variables'][self::AGS_CLAIMS]);
        }
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

    /**
     * @param string $key
     * @return boolean mixed
     *
     * @throws LtiException
     * @throws LtiVariableMissingException
     */
    public function getBooleanVariable($key)
    {
        $original = $this->getVariable($key);
        $var = is_string($original) ? mb_strtolower($original) : null;

        if ($var === 'true') {
            return true;
        } elseif ($var === 'false') {
            return false;
        } else {
            throw new LtiInvalidVariableException(
                'Invalid value of `' . $key . '` variable, boolean string expected.',
                LtiErrorMessage::ERROR_INVALID_PARAMETER
            );
        }
    }

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
        $consumerName = null;

        if ($this->hasVariable(self::TOOL_CONSUMER_INSTANCE_NAME)) {
            $consumerName = $this->getVariable(self::TOOL_CONSUMER_INSTANCE_NAME);
        }

        if (
            $consumerName === null
            && $this->hasVariable(self::TOOL_CONSUMER_INSTANCE_DESCRIPTION)
        ) {
            $consumerName = $this->getVariable(self::TOOL_CONSUMER_INSTANCE_DESCRIPTION);
        }

        return $consumerName;
    }

    /**
     * @return core_kernel_classes_Resource
     * @throws LtiVariableMissingException
     */
    public function getLtiConsumer()
    {
        if (is_null($this->ltiConsumer)) {
            $dataStore = new \tao_models_classes_oauth_DataStore();
            $this->ltiConsumer = $dataStore->findOauthConsumerResource($this->getOauthKey())->getUri();
        }

        return new \core_kernel_classes_Resource($this->ltiConsumer);
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
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserId(): string
    {
        return $this->getVariable(self::LTI_FOR_USER_ID);
    }

    /**
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserEmail(): string
    {
        return $this->getVariable(self::LTI_FOR_USER_EMAIL);
    }

    /**
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserFamilyName(): string
    {
        return $this->getVariable(self::LTI_FOR_USER_FAMILY_NAME);
    }

    /**
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserGivenName(): string
    {
        return $this->getVariable(self::LTI_FOR_USER_GIVEN_NAME);
    }

    /**
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserName(): string
    {
        return $this->getVariable(self::LTI_FOR_USER_NAME);
    }

    /**
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserPersonSourcedId(): string
    {
        return $this->getVariable(self::LTI_FOR_USER_PERSON_SOURCED_ID);
    }

    /**
     * @throws LtiVariableMissingException
     */
    public function getLtiForUserRoles(): array
    {
        return $this->getVariable(self::LTI_FOR_USER_ROLES);
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
    public function jsonSerialize(): array
    {
        return [
            'variables' => array_map(
                fn ($var) => $var instanceof AgsClaim ? $var->normalize() : $var,
                $this->variables
            ),
            'customParams' => $this->customParams,
        ];
    }
}
