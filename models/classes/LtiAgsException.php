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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes;

use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;

final class LtiAgsException extends \common_Exception
{
    /**
     * @var string Unique key to determine error in log
     */
    private $key;

    /** @var AgsClaim */
    private $agsClaim = null;

    /** @var RegistrationInterface */
    private $registration = null;

    /** @var ScoreInterface */
    private $score = null;

    /**
     * LtiException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        if (!is_null($previous)) {
            $message .= ' ' . $previous->getMessage();
        }
        parent::__construct($message, $code, $previous);
    }

    public function getKey(): string
    {
        if (!isset($this->key)) {
            $this->key = uniqid();
        }

        return $this->key;
    }

    public function __toString(): string
    {
        return '[key ' . $this->getKey() . '] ' . parent::__toString();
    }

    public function getAgsClaim(): ?AgsClaim
    {
        return $this->agsClaim;
    }

    public function setAgsClaim(?AgsClaim $agsClaim): self
    {
        $this->agsClaim = $agsClaim;

        return $this;
    }

    public function getRegistration(): ?RegistrationInterface
    {
        return $this->registration;
    }

    public function setRegistration(?RegistrationInterface $registration): self
    {
        $this->registration = $registration;

        return $this;
    }

    public function getScore(): ?ScoreInterface
    {
        return $this->score;
    }

    public function setScore(?ScoreInterface $score): self
    {
        $this->score = $score;

        return $this;
    }
}
