# Swissup Checkout

Dummy checkout module. It's purpose is to add swissup menu and config sections.

## Installation

### For clients

Please do not install this module. It will be installed automatically as a dependency.

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-checkout --prefer-source
bin/magento module:enable Swissup_Checkout
bin/magento setup:upgrade
```
