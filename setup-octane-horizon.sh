#!/bin/bash
# [ai-generated-code]

# Rebuild the Docker container with the new configuration
echo "Shutting down any running containers..."
./vendor/bin/sail down

echo "Building the new container with Octane and Horizon autostart..."
./vendor/bin/sail build --no-cache

echo "Starting the containers..."
./vendor/bin/sail up -d

echo "Containers started with automatic Octane and Horizon processes."
echo ""
echo "You can access:"
echo "- The main application: http://localhost"
echo "- Laravel Octane (if directly accessing it): http://localhost:8000"
echo "- Laravel Horizon dashboard: http://localhost/horizon"
echo ""
echo "To check the status of services, run:"
echo "./vendor/bin/sail exec laravel.test supervisorctl status" 