# Brevo Email Integration for Laravel

This document provides detailed instructions on integrating Brevo (formerly Sendinblue) as your email service provider in Laravel.

## Table of Contents

1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Setup Methods](#setup-methods)
   - [Method 1: SMTP Integration](#method-1-smtp-integration)
   - [Method 2: API Integration](#method-2-api-integration)
4. [Testing Your Integration](#testing-your-integration)
5. [Advanced Usage](#advanced-usage)
6. [Troubleshooting](#troubleshooting)

## Introduction

Brevo (formerly Sendinblue) is a powerful email service provider that offers features like:
- Reliable email delivery
- Detailed analytics
- Email templates
- Transactional emails
- Marketing automation

This integration allows you to send emails from your Laravel application using Brevo's services.

## Prerequisites

- A Brevo account (free tier available at [brevo.com](https://www.brevo.com/))
- Laravel project with Laravel Sail
- Basic understanding of Laravel Mail

## Setup Methods

You have two options for integrating Brevo with Laravel:

### Method 1: SMTP Integration

This is the simpler method that uses Brevo's SMTP server.

1. **Get SMTP credentials from Brevo**
   - Log into your Brevo account
   - Navigate to "SMTP & API" section
   - Copy your SMTP credentials

2. **Update .env file**
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

3. **Restart and clear cache**
   ```bash
   ./vendor/bin/sail down
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan config:clear
   ```

### Method 2: API Integration

For more advanced features, you can use the Brevo API integration.

1. **Install Brevo Mailer package**
   ```bash
   ./vendor/bin/sail composer require symfony/brevo-mailer
   ```

2. **Register Service Provider**
   
   The `BrevoMailServiceProvider` has already been registered in your application.

3. **Update .env file**
   ```
   MAIL_MAILER=brevo
   MAIL_FROM_ADDRESS="no-reply@neuro-graph.com"
   MAIL_FROM_NAME="${APP_NAME}"
   BREVO_API_KEY=your_brevo_api_key
   ```
   
   Get your API key from the "SMTP & API" section in your Brevo dashboard.

4. **Restart and clear cache**
   ```bash
   ./vendor/bin/sail down
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan config:clear
   ```

## Testing Your Integration

To test if your email configuration is working correctly:

```bash
./vendor/bin/sail artisan email:test
```

This will send a test email to the first user in your database.

## Advanced Usage

### Sending Emails with Attachments

```php
Mail::to($recipient)->send(new YourMailable($data));
```

In your Mailable class:

```php
public function build()
{
    return $this->subject('Your Subject')
                ->view('emails.your-template')
                ->attach('/path/to/file');
}
```

### Using Brevo Templates

1. Create an email template in your Brevo dashboard
2. Note the template ID
3. Use it in your mailable:

```php
// This requires additional setup with the Brevo PHP SDK
public function build()
{
    return $this->subject('Your Subject')
                ->view('emails.brevo-template')
                ->with([
                    'template_id' => 123,
                    'variables' => [
                        'name' => $this->user->name,
                        // other variables used in your template
                    ]
                ]);
}
```

## Troubleshooting

### Emails Not Being Sent

1. Check your Brevo account is active and has sending credits
2. Verify SMTP credentials are correct
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure your Brevo account is properly verified

### Emails Going to Spam

1. Set up proper SPF and DKIM records for your domain
2. Use a professional "from" email address
3. Ensure your content doesn't trigger spam filters
4. Warm up your email sending gradually

### Rate Limiting

Be aware of the sending limits on your Brevo plan. The free plan allows 300 emails per day. 