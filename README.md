# TAO _taoLti_ extension

![TAO Logo](https://github.com/oat-sa/taohub-developer-guide/raw/master/resources/tao-logo.png)

![GitHub](https://img.shields.io/github/license/oat-sa/extension-tao-lti.svg)
![GitHub release](https://img.shields.io/github/release/oat-sa/extension-tao-lti.svg)
![GitHub commit activity](https://img.shields.io/github/commit-activity/y/oat-sa/extension-tao-lti.svg)

> Extension to manage LTI provider services for TAO


## Installation instructions

These instructions assume that you have already a TAO installation on your system. If you don't, go to
[package/tao](https://github.com/oat-sa/package-tao) and follow the installation instructions.

If you installed your TAO instance through [package-tao](https://github.com/oat-sa/package-tao),
`oat-sa/extension-tao-lti` is very likely already installed. You can verify this under _Settings -> Extension
manager_, where it would appear on the left hand side as `taoLti`. Alternatively you would find it in
the code at `/config/generis/installation.conf.php`.

_Note, that you have to be logged in as System Administrator to do this._

Add the extension to your TAO composer and to the autoloader:
```bash
composer require oat-sa/extension-tao-lti
```

Install the extension on the CLI from the project root:

**Linux:**
```bash
sudo php tao/scripts/installExtension oat-sa/extension-tao-lti
```

**Windows:**
```bash
php tao\scripts\installExtension oat-sa/extension-tao-lti
```

As a system administrator you can also install it through the TAO Extension Manager:
- Settings (the gears on the right hand side of the menu) -> Extension manager
- Select _taoLti_ on the right hand side, check the box and hit _install_

## Reference
https://www.imsglobal.org/activity/learning-tools-interoperability

## Testing
http://ltiapps.net/test/tc.php

## Custom Parameters
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
