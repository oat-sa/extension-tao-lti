<?php

namespace oat\taoLti\models\classes\Platform\Repository;

use ErrorException;
use LogicException;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Tool\Tool;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Domain\Key\KeyChain as TaoKeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;

class RegistrationRepository extends ConfigurableService implements RegistrationRepositoryInterface
{
    private const OIDC_URL = ROOT_URL . 'taoLti/Security/oidc';

    public function find(string $identifier): ?RegistrationInterface
    {
        $toolKeyChain = $this->getToolKeyChainRepository()
                ->findAll(new KeyChainQuery($identifier))
                ->getKeyChains()[0] ?? null;

        $platformKeyChain = $this->getPlatformKeyChainRepository()
                ->findAll(new KeyChainQuery($identifier))
                ->getKeyChains()[0] ?? null;

        if ($toolKeyChain === null || $platformKeyChain === null) {
            throw new ErrorException('Platform or Tool key missing');
        }

        $platform = new Platform(
            'tao',
            'tao',
            rtrim(ROOT_URL, '/'),
            self::OIDC_URL
        );

        /**
         * @TODO Must come from proper provider configuration...
         */
        $tool = new Tool(
            'local_demo',
            'local_demo',
            'http://localhost:8888/tool',
            'http://localhost:8888/lti1p3/oidc/login-initiation',
            'http://localhost:8888/tool/launch'
        );

        /**
         * @TODO Must come from proper provider configuration...
         */
        $deploymentIds = ['1'];

        /**
         * @TODO Must come from proper provider configuration...
         */
        return new Registration(
            'registrationIdentifier',
            'client_id',
            $platform,
            $tool,
            $deploymentIds,
            $this->translateKeyChain($platformKeyChain),
            $this->translateKeyChain($toolKeyChain),
            'http://test-tao-deploy-nginx/taoLti/Security/jwks'
        );
    }

    public function findAll(): array
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    public function findByClientId(string $clientId): ?RegistrationInterface
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    public function findByPlatformIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    public function findByToolIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    private function throwMissingImplementation(string $method)
    {
        throw new LogicException('Method ' . $method . ' was not required at needs to be implemented');
    }

    private function getToolKeyChainRepository(): ToolKeyChainRepository
    {
        return $this->getServiceLocator()->get(ToolKeyChainRepository::class);
    }

    private function getPlatformKeyChainRepository(): PlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
    }

    private function translateKeyChain(TaoKeyChain $keyChain): KeyChain
    {
        return new KeyChain(
            $keyChain->getIdentifier(),
            $keyChain->getName(),
            $keyChain->getPublicKey()->getValue(),
            $keyChain->getPrivateKey()->getValue()
        );
    }
}