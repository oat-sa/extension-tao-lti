<?php

namespace oat\taoLti\models\classes\Platform\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface OidcLoginAuthenticatorInterface
{
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
