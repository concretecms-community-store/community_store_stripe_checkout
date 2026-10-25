<?php defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
?>


<script>
    (function () {
        // the payment methods can be loaded more than once, only listen for clicks once
        if (window.communityStoreStripeCheckoutInit) {
            return;
        }
        window.communityStoreStripeCheckoutInit = true;

        var paymentMethodID = <?= json_encode((string) $pmID); ?>;

        function loadStripe() {
            return new Promise(function (resolve, reject) {
                if (window.Stripe) {
                    resolve();
                    return;
                }
                var script = document.createElement('script');
                script.src = 'https://js.stripe.com/v3/';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        document.addEventListener('click', function (e) {
            var button = e.target.closest ? e.target.closest('.store-btn-complete-order') : null;
            if (!button) {
                return;
            }

            var selected = document.querySelector('input[name="payment-method"]:checked');
            if (!selected || selected.getAttribute('data-payment-method-id') !== paymentMethodID) {
                return;
            }

            e.preventDefault();

            var label = button.value;
            button.disabled = true;
            button.value = <?= json_encode(t('Processing...')); ?>;

            var paymentform = document.getElementById('store-checkout-form-group-payment');

            loadStripe()
                .then(function () {
                    // create the order
                    return communityStore.request(paymentform.getAttribute('action'), {
                        method: 'POST',
                        data: communityStore.serialize(paymentform)
                    });
                })
                .then(function () {
                    // create the Stripe checkout session
                    return communityStore.request(<?= json_encode((string) \Concrete\Core\Support\Facade\Url::to('/checkout/stripecheckoutcreatesession')); ?>);
                })
                .then(function (sessionID) {
                    var stripe = Stripe(<?= json_encode((string) $publicCheckoutAPIKey); ?>);

                    return stripe.redirectToCheckout({
                        sessionId: sessionID.trim()
                    });
                })
                .then(function (result) {
                    // redirectToCheckout only resolves if it fails, e.g. due to a browser or network error
                    if (result && result.error) {
                        throw result.error;
                    }
                })
                .catch(function () {
                    button.disabled = false;
                    button.value = label;
                });
        });
    })();
</script>
