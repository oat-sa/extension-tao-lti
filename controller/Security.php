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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\controller;

use oat\tao\model\http\Controller;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use function GuzzleHttp\Psr7\stream_for;

class Security extends Controller
{
    use ServiceLocatorAwareTrait;

    public function jwks(): void
    {
        $response = $this->getPsrResponse()
            ->withHeader('ContentType', 'application/json')
            ->withBody(
                #
                # @TODO This data must come from a normalization from a Domain KeySet object
                #
                stream_for(
                '{
                          "keys": [
                            {
                              "kty": "RSA",
                              "e": "AQAB",
                              "n": "4PKmGnI1-voe4M8hgEn9oFdMZkDZwJuZuVUy6oPM4EhvmYhnFbYsUgUHsVjxJTxNPMLdMYZ4cNqVgJHCxxRvSppIDMwn7qvhouqSRjNGHMGe1xxaeb9DFWwyRc3v4m-RFXEWdDmaxjVENMBkLDLSGphVl7Bwl7q628juJ1SMansIoSKx9VtYIAvqfslMjmjBKrqFnIq4V1F3DLgTJhXXmvu-jYuaHMV04K7u_w3n7aiBi_W0iiHsnvKpAyUqNIK4lKNrmyHevLPR8BaKyNGb7N2GmxFY9647Jd7bNH_TpUvuwwH3KaZw9D_dL5-h0k_6NN9cuZyNE9ErJNiAT7KLjQ",
                              "kid": "I-need-to-be-unique",
                              "alg": "RS256",
                              "use": "sig"
                            }
                          ]
                        }
                '
            )
        );

        $this->setResponse($response);
    }
}
