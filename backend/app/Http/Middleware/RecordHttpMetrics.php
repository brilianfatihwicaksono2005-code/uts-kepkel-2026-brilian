<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordHttpMetrics
{
    public function __construct(private CollectorRegistry $registry)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $status = 500;

        try {
            $response = $next($request);
            $status = $response->getStatusCode();

            return $response;
        } catch (Throwable $exception) {
            $status = method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : 500;

            throw $exception;
        } finally {
            $this->record($request, $status);
        }
    }

    private function record(Request $request, int $status): void
    {
        $path = trim($request->path(), '/');

        if ($path === 'metrics' || $path === 'api/health') {
            return;
        }

        $route = $request->route();
        $routeLabel = $route !== null ? $route->uri() : 'tidak_cocok';

        try {
            $this->registry
                ->getOrRegisterCounter(
                    '',
                    'http_requests_total',
                    'Jumlah permintaan HTTP yang diterima aplikasi.',
                    ['method', 'route', 'status']
                )
                ->inc([$request->method(), $routeLabel, (string) $status]);
        } catch (Throwable $exception) {
            Log::warning('Metrik HTTP gagal dicatat', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
