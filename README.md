# Neuro Graph - Laravel 12 Project

This is a Laravel 12 project with Docker using Laravel Sail and Laravel Octane.

## Setup Instructions

### Requirements
- Docker
- Docker Compose

### Installation
1. Clone the repository
2. Run the following command to start the containers:
   ```bash
   ./vendor/bin/sail up -d
   ```
3. Run migrations:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

### YouTube Transcription Functionality

This project includes functionality to extract subtitles from YouTube videos. It requires:
- yt-dlp (YouTube downloader)
- ffmpeg (for subtitle processing)

These dependencies are configured in the custom Dockerfile. The first time you run the project, you'll need to build the Docker image:

```bash
./vendor/bin/sail build --no-cache
```

### Useful Commands
You can add this alias to your shell configuration for easier usage:
```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Then you can use:
```bash
sail up -d     # Start containers in background
sail down      # Stop containers
sail artisan   # Run artisan commands
sail composer  # Run composer commands
sail npm       # Run npm commands
```

## API Authentication with Laravel Passport

This project uses Laravel Passport for API authentication. The following endpoints are available:

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and get access token
- `POST /api/auth/logout` - Logout (requires authentication)
- `GET /api/auth/user` - Get authenticated user (requires authentication)

### Testing API Endpoints

A Postman collection is included in the project root as `postman_collection.json`. You can import this into Postman to test the API endpoints.

To use the collection:
1. Import the collection into Postman
2. Set the `base_url` variable to your local development URL (e.g., `http://localhost`)
3. Register or login to get an access token
4. Set the `auth_token` variable to the token received from the register/login response
5. Use the authenticated endpoints

## Email Configuration

The application is configured to use MailHog for email testing by default. MailHog intercepts emails and displays them in a web interface without actually sending them to recipients.

### Default MailHog Configuration
- Access the MailHog web interface at: http://localhost:8025
- All emails are captured by MailHog and not delivered to actual recipients

### Setting Up Real Email Delivery

#### Option 1: Using Brevo (Formerly Sendinblue)

The application includes a setup script for Brevo email service:

```bash
./setup-brevo.sh
```

This script will guide you through configuring either:
1. **Brevo SMTP**: Simple email delivery using Brevo's SMTP servers
2. **Brevo API**: Advanced features using Brevo's API

For detailed documentation about Brevo integration, see `docs/email/brevo-integration.md`.

#### Option 2: Using Gmail SMTP

For Gmail SMTP configuration:

```bash
# Update your .env file with these settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your.email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your.email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

You may need to generate an app password if you have 2FA enabled on your Gmail account.

### Testing Email Configuration

After setting up email, test it with:

```bash
./vendor/bin/sail artisan email:test
```

## Running with Laravel Octane

This project is configured to use Laravel Octane with Swoole for improved performance. 
To run the application with Octane:

```bash
./vendor/bin/sail composer dev-octane
```

Or if you're using the alias:

```bash
sail composer dev-octane
```

### Using the Octane helper script

For easier management of Octane, you can use the provided helper script:

```bash
./sail-octane start    # Start Octane server with Swoole
./sail-octane stop     # Stop running Octane server
./sail-octane status   # Check Octane server status
./sail-octane reload   # Reload Octane workers
```

The application will be available at http://localhost:8000 when running with Octane.

## Development

The application is available at http://localhost when running with the standard web server,
or at http://localhost:8000 when running with Laravel Octane.

Database credentials are in the .env file.

## Conventional Commits

This project follows the [Conventional Commits](https://www.conventionalcommits.org/) specification for commit messages. This helps to create an explicit commit history and automate versioning and release notes.

### Commit Message Format

Each commit message consists of a **header**, a **body**, and a **footer**:

```
<