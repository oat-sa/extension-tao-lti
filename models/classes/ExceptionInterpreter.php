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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoLti\models\classes;

use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\mvc\error\ExceptionInterpretor;
use oat\tao\model\mvc\error\ResponseInterface;

/**
 * Class ExceptionInterpreter
 * @package oat\taoLti\models\classes
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ExceptionInterpreter extends ExceptionInterpretor
{
    const FILESYSTEM_ID_TO_LOG = 'log';

    /**
     * @var LtiException
     */
    protected $exception;

    /**
     * set exception to interpet
     * @param \Exception $exception
     * @return ExceptionInterpretor
     */
    public function setException(\Exception $exception)
    {
        parent::setException($exception);
        return $this;
    }

    /**
     * return an instance of ResponseInterface
     * @return ResponseInterface
     */
    public function getResponse()
    {
        $this->log((string) $this->exception);

        $response = new LtiReturnResponse(new \Renderer());
        $response->setServiceLocator($this->getServiceLocator());
        $response->setException($this->exception);

        return $response;
    }

    private function log($msg)
    {
        if (!$this->exception instanceof LtiException) {
            return;
        }

        /** @var FileSystem $fs */
        $fs = $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID)
            ->getFileSystem(self::FILESYSTEM_ID_TO_LOG);

        $fs->put('lti_' . $this->exception->getKey() . '.log', $msg);
    }
}
