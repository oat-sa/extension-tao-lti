<?php

namespace oat\taoLti\models\classes\Platform\Service\Oidc;

use OAT\Library\Lti1p3Core\Security\Oidc\Endpoint\OidcLoginAuthenticator as ExternalOidcLoginAuthenticator;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Lti1p3OidcLoginAuthenticator extends ConfigurableService implements OidcLoginAuthenticatorInterface
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

    private function getRegistrationRepository(): Lti1p3RegistrationRepository
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getUserAuthenticator(): Lti1p3UserAuthenticator
    {
        return $this->getServiceLocator()->get(Lti1p3UserAuthenticator::class);
    }
}
