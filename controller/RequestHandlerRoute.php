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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\taoLti\controller;

use LogicException;
use oat\tao\model\routing\AbstractRoute;
use oat\tao\model\routing\RouterException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerRoute extends AbstractRoute
{
    /** @var ServerRequestInterface */
    private $request;

    public function resolve(ServerRequestInterface $request): ?string
    {
        $this->request = $request;

        if (!isset($this->getConfig()['definitions'])) {
            throw new LogicException('RequestHandler routing requires route definitions.');
        }

        foreach ($this->getConfig()['definitions'] as $routeDefinition) {
            $controller =$this->resolveRoute($routeDefinition);
            if (!is_null($controller)) {
                return $controller . '@' . $this->getAction();
            }
        }

        return null;
    }

    private function getAction(): string
    {
        return 'handle';
    }

    private function resolveRoute($routeDefinition): ?string
    {
        try {
            $this->assetRouteDefinitionIsValid($routeDefinition);
            $this->assertHttpMethod($routeDefinition);

            return $this->getMatchedController($routeDefinition);
        } catch (RouterException $e) {
            return null;
        }
    }

    private function assetRouteDefinitionIsValid(array $routeDefinition)
    {
        if (!isset($routeDefinition['handler'])) {
            throw new LogicException('RequestHandler route requires a "handler" key.');
        }

        if (!isset($routeDefinition['pattern'])) {
            throw new LogicException('RequestHandler route requires a "pattern" key.');
        }
    }

    private function assertHttpMethod(array $routeDefinition)
    {
        if (!isset($routeDefinition['httpMethod'])) {
            $routeDefinition['httpMethod'] = 'get';
        }

        $acceptedHttpMethod = is_array($routeDefinition['httpMethod'])
            ? $routeDefinition['httpMethod']
            : [$routeDefinition['httpMethod']];
        $acceptedHttpMethod = array_map('strtolower', $acceptedHttpMethod);

        $currentMethod = strtolower($this->request->getMethod());

        if (!in_array($currentMethod, $acceptedHttpMethod)) {
            throw new RouterException(
                sprintf('Route "%s" does not support "%s" method', $this->getId(), $currentMethod)
            );
        }
    }

    private function getMatchedController(array $routeDefinition)
    {
        $relativeUrl = \tao_helpers_Request::getRelativeUrl($this->request->getRequestTarget());

        if (!$this->isMatchingPattern($routeDefinition, $relativeUrl)) {
            throw new RouterException('Url does not match with pattern');
        }

        $controller = $this->getControllerName($routeDefinition);

        if (!is_a($controller, RequestHandlerInterface::class, true)) {
            throw new RouterException(
                sprintf('Controller "%s" is not implementing "%s".', $controller, RequestHandlerInterface::class)
            );
        }

        return $controller;
    }

    private function getControllerName(array $routeDefinition): string
    {
        $controllerClass = $routeDefinition['handler'];

        if (strpos($controllerClass, '\\') === false) {
            $controllerClass = self::getControllerPrefix() . $routeDefinition['handler'];
        }

        return $controllerClass;
    }

    private function isMatchingPattern(array $routeDefinition, string $relativeUrl): bool
    {
        return $relativeUrl == $routeDefinition['pattern'];
    }

    public static function getControllerPrefix()
    {
        return 'oat\\taoLti\\controller\\';
    }
}
