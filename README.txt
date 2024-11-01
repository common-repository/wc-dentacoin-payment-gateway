=== Dentacoin Payment Gateway for WooCommerce ===
Contributors: mnedelchev94
Tags: crypto, cryptocurrency, crypto payments, DCN, Dentacoin, Bitcoin, Ethereum
Requires at least: 4.5
Tested up to: 5.4
Stable tag: trunk
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Dentacoin (DCN) is the first cryptocurrency for the global dental industry, earned via various oral health apps and accepted as means of payment by dental clinics, labs, shops, and other businesses in 36 countries around the world.

With Dentacoin Payment Gateway plugin, you can accept payments in Dentacoin digital currency directly on your WooCommerce store and extend the target audience of your business to a growing community of over 200K+ active users of Dentacoin smart apps. Through the apps, users earn DCN tokens for performing various valuable activities such as providing legitimate feedback to their dentist, taking dental surveys, and maintaining good oral hygiene.

Dentacoin is an Ethereum-based cryptocurrency and can be easily converted into traditional national currencies, stored for future value multiplication, or used for purchases from trade partners.


== FEATURES ==
* Enables Dentacoin payments on your online store
* Automatically converts prices to DCN by the current market value
* Free, simple to set up and easy to use
* Smooth, fast payments using generated QR codes
* Setup of a custom time frame for processing payments
* Based on secure blockchain technology

== HOW IT WORKS ==
1. Install and activate Dentacoin Payment Gateway plugin in your WooCommerce store. You can find setup guidelines under the Installation tab on the right.

2. Once activated, Dentacoin is listed as a payment option on the checkout page of your store.

3. When choosing Dentacoin as a payment option for their purchase, the customer is prompted to fill in a [digital wallet address](https://wallet.dentacoin.com) in the respective field.

4. Upon proceeding, a QR code is generated with your wallet address where the customer should send the payment.

5. Then, the client scans the QR code with his wallet app or copies it manually, fills in the amount in Dentacoin displayed on the Order Checkout page, and makes the payment.

6. The order remains with status “On hold”, awaiting the payment. Once it is successfully received in your wallet, the order status changes automatically to “Processing”. You are ready to proceed with the shipment of the purchased items.

7. For the customer, the order confirmation screen displays a message “Your payment was successfully processed” with a payment confirmation link.

8. If the payment is not processed within the time frame specified by you in the settings of the plugin, the order status will automatically change to “Failed”.

== Screenshots ==
1. Dentacoin Payment Gateway plugin settings in WooCommerce Payments tab


== FAQ ==

= What is Dentacoin? =

Dentacoin (DCN) is an Ethereum-based cryptocurrency that fuels a set of smart apps incentivizing users to perform activities beneficial to their oral health. It is used for rewards and payments by 100K+ individuals and 1.8K+ dental offices around the world. Learn more [here](http://dentacoin.com).

= How to install the plugin? =

Check the instructions in the Installation tab on the right.

= What are the most important settings to make? =

Firstly, you should enter the wallet address where you want to receive payments in Dentacoin. Then, set the time frame to wait for the buyer to complete the payment. For example, if you set the time frame to 1 hour, the customer must pay for their order within this time. Otherwise, the order status will automatically change to “Failed”.

= Are there any transaction charges to accept Dentacoin? =

No, receiving Dentacoin payments does not involve any additional charges. Only the buyer pays a small transaction fee in Ether (usually below 1$, [check average daily fees here](https://ethgasstation.info/)).

= Can I still accept other payments with this plugin? =

The plugin enables only Dentacoin payments but you can use it in combination with other plugins to accept various currencies and forms of payments.

= I installed and activated the plugin, but Dentacoin doesn’t show up as a payment option on the checkout page? =

To receive Dentacoin payments, you must enter your wallet address in the settings of Dentacoin Payment Gateway plugin. Go to the WooCommerce Payments tab to access them.

== Installation ==

Before you start, note that you need the following minimum requirements to implement the plugin:

WordPress Version: 4.5 or higher
PHP Version: 5.4 or higher

* Go to the administrative panel of your WooCommerce shop.
* In the left-side menu choose the tab “Plugins”, then press “Add a New Plugin”.
* Type into the search bar “Dentacoin”. The plugin will show in as the first result. Press the button “Install”.
* After the installation is finished, press “Activate”.
* Go to the WooCommerce plugin tab on the left, choose Settings, and then go to Payments. You will see Dentacoin Payment Gateway for WooCommerce. Click Manage.
* Enter your wallet address. This is where you will receive DCN payments. Don’t have a wallet? You can create one [here](https://wallet.dentacoin.com).
* Set the title and description of the payment option as you would like them to appear in your online shop e.g. Title: Dentacoin; Description: Ethereum-based cryptocurrency.
* Enter the maximum time frame to wait for payment from the customer before the order status turns to “Failed”. Click Save to keep the updated information.

== NOTES ==

The plugin has implemented the following external requests:
* [https://api.coingecko.com/api/v3/coins/dentacoin](https://api.coingecko.com/api/v3/coins/dentacoin) - GET request to read the current Dentacoin price converted into fiat (national) currencies. No parameters passed to the request. Provided by [https://www.coingecko.com](https://www.coingecko.com).
* https://methods.dentacoin.com/check-for-dcn-transfer – POST request to validate if payment was processed on the blockchain. Parameters passed to the request – Order ID, Customer Wallet Address, Shop administrator Wallet Address, Order price in Dentacoins. Provided by https://dentacoin.com.