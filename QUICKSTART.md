# Quick Start Guide

## Testing the Application Locally

1. **Install Dependencies**

    ```bash
    composer install
    ```

2. **Test the Deploy Command**

    ```bash
    php laravel-deployer deploy --help
    ```

3. **Run an Interactive Deployment**
    ```bash
    php laravel-deployer deploy
    ```

## Building the Application

Build a standalone PHAR file that can be distributed:

```bash
php laravel-deployer app:build
```

This will create a `builds/laravel-deployer` file that can be run anywhere without requiring composer dependencies.

## Testing the Built Application

```bash
./builds/laravel-deployer deploy --help
```

## Development

### Running Tests

```bash
composer test
```

### Code Style

Format code using Laravel Pint:

```bash
composer pint
```

### Adding New Commands

1. Create a new command in `app/Commands/`:

    ```bash
    php laravel-deployer make:command YourCommand
    ```

2. Edit the generated file in `app/Commands/YourCommand.php`

3. The command will be automatically discovered

## Deployment Workflow

### Preparing Your Laravel Application

1. Create a GitHub release with a tag (e.g., v1.0.0)
2. Attach a ZIP file of your Laravel application to the release
3. The ZIP should contain a directory named `your-app-v1.0.0/` with your Laravel files

### Running Deployment

```bash
# Option 1: Interactive (recommended for first time)
php laravel-deployer deploy

# Option 2: With all options
php laravel-deployer deploy \
    --repo=username/repo \
    --app-name=my-app \
    --install-dir=/var/www \
    --web-user=www-data \
    --auto-cleanup
```

## Environment Variables

Create a `.env` file for default configuration:

```bash
cp .env.example .env
```

Edit `.env`:

```env
DEPLOY_GITHUB_REPO=your-username/your-repo
DEPLOY_APP_NAME=your-app
DEPLOY_INSTALL_DIR=/var/www
DEPLOY_WEB_USER=www-data
DEPLOY_GITHUB_TOKEN=ghp_your_token_here
DEPLOY_KEEP_RELEASES=2
```

## Troubleshooting Development

### Class Not Found

Regenerate autoload files:

```bash
composer dump-autoload
```

### Build Fails

Make sure box.json is configured correctly and all dependencies are installed:

```bash
rm -rf vendor
composer install --no-dev
php laravel-deployer app:build
```

### Testing Locally Without GitHub

For testing without actual GitHub releases, you can modify the command to download from a local file or URL.

## Next Steps

1. Customize the deploy configuration in `config/deploy.php`
2. Add custom deployment hooks
3. Create additional commands for rollback, health checks, etc.
4. Add tests for your deployment logic
