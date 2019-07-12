extension-tao-lti
=================
extension to manage LTI services for TAO

# Configuration options:

## auth.conf.php

### Configuration option "config"

*Description:* specifies a single option as the 'adapter' key of the array. This adapter is to be used to authenticate LTI requests and is retrieved in [FactoryLtiAuthAdapterService](taoLti/models/classes/FactoryLtiAuthAdapterService.php).

*Possible values of the 'adapter key'':* 
* an instance of any class that implements the common_user_auth_Adapter interface

*Value examples:* 
* \['config' => \['adapter' => 'oat\\taoLti\\models\\classes\\LtiAuthAdapter'\]\]


## CookieVerifyService.conf.php

### Configuration option "config"

*Description:* whether to check if the 'session' request parameter matches the internal PHP session ID before launching an LTI tool

*Possible values of the 'adapter key'':* 
* true: enable the session check. 2 more HTTP redirects are needed
* false: disable the session check

## FactoryLtiAuthAdapter.conf.php
No options

## LtiUserFactory.conf.php
No options

## LtiUserService.conf.php

### Configuration option "factoryLtiUser"

*Description:* factory for producing LTI users

*Possible values:* 
* an instance of any class that implements the oat\taoLti\models\classes\user\LtiUserFactoryInterface interface

### Configuration option "transaction-safe" (only for OntologyLtiUserService implementation)

*Description:* not used

### Configuration option "transaction-safe-retry" (only for OntologyLtiUserService implementation)

*Description:* not used

### Configuration option "lti_ku_" (only for KvLtiUserService implementation)

*Description:* a prefix for storing taoId => ltiId connection in the key-value storage to look up lti users

*Possible values:* 
* any unique string

### Configuration option "lti_ku_lkp_" (only for KvLtiUserService implementation)

*Description:* a prefix for storing ltiId => taoId connection in the key-value storage to execute reverse lookup

*Possible values:* 
* any unique string

## LtiValidatorService.conf.php

### Configuration option "launchDataValidator"

*Description:* specifies a list of validators to be used for validating LTI launch data

*Possible values:* 
* a list of instances of any classes that implement the LtiValidatorInterface interface. Validators should throw LtiException in case of not valid data, return values are not considered

*Value examples:* 
* [ new oat\taoLti\models\classes\LaunchData\Validator\Lti11LaunchDataValidator() ]

## ResourceLink.conf.php
No options