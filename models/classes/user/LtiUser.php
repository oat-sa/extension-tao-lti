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

namespace oat\taoLti\models\classes\user;

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\taoLti\models\classes\LtiInvalidVariableException;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\LtiUtils;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\oatbox\user\UserLanguageService;

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 */
class LtiUser extends \common_user_User implements ServiceLocatorAwareInterface, \JsonSerializable, LtiUserInterface
{
    use ServiceLocatorAwareTrait;

    const USER_IDENTIFIER = 'identifier';

    /**
     * Data with which this session was launched
     * @var LtiLaunchData
     */
    private $ltiLaunchData;

    /**
     * Local representation of user
     * @var \core_kernel_classes_Resource
     */
    private $userUri;

    /**
     * Cache of the current user's lti roles
     * @var array
     */
    protected $taoRoles;

    private $language;

    private $firstname;

    private $lastname;

    private $email;

    private $label;

    /**
     * Currently used UI languages.
     *
     * @var array
     */
    protected $uiLanguage;

    /**
     * @param LtiLaunchData $launchData
     * @param $userUri
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function __construct($launchData, $userUri)
    {
        $this->ltiLaunchData = $launchData;
        $this->userUri = $userUri;
        $taoRoles = $this->determineTaoRoles($launchData);
        if (empty($taoRoles)) {
            $message = "Invalid LTI role parameter value: " . $this->ltiLaunchData->getVariable(LtiLaunchData::ROLES);
            throw new LtiInvalidVariableException($message);
        }

        $this->setRoles($taoRoles);

        $email = '';
        $label = $launchData->getUserFullName();
        $firstname = $launchData->getUserGivenName();
        $lastname = $launchData->getUserFamilyName();

        if ($launchData->hasVariable(LtiLaunchData::LIS_PERSON_CONTACT_EMAIL_PRIMARY)) {
            $email = $launchData->getUserEmail();
        }

        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->label = $label;
    }

    public function setRoles($roles)
    {
        $newRoles = array_map(function ($value) {
            return ($value instanceof \core_kernel_classes_Resource) ? $value->getUri() : $value;
        }, $roles);

        $this->taoRoles = $newRoles;
    }

    /**
     * (non-PHPdoc)
     * @see \common_user_User::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->userUri;
    }

    public function setIdentifier($userId)
    {
        $this->userUri = $userId;
    }

    /**
     * @return LtiLaunchData
     */
    public function getLaunchData()
    {
        return $this->ltiLaunchData;
    }

    /**
     * Get user langugae
     * @return string
     */
    public function getLanguage()
    {
        if (!isset($this->language)) {
            if ($this->getLaunchData()->hasLaunchLanguage()) {
                $launchLanguage = $this->getLaunchData()->getLaunchLanguage();
                $this->language = LtiUtils::mapCode2InterfaceLanguage($launchLanguage);
            } else {
                $this->language = $this->getServiceLocator()->get(UserLanguageService::SERVICE_ID)->getDefaultLanguage();
            }
        }
        return $this->language;
    }

    /**
     * (non-PHPdoc)
     * @see \common_user_User::getPropertyValues()
     */
    public function getPropertyValues($property)
    {
        $returnValue = null;
        switch ($property) {
            case GenerisRdf::PROPERTY_USER_DEFLG:
                $returnValue = [$this->getServiceLocator()->get(UserLanguageService::SERVICE_ID)->getDefaultLanguage()];
                break;
            case GenerisRdf::PROPERTY_USER_UILG:
                $returnValue = [$this->getLanguage()];
                break;
            case GenerisRdf::PROPERTY_USER_ROLES:
                $returnValue = $this->taoRoles;
                break;
            case GenerisRdf::PROPERTY_USER_FIRSTNAME:
                $returnValue = $this->firstname !== null ? [$this->firstname] : '';
                break;
            case GenerisRdf::PROPERTY_USER_LASTNAME:
                $returnValue = $this->lastname !== null ? [$this->lastname] : '';
                break;
            case OntologyRdfs::RDFS_LABEL:
                $returnValue = [$this->label];
                break;
            default:
                \common_Logger::d('Unkown property ' . $property . ' requested from ' . __CLASS__);
                $returnValue = [];
        }
        return $returnValue;
    }

    /**
     * (non-PHPdoc)
     * @see \common_user_User::refresh()
     */
    public function refresh()
    {
        // nothing to do
    }

    public function jsonSerialize()
    {
        return [
            self::USER_IDENTIFIER => $this->userUri,
            GenerisRdf::PROPERTY_USER_ROLES => $this->taoRoles,
            GenerisRdf::PROPERTY_USER_UILG => $this->getLanguage(),
            GenerisRdf::PROPERTY_USER_FIRSTNAME => $this->firstname,
            GenerisRdf::PROPERTY_USER_LASTNAME => $this->lastname,
            GenerisRdf::PROPERTY_USER_MAIL => $this->email,
            OntologyRdfs::RDFS_LABEL => $this->label,
        ];
    }

    /**
     * Calculate your primary tao roles from the launchdata
     *
     * @param LtiLaunchData $ltiLaunchData
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    protected function determineTaoRoles(LtiLaunchData $ltiLaunchData)
    {
        $roles = [];
        if ($ltiLaunchData->hasVariable(LtiLaunchData::ROLES)) {
            foreach ($ltiLaunchData->getUserRoles() as $role) {
                $taoRole = LtiUtils::mapLTIRole2TaoRole($role);
                if (!is_null($taoRole)) {
                    $roles[] = $taoRole;
                }
            }
            $roles = array_unique($roles);
        } else {
            return [LtiRoles::INSTANCE_LTI_BASE];
        }
        return $roles;
    }
}
