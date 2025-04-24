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
