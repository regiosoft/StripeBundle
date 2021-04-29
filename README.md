# RegiosoftStripeBundle
A simple Symfony bundle for Stripe Api.

# Only SMS/WhatsApp!

## Setup

### Step 1: Download RegiosoftStripeBundle using composer

Add Stripe Bundle in your composer.json:

```js
{
    "require": {
        "regiosoft/stripe-bundle": "^1.0"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update "regiosoft/stripe-bundle"
```


### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Regiosoft\StripeBundle\RegiosoftStripeBundle()
    );
}
```

### Step 3: Add configuration

``` yml
# app/config/config.yml
regiosoft:
        stripe:
            stripe_private_key:    %stripe_private_key%
```

## Usage

**Using service**

``` php
<?php
        $stripeClient = $this->get('stripe_client');
?>
```

##Example

###Create customer & subscription
``` php
<?php 
    $customer = $stripeClient->createCustomer($token, $email, $name, $phone);

    // Store customer information

    $subsciption = $stripeClient->createSubscription($customerId, $planId);

    // Store subscription information

?>
```
