# Migration Guide: From Bash Script to Laravel Zero

This guide helps you transition from using the bash deployment script (`deploy-latest.sh`) to the new Laravel Zero application.

## Key Differences

### Bash Script

```bash
./deploy-latest.sh
```

### Laravel Zero

```bash
php laravel-deployer deploy
```

Or after building:

```bash
./laravel-deployer deploy
```

## Feature Comparison

| Feature                     | Bash Script | Laravel Zero | Notes                   |
| --------------------------- | ----------- | ------------ | ----------------------- |
| Zero-downtime deployment    | ✅          | ✅           | Same functionality      |
| GitHub releases integration | ✅          | ✅           | Same functionality      |
| Automatic rollback          | ✅          | ✅           | Enhanced error handling |
| File preservation           | ✅          | ✅           | Same files preserved    |
| Laravel optimization        | ✅          | ✅           | Same commands           |
| Health checks               | ✅          | ✅           | Same checks             |
| Cleanup old releases        | ✅          | ✅           | Same functionality      |
| Interactive prompts         | ✅          | ✅           | Better UX with colors   |
| Configuration via env       | ❌          | ✅           | New feature!            |
| Configuration file          | ❌          | ✅           | New feature!            |
| Command-line options        | Limited     | ✅           | More flexible           |
| Testable                    | ❌          | ✅           | Has test suite          |
| Cross-platform              | Linux/macOS | ✅           | Works on Windows too    |

## Configuration Migration

### Bash Script Variables

```bash
GITHUB_REPO="4msar/bill-organizer"
APP_NAME="bill-organizer"
INSTALL_DIR="${CURRENT_FILE_DIR}/${APP_NAME}"
WEB_USER="www-data"
GITHUB_TOKEN=""
KEEP_RELEASES="2"
```

### Laravel Zero .env

```env
DEPLOY_GITHUB_REPO=4msar/bill-organizer
DEPLOY_APP_NAME=bill-organizer
DEPLOY_INSTALL_DIR=/var/www
DEPLOY_WEB_USER=www-data
DEPLOY_GITHUB_TOKEN=
DEPLOY_KEEP_RELEASES=2
```

### Laravel Zero Command Options

```bash
php laravel-deployer deploy \
    --repo=4msar/bill-organizer \
    --app-name=bill-organizer \
    --install-dir=/var/www \
    --web-user=www-data \
    --keep-releases=2
```

## Side-by-Side Comparison

### Running a Basic Deployment

**Bash:**

```bash
sudo ./deploy-latest.sh
```

**Laravel Zero:**

```bash
sudo php laravel-deployer deploy
```

### With Custom Configuration

**Bash:**
Edit the script variables at the top of `deploy-latest.sh`:

```bash
GITHUB_REPO="myuser/myapp"
APP_NAME="myapp"
# ... more variables
```

**Laravel Zero:**
Option 1 - Command line:

```bash
php laravel-deployer deploy --repo=myuser/myapp --app-name=myapp
```

Option 2 - .env file:

```bash
echo "DEPLOY_GITHUB_REPO=myuser/myapp" >> .env
php laravel-deployer deploy
```

### Skip Migrations

**Bash:**
The script prompts: "Run database migrations? [y/N]:"
Type `N`

**Laravel Zero:**

```bash
php laravel-deployer deploy --skip-migrations
```

### Force Deployment

**Bash:**
The script prompts: "Do you want to continue anyway? [y/N]:"
Type `Y`

**Laravel Zero:**

```bash
php laravel-deployer deploy --force
```

### Auto Cleanup

**Bash:**
The script prompts: "Do you want to clean up old releases? [y/N]:"
Type `Y`

**Laravel Zero:**

```bash
php laravel-deployer deploy --auto-cleanup
```

## Advantages of Laravel Zero Version

### 1. Better Configuration Management

**Before (Bash):**

- Had to edit the script file
- Configuration mixed with code
- No validation

**After (Laravel Zero):**

- Configuration via `.env` file
- Configuration via command options
- Configuration via `config/deploy.php`
- Validation built-in

### 2. More Flexible Usage

**Before (Bash):**

