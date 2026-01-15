<?php

namespace App\Services;

use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\SpanContext;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;

class DemoService
{
    public static function executeDemo(): array
    {
        $mode = $_GET['mode'] ?? 'normal';
        
        $txContext = new TransactionContext();
        $txContext->setName('GET /demo');
        $txContext->setOp('http.server');
        $txContext->setDescription("Mode: {$mode}");

        $transaction = \Sentry\startTransaction($txContext);
        SentrySdk::getCurrentHub()->setSpan($transaction);

        try {
            // Breadcrumb: Request Start
            \Sentry\addBreadcrumb(
                new Breadcrumb(
                    Breadcrumb::LEVEL_INFO,
                    Breadcrumb::TYPE_HTTP,
                    'http.request',
                    'Demo Request Started',
                    ['mode' => $mode]
                )
            );

            // 1. Database Query Span
            $dbSpan = $transaction->startChild(
                (new SpanContext())
                    ->setOp('db.mysql')
                    ->setDescription('SELECT * FROM users WHERE status = active')
                    ->setData([
                        'query' => 'SELECT * FROM users',
                        'rows_affected' => 1250,
                        'connection' => 'mysql-prod-01'
                    ])
            );
            usleep(300000); // 300ms
            $dbSpan->finish();
            
            \Sentry\addBreadcrumb(
                new Breadcrumb(
                    Breadcrumb::LEVEL_DEBUG,
                    Breadcrumb::TYPE_DEFAULT,
                    'db.query',
                    'Database query completed',
                    ['rows' => 1250]
                )
            );

            // 2. Cache Check Span
            $cacheSpan = $transaction->startChild(
                (new SpanContext())
                    ->setOp('cache.get')
                    ->setDescription('Redis cache check')
                    ->setData(['key' => 'user_list', 'ttl' => 3600])
            );
            usleep(50000); // 50ms
            $cacheSpan->finish();

            // 3. External API Call Span
            $apiSpan = $transaction->startChild(
                (new SpanContext())
                    ->setOp('http.client')
                    ->setDescription('POST https://api.example.com/webhook')
                    ->setData([
                        'url' => 'https://api.example.com/webhook',
                        'method' => 'POST',
                        'status_code' => 200,
                        'response_time_ms' => 245
                    ])
            );
            usleep(500000); // 500ms
            $apiSpan->finish();
            
            \Sentry\addBreadcrumb(
                new Breadcrumb(
                    Breadcrumb::LEVEL_INFO,
                    Breadcrumb::TYPE_HTTP,
                    'api.call',
                    'External API responded',
                    ['status' => 200]
                )
            );

            // 4. Processing Span
            $processSpan = $transaction->startChild(
                (new SpanContext())
                    ->setOp('app.processing')
                    ->setDescription('Data transformation')
                    ->setData(['items_processed' => 150])
            );
            usleep(200000); // 200ms
            $processSpan->finish();

            // Error Modes
            if ($mode === 'error') {
                throw new \Exception("Test-Exception aus Mini-Projekt");
            } elseif ($mode === 'crash') {
                trigger_error("Kritischer Fehler!", E_USER_ERROR);
            } elseif ($mode === 'deprecated') {
                trigger_error("Deprecated function used", E_USER_DEPRECATED);
            } elseif ($mode === 'warning') {
                trigger_error("Test Warning", E_USER_WARNING);
            }

            // Success Response
            \Sentry\addBreadcrumb(
                new Breadcrumb(
                    Breadcrumb::LEVEL_INFO,
                    Breadcrumb::TYPE_DEFAULT,
                    'request.completed',
                    'Demo completed successfully'
                )
            );

            return [
                'status' => 'ok',
                'time' => date('c'),
                'mode' => $mode,
                'message' => 'Demo erfolgreich abgeschlossen'
            ];

        } catch (\Throwable $e) {
            \Sentry\captureException($e);
            
            \Sentry\addBreadcrumb(
                new Breadcrumb(
                    Breadcrumb::LEVEL_ERROR,
                    Breadcrumb::TYPE_ERROR,
                    'exception.caught',
                    'Exception captured',
                    ['exception' => get_class($e)]
                )
            );
            
            http_response_code(500);
            return [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];

        } finally {
            $transaction->finish();
        }
    }
}
