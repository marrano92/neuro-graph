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
<type>(<scope>): <description>

<body>

<footer>
```

The **header** is mandatory and must conform to the commit message format.
The **body** and **footer** are optional.

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, etc)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `build`: Changes that affect the build system or external dependencies
- `ci`: Changes to CI configuration files and scripts
- `chore`: Other changes that don't modify src or test files
- `revert`: Reverts a previous commit

### Examples

```
feat(auth): add ability to reset password

fix(database): resolve connection timeout issue

docs(readme): update installation instructions
```

This validation is enforced using commitlint with a pre-commit hook.

## Structure Discoverer Integration

This project includes [spatie/php-structure-discoverer](https://github.com/spatie/php-structure-discoverer) for automatically discovering classes in the application.

### Available Structure Scouts

The following structure scouts are defined:

- `ModelStructureScout`: Discovers models extending `Illuminate\Database\Eloquent\Model`
- `ControllerStructureScout`: Discovers controllers extending `App\Http\Controllers\Controller`
- `TransformerStructureScout`: Discovers classes implementing `App\Contracts\GraphTransformer`

### Discovery Commands

To demonstrate the discovery functionality, the following Artisan commands are available:

```bash
# Discover models in the application
./vendor/bin/sail artisan discover:models

# Discover controllers and their public methods
./vendor/bin/sail artisan discover:controllers

# Discover GraphTransformer implementations
./vendor/bin/sail artisan discover:transformers
```

These commands provide a good example of how to use the Structure Discoverer in your own application.

### Caching Structure Scouts

For better performance in production, structure scouts are automatically cached. The caching mechanism is set up in the `StructureDiscovererServiceProvider`.

To manually warm up the cache:

```bash
# Clear the structure scout cache
./vendor/bin/sail artisan structure-scouts:clear

# Cache all structure scouts
./vendor/bin/sail artisan structure-scouts:cache
```

The caching process is automatically triggered when the application boots in production mode.

## Laravel Horizon

This project includes [Laravel Horizon](https://laravel.com/docs/horizon) for managing and monitoring Laravel Redis queues.

### Running Horizon

To start the Horizon process:

```bash
./vendor/bin/sail artisan horizon
```

Or if you're using the alias:

```bash
sail artisan horizon
```

### Horizon Dashboard

The Horizon dashboard is available at `/horizon` and provides a beautiful interface for monitoring:

- Job metrics
- Failed jobs
- Queue workload
- Process counts
- Job throughput
- Recent jobs

### Horizon Configuration

The Horizon configuration is located in `config/horizon.php`. You can customize the queues, workers, and other settings in this file.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
