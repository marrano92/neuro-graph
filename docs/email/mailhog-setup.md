# Setting up MailHog for Local Email Testing

Laravel Sail includes MailHog, which is a development tool that provides a local SMTP server for testing emails without actually sending them to real recipients. Here's how to set it up:

## 1. Add MailHog to your docker-compose.yml file

If you're using Laravel Sail, ensure that the MailHog service is included in your docker-compose.yml file. If it's not already there, add the following:

```yaml
mailhog:
    image: 'mailhog/mailhog:latest'
    ports:
        - '${FORWARD_MAILHOG_PORT:-1025}:1025'
        - '${FORWARD_MAILHOG_DASHBOARD_PORT:-8025}:8025'
    networks:
        - sail
```

## 2. Update your .env file

Update your .env file with the following mail configuration:

```
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@neuro-graph.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 3. Restart Laravel Sail

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

## 4. Clear the configuration cache

```bash
./vendor/bin/sail artisan config:clear
```

## 5. Access the MailHog Web Interface

After setting up MailHog, you can access its web interface at:

```
http://localhost:8025
```

This web interface allows you to view all the emails sent by your application.

## 6. Test sending an email

```bash
./vendor/bin/sail artisan email:test
```

The email will be captured by MailHog and displayed in its web interface, where you can inspect it without it being sent to any real email address. 