<?php
namespace Regiosoft\StripeBundle\Services;
use Stripe\Invoice;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Plan;
use Stripe\Product;
use Stripe\Source;
use Exception;
use Stripe\Token;

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
                    "phone" => $phone
                )
            );
            return $customer;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $token
     * @param $email
     * @param $name
     * @param $phone
     * @param $address
     * @return Customer|string
     */
    public function createCustomerWithAddress($token, $email, $name, $phone, $address)
    {
        try {
            $customer = Customer::create(array(
                    "source" => $token,
                    "name" => $name,
                    "email" => $email,
                    "phone" => $phone,
                    "address" => $address
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
     * @return Customer|string
     */
    public function getAllSources($customerId)
    {
        try {
            $cards = Customer::allSources($customerId);
            return $cards;
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
     * @param $cardId
     * @return Source
     */
    public function getCard($customerId, $cardId)
    {
        return Customer::retrieveSource(
            $customerId,
            $cardId
        );
    }

    /**
     * @param $customerId
     * @param $cardId
     * @return Source
     */
    public function deleteCard($customerId, $cardId)
    {
        return Customer::deleteSource(
            $customerId,
            $cardId
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
    ##        Card         ##
    #########################

    /**
     * @param $data
     * @return Source|string
     */
    public function createToken($data)
    {
        try {
            $token = Token::create([
                'card' => [
                    'name' => $data['name'],
                    'number' => $data['number'],
                    'exp_month' => $data['exp_month'],
                    'exp_year' => $data['exp_year'],
                    'cvc' => $data['cvc'],
                    'address_zip' => $data['zip_code']
                ]
            ]);
            return $token;
        } catch (Exception $error) {
            return $error->getMessage();
        }
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
        if ($cardId) $subscriptionOptions['default_payment_method'] = $cardId;
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
                'default_payment_method' => $cardId,
            ]
        );
    }

    /**
     * @param $planId
     * @return Plan
     */
    public function getPlan($planId)
    {
        return Plan::retrieve($planId);
    }

    /**
     * @param $subscriptionId
     * @param $planId
     * @return Subscription
     */
    public function updateSubscriptionPlan($subscriptionId, $planId)
    {
        $subscription = Subscription::retrieve($subscriptionId);
        return Subscription::update(
            $subscriptionId,
            [
                'billing_cycle_anchor' => 'now',
                'proration_behavior' => 'create_prorations',
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $planId,
                    ],
                ],
            ]
        );
    }

    #########################
    ##       Product       ##
    #########################

    /**
     * @param $data
     * @return Product
     */
    public function createProduct($data)
    {
        return Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * @param $productId
     * @param $data
     * @return Product
     */
    public function updateProduct($productId, $data)
    {
        return Product::update($productId, [
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    /**
     * @param $productId
     * @return Product
     */
    public function getProduct($productId)
    {
        return Product::retrieve($productId);
    }

    #########################
    ##        Price        ##
    #########################

    /**
     * @param $productId
     * @param $data
     * @return Price
     */
    public function createPrice($productId, $data)
    {
        return Price::create([
            'unit_amount' => $data['amount'],
            'currency' => $data['currency'],
            'recurring' => ['interval' => $data['interval']],
            'product' => $productId,
        ]);
    }

    /**
     * @param $productId
     * @param $data
     * @return Price
     */
    public function createOneTimePrice($productId, $data)
    {
        return Price::create([
            'unit_amount' => $data['amount'],
            'currency' => $data['currency'],
            'product' => $productId,
        ]);
    }

    /**
     * @param $priceId
     * @return Price
     */
    public function getPrice($priceId)
    {
        return Price::retrieve($priceId);
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
     * @param $customer
     * @param $amount
     * @param $description
     * @param string $currency
     * @return Charge|string
     */
    public function createChargeFromSource($customer, $source, $amount, $description, $currency = 'mxn')
    {
        try {
            $charge = Charge::create([
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'customer' => $customer,
                'source' => $source,
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
    public function createPaymentIntent(Customer $customer, $amount, $description, $confirm, $currency = 'mxn', $cardId = null, $returnUrl = '')
    {
        if (!$cardId) $cardId = $customer->default_source;
        try {
            $paymentIntent = PaymentIntent::create([
                'customer' => $customer,
                'setup_future_usage' => 'off_session',
                'amount' => $amount,
                'description' => $description,
                'currency' => $currency,
                'confirm' => $confirm,
                'payment_method' => $cardId,
                'return_url' => $returnUrl
            ]);
            return $paymentIntent;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $amount
     * @param $description
     * @param $confirm
     * @param string $currency
     * @return PaymentIntent|string
     */
    public function createInstallmentsPaymentIntent($amount, $description, $confirm, $currency = 'mxn', $paymentMethodId, $customer)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'description' => $description,
                'currency' => $currency,
                'confirm' => $confirm,
                'customer' => $customer,
                'payment_method' => $paymentMethodId,
                'payment_method_options' => [
                    'card' => [
                        'installments' => [
                            'enabled' => true
                        ]
                    ]
                ]
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
    public function confirmPaymentIntent($paymentIntentId, $confirm_data = [])
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        } catch (Exception $error) {
            return $error->getMessage();
        }
        try {
            $paymentIntent->confirm($params = $confirm_data);
            return $paymentIntent;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $paymentIntentId
     * @return Refund|string
     */
    public function createPaymentIntentRefund($paymentIntentId)
    {
        try {
            $refund = Refund::create([
                'payment_intent' => $paymentIntentId
            ]);
            return $refund;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $paymentIntentId
     * @return PaymentIntent
     */
    public function getPaymentIntent($paymentIntentId)
    {
        return PaymentIntent::retrieve(
            $paymentIntentId
        );
    }

    #########################
    ##   Setup intent    ##
    #########################

    /**
     * @param $customer
     * @param $amount
     * @param $description
     * @param $confirm
     * @param string $currency
     * @return PaymentIntent|string
     */
    public function createSetupIntent(Customer $customer, $confirm)
    {
        try {
            $paymentIntent = SetupIntent::create([
                'customer' => $customer,
                'confirm' => $confirm,
                'payment_method' => $customer->default_source
            ]);
            return $paymentIntent;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    #########################
    ##       Invoice       ##
    #########################

    /**
     * @param $invoiceId
     * @return Invoice
     */
    public function getInvoice($invoiceId)
    {
        return Invoice::retrieve($invoiceId);
    }


}