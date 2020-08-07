<?php

namespace oat\taoLti\models\classes\Platform\Service;

use OAT\Library\Lti1p3Core\Security\Oidc\Endpoint\OidcLoginAuthenticator as ExternalOidcLoginAuthenticator;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OidcLoginAuthenticator extends ConfigurableService implements OidcLoginAuthenticatorInterface
{
    /** @var ExternalOidcLoginAuthenticator */
    private $loginAuthenticator;

    public function withLoginAuthenticator(ExternalOidcLoginAuthenticator $loginAuthenticator)
    {
        $this->loginAuthenticator = $loginAuthenticator;
    }

    public function authenticate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $launchRequest = $this->getExternalOidcLoginAuthenticator()
            ->authenticate($request);

        $response->getBody()
            ->write($launchRequest->toHtmlRedirectForm());

        return $response;
    }

    private function getExternalOidcLoginAuthenticator(): ExternalOidcLoginAuthenticator
    {
        if (!$this->loginAuthenticator) {
            $this->loginAuthenticator = new ExternalOidcLoginAuthenticator(
                $this->getRegistrationRepository(),
                $this->getUserAuthenticator()
            );
        }

        return $this->loginAuthenticator;
    }

    private function getRegistrationRepository(): RegistrationRepository
    {
        return $this->getServiceLocator()->get(RegistrationRepository::class);
    }

    private function getUserAuthenticator(): UserAuthenticator
    {
        return $this->getServiceLocator()->get(UserAuthenticator::class);
    }
}
