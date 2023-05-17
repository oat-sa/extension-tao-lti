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
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\ltiTestReview\controller\Review;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\user\TaoRoles;
use oat\taoLti\controller\AuthoringTool;
use oat\taoLti\controller\CookieUtils;
use oat\taoLti\controller\Security;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\ServiceProvider\LtiServiceProvider;
use oat\taoLti\scripts\install\CreateLti1p3RegistrationSnapshotSchema;
use oat\taoLti\scripts\install\GenerateKeys;
use oat\taoLti\scripts\install\GenerisSearchWhitelist;
use oat\taoLti\scripts\install\SetupServices;
use oat\taoLti\scripts\install\MapLtiSectionVisibility;
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
    'author' => 'Open Assessment Technologies SA',
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
            SetupServices::class,
            GenerateKeys::class,
            MapLtiSectionVisibility::class,
            GenerisSearchWhitelist::class,
            CreateLti1p3RegistrationSnapshotSchema::class,
        ]
    ],
    'update' => Updater::class,
    'managementRole' => 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole',
    'acl' => [
        [AccessRule::GRANT, 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole', ['ext' => 'taoLti']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS, CookieUtils::class],
        [AccessRule::GRANT, TaoRoles::BASE_USER, ['ext' => 'taoLti','mod' => 'LtiConsumer', 'act' => 'call']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS, Security::class],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS, ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'launch']],
        [
            AccessRule::GRANT,
            LtiRoles::CONTEXT_LTI1P3_CONTENT_DEVELOPER,
            ['ext' => 'taoLti', 'mod' => 'AuthoringTool', 'act' => 'run']
        ],
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
    ],
    'containerServiceProviders' => [
        LtiServiceProvider::class,
    ],
];
