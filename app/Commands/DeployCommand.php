<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use ZipArchive;

class DeployCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'deploy
                            {--repo= : GitHub repository (owner/repo)}
                            {--app-name= : Application name}
                            {--install-dir= : Installation directory}
                            {--web-user=www-data : Web server user}
                            {--github-token= : GitHub token for private repos}
                            {--keep-releases=2 : Number of old releases to keep}
                            {--skip-migrations : Skip database migrations}
                            {--auto-cleanup : Automatically cleanup old releases}
                            {--force : Force deployment even if same version}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Deploy Laravel application from GitHub releases with zero downtime';

    private string $githubRepo;
    private string $appName;
    private string $currentFileDir;
    private string $installDir;
    private string $backupDir;
    private string $tempDir;
    private string $webUser;
    private ?string $githubToken;
    private int $keepReleases;
    private string $latestVersion;
    private string $downloadUrl;
    private string $assetName;
    private string $currentVersion;
    private string $newAppDir;
    private string $releaseDir;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Laravel Application Deployment');
        $this->newLine();

        // Initialize configuration
        if (!$this->initializeConfig()) {
            return self::FAILURE;
        }

        // Pre-deployment checks
        if (!$this->checkDependencies()) {
            return self::FAILURE;
        }

        if (!$this->getLatestRelease()) {
            return self::FAILURE;
        }

        if (!$this->checkCurrentVersion()) {
            return self::FAILURE;
        }

        $this->newLine(2);

        // Deployment process
        try {
            if (!$this->downloadAndPrepare()) {
                return self::FAILURE;
            }

            $this->preserveFiles();

            if (!$this->deploy()) {
                return self::FAILURE;
            }

            if (!$this->healthCheck()) {
                $this->rollback();
                return self::FAILURE;
            }

            // Cleanup
            if ($this->option('auto-cleanup') || $this->confirm('Deployment successful! Clean up old releases?', true)) {
                $this->cleanup();
            }

            $this->newLine();
            $this->info("🎉 Deployment completed successfully!");
            $this->info("New version {$this->latestVersion} is now live");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Deployment failed: {$e->getMessage()}");
            $this->rollback();
            return self::FAILURE;
        }
    }

    /**
     * Initialize configuration from options or prompts.
     */
    private function initializeConfig(): bool
    {
        $this->githubRepo = $this->option('repo')
            ?: $this->ask('GitHub repository (owner/repo)', '4msar/bill-organizer');

        $this->appName = $this->option('app-name')
            ?: $this->ask('Application name', basename($this->githubRepo));

        $this->currentFileDir = $this->option('install-dir')
            ?: $this->ask('Installation directory', getcwd());

        $this->installDir = "{$this->currentFileDir}/{$this->appName}";
        $this->backupDir = "{$this->currentFileDir}/backups";
        $this->tempDir = "{$this->currentFileDir}/temp";
        $this->webUser = $this->option('web-user');
        $this->githubToken = $this->option('github-token');
        $this->keepReleases = (int) $this->option('keep-releases');

        $this->info("Working directory: {$this->currentFileDir}");
        $this->info("Repository: {$this->githubRepo}");
        $this->info("Install directory: {$this->installDir}");
        $this->newLine();

        return true;
    }

    /**
     * Check if required dependencies are available.
     */
    private function checkDependencies(): bool
    {
        $this->task('Checking dependencies', function () {
            $deps = ['curl', 'unzip', 'php', 'composer'];
            foreach ($deps as $dep) {
                exec("command -v $dep", $output, $returnVar);
                if ($returnVar !== 0) {
                    throw new \Exception("Required dependency '$dep' is not installed");
                }
            }
            return true;
        });

        return true;
    }

    /**
     * Get latest release information from GitHub.
     */
    private function getLatestRelease(): bool
    {
        return $this->task('Fetching latest release information', function () {
            $headers = [];
            if ($this->githubToken) {
                $headers['Authorization'] = "Bearer {$this->githubToken}";
            }

            $response = Http::withHeaders($headers)
                ->get("https://api.github.com/repos/{$this->githubRepo}/releases/latest");

            if (!$response->successful()) {
                throw new \Exception('Repository not found or no releases available');
            }

            $releaseInfo = $response->json();

            $this->latestVersion = $releaseInfo['tag_name'] ?? null;
            $this->downloadUrl = $releaseInfo['assets'][0]['browser_download_url'] ?? null;
            $this->assetName = $releaseInfo['assets'][0]['name'] ?? null;

            if (!$this->latestVersion || !$this->downloadUrl) {
                throw new \Exception('Could not fetch release information');
            }

            $this->info("  Latest version: {$this->latestVersion}");
            return true;
        });
    }

    /**
     * Check current installed version.
     */
    private function checkCurrentVersion(): bool
    {
        $versionFile = "{$this->installDir}/version.txt";

        if (file_exists($versionFile)) {
            $this->currentVersion = trim(file_get_contents($versionFile));
            $this->info("Current version: {$this->currentVersion}");

            if ($this->currentVersion === $this->latestVersion && !$this->option('force')) {
                $this->warn("Already running the latest version ({$this->latestVersion})");
                if (!$this->confirm('Do you want to continue anyway?', false)) {
                    $this->info('Deployment cancelled');
                    return false;
                }
            }
        } else {
            $this->warn('No version file found, proceeding with deployment');
            $this->currentVersion = 'none';
        }

        return true;
    }

    /**
     * Download and prepare new version.
     */
    private function downloadAndPrepare(): bool
    {
        return $this->task('Downloading and extracting release', function () {
            // Clean and create temp directory
            if (File::exists($this->tempDir)) {
                File::deleteDirectory($this->tempDir);
            }
            File::makeDirectory($this->tempDir, 0755, true);

            // Download the release
            $headers = [];
            if ($this->githubToken) {
                $headers['Authorization'] = "Bearer {$this->githubToken}";
            }

            $zipPath = "{$this->tempDir}/{$this->assetName}";
            $response = Http::withHeaders($headers)->get($this->downloadUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download release');
            }

            file_put_contents($zipPath, $response->body());

            // Extract the release
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to extract release archive');
            }
            $zip->extractTo($this->tempDir);
            $zip->close();

            // Find the extracted directory
            $directories = File::directories($this->tempDir);
            $extractedDir = collect($directories)
                ->filter(fn($dir) => str_contains(basename($dir), $this->appName))
                ->first();

            if (!$extractedDir) {
                throw new \Exception('Could not find extracted application directory');
            }

            $this->newAppDir = $extractedDir;
            return true;
        });
    }

    /**
     * Preserve important files from current installation.
     */
    private function preserveFiles(): void
    {
        $this->task('Preserving important files', function () {
            $preserveFiles = [
                '.env',
                'storage/app',
                'storage/logs',
                'public/storage',
            ];

            foreach ($preserveFiles as $file) {
                $sourcePath = "{$this->installDir}/{$file}";
                $targetPath = "{$this->newAppDir}/{$file}";

                if (File::exists($sourcePath)) {
                    // Remove target if exists
                    if (File::exists($targetPath)) {
                        if (File::isDirectory($targetPath)) {
                            File::deleteDirectory($targetPath);
                        } else {
                            File::delete($targetPath);
                        }
                    }

                    // Copy the file/directory
                    if (File::isDirectory($sourcePath)) {
                        File::copyDirectory($sourcePath, $targetPath);
                    } else {
                        File::copy($sourcePath, $targetPath);
                    }
                }
            }

            return true;
        });
    }

    /**
     * Deploy new version.
     */
    private function deploy(): bool
    {
        $this->task('Deploying new version', function () {
            // Create new release directory
            $this->releaseDir = "{$this->installDir}-{$this->latestVersion}";

            // Copy new version to release directory
            File::copyDirectory($this->newAppDir, $this->releaseDir);

            // Set proper permissions
            $this->setPermissions();

            // Create symlink
            if (File::exists($this->installDir) && is_link($this->installDir)) {
                unlink($this->installDir);
            }
            symlink($this->releaseDir, $this->installDir);

            return true;
        });

        // Run Laravel optimization
        $this->optimizeLaravel();

        // Update version file
        file_put_contents("{$this->installDir}/version.txt", $this->latestVersion);

        return true;
    }

    /**
     * Set proper file permissions.
     */
    private function setPermissions(): void
    {
        // Check if running as root or if web user exists
        exec("id -u {$this->webUser}", $output, $returnVar);
        if ($returnVar === 0 && posix_geteuid() === 0) {
            exec("chown -R {$this->webUser}:{$this->webUser} {$this->releaseDir}");
        }

        exec("chmod -R 755 {$this->releaseDir}");
        exec("chmod -R 775 {$this->releaseDir}/storage {$this->releaseDir}/bootstrap/cache 2>/dev/null || true");
    }

    /**
     * Optimize Laravel application.
     */
    private function optimizeLaravel(): void
    {
        $this->info('Optimizing Laravel application...');

        $commands = [
            'config:clear',
            'route:clear',
            'view:clear',
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        foreach ($commands as $command) {
            exec("cd {$this->releaseDir} && php artisan {$command} 2>/dev/null || true");
        }

        // Run migrations
        if (!$this->option('skip-migrations')) {
            if ($this->confirm('Run database migrations?', false)) {
                $this->task('Running migrations', function () {
                    exec("cd {$this->releaseDir} && php artisan migrate --force", $output, $returnVar);
                    if ($returnVar !== 0) {
                        throw new \Exception('Migration failed');
                    }
                    return true;
                });
            }
        }
    }

    /**
     * Perform health check on deployed application.
     */
    private function healthCheck(): bool
    {
        return $this->task('Performing health check', function () {
            exec("cd {$this->installDir} && php artisan --version", $output, $returnVar);
            if ($returnVar !== 0) {
                throw new \Exception('Application health check failed');
            }
            return true;
        });
    }

    /**
     * Cleanup old releases.
     */
    private function cleanup(): void
    {
        $this->task('Cleaning up old releases', function () {
            // Remove temp directory
            if (File::exists($this->tempDir)) {
                File::deleteDirectory($this->tempDir);
            }

            // Find and remove old releases
            $pattern = dirname($this->installDir) . "/{$this->appName}-v*";
            $releases = glob($pattern, GLOB_ONLYDIR);

            if (!empty($releases)) {
                usort($releases, 'version_compare');
                $currentRelease = "{$this->appName}-{$this->latestVersion}";

                foreach ($releases as $release) {
                    if (basename($release) !== $currentRelease) {
                        $this->info("  Removing old release: " . basename($release));
                        File::deleteDirectory($release);
                    }
                }
            }

            return true;
        });
    }

    /**
     * Rollback to previous release.
     */
    private function rollback(): void
    {
        $this->error('Deployment failed! Attempting rollback...');

        $pattern = dirname($this->installDir) . "/{$this->appName}-v*";
        $releases = glob($pattern, GLOB_ONLYDIR);

        if (!empty($releases)) {
            usort($releases, 'version_compare');
            $previousRelease = $releases[count($releases) - 2] ?? null;

            if ($previousRelease) {
                if (File::exists($this->installDir) && is_link($this->installDir)) {
                    unlink($this->installDir);
                }
                symlink($previousRelease, $this->installDir);
                $this->warn("Rolled back to: " . basename($previousRelease));
            } else {
                $this->error('No previous release found for rollback');
            }
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->daily();
    }
}
