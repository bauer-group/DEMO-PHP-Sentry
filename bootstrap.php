<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Get Sentry config from .env with defaults
$sentryDsn = $_ENV['SENTRY_DSN'] ?? $_SERVER['SENTRY_DSN'] ?? '';
$environment = $_ENV['SENTRY_ENVIRONMENT'] ?? $_SERVER['SENTRY_ENVIRONMENT'] ?? 'development';
$release = $_ENV['SENTRY_RELEASE'] ?? $_SERVER['SENTRY_RELEASE'] ?? 'mini-project@0.1.0';
$tracesSampleRate = (float)($_ENV['SENTRY_TRACES_SAMPLE_RATE'] ?? $_SERVER['SENTRY_TRACES_SAMPLE_RATE'] ?? 1.0);
$profilesSampleRate = (float)($_ENV['SENTRY_PROFILES_SAMPLE_RATE'] ?? $_SERVER['SENTRY_PROFILES_SAMPLE_RATE'] ?? 1.0);
$maxBreadcrumbs = (int)($_ENV['SENTRY_MAX_BREADCRUMBS'] ?? $_SERVER['SENTRY_MAX_BREADCRUMBS'] ?? 50);

// Validate that Sentry DSN is set
if (empty($sentryDsn)) {
    // Fallback to mock DSN für Demo - ist gültig aber sendet nirgends hin
    $sentryDsn = 'https://mock@errors-observability.app/0';
    // Oder zeige Warning in Header-Comments
    error_log('[Sentry] Warning: SENTRY_DSN not configured. Events will not be sent.');
}

\Sentry\init([
    'dsn' => $sentryDsn,

    // Tracing & Profiling
    'traces_sample_rate' => $tracesSampleRate,
    'profiles_sample_rate' => $profilesSampleRate,

    'environment' => $environment,
    'release' => $release,
    'attach_stacktrace' => true,
    'max_breadcrumbs' => $maxBreadcrumbs,
]);

\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    // Tags für Kategorisierung
    $scope->setTag('os', php_uname('s'));
    $scope->setTag('php_sapi', php_sapi_name());
    $scope->setTag('server', gethostname());
    $scope->setTag('component', 'demo-app');
    $scope->setTag('version', $_ENV['SENTRY_RELEASE'] ?? $_SERVER['SENTRY_RELEASE'] ?? 'mini-project@0.1.0');
    
    // Context für detaillierte Infos
    $scope->setContext('runtime', [
        'php' => PHP_VERSION,
        'sapi' => php_sapi_name(),
        'cwd' => getcwd(),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
    ]);
    
    $scope->setContext('request', [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
    ]);
    
    $scope->setContext('system', [
        'os' => php_uname(),
        'load_avg' => implode(', ', sys_getloadavg()),
        'disk_free' => format_bytes(disk_free_space('.')),
        'memory_usage' => format_bytes(memory_get_usage(true)),
    ]);
    
    // User Context (Demo)
    $scope->setUser([
        'id' => 'demo-user',
        'username' => 'mini-project',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
    
    // Breadcrumb für App-Start
    \Sentry\addBreadcrumb(
        new \Sentry\Breadcrumb(
            \Sentry\Breadcrumb::LEVEL_INFO,
            \Sentry\Breadcrumb::TYPE_DEFAULT,
            'app.lifecycle',
            'Application started'
        )
    );
});

function format_bytes(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}
