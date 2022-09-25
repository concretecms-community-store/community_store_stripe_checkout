<?php

namespace Concrete\Package\CommunityStoreStripeCheckout;

use \Concrete\Core\Package\Package;
use \Concrete\Core\Support\Facade\Route;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Whoops\Exception\ErrorException;


class Controller extends Package
{
    protected $pkgHandle = 'community_store_stripe_checkout';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '1.1';
    protected $packageDependencies = ['community_store'=>'2.0'];
    public static $stripeAPIVersion = '2022-08-01';

    public function on_start()
    {
        require __DIR__ . '/vendor/autoload.php';
        Route::register('/checkout/stripecheckoutcreatesession','\Concrete\Package\CommunityStoreStripeCheckout\Src\CommunityStore\Payment\Methods\CommunityStoreStripeCheckout\CommunityStoreStripeCheckoutPaymentMethod::createSession');
        Route::register('/checkout/stripecheckoutresponse','\Concrete\Package\CommunityStoreStripeCheckout\Src\CommunityStore\Payment\Methods\CommunityStoreStripeCheckout\CommunityStoreStripeCheckoutPaymentMethod::chargeResponse');
    }

    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStoreStripeCheckout\Src\CommunityStore',
    ];

    public function getPackageDescription()
    {
        return t("Stripe Checkout Payment Method for Community Store");
    }

    public function getPackageName()
    {
        return t("Stripe Checkout Payment Method");
    }

    public function install()
    {
        if (!@include(__DIR__ . '/vendor/autoload.php')) {
            throw new ErrorException(t('Third party libraries not installed. Use a release version of this add-on with libraries pre-installed, or run composer install against the package folder.'));
        }

        $pkg = parent::install();
        $pm = new PaymentMethod();
        $pm->add('community_store_stripe_checkout','Stripe Checkout',$pkg);
    }
    public function uninstall()
    {
        $pm = PaymentMethod::getByHandle('community_store_stripe_checkout');
        if ($pm) {
            $pm->delete();
        }
        $pkg = parent::uninstall();
    }

}
?>
