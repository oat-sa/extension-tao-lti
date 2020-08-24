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

use oat\tao\model\routing\AbstractApiRoute;
use oat\tao\model\routing\RouterException;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareRoute extends AbstractApiRoute
{
    /** @var ServerRequestInterface */
    private $request;

    public function resolve(ServerRequestInterface $request)
    {
        $relativeUrl = \tao_helpers_Request::getRelativeUrl($request->getRequestTarget());

        $this->request = $request;

        $action = 'handle';

//        $this->getAction($request->getMethod())


        try {
            $controller = $this->getController($relativeUrl) . '@' . $action;

            \common_Logger::i($relativeUrl);
            \common_Logger::i(print_r($this->getExtension(), true));
            \common_Logger::i(print_r($this->getId(), true));
            \common_Logger::i(print_r($this->getConfig(), true));

            return $controller;
        } catch (RouterException $e) {
            return null;
        }
    }


//    const REST_CONTROLLER_PREFIX = 'oat\\taoTestTaker\\actions\\Rest';

    protected function getController($relativeUrl)
    {
        $parts = explode('/', $relativeUrl);
        $prefix = $this->getControllerPrefix();
        if (strpos($relativeUrl, $this->getId()) !== 0) {
            throw new RouterException('Path does not match');
        }

        $controller = $prefix . ucfirst($parts[1]);// . ucfirst(strtolower($this->request->getMethod()));

        if (!class_exists($controller)) {
            throw new RouterException('Controller ' . $controller . ' does not exists');
        }

        return $controller;
    }


    /**
     * @inheritdoc
     */
    public static function getControllerPrefix()
    {
        return 'oat\\taoLti\\controller\\';
    }
}
