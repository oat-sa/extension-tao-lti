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
use oat\taoLti\models\classes\LtiVariableMissingException;

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 
 */
class taoLti_models_classes_LtiUser
    extends common_user_User
{
    /**
     * Data with which this session was launched
     * @var taoLti_models_classes_LtiLaunchData
     */
    private $ltiLaunchData;

    /**
     * Local represenation of user
     * @var core_kernel_classes_Resource
     */
    private $userUri;

    /**
     * Cache of the current user's lti roles
     * @var array
     */
    protected $roles;

    /**
     * Currently used UI languages.
     *
     * @var array
     */
    protected $uiLanguage;

    public function __construct(taoLti_models_classes_LtiLaunchData $ltiLaunchData) {
        $this->ltiLaunchData = $ltiLaunchData;
        $this->userUri = taoLti_models_classes_LtiService::singleton()->findOrSpawnUser($ltiLaunchData)->getUri();
        $this->roles = $this->determinTaoRoles();
    }

    /**
     * @return taoLti_models_classes_LtiLaunchData
     */
    public function getLaunchData() {
        return $this->ltiLaunchData;
    }

    /**
     * (non-PHPdoc)
     * @see common_user_User::getIdentifier()
     */
    public function getIdentifier() {
        return $this->userUri;
    }

    /**
     * (non-PHPdoc)
     * @see common_user_User::getPropertyValues()
     */
    public function getPropertyValues($property) {
        $returnValue = null;
        switch ($property) {
            case PROPERTY_USER_DEFLG :
                $returnValue = array(DEFAULT_LANG);
                break;
            case PROPERTY_USER_UILG :
                $returnValue = array($this->getUiLanguage());
                break;
            case PROPERTY_USER_ROLES :
                $returnValue = $this->roles;
                break;
            case PROPERTY_USER_FIRSTNAME :
                try {
                    $returnValue = [$this->getLaunchData()->getUserGivenName()];
                } catch (LtiVariableMissingException $e) {
                    $returnValue = '';
                }
                break;
            case PROPERTY_USER_LASTNAME :
                try {
                    $returnValue = [$this->getLaunchData()->getUserFamilyName()];
                } catch (LtiVariableMissingException $e) {
                    $returnValue = '';
                }
                break;
            default:
                common_Logger::d('Unkown property '.$property.' requested from '.__CLASS__);
                $returnValue = array();
        }
        return $returnValue;
    }

    /**
     * (non-PHPdoc)
     * @see common_user_User::refresh()
     */
    public function refresh() {
        // nothing to do
    }

    /**
     * Returns the validated launch interface language.
     *
     * @return string
     */
    private function getUiLanguage() {
        if (is_null($this->uiLanguage)) {
            if ($this->getLaunchData()->hasLaunchLanguage()) {
                $launchLanguage = $this->getLaunchData()->getLaunchLanguage();
                $this->uiLanguage = taoLti_models_classes_LtiUtils::mapCode2InterfaceLanguage($launchLanguage);
            } else {
                $this->uiLanguage = DEFAULT_LANG;
            }
        }
        return $this->uiLanguage;
    }

    private function determinTaoRoles() {
        $roles = array();
        if ($this->getLaunchData()->hasVariable(taoLti_models_classes_LtiLaunchData::ROLES)) {
            foreach ($this->getLaunchData()->getUserRoles() as $role) {
                $taoRole = taoLti_models_classes_LtiUtils::mapLTIRole2TaoRole($role);
                if (!is_null($taoRole)) {
                    $roles[] = $taoRole;
                    foreach (core_kernel_users_Service::singleton()->getIncludedRoles(new core_kernel_classes_Resource($taoRole)) as $includedRole) {
                        $roles[] = $includedRole->getUri();
                    }
                }
            }
            $roles = array_unique($roles);
        } else {
            return array(INSTANCE_ROLE_LTI_BASE);
        }
        return $roles;
    }
}
