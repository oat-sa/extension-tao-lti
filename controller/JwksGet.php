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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\controller;

use oat\tao\helpers\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\taoLti\models\classes\Platform\Service\CachedKeyChainGenerator;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use \tao_actions_CommonModule as CommonModule;
use function GuzzleHttp\Psr7\stream_for;

class JwksGet extends CommonModule //implements RequestHandlerInterface
{
    public function handle(/*ServerRequestInterface $request*/): ResponseInterface
    {
        $this->setData('jwks-key', json_encode($this->getJwksRepository()->find()));
        $this->setData('jwks-generate-url', $this->getUrlGenerator()->buildUrl('jwks', 'Security'));
        $this->setView('jwks/Jwks.tpl');

        return $this->getPsrResponse();
    }

    public function view(): void
    {
        \common_Logger::w(print_r($this->getPsrRequest()->getHeaders(), true));
        $this->setData('jwks-key', json_encode($this->getJwksRepository()->find()));
        $this->setData('jwks-generate-url', $this->getUrlGenerator()->buildUrl('jwks', 'Security'));
        $this->setView('jwks/Jwks.tpl');
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        \common_Logger::w(print_r($this->getPsrRequest()->getHeaders(), true));
        $this->setData('jwks-key', json_encode($this->getJwksRepository()->find()));
        $this->setData('jwks-generate-url', $this->getUrlGenerator()->buildUrl('jwks', 'Security'));
        $this->setView('jwks/Jwks.tpl');

        return $next($request, $response);
    }

    public function index(): void
    {
        switch ($this->getRequestMethod()) {
            case 'POST':
                $this->postJwks();
                break;

            default:
                $this->setResponse(
                    $this->getPsrResponse()
                        ->withStatus(501)
                        ->withBody(stream_for(__('Not Implemented')))
                );
        }
    }

    private function postJwks(): void
    {
        $this->getCachedKeyChainGenerator()->generate();
    }

    private function getJwksRepository(): JwksRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformJwksRepository::class);
    }

    private function getUrlGenerator(): UrlHelper
    {
        return $this->getServiceLocator()->get(UrlHelper::class);
    }

    private function getCachedKeyChainGenerator(): CachedKeyChainGenerator
    {
        return $this->getServiceLocator()->get(CachedKeyChainGenerator::class);
    }
}
