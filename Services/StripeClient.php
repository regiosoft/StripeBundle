<?php

namespace DriveOp\StripeBundle\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\PaymentIntent;
use Exception;


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
     * @return Customer|string
     */
    public function createCustomer($token, $email, $name, $phone)
    {

        try {
            $customer = Customer::create(array(
                    "source" => $token,
                    "name" => $name,
                    "email" => $email,
                    'phone' => $phone
                )
            );
            return $customer;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $customerId
     * @return Customer|string
     */
    public function getCustomer($customerId)
    {
        try {
            $customer = Customer::retrieve($customerId);
            return $customer;
        } catch (Exception $error) {
            return $error->getMessage();
        }

    }

    /**
     * @param $customerId
     * @param $source
     * @return string|\Stripe\Source
     */
    public function addNewCard($customerId, $source)
    {

        try {
            $source = Customer::createSource(
                $customerId,
                ['source' => $source]
            );
            return $source;
        } catch (Exception $error) {
            return $error->getMessage();
        }
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
     * @return Subscription|string
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

        try {
            $subscription = Subscription::create($subscriptionOptions);
            return $subscription;
        } catch (Exception $error) {
            return $error->getMessage();
        }

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
        Subscription::update(
            $subscriptionId,
            [
                'cancel_at_period_end' => true,
            ]
        );
        //$subscription = $this->getSubscription($subscriptionId);
        //$subscription->cancel();
    }

    /**
     * @param $subscriptionId
     */
    public function activateSubscription($subscriptionId)
    {
        Subscription::update(
            $subscriptionId,
            [
                'cancel_at_period_end' => false,
            ]
        );
    }

    /**
     * @param $subscriptionId
     * @param $cardId
     */
    public function updateSubscriptionSource($subscriptionId, $cardId)
    {
        Subscription::update(
            $subscriptionId,
            [
                'default_source' => $cardId,
            ]
        );
    }

    #########################
    ##        Charge       ##
    #########################

    /**
     * @param $customer
     * @param $amount
     * @param $description
     * @param string $currency
     * @return Charge|string
     */
    public function createCharge($customer, $amount, $description, $currency = 'mxn')
    {

        try {
            $charge = Charge::create([
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'customer' => $customer,
            ]);
            return $charge;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $charge
     * @return Charge|string
     */
    public function createRefund($charge)
    {

        try {
            $refund = Refund::create([
                'charge' => $charge
            ]);
            return $refund;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    #########################
    ##   Payment intent    ##
    #########################


    /**
     * @param $customer
     * @param $amount
     * @param $description
     * @param $confirm
     * @param string $currency
     * @return PaymentIntent|string
     */
    public function createPaymentIntent(Customer $customer, $amount, $description, $confirm, $currency = 'mxn')
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'customer' => $customer,
                'amount' => $amount,
                'description' => $description,
                'currency' => $currency,
                'confirm' => $confirm,
                'payment_method' => $customer->default_source
            ]);
            return $paymentIntent;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $paymentIntentId
     * @return PaymentIntent|string
     */
    public function confirmPaymentIntent($paymentIntentId)
    {

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        } catch (Exception $error) {
            return $error->getMessage();
        }

        try {
            $paymentIntent->confirm();
            return $paymentIntent;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

}