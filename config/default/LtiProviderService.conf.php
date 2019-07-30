<?php
use oat\oatbox\service\EnvironmentVariable;
use oat\taoLti\models\classes\LtiProvider\ConfigurableLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;

return new LtiProviderService([
    LtiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS => [
        new RdfLtiProviderRepository(),
        new ConfigurableLtiProviderRepository([
            ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST_URL => new EnvironmentVariable(ConfigurableLtiProviderRepository::ENV_LTI_PROVIDER_LIST_URL)
        ]),
    ]
]);
