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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoLti\scripts\update;

use common_Exception;
use common_ext_ExtensionsManager;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\tao\model\oauth\lockout\NoLockout;
use oat\tao\model\oauth\nonce\NoNonce;
use oat\tao\model\oauth\OauthService;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\CookieVerifyService;
use oat\taoLti\models\classes\ExceptionInterpreter;
use oat\taoLti\models\classes\FactoryLtiAuthAdapterService;
use oat\taoLti\models\classes\FactoryLtiAuthAdapterServiceInterface;
use oat\taoLti\models\classes\LaunchData\Validator\Lti11LaunchDataValidator;
use oat\taoLti\models\classes\LaunchData\Validator\LtiValidatorService;
use oat\taoLti\models\classes\Lis\LisOauthDataStore;
use oat\taoLti\models\classes\Lis\LisOauthService;
use oat\taoLti\models\classes\LtiAuthAdapter;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\ResourceLink\OntologyLink;
use oat\taoLti\models\classes\Security\Business\Contract\SecretKeyServiceInterface;
use oat\taoLti\models\classes\Security\Business\Service\SecretKeyService;
use oat\taoLti\models\classes\user\LtiUserFactoryService;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoLti\models\classes\user\OntologyLtiUserService;
use oat\taoLti\models\classes\user\UserService;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater
{
    /**
     * @param string $initialVersion
     *
     * @return string $versionUpdatedTo
     *
     * @throws common_Exception
     */
    public function update($initialVersion)
    {
        $this->skip('0', '1.2');

        if ($this->isVersion('1.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('1.3.0');
        }

        $this->skip('1.3.0', '1.5.2');

        // add teacher assistant role
        if ($this->isVersion('1.5.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('1.6.0');
        }
        $this->skip('1.6.0', '1.12.0');

        if ($this->isVersion('1.12.0')) {
            $service = $this->getServiceManager()->get(ExceptionInterpreterService::SERVICE_ID);
            $interpreters = $service->getOption(ExceptionInterpreterService::OPTION_INTERPRETERS);
            $interpreters['taoLti_models_classes_LtiException'] = ExceptionInterpreter::class;
            $service->setOption(ExceptionInterpreterService::OPTION_INTERPRETERS, $interpreters);
            $this->getServiceManager()->register(ExceptionInterpreterService::SERVICE_ID, $service);
            $this->setVersion('1.13.0');
        }

        $this->skip('1.13.0', '2.0.0');

        if ($this->isVersion('2.0.0')) {
            $service = new CookieVerifyService([
                CookieVerifyService::OPTION_VERIFY_COOKIE => true
            ]);
            $service->setServiceManager($this->getServiceManager());
            $this->getServiceManager()->register(CookieVerifyService::SERVICE_ID, $service);

            $this->setVersion('2.1.0');
        }

        $this->skip('2.1.0', '3.3.1');

        if ($this->isVersion('3.3.1')) {
            $service = new OntologyLtiUserService();
            $service->setServiceManager($this->getServiceManager());
            $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $service);

            $this->setVersion('3.4.0');
        }
        $this->skip('3.4.0', '3.4.4');

        if ($this->isVersion('3.4.4')) {
            /* option no longer has any effect
             $userService = $this->getServiceManager()->get(LtiUserService::SERVICE_ID);
            if ($userService instanceof OntologyLtiUserService) {
                $userService->setOption(OntologyLtiUserService::OPTION_TRANSACTION_SAFE, false);
                $userService->setOption(OntologyLtiUserService::OPTION_TRANSACTION_SAFE_RETRY, 1);
            }
            $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $userService);
            */

            $this->setVersion('3.5.0');
        }

        $this->skip('3.5.0', '3.6.0');

        if ($this->isVersion('3.6.0')) {
            if (!$this->getServiceManager()->has(LinkService::SERVICE_ID)) {
                $this->getServiceManager()->register(LinkService::SERVICE_ID, new OntologyLink());
            }
            $this->setVersion('3.7.0');
        }

        $this->skip('3.7.0', '6.0.0');

        if ($this->isVersion('6.0.0')) {
            /** @var ExceptionInterpreterService $exceptionInterpreterService */
            $exceptionInterpreterService = $this->getServiceManager()->get(ExceptionInterpreterService::SERVICE_ID);
            $interpretersList = $exceptionInterpreterService->getOption(
                ExceptionInterpreterService::OPTION_INTERPRETERS
            );
            // unregister old exception.
            if (array_key_exists('taoLti_models_classes_LtiException', $interpretersList)) {
                unset($interpretersList['taoLti_models_classes_LtiException']);
            }
            $interpretersList[LtiException::class] = ExceptionInterpreter::class;
            $exceptionInterpreterService->setOption(
                ExceptionInterpreterService::OPTION_INTERPRETERS,
                $interpretersList
            );
            $this->getServiceManager()->register(ExceptionInterpreterService::SERVICE_ID, $exceptionInterpreterService);
            $this->setVersion('6.0.1');
        }

        $this->skip('6.0.0', '6.3.3');

        if ($this->isVersion('6.3.3')) {
            $extensionManager = $this->getServiceManager()->get(common_ext_ExtensionsManager::SERVICE_ID);
            $extensionManager->getExtensionById('taoLti')->setConfig('auth', ['adapter' => LtiAuthAdapter::class]);
            $this->setVersion('6.4.0');
        }

        $this->skip('6.4.0', '6.5.0');

        if ($this->isVersion('6.5.0')) {
            $factoryAuth = new FactoryLtiAuthAdapterService();

            $this->getServiceManager()->register(FactoryLtiAuthAdapterServiceInterface::SERVICE_ID, $factoryAuth);

            $this->setVersion('6.6.0');
        }

        if ($this->isVersion('6.6.0')) {
            $ltiValidatorService = new LtiValidatorService([
                LtiValidatorService::OPTION_LAUNCH_DATA_VALIDATOR => new Lti11LaunchDataValidator()
            ]);
            $this->getServiceManager()->register(LtiValidatorService::SERVICE_ID, $ltiValidatorService);
            $this->setVersion('6.7.0');
        }

        $this->skip('6.7.0', '7.1.0');

        if ($this->isVersion('7.1.0')) {
            $userService = $this->getServiceManager()->get(\tao_models_classes_UserService::SERVICE_ID);
            $config = $userService->getOptions();
            $newLtiUserService = new UserService($config);
            $this->getServiceManager()->register(\tao_models_classes_UserService::SERVICE_ID, $newLtiUserService);
            $this->setVersion('7.2.0');
        }

        $this->skip('7.2.0', '7.3.1');

        if ($this->isVersion('7.3.1')) {
            $ltiUserFactory = new LtiUserFactoryService();
            $this->getServiceManager()->register(LtiUserFactoryService::SERVICE_ID, $ltiUserFactory);

            /** @var LtiUserService $ltiUserService */
            $ltiUserService = $this->getServiceManager()->get(LtiUserService::SERVICE_ID);
            $ltiUserService->setOption(LtiUserService::OPTION_FACTORY_LTI_USER, LtiUserFactoryService::SERVICE_ID);
            $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $ltiUserService);

            $this->setVersion('8.0.0');
        }

        $this->skip('8.0.0', '9.0.0');

        if ($this->isVersion('9.0.0')) {
            OntologyUpdater::syncModels();
            // Removed as of 9.2.0 due to autowiring
            // $this->getServiceManager()->register(ConsumerService::SERVICE_ID, new ConsumerService());
            // $this->getServiceManager()->register(ProviderService::SERVICE_ID, new ProviderService());

            $this->setVersion('9.1.0');
        }

        if ($this->isVersion('9.1.0')) {
            $this->getServiceManager()->unregister(ConsumerService::SERVICE_ID);
            $this->getServiceManager()->unregister('taoLti/ProviderService');
            $this->setVersion('9.2.0');
        }

        $this->skip('9.2.0', '10.2.0');

        if ($this->isVersion('10.2.0')) {
            if ($this->getServiceManager()->has(LtiProviderService::SERVICE_ID)) {
                /** @var LtiProviderService $ltiProviderService */
                $ltiProviderService = $this->getServiceManager()->get(LtiProviderService::SERVICE_ID);
                $ltiProviderService->setOption($ltiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS, [
                    new RdfLtiProviderRepository(),
                ]);
            } else {
                $ltiProviderService = new LtiProviderService([
                    LtiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS => [
                        new RdfLtiProviderRepository(),
                    ]
                ]);
            }
            $this->getServiceManager()->register(LtiProviderService::SERVICE_ID, $ltiProviderService);
            $this->setVersion('10.3.0');
        }

        $this->skip('10.3.0', '10.5.2');

        if ($this->isVersion('10.5.2')) {
            $ltiUserService = $this->getServiceManager()->get(LtiUserService::SERVICE_ID);
            if (!$ltiUserService->hasOption(LtiUserService::OPTION_FACTORY_LTI_USER)) {
                $ltiUserService->setOption(LtiUserService::OPTION_FACTORY_LTI_USER, LtiUserFactoryService::SERVICE_ID);
                $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $ltiUserService);
            }
            $this->setVersion('10.5.3');
        }

        $this->skip('10.5.3', '10.9.1');

        if ($this->isVersion('10.9.1')) {
            $this->getServiceManager()->register(
                LisOauthService::SERVICE_ID,
                new LisOauthService([
                    LisOauthService::OPTION_DATA_STORE => new LisOauthDataStore([
                        LisOauthDataStore::OPTION_NONCE_STORE => new NoNonce()
                    ])
                ])
            );
            $this->setVersion('11.0.0');
        }

        $this->skip('11.0.0', '11.5.0');

        if ($this->isVersion('11.5.0')) {
            $this->getServiceManager()->register(
                SecretKeyServiceInterface::SERVICE_ID,
                new SecretKeyService(40)
            );

            $this->setVersion('11.6.0');
        }

        $this->skip('11.6.0', '11.8.0');

        if ($this->isVersion('11.8.0')) {
            $lisOauthService = $this->getServiceManager()->get(LisOauthService::SERVICE_ID);

            if (!$lisOauthService->hasOption(OauthService::OPTION_LOCKOUT_SERVICE)) {
                $lisOauthService->setOption(OauthService::OPTION_LOCKOUT_SERVICE, new NoLockout());
                $this->getServiceManager()->register(LisOauthService::SERVICE_ID, $lisOauthService);
            }

            $this->setVersion('11.8.1');
        }

        $this->skip('11.8.1', '11.8.3');
    }
}
