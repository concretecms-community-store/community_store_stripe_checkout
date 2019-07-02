<?php
namespace Concrete\Package\CommunityStoreStripeCheckout\Src\CommunityStore\Payment\Methods\CommunityStoreStripeCheckout;

use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;

class CommunityStoreStripeCheckoutPaymentMethod extends StorePaymentMethod
{
    private function getCurrencies()
    {
        return [
            'USD' => t('US Dollar'),
            'EUR' => t('Euro'),
            'GBP' => t('British Pounds Sterling'),
            'AUD' => t('Australian Dollar'),
            'BRL' => t('Brazilian Real'),
            'CAD' => t('Canadian Dollar'),
            'CLP' => t('Chilean Peso'),
            'CZK' => t('Czech Koruna'),
            'DKK' => t('Danish Krone'),
            'HKD' => t('Hong Kong Dollar'),
            'HUF' => t('Hungarian Forint'),
            'IRR' => t('Iranian Rial'),
            'ILS' => t('Israeli Shekel'),
            'JPY' => t('Japanese Yen'),
            'MYR' => t('Malaysian Ringgit'),
            'MXN' => t('Mexican Peso'),
            'NZD' => t('New Zealand Dollar'),
            'NOK' => t('Norwegian Krone'),
            'PHP' => t('Philippine Peso'),
            'PLN' => t('Polish Zloty'),
            'RUB' => t('Russian Rubles'),
            'SGD' => t('Singapore Dollar'),
            'KRW' => t('South Korean Won'),
            'SEK' => t('Swedish Krona'),
            'CHF' => t('Swiss Franc)'),
            'TWD' => t('Taiwan New Dollar'),
            'THB' => t('Thai Baht'),
            'TRY' => t('Turkish Lira'),
            'VND' => t('Vietnamese Dong'),
        ];
    }

    public function dashboardForm()
    {
        $this->set('stripeCheckoutMode', Config::get('community_store_stripe_checkout.mode'));
        $this->set('stripeCheckoutCurrency', Config::get('community_store_stripe_checkout.currency'));
        $this->set('stripeCheckoutTestPublicApiKey', Config::get('community_store_stripe_checkout.testPublicApiKey'));
        $this->set('stripeCheckoutLivePublicApiKey', Config::get('community_store_stripe_checkout.livePublicApiKey'));
        $this->set('stripeCheckoutTestPrivateApiKey', Config::get('community_store_stripe_checkout.testPrivateApiKey'));
        $this->set('stripeCheckoutLivePrivateApiKey', Config::get('community_store_stripe_checkout.livePrivateApiKey'));
        $this->set('stripeCheckoutSigningSecretKey', Config::get('community_store_stripe_checkout.signingSecretKey'));
        $this->set('form', Application::getFacadeApplication()->make("helper/form"));
        $this->set('stripeCheckoutCurrencies', $this->getCurrencies());
    }

    public function save(array $data = [])
    {
        Config::save('community_store_stripe_checkout.mode', $data['stripeCheckoutMode']);
        Config::save('community_store_stripe_checkout.currency', $data['stripeCheckoutCurrency']);
        Config::save('community_store_stripe_checkout.testPublicApiKey', $data['stripeCheckoutTestPublicApiKey']);
        Config::save('community_store_stripe_checkout.livePublicApiKey', $data['stripeCheckoutLivePublicApiKey']);
        Config::save('community_store_stripe_checkout.testPrivateApiKey', $data['stripeCheckoutTestPrivateApiKey']);
        Config::save('community_store_stripe_checkout.livePrivateApiKey', $data['stripeCheckoutLivePrivateApiKey']);
        Config::save('community_store_stripe_checkout.signingSecretKey', $data['stripeCheckoutSigningSecretKey']);
    }

    public function validate($args, $e)
    {
        return $e;
    }

    public function checkoutForm()
    {
        $mode = Config::get('community_store_stripe_checkout.mode');
        $this->set('mode', $mode);
        $this->set('currency', Config::get('community_store_stripe_checkout.currency'));

        if ($mode == 'live') {
            $this->set('publicCheckoutAPIKey', Config::get('community_store_stripe_checkout.livePublicApiKey'));
        } else {
            $this->set('publicCheckoutAPIKey', Config::get('community_store_stripe_checkout.testPublicApiKey'));
        }

        $pmID = StorePaymentMethod::getByHandle('community_store_stripe_checkout')->getID();
        $this->set('pmID', $pmID);
    }

    public function submitPayment()
    {
        //nothing to do except return true
        return ['error' => 0, 'transactionReference' => ''];
    }

    public function getPaymentMinimum()
    {
        return 0.5;
    }

    public function getName()
    {
        return 'Stripe Checkout';
    }

