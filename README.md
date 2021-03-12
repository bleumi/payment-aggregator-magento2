# Bleumi for Magento 2

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/bleumi/payment-aggregator-magento2/master/LICENSE)

[Bleumi](https://bleumi.com) is an all-in-one global Payment Orchestration Platform. With this extension, customers can accept Traditional (PayPal, Credit/Debit Card) or Digital Currency (USD Coin, Tether, Monerium) payments in your Magento 2 Store.

## Installation

### Prerequisites

To use this plugin with your Magento 2 store you will need:

* [Magento](https://magento.com/) (tested from 2.3.4, tested up to 2.4.1)

If you want to know if Bleumi supports your system or if you face any integration problem, please reach us at support@bleumi.com

### Overview

**Step 1: Configure your Bleumi Account:**

* Add gateway accounts in the [Bleumi Portal](https://account.bleumi.com/account/?app=paymentlink&tab=gateway)
* Configure and Enable payment methods for each gateway account
* Create an API key from the [Integration Settings Screen](http://account.bleumi.com/account/?app=paymentlink&tab=integration)

**Step 2: Install Bleumi Extension in Your Magento 2 Store**

Run the following commands in the root folder of your Magento installation,

* composer require bleumi/payment-aggregator-magento2
* bin/magento setup:upgrade
* bin/magento cache:clean
* bin/magento cache:flush
* bin/magento setup:di:compile
* bin/magento setup:static-content:deploy

**Step 3: Configure Bleumi Extension in Your Magento 2 Store**

* Customize the Title & Payment Options field. These are shown to the buyer at the Checkout screen
* Add the API key created from the [Integration Settings Screen](http://account.bleumi.com/account/?app=paymentlink&tab=integration)
* Enable the extension
* Click on the "Save Config" button

## License

Copyright 2020 Bleumi, Inc.

Code licensed under the [MIT License](LICENSE).