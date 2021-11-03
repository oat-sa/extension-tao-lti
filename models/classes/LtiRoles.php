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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoLti\models\classes;

/**
 * Interface containing the Lti Role URIs
 */
interface LtiRoles
{
    public const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIRole';

    public const PROPERTY_URN = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#RoleURN';

    public const INSTANCE_LTI_BASE = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiBaseRole';

    public const CONTEXT_TEACHING_ASSISTANT = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#TeachingAssistant';

    public const CONTEXT_LEARNER = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#Learner';

    public const CONTEXT_INSTRUCTOR = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#Instructor';

    public const CONTEXT_ADMINISTRATOR = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#Administrator';

    // LTI 1p3
    public const CONTEXT_LTI1P3_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner';

    public const CONTEXT_LTI1P3_MENTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Mentor';

    public const CONTEXT_LTI1P3_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor';
}