```bash
# All configuration in one script
./deploy-latest.sh
```

**After (Laravel Zero):**

```bash
# Interactive
php laravel-deployer deploy

# With options
php laravel-deployer deploy --repo=user/repo

# From .env
php laravel-deployer deploy

# Mix and match
php laravel-deployer deploy --force --auto-cleanup
```

### 3. Better Error Handling

**Before (Bash):**

- Basic error messages
- Limited error context

**After (Laravel Zero):**

- Detailed error messages
- Stack traces in verbose mode
- Colored output for better visibility

### 4. Testing

**Before (Bash):**

- No automated tests
- Manual testing only

**After (Laravel Zero):**

```bash
php laravel-deployer test
```

### 5. Cross-Platform Support

**Before (Bash):**

- Linux and macOS only
- Requires bash shell

**After (Laravel Zero):**

- Works on Linux, macOS, and Windows
- Only requires PHP

### 6. Distribution

**Before (Bash):**

```bash
# Copy the script
cp deploy-latest.sh /usr/local/bin/
chmod +x /usr/local/bin/deploy-latest.sh
```

**After (Laravel Zero):**

```bash
# Build a PHAR
php laravel-deployer app:build

# Or install via Composer
composer global require laravel-zero/laravel-deployer
```

## Migration Steps

### Step 1: Install Laravel Zero Version

```bash
cd /path/to/laravel-deployer
composer install
```

### Step 2: Create .env File

```bash
cp .env.example .env
```

### Step 3: Copy Configuration from Bash Script

Extract the variables from your bash script and put them in `.env`:

```env
DEPLOY_GITHUB_REPO=your-repo
DEPLOY_APP_NAME=your-app
DEPLOY_INSTALL_DIR=/var/www
DEPLOY_WEB_USER=www-data
DEPLOY_GITHUB_TOKEN=your-token
DEPLOY_KEEP_RELEASES=2
```

### Step 4: Test the New Command

```bash
php laravel-deployer deploy --help
```

### Step 5: Run a Test Deployment

```bash
# In a staging environment first
php laravel-deployer deploy
```

### Step 6: Build Production Binary (Optional)

```bash
php laravel-deployer app:build
sudo mv builds/laravel-deployer /usr/local/bin/
```

### Step 7: Update Your Deployment Process

Replace your bash script calls:

**Old cron job:**

```cron
0 2 * * * /path/to/deploy-latest.sh
```

**New cron job:**

```cron
0 2 * * * php /path/to/laravel-deployer deploy --auto-cleanup
```

### Step 8: Update CI/CD Pipelines

**Old GitHub Actions:**

```yaml
- name: Deploy
  run: ./deploy-latest.sh
```

**New GitHub Actions:**

```yaml
- name: Deploy
  run: php laravel-deployer deploy --auto-cleanup --force
```

## Rollback Plan

If you need to rollback to the bash script:

1. Keep your old `deploy-latest.sh` file
2. The deployment structure is identical, so you can use either tool
3. Both tools create the same directory structure and symlinks

```bash
# Use bash script if needed
./deploy-latest.sh

# Or use Laravel Zero
php laravel-deployer deploy
```

## Frequently Asked Questions

### Can I use both tools?

Yes! They create the same directory structure and are fully compatible.

### Do I need to redeploy if I switch tools?

No. Both tools work with the same deployment structure.

### What about my existing deployments?

They will work fine. The Laravel Zero version will detect and use existing releases.

### Can I customize the deployment process?

Yes! Edit `app/Commands/DeployCommand.php` or add hooks in `config/deploy.php`.

### Is there a performance difference?

The Laravel Zero version is slightly slower to start (PHP bootstrap) but has better error handling and progress feedback.

## Getting Help

- Read the [README.md](README.md) for full documentation
- Check [EXAMPLES.md](EXAMPLES.md) for usage examples
- See [QUICKSTART.md](QUICKSTART.md) for development guide

## Contributing

Found an issue or want to add a feature? Contributions are welcome!

```bash
git clone https://github.com/your-org/laravel-deployer.git
cd laravel-deployer
composer install
# Make your changes
composer test
```
