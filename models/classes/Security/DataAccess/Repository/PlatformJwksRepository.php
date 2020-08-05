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

namespace oat\taoLti\models\classes\Security\DataAccess\Repository;

use OAT\Library\Lti1p3Core\Security\Jwks\Exporter\Jwk\JwkExporterInterface;
use OAT\Library\Lti1p3Core\Security\Jwks\Exporter\Jwk\JwkRS256Exporter;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Jwk;
use oat\tao\model\security\Business\Domain\Key\Jwks;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;

class PlatformJwksRepository extends ConfigurableService implements JwksRepositoryInterface
{
    /** @var JwkExporterInterface */
    private $jwksExporter;

    public function find(): Jwks
    {
        $collection = $this->getKeyChainRepository()
            ->findAll(new KeyChainQuery())
            ->getKeyChains();

        $jwkList = [];
        $exporter = $this->getJwksExporter();

        foreach ($collection as $key) {
            $keyChain = new KeyChain(
                $key->getIdentifier(),
                $key->getName(),
                $key->getPublicKey()->getValue()
            );

            $exported = $exporter->export($keyChain);

            $jwkList[] = new Jwk(
                $exported['kty'],
                $exported['e'],
                $exported['n'],
                $exported['kid'],
                $exported['alg'],
                $exported['use']
            );
        }

        return new Jwks(...$jwkList);
    }

    public function withJwksExporter(JwkExporterInterface $jwksExporter): self
    {
        $this->jwksExporter = $jwksExporter;

        return $this;
    }

    private function getJwksExporter(): JwkExporterInterface
    {
        return $this->jwksExporter ?? new JwkRS256Exporter();
    }

    private function getKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::class);
    }
}
