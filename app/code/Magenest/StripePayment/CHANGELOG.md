# Change Log
All notable changes to this extension will be documented in this file.
This extension adheres to [Magenest](http://magenest.com/).


Stripe compatible with 
```
Magento Commerce 2.1.x, 2.2.x, 2.3.x
Magento OpenSource 2.1.x, 2.2.x, 2.3.x
```
## [2.4.4] - 2020-08-31
-   Add: Magento 2.4 compatibility

## [2.4.3] - 2020-05-20
-   Add: Stripe Payment Intent 2.0
-   Add: Save Card For Payment Intent 2.0

## [2.4.2] - 2019-10-08
-   Improve performance and security
-   Fix: Issue round order total amount with 1 cent different
-   Fix: Issue Order status was suspected fraud
-   Fix: Cannot update some adminhtml config in store view scope
-   Fix: ApplePay button not visible in OneStepCheckout page
-   Fix: Issue when using multiple stripe account for multiple website

## [2.4.1] - 2019-09-03
-   Add: Payment action select for Stripe Payment Intent
-   Add: New Order status select for Stripe Payment Intent
-   Add: Display credit card type for Stripe Payment Intent
-   Add: Manual or Automatic capture for Stripe Payment Intent
-   Fix bug: Stripe Payment Intents sent mass request in checkout page

## [2.4.0] - 2019-08-12
-   Support for SCA compliant payments (Stripe Payment Intents and Stripe Checkout)
-   Add: Stripe Checkout https://stripe.com/docs/payments/checkout
-   Add: Stripe Payment Intents https://stripe.com/docs/payments/payment-intents
-   Add: Stripe SEPA direct debit payment
-   Add: Browser https validate to improve security
-   Improve Performance and security
-   Fix bug: validate public key
-   Fix bug: get customer email
-   Fix bug: form key validate fail with Stripe Ideal
-   Fix bug: WechatPay used store currency

## [2.3.0] - 2019-04-18
-   Add: Payment Intents API
-   Add: WechatPay Payment
-   Add: Support multiple language
-   Fix: Place order with applepay in catalog page
-   Fix: Conflict javascript code
-   Fix: Place order in adminhtml
-   Fix: Missing credit card data in order
-   Fix: Handle 3d secure response
-   Fix: Error display credit card field in save card page

## [2.2.3] - 2019-01-10
-   Improve security and performance
-   Upgrade: Stripe payment sdk
-   Add: Stripe checkout button in product detail page.
-   Add: Stripe checkout button in Shopping cart.
-   Fix: Process payment with source: Ideal
-   Fix: MOTO transaction require customer id
-   Fix: Delete card timeout 
-   Fix: Display save card list in adminhtml
-   Remove: Collect billing and shipping address stripe Iframe

## [2.2.2] - 2018-12-11
Ready for Magento 2.3
-   Add: Applepay button in Stripe Card
-   Add: Payment Instruction in payment method
-   Add: Statement descriptor
-   Add: Api register applepay domain
-   Upgrade: Stripe API
-   Fix bug: missing ApplePay button in checkout page

## [2.2.1] - 2018-10-26
-   Fix bug customer don't have order confirmation email
-   Fix bug order cancelled/refund unexpected
-   Fix bug payment cannot charge amount
-   Fix bug customer double click, prevent duplicate response
-   Fix bug order sometime response null from checkout session
-   Fix Web hook processing: webhook now also listen 3d secure payment
-   Fix stripe icon missing in backend html
-   Fix bug magento sometime cannot get public key
-   Fix bug Applepay, Google display wrong amount when using OneStepcheckout
-   Fix display all stripe message when payment error
-   Fix bug payment error on Multibanco method
-   Fix bug save card error when.
-   Fix bug refund error when using direct api
-   Webhook now also processing payment, handle when customer close their browser after authorize payment
-   Add debug file line
-   Add validate quote info when customer submit order
-   Upgrade stripe library sdk

## [2.2.0] - 2018-08-04
-   Add Stripe Library v6.13.0
-   Add Stripe WebHooks to get payment notification
-   Add Alipay Payments with Sources
-   Add Bancontact Payments with Sources
-   Add EPS Payments with Sources
-   Add DEAL Payments with Sources
-   Add Multibanco Payments with Sources
-   Add P24 Payments with Sources
-   Add SOFORT Payments with Sources

## [2.0.5] - 2018-05-10
### Added
-   Upgrade API to lastest version 2018-02-28
-   Working with all One Step Checkout
-   Stripe Element 
-   Stripe direct API
-   Stripe Microsoft Pay
-   Multiple language for stripe iframe
-   Option for Use customer save card in Backend order
### Fixed
-   Minify js library error
-   Fix bug Terms and Conditions at payment page 
-   error show save card section in customer_account
### Removed
-   Remove Bitcoin payment


## [2.0.0] - 2017-12-27
Stripe now compatible with 
```
Magento Commerce 2.1.x, 2.2.x, 
Magento OpenSource 2.1.x, 2.2.x
```
### Added
-   Improve security
-   Support: Stripe.js v3
-   Support: Apple Pay
-   Support: Android Pay(Pay with Google)
-   Support: Giro Pay
-   Support: Alipay
-   Add validate payment source when receive from customer
-   Stripe logger will stored in var/log/stripe
-   Add sort order option in backend
-   Add Payment Instruction text box in backend
-   Add support information in backend
### Fixed
-   Save card, delete card error
-   Fix bug response duplicated. 
### Removed
-   Remove dependency with Stripe Library (Don't need to run `composer require stripe/stripe-php`)
-   Remove option enable debug log

## [1.0.4] - 2017-17-16
### Added
-   User can save 3d secure card
### Fixed
-   Fix bug send email for customer
-   Fix bug order state
-   Fix bug show message error.
### Removed
-   Alipay (current not support)

## [1.0.3] - 2017-06-12
### Added
-   3d secure action
-   Admin payment
-   Payment with source
### Fixed
-   iframe payment
-   Fix bug shipping address

## [1.0.2] - 2017-05-19
### Added
-   3d secure check
### Fixed
-   iframe payment

## [1.0.1] - 2016-07-30
### Added
1. Magento 2.1 compatible

## [1.0.0] - 2016-06-15
### Added
1. Allow customers to checkout using Stripe Payment Gateway
2. Allow admins to easily tweak and manage payments via Stripe
