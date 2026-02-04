# Usage Examples

This file contains practical examples of how to use Laravel Deployer in different scenarios.

## Basic Usage

### Interactive Mode (Recommended for First Time)

```bash
php laravel-deployer deploy
```

The command will prompt you for:

- GitHub repository (e.g., username/repo)
- Application name
- Installation directory
- And confirm actions throughout

### Quick Deploy with Minimal Options

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www
```

## Complete Examples

### Example 1: Deploy a Public Repository

```bash
php laravel-deployer deploy \
    --repo=4msar/bill-organizer \
    --app-name=bill-organizer \
    --install-dir=/var/www/html \
    --web-user=www-data \
    --keep-releases=3 \
    --auto-cleanup
```

### Example 2: Deploy a Private Repository

```bash
php laravel-deployer deploy \
    --repo=mycompany/private-app \
    --app-name=private-app \
    --install-dir=/var/www \
    --github-token=ghp_xxxxxxxxxxxxxxxxxxxx \
    --web-user=nginx \
    --keep-releases=5
```

### Example 3: Force Re-deployment of Same Version

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www \
    --force
```

### Example 4: Deploy Without Running Migrations

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www \
    --skip-migrations
```

### Example 5: Deploy with Auto-Cleanup

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www \
    --auto-cleanup \
    --keep-releases=1
```

## Using Environment Variables

Create a `.env` file:

```env
DEPLOY_GITHUB_REPO=myuser/myapp
DEPLOY_APP_NAME=myapp
DEPLOY_INSTALL_DIR=/var/www
DEPLOY_WEB_USER=www-data
DEPLOY_GITHUB_TOKEN=ghp_xxxxxxxxxxxx
DEPLOY_KEEP_RELEASES=2
```

Then simply run:

```bash
php laravel-deployer deploy
```

## Production Server Examples

### Ubuntu/Debian with Apache

```bash
sudo php laravel-deployer deploy \
    --repo=mycompany/webapp \
    --app-name=webapp \
    --install-dir=/var/www \
    --web-user=www-data \
    --auto-cleanup
```

### CentOS/RHEL with Nginx

```bash
sudo php laravel-deployer deploy \
    --repo=mycompany/webapp \
    --app-name=webapp \
    --install-dir=/var/www/html \
    --web-user=nginx \
    --keep-releases=3
```

### Using with Multiple Applications

Deploy first app:

```bash
php laravel-deployer deploy \
    --repo=company/app1 \
    --app-name=app1 \
    --install-dir=/var/www
```

Deploy second app:

```bash
php laravel-deployer deploy \
    --repo=company/app2 \
    --app-name=app2 \
    --install-dir=/var/www
```

Result:

```
/var/www/
├── app1 -> app1-v1.0.0
├── app1-v1.0.0/
├── app2 -> app2-v2.3.1
└── app2-v2.3.1/
```

## CI/CD Integration Examples

### GitHub Actions

```yaml
name: Deploy to Production

on:
    release:
        types: [published]

jobs:
    deploy:
        runs-on: ubuntu-latest
        steps:
            - name: Install Laravel Deployer
              run: composer global require laravel-zero/laravel-deployer

            - name: Deploy to Server
              env:
                  DEPLOY_GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
              run: |
                  laravel-deployer deploy \
                    --repo=${{ github.repository }} \
                    --install-dir=/var/www/html \
                    --github-token=$DEPLOY_GITHUB_TOKEN \
                    --auto-cleanup \
                    --force
```

### GitLab CI

```yaml
deploy:
    stage: deploy
    script:
        - composer global require laravel-zero/laravel-deployer
        - |
            laravel-deployer deploy \
              --repo=$CI_PROJECT_PATH \
              --install-dir=/var/www \
              --github-token=$GITHUB_TOKEN \
              --auto-cleanup
    only:
        - tags
```

### Jenkins Pipeline

```groovy
pipeline {
    agent any

    stages {
        stage('Deploy') {
            steps {
                sh '''
                    composer global require laravel-zero/laravel-deployer
                    laravel-deployer deploy \
                        --repo=company/app \
                        --install-dir=/var/www \
                        --github-token=${GITHUB_TOKEN} \
                        --auto-cleanup \
                        --skip-migrations
                '''
            }
        }
    }
}
```

## Scheduled Deployments

### Using Cron

Deploy every night at 2 AM:

```cron
0 2 * * * cd /opt/deployer && php laravel-deployer deploy --repo=myuser/myapp --install-dir=/var/www --auto-cleanup --force >> /var/log/deploy.log 2>&1
```

## Advanced Scenarios

### Deploy with Custom Web User

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/home/deploy/apps \
    --web-user=deploy
```

### Deploy to Custom Directory Structure

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --app-name=myapp-prod \
    --install-dir=/opt/applications
```

Result:

```
/opt/applications/
├── myapp-prod -> myapp-prod-v1.0.0
└── myapp-prod-v1.0.0/
```

### Deploy Multiple Environments

Staging:

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --app-name=myapp-staging \
    --install-dir=/var/www/staging
```

Production:

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --app-name=myapp-production \
    --install-dir=/var/www/production
```

## Troubleshooting Examples

### Verbose Output for Debugging

```bash
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www \
    -vvv
```

### Test Download Without Deploying

You can modify the command or test connectivity:

```bash
# Test GitHub API access
curl -H "Authorization: Bearer ghp_token" \
  https://api.github.com/repos/myuser/myapp/releases/latest
```

### Manual Rollback

If automatic rollback fails:

```bash
cd /var/www
ls -la myapp-*
ln -sfn myapp-v1.0.0 myapp
```

## Best Practices

### 1. Always Test in Staging First

```bash
# Stage deployment
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www/staging

# If successful, deploy to production
php laravel-deployer deploy \
    --repo=myuser/myapp \
    --install-dir=/var/www/production
```

### 2. Use Version Tags

Make sure your GitHub releases use semantic versioning:

- v1.0.0 (major release)
- v1.1.0 (minor release)
- v1.1.1 (patch)

### 3. Keep Multiple Releases for Quick Rollback

```bash
php laravel-deployer deploy \
    --keep-releases=3
```

### 4. Always Backup Before Deployment

```bash
# Backup database first
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql

# Then deploy
php laravel-deployer deploy --repo=myuser/myapp
```

### 5. Monitor Deployment

```bash
# Watch logs during deployment
tail -f /var/www/myapp/storage/logs/laravel.log
```
