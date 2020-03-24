<?php declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\models\classes\Security\Business\Service;

use Exception;
use oat\tao\model\service\InjectionAwareService;
use oat\taoLti\models\classes\Security\Business\Contract\SecretKeyServiceInterface;
use oat\taoLti\models\classes\Security\Business\Domain\Exception\SecretKeyGenerationException;

final class SecretKeyService extends InjectionAwareService implements SecretKeyServiceInterface
{
    /** @var int */
    private $length;

    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function generate(): string
    {
        try {
            return substr(bin2hex(random_bytes((int)ceil($this->length / 2))), 0, $this->length);
        } catch (Exception $exception) {
            throw SecretKeyGenerationException::create($exception);
        }
    }
}
