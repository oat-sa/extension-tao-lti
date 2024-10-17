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
 *
 * phpcs:disable Generic.Files.LineLength
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
    //Learner roles set
    public const CONTEXT_LTI1P3_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner';
    public const CONTEXT_LTI1P3_LEARNER_SUB_EXTERNAL_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#ExternalLearner';
    public const CONTEXT_LTI1P3_LEARNER_SUB_GUEST_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#GuestLearner';
    public const CONTEXT_LTI1P3_LEARNER_SUB_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#Instructor';
    public const CONTEXT_LTI1P3_LEARNER_SUB_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#Learner';
    public const CONTEXT_LTI1P3_LEARNER_SUB_NON_CREDIT_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#NonCreditLearner';
    //Mentor roles set
    public const CONTEXT_LTI1P3_MENTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Mentor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_ADVISOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Advisor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_AUDITOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Auditor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_EXTERNAL_ADVISOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalAdvisor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_EXTERNAL_AUDITOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalAuditor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_EXTERNAL_LEARNING_FACILITATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalLearningFacilitator';
    public const CONTEXT_LTI1P3_MENTOR_SUB_EXTERNAL_MENTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalMentor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_EXTERNAL_REVIEWER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalReviewer';
    public const CONTEXT_LTI1P3_MENTOR_SUB_EXTERNAL_TUTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalTutor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_LEARNING_FACILITATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#LearningFacilitator';
    public const CONTEXT_LTI1P3_MENTOR_SUB_MENTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Mentor';
    public const CONTEXT_LTI1P3_MENTOR_SUB_REVIEWER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Reviewer';
    public const CONTEXT_LTI1P3_MENTOR_SUB_TUTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Tutor';
    //Instructor roles set
    public const CONTEXT_LTI1P3_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_EXTERNAL_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#ExternalInstructor';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_GRADER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#Grader';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_GUEST_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#GuestInstructor';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_LECTURER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#Lecturer';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_PRIMARY_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#PrimaryInstructor';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_SECONDARY_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#SecondaryInstructor';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_TEACHING_ASSISTANT = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistant';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_TEACHING_ASSISTANT_GROUP = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantGroup';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_TEACHING_ASSISTANT_OFFERING = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantOffering';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_TEACHING_ASSISTANT_SECTION = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantSection';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_TEACHING_ASSISTANT_SECTION_ASSOCIATION = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantSectionAssociation';
    public const CONTEXT_LTI1P3_INSTRUCTOR_SUB_TEACHING_ASSISTANT_TEMPLATE = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantTemplate';
    //ContentDeveloper roles set
    public const CONTEXT_LTI1P3_CONTENT_DEVELOPER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#ContentDeveloper';
    public const CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_DEVELOPER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ContentDeveloper';
    public const CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_CREATOR= 'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ContentCreator';
    public const CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_CONTENT_EXPERT = 'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ContentExpert';
    public const CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_EXTERNAL_CONTENT_EXPERT = 'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ExternalContentExpert';
    public const CONTEXT_LTI1P3_CONTENT_DEVELOPER_SUB_LIBRARIAN = 'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#Librarian';
    //Manager roles set
    public const CONTEXT_LTI1P3_MANAGER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Manager';
    public const CONTEXT_LTI1P3_MANAGER_SUB_AREA_MANAGER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#AreaManager';
    public const CONTEXT_LTI1P3_MANAGER_SUB_COURSE_COORDINATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#CourseCoordinator';
    public const CONTEXT_LTI1P3_MANAGER_SUB_EXTERNAL_OBSERVER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#ExternalObserver';
    public const CONTEXT_LTI1P3_MANAGER_SUB_MANAGER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#Manager';
    public const CONTEXT_LTI1P3_MANAGER_SUB_OBSERVER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#Observer';
    //Member roles set
    public const CONTEXT_LTI1P3_MEMBER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Member';
    public const CONTEXT_LTI1P3_MEMBER_SUB_MEMBER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Member#Member';
    //Officer roles set
    public const CONTEXT_LTI1P3_OFFICER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Officer';
    public const CONTEXT_LTI1P3_OFFICER_SUB_CHAIR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Chair';
    public const CONTEXT_LTI1P3_OFFICER_SUB_COMMUNICATIONS = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Communications';
    public const CONTEXT_LTI1P3_OFFICER_SUB_SECRETARY = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Secretary';
    public const CONTEXT_LTI1P3_OFFICER_SUB_TREASURER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Treasurer';
    public const CONTEXT_LTI1P3_OFFICER_SUB_VICE_CHAIR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Vice-Chair';
    //Administrator roles set
    public const CONTEXT_LTI1P3_ADMINISTRATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Administrator';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_ADMINISTRATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Administrator';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_DEVELOPER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Developer';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_EXTERNAL_DEVELOPER = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#ExternalDeveloper';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_EXTERNAL_SUPPORT = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#ExternalSupport';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_EXTERNAL_SYSTEM_ADMINISTRATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#ExternalSystemAdministrator';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_SUPPORT = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Support';
    public const CONTEXT_LTI1P3_ADMINISTRATOR_SUB_SYSTEM_ADMINISTRATOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#SystemAdministrator';

    public const CONTEXT_INSTITUTION_LTI1P3_ADMINISTRATOR = 'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator';
}
