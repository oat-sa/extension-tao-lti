<?php

/**
 * This program is free software; you can redistribute it and/or
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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

use oat\tao\model\user\TaoRoles;
use oat\taoLti\controller\CookieUtils;
use oat\taoLti\scripts\install\InstallServices;
use oat\taoLti\scripts\update\Updater;

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
$extpath = __DIR__ . DIRECTORY_SEPARATOR;

return [
    'name' => 'taoLti',
    'label' => 'LTI library',
    'description' => 'TAO LTI library and helpers',
    'license' => 'GPL-2.0',
    'version' => '11.9.0',
      'author' => 'Open Assessment Technologies SA',
      'requires' => [
        'generis' => '>=12.15.0',
        'tao' => '>=41.8.0'
    ],
    'routes' => [
        '/taoLti' => 'oat\\taoLti\\controller'
    ],
    'models' => [
        'http://www.tao.lu/Ontologies/TAOLTI.rdf',
        'http://www.imsglobal.org/imspurl/lis/v1/vocab/person',
        'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership'
     ],
    'install' => [
        'rdf' => [
            $extpath . 'install/ontology/lti.rdf',
            $extpath . 'install/ontology/roledefinition.rdf',
            $extpath . 'install/ontology/ltiroles_person.rdf',
            $extpath . 'install/ontology/ltiroles_membership.rdf'
        ],
        'php' => [
            InstallServices::class
        ]
    ],
    'update' => Updater::class,
    'managementRole' => 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole',
    'acl' => [
        ['grant', 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole', ['ext' => 'taoLti']],
        ['grant', TaoRoles::ANONYMOUS, CookieUtils::class],
        ['grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole', ['ext' => 'taoLti','mod' => 'LtiConsumer', 'act' => 'call']]
    ],
    'constants' => [
        # controller directory
        'DIR_ACTIONS' => $extpath . 'controller' . DIRECTORY_SEPARATOR,

        # views directory
        'DIR_VIEWS'   => $extpath . 'views' . DIRECTORY_SEPARATOR,

        #BASE PATH: the root path in the file system (usually the document root)
        'BASE_PATH'   => $extpath ,

        #BASE URL (usually the domain root)
        'BASE_URL'    => ROOT_URL . 'taoLti/',
    ],
    'extra' => [
        'structures' => $extpath . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml',
    ]
];
