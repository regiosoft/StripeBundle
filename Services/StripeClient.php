<?php

namespace DriveOp\StripeBundle\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Charge;

class StripeClient
{


    public function __construct($stripe_private_key)
    {
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        Stripe::setApiKey($stripe_private_key);
    }


    #########################
    ##       Customer      ##
    #########################

    /**
     * @param $token
     * @param $email
     * @param $name
     * @param $phone
     * @return Customer
     */
    public function createCustomer($token, $email, $name, $phone)
    {
        return Customer::create(array(
                "source" => $token,
                "name" => $name,
                "email" => $email,
                'phone' => $phone
            )
        );
    }

    /**
     * @param $customerId
     * @return Customer
     */
    public function getCustomer($customerId)
    {
        return Customer::retrieve($customerId);
    }

    /**
     * @param $customerId
     * @param $source
     * @return Card
     */
    public function addNewCard($customerId, $source)
    {
        return Customer::createSource(
            $customerId,
            ['source' => $source]
        );
    }

    /**
     * @param $customerId
     * @return Customer
     */
    public function updateCustomerDefaultSource($customerId, $sourceId)
    {
        return Customer::update(
            $customerId,
            ['default_source' => $sourceId]
        );
    }

    #########################
    ##     Subscription    ##
    #########################

    /**
     * @param $customerId
     * @param $planId
     * @param $cardId
     * @param null $trialEnd
     * @param null $billingCycleAnchor
     * @param null $coupon
     * @return Subscription
     */
    public function createSubscription($customerId, $planId, $cardId = null, $trialEnd = null, $billingCycleAnchor = null, $coupon = null)
    {
        $subscriptionOptions = [
            'customer' => $customerId,
            'items' => [['plan' => $planId]]
        ];

        if ($cardId) $subscriptionOptions['default_source'] = $cardId;
        if ($trialEnd) $subscriptionOptions['trial_end'] = $trialEnd;
        if ($billingCycleAnchor) $subscriptionOptions['billing_cycle_anchor'] = $billingCycleAnchor;
        if ($coupon) $subscriptionOptions['coupon'] = $coupon;

        return Subscription::create($subscriptionOptions);
    }

    /**
     * @param $subscriptionId
     * @return Subscription
     */
    public function getSubscription($subscriptionId)
    {
        return Subscription::retrieve($subscriptionId);
    }

    /**
     * @param $subscriptionId
     */
    public function cancelSubscription($subscriptionId)
    {
        $subscription = $this->getSubscription($subscriptionId);
        $subscription->cancel();
    }


    #########################
    ##        Charge       ##
    #########################

    /**
     * @param $customer
     * @param $amount
     * @param $description
     * @param string $currency
     * @return Charge
     */
    public function createCharge($customer, $amount, $description, $currency = 'mxn')
    {
        return Charge::create([
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'customer' => $customer,
        ]);
    }

}