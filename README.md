# Lunar Online Payments for X-Cart 4

## Supported X-Cart versions

*The plugin has been tested with most versions of X-Cart v4 at every iteration. We recommend using the latest version of X-Cart v4, but if that is not possible for some reason, test the plugin with your X-Cart version and it would probably function properly.*

## Installation

Once you have installed X-Cart v4, follow these simple steps:
  1. Signup at [lunar.app](https://lunar.app) (it’s free)  
  1. Create an account
  1. Create an app key for your website
  1. Follow the instructions in the [install.md](/install.md) file
  1. Insert the app key and your public key in the settings for the payment gateway you just created
  

## Updating settings

Under the payment method settings, you can:
 * Update the payment title that shows up in the checkout  
 * Update the payment method description in the payment gateways list
 * Add app & public keys
 * Change the capture type (Auth only/Auth and capture)
 
 ## How to
 
 1. Capture
 * In Auth and Capture mode, the orders are captured automatically
 * In Auth only mode you can capture an order On the edit screen. You will find a capture button bellow the customer notes
 2. Refund
   * You can refund an order On the edit screen. You will find a Decline button bellow the customer notes
 3. Void
   * You can void an order On the edit screen. You will find a Decline button bellow the customer notes. The void only applies if the order was not captured. Otherwise it will initiate a refund. 
