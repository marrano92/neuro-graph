# Setting up Brevo SMTP for Real Email Delivery in Laravel

This guide will help you configure your Laravel application to send real emails using Brevo (formerly Sendinblue) SMTP service.

## Step 1: Create a Brevo Account

1. If you don't already have one, sign up for a Brevo account at https://www.brevo.com/
2. After signing up and logging in, navigate to the SMTP & API section
3. Get your SMTP credentials:
   - SMTP server
   - Port
   - Login (usually your email)
   - SMTP key (this is different from your password)

## Step 2: Update Your .env File

Edit your `.env` file with the following settings:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=your_brevo_smtp_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@neuro-graph.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Replace:
- `your_brevo_email@example.com` with the email associated with your Brevo account
- `your_brevo_smtp_key` with the SMTP key provided by Brevo (not your account password)

## Step 3: Restart and Clear Cache

1. Restart Laravel Sail:
   ```bash
   ./vendor/bin/sail down
   ./vendor/bin/sail up -d
   ```

2. Clear the configuration cache:
   ```bash
   ./vendor/bin/sail artisan config:clear
   ```

## Step 4: Test Email Sending

Test if emails are now being delivered:
```bash
./vendor/bin/sail artisan email:test
```

## Additional Brevo Features

### Email Templates
You can create reusable email templates in your Brevo dashboard that can be used in your Laravel application.

### Email API
If you prefer using an API instead of SMTP, Brevo also provides a comprehensive API for sending emails. You can integrate it using:

```bash
composer require sendinblue/api-v3-sdk
```

And then configure your `.env` file with:

```
MAIL_MAILER=brevo
MAIL_FROM_ADDRESS="no-reply@neuro-graph.com"
MAIL_FROM_NAME="${APP_NAME}"
BREVO_API_KEY=your_brevo_api_key
```

Note: Using the API requires additional setup with a custom mail transport.

### Monitoring Email Performance
Brevo provides detailed statistics on email deliverability, open rates, click rates, etc. in their dashboard.

## Troubleshooting

1. **Emails Not Sending**: Make sure your SMTP key is correct and your account is verified.
2. **Deliverability Issues**: Check Brevo's sending report for any bounces or spam reports.
3. **Rate Limiting**: Be aware of the sending limits on your Brevo plan.

For more information, refer to Brevo's official documentation at https://help.brevo.com/ 