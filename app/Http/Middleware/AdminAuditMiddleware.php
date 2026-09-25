<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminAuditMiddleware
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'secret',
        'authorization',
        'card',
        'card_number',
        'cvv',
        'cvc',
        'account_number',
        'iban',
        'swift',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        $admin = Auth::guard('admin')->user();

        try {
            $response = $next($request);

            $this->writeLog(
                $request,
                $admin?->id,
                $response->getStatusCode(),
                $response->getStatusCode() >= 400 ? 'failed' : 'success',
                $startedAt
            );

            return $response;
        } catch (Throwable $exception) {
            $this->writeLog(
                $request,
                $admin?->id,
                500,
                'failed',
                $startedAt
            );

            throw $exception;
        }
    }

    private function writeLog(
        Request $request,
        ?int $adminId,
        int $statusCode,
        string $outcome,
        float $startedAt
    ): void {
        try {
            $route = $request->route();
            $routeName = $route?->getName();
            $action = $this->actionName($routeName, $request);
            [$auditableType, $auditableId] = $this->auditable($request);

            AdminAuditLog::create([
                // New separated administrator ownership.
                'admin_id' => $adminId,

                // Intentionally NULL for new records. user_id remains only as
                // a legacy historical bridge until final cleanup.
                'user_id' => null,

                'action' => $action,
                'route_name' => $routeName,
                'method' => strtoupper($request->method()),
                'url' => $request->fullUrl(),
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'description' => $this->description($action, $outcome),
                'request_data' => [
                    'input' => $this->sanitize($request->except([
                        '_token',
                        '_method',
                    ])),
                    'duration_ms' => round(
                        (microtime(true) - $startedAt) * 1000,
                        2
                    ),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $statusCode,
                'outcome' => $outcome,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Audit logging must never break the administrator action itself.
        }
    }

    private function actionName(?string $routeName, Request $request): string
    {
        if (filled($routeName)) {
            return str_starts_with($routeName, 'admin.')
                ? substr($routeName, 6)
                : $routeName;
        }

        return strtolower($request->method()) . ':' . trim($request->path(), '/');
    }

    private function auditable(Request $request): array
    {
        $parameters = $request->route()?->parameters() ?? [];

        foreach (array_reverse($parameters, true) as $parameter) {
            if ($parameter instanceof \Illuminate\Database\Eloquent\Model) {
                return [
                    $parameter::class,
                    (string) $parameter->getKey(),
                ];
            }
        }

        foreach (array_reverse($parameters, true) as $value) {
            if (is_scalar($value) && (string) $value !== '') {
                return [null, (string) $value];
            }
        }

        return [null, null];
    }

    private function description(string $action, string $outcome): string
    {
        $label = str_replace(['.', '-', '_'], ' ', $action);
        $label = ucwords(trim($label));

        return $label . ' — ' . ucfirst($outcome);
    }

    private function sanitize(mixed $value, ?string $key = null): mixed
    {
        if (
            $key !== null
            && $this->isSensitiveKey($key)
        ) {
            return '[REDACTED]';
        }

        if (!is_array($value)) {
            return is_string($value)
                ? mb_substr($value, 0, 2000)
                : $value;
        }

        $sanitized = [];

        foreach ($value as $childKey => $childValue) {
            $sanitized[$childKey] = $this->sanitize(
                $childValue,
                (string) $childKey
            );
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (str_contains($normalized, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }
}
