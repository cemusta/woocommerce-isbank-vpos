# Innova İşbank Vpos Payment Gateway #
###################

**Contributors:** cemusta

**Tags:** woocommerce, payment gateway, payment gateways, işbank, innova

**Requires at least:** 4.6

**Tested up to:** 4.6

**Stable tag:** 4.6.0

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

This is a woocommerce gateway extension for accepting payment on your WooCommerce using Innova/İşbank Vpos (via Visa Card and MasterCard).

## Description ##

This is a payment gateway specifically written for Innova/İşbank Vpos using its latest documentation v1.7 (can be found in resources section).

With this WooCommerce Payment Gateway plugin, you will be able to accept the following payment methods in your shop:

* __MasterCard__
* __Visa Card__

## Requirements  ##

-  PHP version 5.4+
-  WordPress 4.6+
-  WooCommerce 2.6+

## Installation  ##

### Automatic Installation  ###

Automatic installation isn't ready at the moment.

### Manual Installation ###

> 1. Unzip the files and upload the folder into your plugins folder (wp-content/plugins/) overwriting old versions if they exist.
> 2. Activate the plugin in your WordPress admin area.
> 3. Open the settings page for WooCommerce and click the "Checkout" tab.
> 4. Click on the sub-item "Innova İşbank Vpos".
> 5. Configure your settings accordingly.

## Configuration ##

Login to your WordPress control panel and go to WooCommerce -> Settings. Then click into the Checkout tab and click on the sub-item "Innova İşbank Vpos".


* __Enable/Disable__ - enable or disable Innova İşbank Vpos Payment Gateway.
* __Title__ - allows you to determine what your customers will see this payment option as on the checkout page.
* __Description__ - controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept.
* __Test__  - enable or disable Test mode. In test mode no real transactions will be made.
* __Test/Live Merchant ID__  - enter your Merchant ID.
* __Test/Live Password__  - enter your Merchant Password.
* Click on __Save Changes__.


## Resources ##

- [jQuery Credit Card Validator] - Jquery validator used for credit card validation. ([Validator Git Link])
- [Innova İşbank documentation] - Innova İşbank Vpos documentation

   [Innova İşbank documentation]: <http://sanalpos.innova.com.tr/doc/ISBANK.RAR>
   [Validator Git Link]: <https://github.com/PawelDecowski/jQuery-CreditCardValidator/>
   [jQuery Credit Card Validator]: <http://jquerycreditcardvalidator.com/>
