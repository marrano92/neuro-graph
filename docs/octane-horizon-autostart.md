# Laravel Octane and Horizon Autostart

[ai-generated-code]

This project is configured to automatically start Laravel Octane and Horizon when the Docker container starts.

## Configuration

The following components work together to enable autostart:

1. **supervisord-custom.conf** - A custom Supervisor configuration file that defines three programs:
   - `php` - The standard PHP-FPM process
   - `octane` - Starts Laravel Octane with Swoole
   - `horizon` - Starts Laravel Horizon for queue processing

2. **Dockerfile** - Updated to copy the custom supervisord configuration file into the container

## How It Works

When the Docker container starts, Supervisor automatically starts all configured services:
- PHP-FPM for serving regular web requests
- Laravel Octane with Swoole for high-performance handling of requests
- Laravel Horizon for managing and processing queued jobs

## Ports

- Laravel Octane runs on port 8000 (exposed via docker-compose.yml)
- Laravel Horizon dashboard is available at `/horizon` path

## Manual Control

You can still manually control these services if needed:

### Octane

```bash
# Using the sail-octane script
./sail-octane start|stop|status|reload

# Or directly with artisan
sail artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --watch
sail artisan octane:stop
sail artisan octane:status
sail artisan octane:reload
```

### Horizon

```bash
# Start Horizon
sail artisan horizon

# Terminate Horizon workers
sail artisan horizon:terminate

# Pause Horizon processing
sail artisan horizon:pause

# Continue Horizon processing
sail artisan horizon:continue
``` 