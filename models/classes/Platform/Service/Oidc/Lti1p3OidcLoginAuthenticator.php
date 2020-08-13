<?php

namespace oat\taoLti\models\classes\Platform\Service\Oidc;

use OAT\Library\Lti1p3Core\Security\Oidc\Endpoint\OidcLoginAuthenticator;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Lti1p3OidcLoginAuthenticator extends ConfigurableService implements OidcLoginAuthenticatorInterface
{
    /** @var OidcLoginAuthenticator */
    private $loginAuthenticator;

    public function withLoginAuthenticator(OidcLoginAuthenticator $loginAuthenticator): void
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

    private function getExternalOidcLoginAuthenticator(): OidcLoginAuthenticator
    {
        if (!$this->loginAuthenticator) {
            $this->loginAuthenticator = new OidcLoginAuthenticator(
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
