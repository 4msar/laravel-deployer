# Laravel Deployer

A powerful zero-downtime deployment tool for Laravel applications built with Laravel Zero. Deploy your Laravel applications from GitHub releases with confidence using an easy-to-use CLI tool.

## Features

- 🚀 **Zero Downtime Deployment** - Symlink-based deployments ensure your application stays online
- 📦 **GitHub Releases Integration** - Automatically fetch and deploy from GitHub releases
- 🔄 **Automatic Rollback** - Rolls back to previous version if deployment fails
- 🗂️ **Release Management** - Keep multiple releases and easily cleanup old ones
- 🔐 **Preserve Important Files** - Automatically preserves `.env`, storage, and other critical files
- ⚡ **Laravel Optimization** - Runs cache optimization commands automatically
- 🔍 **Health Checks** - Verifies deployment success before finalizing
- 🎨 **Beautiful CLI** - Clean, colored output with progress indicators
- ⚙️ **Highly Configurable** - Configure via `.env`, config file, or command options

## Installation

### Via Composer (Recommended)

```bash
composer global require 4msar/laravel-deployer
```

### Via Download

Download the latest `laravel-deployer` PHAR file from the releases page:

```bash
wget https://github.com/4msar/laravel-deployer/raw/main/builds/laravel-deployer -O laravel-deployer
chmod +x laravel-deployer
sudo mv laravel-deployer /usr/local/bin/laravel-deployer
```

#### One click install script

```bash
curl -sS https://raw.githubusercontent.com/4msar/laravel-deployer/main/install.sh | bash
```

### Build from Source

```bash
git clone https://github.com/4msar/laravel-deployer.git
cd laravel-deployer
composer install
php laravel-deployer app:build
```

## Configuration

### Using .env File

Copy the example configuration:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
DEPLOY_GITHUB_REPO=your-username/your-repo
DEPLOY_APP_NAME=your-app
DEPLOY_INSTALL_DIR=/var/www
DEPLOY_WEB_USER=www-data
DEPLOY_GITHUB_TOKEN=your-github-token
DEPLOY_KEEP_RELEASES=2
```

### Using Command Options

You can override configuration using command-line options:

```bash
laravel-deployer deploy \
    --repo=your-username/your-repo \
    --app-name=your-app \
    --install-dir=/var/www \
    --web-user=www-data \
    --keep-releases=2
```

## Usage

### Basic Deployment

```bash
# Interactive mode (prompts for configuration)
laravel-deployer deploy

# With options
laravel-deployer deploy --repo=username/repo --install-dir=/var/www
```

### Advanced Options

```bash
# Force deployment even if same version
laravel-deployer deploy --force

# Skip database migrations
laravel-deployer deploy --skip-migrations

# Auto cleanup old releases without prompting
laravel-deployer deploy --auto-cleanup

# Use GitHub token for private repos
laravel-deployer deploy --github-token=your_token_here

# Keep specific number of old releases
laravel-deployer deploy --keep-releases=5
```

### Full Example

```bash
laravel-deployer deploy \
    --repo=4msar/bill-organizer \
    --app-name=bill-organizer \
    --install-dir=/var/www \
    --web-user=www-data \
    --github-token=ghp_xxxxxxxxxxxx \
    --keep-releases=3 \
    --auto-cleanup
```

## How It Works

1. **Fetch Release**: Downloads the latest release from GitHub
2. **Prepare**: Extracts the release to a temporary directory
3. **Preserve Files**: Copies important files from current installation (`.env`, storage, etc.)
4. **Deploy**: Creates a new release directory and copies files
5. **Optimize**: Runs Laravel optimization commands (config, route, view cache)
6. **Migrate**: Optionally runs database migrations
7. **Switch**: Updates symlink to point to new release
8. **Health Check**: Verifies the application is working
9. **Cleanup**: Removes old releases (optional)
10. **Rollback**: Automatically rolls back on failure

## Directory Structure

After deployment, your directory structure will look like:

```
/var/www/
├── your-app -> your-app-v1.2.0  (symlink to current release)
├── your-app-v1.2.0/              (current release)
├── your-app-v1.1.0/              (previous release)
└── backups/                      (backup directory)
```

## Requirements

- PHP ^8.4
- Composer
- Git
- `curl`, `unzip` command-line tools
- Proper file permissions on target server

## GitHub Token

For private repositories or to avoid rate limiting, create a GitHub Personal Access Token:

1. Go to https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Select scopes: `repo` (for private repos) or `public_repo` (for public repos)
4. Copy the token and use it with `--github-token` option or in `.env`

## Deployment Best Practices

1. **Test Locally**: Always test your release locally before deploying
2. **Backup Database**: Backup your database before running migrations
3. **Use Releases**: Tag your code with semantic versioning (v1.0.0, v1.1.0, etc.)
4. **Preserve Files**: Ensure all important files are listed in config
5. **Health Checks**: Verify your application after deployment
6. **Keep Releases**: Keep at least 2 previous releases for quick rollback

## Troubleshooting

### Permission Denied

Run with sudo or ensure your user has write permissions:

```bash
sudo laravel-deployer deploy
```

### GitHub Rate Limiting

Use a GitHub token to increase rate limits:

```bash
laravel-deployer deploy --github-token=your_token
```

### Migration Failures

Skip migrations and run them manually:

```bash
laravel-deployer deploy --skip-migrations
cd /var/www/your-app
php artisan migrate
```

### Rollback Manually

If automatic rollback fails:

```bash
cd /var/www
ln -sfn your-app-v1.1.0 your-app
```

## Self Update

To update `laravel-deployer` to the latest version, run:

```bash
laravel-deployer self-update
```

It will download and replace the current PHAR with the latest release.

## License

This project is open-sourced software licensed under the MIT license.

## Credits

Built with [Laravel Zero](https://laravel-zero.com) - The PHP framework for console applications.
