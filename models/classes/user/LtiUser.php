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
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;


/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 
 */
class LtiUser extends \common_user_User implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Data with which this session was launched
     * @var \taoLti_models_classes_LtiLaunchData
     */
    private $ltiLaunchData;

    /**
     * Local represenation of user
     * @var \core_kernel_classes_Resource
     */
    private $userUri;

    /**
     * Cache of the current user's lti roles
     * @var array
     */
    protected $roles;

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

    public function __construct($launchData, $userUri, $roles, $language = DEFAULT_LANG, $firstname = '', $lastname = '', $email = '', $label = '') {
        $this->ltiLaunchData = $launchData;
        $this->userUri = $userUri;
        $this->roles = $roles;
        $this->language = $language;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
    }

    /**
     * (non-PHPdoc)
     * @see common_user_User::getIdentifier()
     */
    public function getIdentifier() {
        return $this->userUri;
    }


    public function getLaunchData()
    {
        return $this->ltiLaunchData;
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
                $returnValue = array($this->language);
                break;
            case PROPERTY_USER_ROLES :
                $returnValue = $this->roles;
                break;
            case PROPERTY_USER_FIRSTNAME :
                    $returnValue = [$this->firstname];
                break;
            case PROPERTY_USER_LASTNAME :
                    $returnValue = [$this->lastname];
                break;
            default:
                \common_Logger::d('Unkown property '.$property.' requested from '.__CLASS__);
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
     * @param $data string json representing a lti user
     * @throws \taoLti_models_classes_LtiException
     * @return LtiUser
     */
    public static function unserialize($data)
    {
        $user = $data !== false ? json_decode($data,true) : array();

        if(isset($user['launchData']) && isset($user['userUri']) && isset($user['roles']) && isset($user['language']) && isset($user['firstname']) && isset($user['lastname']) && isset($user['email']) && isset($user['label'])){
            return new self($user['launchData'], $user['userUri'], $user['roles'], $user['language'], $user['firstname'], $user['lastname'], $user['email'], $user['label']);
        }

        return null;
    }


    public function jsonSerialize()
    {
        return [
            'launchData' => $this->ltiLaunchData,
            'userUri' => $this->userUri,
            'roles' => $this->roles,
            'language' => $this->language,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'label' => $this->label,
        ];
    }

}
