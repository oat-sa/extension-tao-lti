extension-tao-lti
=================
extension to manage LTI provider services for TAO

#Reference
https://www.imsglobal.org/activity/learning-tools-interoperability

#Testing
http://ltiapps.net/test/tc.php

#Custom Parameters
Additionally, TAO accepts custom parameters to control the delivery engine behaviour.

```
proctored=<boolean>
```
Defines whether this test execution is to require proctoring. Overrides configured default. Requires extension-tao-proctoring
```
secure=<boolean>
```
Defines whether this test execution is to utilise TAO basic security. Overrides configured default. Requires extension-tao-security (in development)
```
max_attempts=<integer>
```
Overrides the max attempts setting for a delivery
```
approved_os=<class name>
```
Restricts the OS version to those specified in the provided class and sub-classes
```
approved_browser=<class name>
```
Restricts the browser version to those specified in the provided class and sub-classes
```
x_tao_tools={"areaMasking":<boolean>, "magnifier":<boolean>, "eliminator":<boolean>, "lineReader":<boolean>}
```
Enables / disables the defined tool. Requires the tool to be configured in TAO and within the item category
```
custom_theme=theme_name<theme_number>
```
Switches to a custom theme. Requires the theme to be configured on a platform level
