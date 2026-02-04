<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default GitHub Repository
    |--------------------------------------------------------------------------
    |
    | The default GitHub repository to deploy from in the format owner/repo.
    | This can be overridden using the --repo option when running the command.
    |
    */

    'github_repo' => env('DEPLOY_GITHUB_REPO', '4msar/bill-organizer'),

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | The name of the application being deployed. This is used for directory
    | naming and identification purposes.
    |
    */

    'app_name' => env('DEPLOY_APP_NAME', 'bill-organizer'),

    /*
    |--------------------------------------------------------------------------
    | Installation Directory
    |--------------------------------------------------------------------------
    |
    | The base directory where the application will be installed.
    | Releases will be created as subdirectories within this path.
    |
    */

    'install_dir' => env('DEPLOY_INSTALL_DIR'),

    /*
    |--------------------------------------------------------------------------
    | Web Server User
    |--------------------------------------------------------------------------
    |
    | The user that runs the web server. Files will be owned by this user
    | to ensure proper permissions. Common values: www-data, nginx, apache.
    |
    */

    'web_user' => env('DEPLOY_WEB_USER', 'www-data'),

    /*
    |--------------------------------------------------------------------------
    | GitHub Personal Access Token
    |--------------------------------------------------------------------------
    |
    | Optional GitHub token for accessing private repositories or to avoid
    | rate limiting. Can be generated at https://github.com/settings/tokens
    |
    */

    'github_token' => env('DEPLOY_GITHUB_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Keep Releases
    |--------------------------------------------------------------------------
    |
    | The number of old releases to keep. Set to 0 to keep none.
    | Older releases will be automatically deleted during cleanup.
    |
    */

    'keep_releases' => env('DEPLOY_KEEP_RELEASES', 2),

    /*
    |--------------------------------------------------------------------------
    | Files to Preserve
    |--------------------------------------------------------------------------
    |
    | List of files and directories that should be preserved between
    | deployments. These are typically configuration and user data.
    |
    */

    'preserve_files' => [
        '.env',
        'storage/app',
        'storage/logs',
        'public/storage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Hooks
    |--------------------------------------------------------------------------
    |
    | Commands to run at different stages of the deployment process.
    | Leave empty to skip. Commands are run in the application directory.
    |
    */

    'hooks' => [
        'before_deploy' => [
            // 'php artisan down',
        ],
        'after_deploy' => [
            'php artisan config:cache',
            'php artisan route:cache',
            'php artisan view:cache',
        ],
        'after_success' => [
            // 'php artisan up',
        ],
    ],

];
