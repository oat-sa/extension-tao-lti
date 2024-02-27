<?php

namespace oat\taoLti\test\unit\models\classes\Platform\Service;

use oat\generis\test\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use oat\oatbox\cache\SimpleCache;
use oat\taoLti\models\classes\Platform\Service\CachedKeyChainGenerator;
use oat\taoLti\models\classes\Platform\Service\OpenSslKeyChainGenerator;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use PHPUnit\Framework\TestCase;

class CachedKeyChainGeneratorTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var CachedKeyChainGenerator */
    private CachedKeyChainGenerator $subject;

    /** @var OpenSslKeyChainGenerator|MockObject */
    private OpenSslKeyChainGenerator $keyChainGeneratorMock;

    /** @var PlatformKeyChainRepository|MockObject */
    private PlatformKeyChainRepository $platformKeyChainRepositoryMock;

    /** @var SimpleCache|MockObject */
    private SimpleCache $simpleCacheMock;

    /** @var KeyChainInterface */
    private KeyChainInterface $keyChain;

    public function setUp(): void
    {

        $this->subject = new CachedKeyChainGenerator();
        $this->keyChainGeneratorMock = $this->createMock(OpenSslKeyChainGenerator::class);
        $this->platformKeyChainRepositoryMock = $this->createMock(PlatformKeyChainRepository::class);
        $this->simpleCacheMock = $this->createMock(SimpleCache::class);

        $this->keyChain = new KeyChain(
            'id',
            'name',
            new Key('public key'),
            new Key('private key')
        );

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    OpenSslKeyChainGenerator::class => $this->keyChainGeneratorMock,
                    PlatformKeyChainRepository::class => $this->platformKeyChainRepositoryMock,
                    SimpleCache::SERVICE_ID => $this->simpleCacheMock,
                ]
            )
        );
    }

    public function testGenerate(): void
    {
        $this->keyChainGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn($this->keyChain);

        $this->platformKeyChainRepositoryMock
            ->expects($this->once())
            ->method('saveDefaultKeyChain');

        $this->simpleCacheMock
            ->expects($this->exactly(3))
            ->method('delete')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, 'id')],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, 'id')],
                [CachedPlatformJwksRepository::JWKS_KEY]
            );

        $this->subject->generate();
    }
}
