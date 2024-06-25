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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\models\classes;

use common_Exception;
use common_exception_Error;
use common_http_Request;
use common_session_SessionManager;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\GenerisRdf;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\LaunchPresentationClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Message\Payload\MessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\session\SessionService;
use oat\tao\model\session\Context\TenantDataSessionContext;
use oat\tao\model\session\Context\UserDataSessionContext;
use oat\tao\model\TaoOntology;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use oat\taoLti\models\classes\user\Lti1p3User;
use Psr\Log\LogLevel;

class LtiService extends ConfigurableService
{
    public const LIS_CONTEXT_ROLE_NAMESPACE = 'urn:lti:role:ims/lis/';

    public const LTICONTEXT_SESSION_KEY = 'LTICONTEXT';

    public const DEFAULT_USER_EXTENSION = 'tao/Main/index?structure=items&ext=taoItems';

    public function createLtiSession(common_http_Request $request)
    {
        try {
            /** @var FactoryLtiAuthAdapterService $factoryAuth */
            $factoryAuth = $this->getServiceLocator()->get(FactoryLtiAuthAdapterServiceInterface::SERVICE_ID);
            $adapter = $factoryAuth->create($request);
            $user = $adapter->authenticate();
            $session = new TaoLtiSession($user);

            $this->getServiceLocator()->propagate($session);
            return $session;
        } catch (LtiInvalidVariableException $e) {
            $this->getServiceLocator()->get(LoggerService::SERVICE_ID)
                ->log(LogLevel::INFO, $e->getMessage());
            throw new LtiException(
                __('You are not authorized to use this system'),
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    public function createLti1p3Session(
        LtiMessagePayloadInterface $messagePayload,
        core_kernel_classes_Resource $user = null
    ) {
        try {
            /** @var RegistrationRepositoryInterface $registrationRepository */
            $registrationRepository = $this->getServiceLocator()
                ->getContainer()
                ->get(RegistrationRepositoryInterface::class);

            $issuer = $messagePayload->getMandatoryClaim(MessagePayloadInterface::CLAIM_ISS);
            $clientId = $messagePayload->getMandatoryClaim(MessagePayloadInterface::CLAIM_AUD)[0];

            $registration = $registrationRepository->findByPlatformIssuer($issuer, $clientId);

            if ($registration === null) {
                throw new LtiException(
                    sprintf('Cannot find a registration with issuer "%s" and client ID "%s"', $issuer, $clientId),
                    LtiErrorMessage::ERROR_UNAUTHORIZED
                );
            }

            $ltiUser = new Lti1p3User(
                LtiLaunchData::fromLti1p3MessagePayload($messagePayload, $registration->getPlatform()),
                $user ? $user->getUri() : null
            );

            if ($user !== null) {
                $userLatestExtension = new core_kernel_classes_Property(TaoOntology::PROPERTY_USER_LAST_EXTENSION);

                //do not consider lti users with UserFirstTime as true because they should not see the help modal
                $ltiUser->setUserFirstTimeUri(GenerisRdf::GENERIS_FALSE);
                $ltiUser->setUserLatestExtension(self::DEFAULT_USER_EXTENSION);


                $userLatestExtensionValue = (string)$user->getOnePropertyValue($userLatestExtension);
                if (!empty($userLatestExtensionValue)) {
                    $ltiUser->setUserLatestExtension($userLatestExtensionValue);
                }
            }

            $ltiUser->setRegistrationId($registration->getIdentifier());

            $contexts = [];
            if ($clientId) {
                $userId = $messagePayload->getUserIdentity();
                $clientIdParts = explode('-', $clientId);
                $contexts = [
                    new UserDataSessionContext(
                        $userId->getIdentifier(),
                        $userId->getIdentifier(),
                        $userId->getName(),
                        $userId->getEmail(),
                        $userId->getLocale() ?? $this->getLocaleFromMessagePayload($messagePayload)
                    ),
                    new TenantDataSessionContext(end($clientIdParts))
                ];
            }

            $session = TaoLtiSession::fromVersion1p3($ltiUser, $contexts);

            $this->getServiceLocator()->propagate($session);


            return $session;
        } catch (LtiInvalidVariableException $e) {
            $this->logInfo($e->getMessage());

            throw new LtiException(
                $e->getMessage(),
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    /**
     * start a session from the provided OAuth Request
     *
     * @param  common_http_Request  $request
     *
     * @throws LtiException
     * @throws common_Exception
     * @throws \ResolverException
     */
    public function startLtiSession(common_http_Request $request)
    {
        $this->getServiceLocator()->get(SessionService::SERVICE_ID)->setSession($this->createLtiSession($request));
    }

    public function startLti1p3Session(
        LtiMessagePayloadInterface $messagePayload,
        core_kernel_classes_Resource $user = null
    ) {
        $this->getServiceLocator()->get(SessionService::SERVICE_ID)->setSession(
            $this->createLti1p3Session($messagePayload, $user)
        );
    }

    /**
     * Returns the current LTI session
     *
     * @return TaoLtiSession
     * @throws LtiException
     * @throws common_exception_Error
     */
    public function getLtiSession()
    {
        $session = common_session_SessionManager::getSession();
        if (!$session instanceof TaoLtiSession) {
            throw new LtiException(__FUNCTION__ . ' called on a non LTI session', LtiErrorMessage::ERROR_SYSTEM_ERROR);
        }
        $this->getServiceLocator()->propagate($session);

        return $session;
    }

    /**
     * @param $key
     * @return mixed
     * @throws LtiException
     */
    public function getCredential($key)
    {
        $class = new core_kernel_classes_Class(ConsumerService::CLASS_URI);
        $instances = $class->searchInstances([TaoOntology::PROPERTY_OAUTH_KEY => $key], ['like' => false]);
        if (count($instances) == 0) {
            throw new LtiException('No Credentials for consumer key ' . $key, LtiErrorMessage::ERROR_UNAUTHORIZED);
        }
        if (count($instances) > 1) {
            throw new LtiException(
                'Multiple Credentials for consumer key ' . $key,
                LtiErrorMessage::ERROR_INVALID_PARAMETER
            );
        }

        return current($instances);
    }

    /**
     * Returns the LTI Consumer resource associated to this lti session
     *
     * @access public
     * @param  LtiLaunchData  $launchData
     * @return core_kernel_classes_Resource resource of LtiConsumer
     * @throws LtiVariableMissingException
     * @author Joel Bout, <joel@taotesting.com>
     * @deprecated use LtiLaunchData::getLtiConsumer instead
     */
    public function getLtiConsumerResource($launchData)
    {
        return $launchData->getLtiConsumer();
    }

    /**
     * @return LtiService
     * @deprecated
     */
    public static function singleton()
    {
        return ServiceManager::getServiceManager()->get(static::class);
    }

    private function getLocaleFromMessagePayload(LtiMessagePayloadInterface $messagePayload): ?string
    {
        if ($messagePayload && $messagePayload->getLaunchPresentation() instanceof LaunchPresentationClaim) {
            return $messagePayload->getLaunchPresentation()->getLocale();
        }

        return null;
    }
}
