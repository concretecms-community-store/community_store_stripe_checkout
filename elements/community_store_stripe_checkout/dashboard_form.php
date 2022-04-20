<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="form-group">
    <?= $form->label('invoiceMinimum', t('Minimum Order Value'))?>
    <?= $form->number("stripeCheckoutMinimum", $stripeCheckoutMinimum); ?>
</div>

<div class="form-group">
    <?= $form->label('invoiceMaximum', t('Maximum Order Value'))?>
    <?= $form->number("stripeCheckoutMaximum", $stripeCheckoutMaximum); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeCheckoutCurrency', t('Currency'))?>
    <?= $form->select('stripeCheckoutCurrency', $stripeCheckoutCurrencies, $stripeCheckoutCurrency)?>
</div>

<div class="form-group">
    <?= $form->label('stripeCheckoutMode', t('Mode'))?>
    <?= $form->select('stripeCheckoutMode', ['test' => t('Test'), 'live' => t('Live')], $stripeCheckoutMode)?>
</div>

<div class="form-group">
    <?=$form->label('stripeCheckoutTestPublicApiKey', t('Test Publishable Key'))?>
    <?= $form->text("stripeCheckoutTestPublicApiKey", $stripeCheckoutTestPublicApiKey); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeCheckoutTestPrivateApiKey', t('Test Secret Key'))?>
    <?= $form->text("stripeCheckoutTestPrivateApiKey", $stripeCheckoutTestPrivateApiKey); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeCheckoutLivePublicApiKey', t('Live Publishable Key'))?>
    <?= $form->text("stripeCheckoutLivePublicApiKey", $stripeCheckoutLivePublicApiKey); ?>
</div>

<div class="form-group">
    <?= $form->label('stripeCheckoutLivePrivateApiKey', t('Live Secret Key'))?>
    <?= $form->text("stripeCheckoutLivePrivateApiKey", $stripeCheckoutLivePrivateApiKey); ?>
</div>

<?=$form->label('webhook', t('Required Webhook'))?>
<p><?= t('Within the Stripe Dashboard configure a Webhook endpoint for the following URL'); ?>:
    <br /><a href="<?= \URL::to('/checkout/stripecheckoutresponse'); ?>"><?= \URL::to('/checkout/stripecheckoutresponse'); ?></a></p>
<p><?= t('With the Events to send'); ?>:
        <span class="label label-primary">checkout.session.completed</span>
        <span class="label label-primary">charge.refunded</span>
</p>

<p><?= t('After creating the webhook, the Signing Secret can be found within webhook details page'); ?></p>

<div class="form-group">
    <?= $form->label('stripeCheckoutTestSigningSecretKey', t('Test Signing Secret Key'))?>
    <?= $form->text("stripeCheckoutTestSigningSecretKey", $stripeCheckoutTestSigningSecretKey); ?>
</div>


<div class="form-group">
    <?= $form->label('stripeCheckoutSigningSecretKey', t('Live Signing Secret Key'))?>
    <?= $form->text("stripeCheckoutSigningSecretKey", $stripeCheckoutSigningSecretKey); ?>
</div>


