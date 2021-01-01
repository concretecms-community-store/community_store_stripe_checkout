<?php defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
?>


<script>
    $(window).on('load', function() {

        $('.store-btn-complete-order').on('click', function (e) {
            // Open Checkout with further options
            var currentpmid = $('input[name="payment-method"]:checked:first').data('payment-method-id');

            if (currentpmid === <?= $pmID; ?>) {
                $(this).prop('disabled', true);
                $(this).val('<?= t('Processing...'); ?>');

                $.getScript( "https://js.stripe.com/v3/" )
                    .done(function( script ) {

                        var paymentform = $('#store-checkout-form-group-payment');
                        var data = paymentform.serialize();
                        $.ajax({
                            url: paymentform.attr('action'),
                            type: 'post',
                            cache: false,
                            data: data,
                            dataType: 'text',
                            success: function(data) {
                                $.ajax({
                                    url: '<?= \URL::to('/checkout/stripecheckoutcreatesession'); ?>',
                                    type: 'get',
                                    cache: false,
                                    dataType: 'text',
                                    success: function(data) {

                                        var stripe = Stripe('<?php echo $publicCheckoutAPIKey; ?>');

                                        stripe.redirectToCheckout({
                                            sessionId: data,
                                        }).then(function (result) {
                                            // If `redirectToCheckout` fails due to a browser or network
                                            // error, display the localized error message to your customer
                                            // using `result.error.message`.
                                        });
                                    }
                                });

                            }
                        });

                    })
                    .fail(function( jqxhr, settings, exception ) {

                    });


                e.preventDefault();
            }
        });

    });
</script>
