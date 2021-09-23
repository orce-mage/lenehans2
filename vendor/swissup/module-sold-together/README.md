# SoldTogether

When it comes to sales growth you may focus on cross-selling strategy. Build your own strategy with our module.

### Installation

#### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)
 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](http://docs.swissuplabs.com/m2/extensions/soldtogether/installation/composer/) - **recommended**
    - Download archive and use [manual installation](http://docs.swissuplabs.com/m2/extensions/soldtogether/installation/manual/)

#### For Swissuplabs developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-sold-together --prefer-source
bin/magento setup:upgrade
```

### Usage

Module adds "Frequntly bought Together" and "Customer Also Bought" blocks to product page under product describtion.

#### Emails

You can add soldtogether blocks to the sales email template using the following code:

```
{{block class='Swissup\\SoldTogether\\Block\\Email\\Customer' area='frontend' template='Swissup_SoldTogether::email/customer.phtml' order=$order}}
{{block class='Swissup\\SoldTogether\\Block\\Email\\Order' area='frontend' template='Swissup_SoldTogether::email/order.phtml' order=$order}}
```

#### CLI commands

```bash
bin/magento swissup:soldtogether:customer:reindex
bin/magento swissup:soldtogether:order:reindex
```
