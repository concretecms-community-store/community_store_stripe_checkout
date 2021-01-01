# Community Store Stripe Checkout
Stripe Checkout payment add-on for Community Store for concrete5

https://stripe.com/docs/payments/checkout

This is a Strong Customer Authentication (SCA) compliant payment method for Stripe, required by European regulation from September 2019.
If your business is based in the European Economic Area (EEA) and you serve customers in the EEA, you are likely to need to update your Stripe integration to a SCA compliant method such as this one.

## Setup
Install Community Store First.

Download a 'release' zip of the add-on, unzip this to the packages folder of your concrete5 install (alongside the community_store folder) and install via the dashboard.

Once installed, configure the payment method through the Settings/Payments dashboard section for 'Store'. 
You will need to log into Stripe's Dashboard, and through the Developers section copy in test and live API Keys.
Additionally, you will also need to configure a webhook - details for this are displayed on the configuration form for the payment method.

