Setup for Stripe Checkout integration

1. Add Stripe keys to your .env (do NOT commit the real keys):

   STRIPE_PUBLISHABLE_KEY=pk_test_xxx
   STRIPE_SECRET=sk_test_xxx
   STRIPE_WEBHOOK_SECRET=whsec_xxx
   STRIPE_CURRENCY=usd
   STRIPE_AMOUNT_MULTIPLIER=100

2. Run composer install (already done if package was added):
   composer install

3. Expose webhook endpoint to Stripe during development using a tool like stripe CLI:
   stripe listen --forward-to http://localhost:8000/api/stripe/webhook

4. Test a checkout flow by opening a project map, reserving a plot and completing Stripe Checkout in test mode.

Notes:
- The code creates a Booking (status=held) when a user reserves a plot. A Stripe Checkout Session is then created and the webhook (checkout.session.completed) marks the booking as paid and creates a Payment record.
- Make sure STRIPE_CURRENCY is supported by Stripe and STRIPE_AMOUNT_MULTIPLIER is set appropriately (100 if your prices are in dollars and you need cents).