    public function createSession()
    {
        $mode = Config::get('community_store_stripe_checkout.mode');
        $this->set('currency', Config::get('community_store_stripe_checkout.currency'));

        if ($mode == 'live') {
            $secretKey = Config::get('community_store_stripe_checkout.livePrivateApiKey');
        } else {
            $secretKey = Config::get('community_store_stripe_checkout.testPrivateApiKey');
        }

        $referrer = $this->request->server->get('HTTP_REFERER');;
        $c = \Page::getByPath(parse_url($referrer, PHP_URL_PATH));
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if ($al !== null) {
            $langpath = $al->getCollectionHandle();
        }

        // fetch order just submitted
        $order = StoreOrder::getByID(Session::get('orderID'));
        $lineitems = [];
        $currency = Config::get('community_store_stripe_checkout.currency');

        $currencyMultiplier = StorePrice::getCurrencyMultiplier($currency);

        if ($order) {
            $items = $order->getOrderItems();
            if ($items) {
                foreach ($items as $item) {
                    if ($item->getPricePaid() > 0) {

                        $stripeItem =
                            ['name' => $item->getProductName() . ($item->getSKU() ? '(' . $item->getSKU() . ')' : ''),
                                'amount' => round($item->getPricePaid() * $currencyMultiplier, 0),
                                'quantity' => $item->getQty(),
                                'currency' => $currency
                            ];

                        $imagesrc = '';
                        $fileObj = $item->getProductObject()->getImageObj();
                        if (is_object($fileObj)) {
                            $imagesrc = $fileObj->getURL();
                            $stripeItem['images'] = [$imagesrc];
                        }

                        $options = $item->getProductOptions();
                        if ($options) {
                            $optionOutput = [];
                            foreach ($options as $option) {
                                if ($option['oioValue']) {
                                    $optionOutput[] = $option['oioKey'] . ": " . $option['oioValue'];
                                }
                            }
                            $stripeItem['description'] = implode("\n", $optionOutput);
                        }
                        
                        $lineitems[] = $stripeItem;
                    }
                }
            }

            if ($order->isShippable()) {

                $shippingItem = [
                    'name' => $order->getShippingMethodName(),
                    'amount' => round($order->getShippingTotal() * $currencyMultiplier, 0),
                    'currency' => $currency,
                    'quantity' => 1
                ];

                $lineitems[] = $shippingItem;
            }

            $taxes = $order->getTaxes();

            if (!empty($taxes)) {
                foreach ($order->getTaxes() as $tax) {
                    if ($tax['amount']) {
                        $taxItem = ['name' => $tax['label'],
                            'amount' => round($tax['amount'] * $currencyMultiplier, 0),
                            'currency' => $currency,
                            'quantity' => 1
                        ];
                        $lineitems[] = $taxItem;
                    }
                }
            }

            \Stripe\Stripe::setApiKey($secretKey);
            $session = \Stripe\Checkout\Session::create([
                'client_reference_id' => $order->getOrderID(),
                'payment_method_types' => ['card'],
                'customer_email' => $order->getAttribute('email'),
                'line_items' => $lineitems,
                'success_url' => URL::to($langpath . '/checkout/complete'),
                'cancel_url' => URL::to($langpath . '/checkout'),
            ]);

            echo $session['id'];
        }
        exit();

    }

    function chargeResponse() {

        $mode = Config::get('community_store_stripe_checkout.mode');

        if ($mode == 'live') {
            $secretKey = Config::get('community_store_stripe_checkout.livePrivateApiKey');
        } else {
            $secretKey = Config::get('community_store_stripe_checkout.testPrivateApiKey');
        }

        $signingSecretKey = Config::get('community_store_stripe_checkout.signingSecretKey');

        if ($secretKey && $signingSecretKey) {
            \Stripe\Stripe::setApiKey($secretKey);

            $payload = @file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            $event = null;

            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $signingSecretKey
                );
            } catch (\UnexpectedValueException $e) {
                // Invalid payload
                http_response_code(400);
                exit();
            } catch (\Stripe\Error\SignatureVerification $e) {
                // Invalid signature
                http_response_code(400);
                exit();
            }

            $success = false;

            // Handle the checkout.session.completed event
            if ($event->type == 'checkout.session.completed') {
                $session = $event->data->object;
                $order = StoreOrder::getByID($session->client_reference_id);

                if ($order) {
                    $order->completeOrder($session->payment_intent);
                    $order->updateStatus(StoreOrderStatus::getStartingStatus()->getHandle());
                    $success = true;
                }
            }

            // handle a refund
            if ($event->type == 'charge.refunded') {
                $session = $event->data->object;

                $em = \Concrete\Core\Support\Facade\DatabaseORM::entityManager();
                $order = $em->getRepository('\Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order')->findOneBy(['transactionReference' => $session->payment_intent]);

                if ($order) {
                    $order->setRefunded(new \DateTime());
                    $order->setRefundReason($session->refunds->data->reason);
                    $order->save();
                    $success = true;
                }
            }

            if ($success) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(400);
        }
    }

    public function isExternal()
    {
        return true;
    }
}

return __NAMESPACE__;
