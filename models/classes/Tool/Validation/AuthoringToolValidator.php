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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool\Validation;

use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidatorInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcher;
use OAT\Library\Lti1p3Core\Security\Jwt\Parser\Parser;
use OAT\Library\Lti1p3Core\Security\Jwt\Validator\Validator;

/**
     * @see https://www.imsglobal.org/spec/security/v1p0/#authentication-response-validation
     */
class AuthoringToolValidator extends ToolLaunchValidator implements ToolLaunchValidatorInterface
{
    public function __construct(
        RegistrationRepositoryInterface $registrationRepository
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->fetcher = new JwksFetcher();
        $this->validator = new Validator();
        $this->parser = new Parser();
        $this->isStateValidationRequired = false;
        $this->isNonceValidationRequired = false;
    }
}
