<?php

namespace oat\taoLti\models\classes\Platform\Service\Oidc;

use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OidcLoginAuthenticatorProxy extends ConfigurableService implements OidcLoginAuthenticatorInterface
{
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getLti1p3OidcLoginAuthenticator()->authenticate($request, $response);
    }

    private function getLti1p3OidcLoginAuthenticator(): OidcLoginAuthenticatorInterface
    {
        return $this->getServiceLocator()->get(Lti1p3OidcLoginAuthenticator::class);
    }
}
