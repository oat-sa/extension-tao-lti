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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\models\classes\user;

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class KvLtiUser
 * @package oat\taoLti\models\classes\user
 */
class KvLtiUser extends \common_user_User implements ServiceLocatorAwareInterface, \JsonSerializable
{
    use ServiceLocatorAwareTrait;

    CONST USER_IDENTIFIER = 'identifier';

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
     * KvLtiUser constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->userUri = isset($data[self::USER_IDENTIFIER]) ? $data[self::USER_IDENTIFIER] : null;
        $this->taoRoles = isset($data[GenerisRdf::PROPERTY_USER_ROLES]) ? $data[GenerisRdf::PROPERTY_USER_ROLES] : [];
        $this->language = isset($data[GenerisRdf::PROPERTY_USER_UILG]) ? $data[GenerisRdf::PROPERTY_USER_UILG] : null;
        $this->label = isset($data[OntologyRdfs::RDFS_LABEL]) ? $data[OntologyRdfs::RDFS_LABEL] : null;
        $this->firstname = isset($data[GenerisRdf::PROPERTY_USER_FIRSTNAME]) ? $data[GenerisRdf::PROPERTY_USER_FIRSTNAME] : null;
        $this->lastname = isset($data[GenerisRdf::PROPERTY_USER_LASTNAME]) ? $data[GenerisRdf::PROPERTY_USER_LASTNAME] : null;
        $this->email = isset($data[GenerisRdf::PROPERTY_USER_MAIL]) ? $data[GenerisRdf::PROPERTY_USER_MAIL] : null;
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
     * (non-PHPdoc)
     * @see \common_user_User::getPropertyValues()
     */
    public function getPropertyValues($property)
    {
        $returnValue = null;
        switch ($property) {
            case GenerisRdf::PROPERTY_USER_DEFLG :
                $returnValue = array(DEFAULT_LANG);
                break;
            case GenerisRdf::PROPERTY_USER_UILG :
                $returnValue = array(new \core_kernel_classes_Literal($this->language));
                break;
            case  GenerisRdf::PROPERTY_USER_ROLES :
                $returnValue = $this->taoRoles;
                break;
            case  GenerisRdf::PROPERTY_USER_FIRSTNAME :
                $returnValue = [new \core_kernel_classes_Literal($this->firstname)];
                break;
            case  GenerisRdf::PROPERTY_USER_LASTNAME :
                $returnValue = [new \core_kernel_classes_Literal($this->lastname)];
                break;
            case  OntologyRdfs::RDFS_LABEL :
                $returnValue = [new \core_kernel_classes_Literal($this->label)];
                break;
            case  GenerisRdf::PROPERTY_USER_MAIL :
                $returnValue = [new \core_kernel_classes_Literal($this->email)];
                break;
            default:
                \common_Logger::d('Unkown property ' . $property . ' requested from ' . __CLASS__);
                $returnValue = array();
        }
        return $returnValue;
    }

    /**
     * @param $properties
     * @return array
     */
    public function getPropertiesValues($properties)
    {
        $returnValues = [];
        foreach ($properties as $property) {
            $returnValues[$property] = $this->getPropertyValues($property);
        }
        return $returnValues;
    }


    /**
     * (non-PHPdoc)
     * @see \common_user_User::refresh()
     */
    public function refresh()
    {
        // nothing to do
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            self::USER_IDENTIFIER => $this->userUri,
            GenerisRdf::PROPERTY_USER_ROLES => $this->taoRoles,
            GenerisRdf::PROPERTY_USER_UILG => $this->language,
            GenerisRdf::PROPERTY_USER_FIRSTNAME => $this->firstname,
            GenerisRdf::PROPERTY_USER_LASTNAME => $this->lastname,
            GenerisRdf::PROPERTY_USER_MAIL => $this->email,
            OntologyRdfs::RDFS_LABEL => $this->label,
        ];
    }

}
