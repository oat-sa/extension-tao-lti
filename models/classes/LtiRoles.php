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
    const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIRole';

    const PROPERTY_URN = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#RoleURN';

    const INSTANCE_LTI_BASE = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiBaseRole';

    const CONTEXT_TEACHING_ASSISTANT = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#TeachingAssistant';

    const CONTEXT_LEARNER = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#Learner';

    const CONTEXT_INSTRUCTOR = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#Instructor';

    const CONTEXT_ADMINISTRATOR = 'http://www.imsglobal.org/imspurl/lis/v1/vocab/membership#Administrator';
}
