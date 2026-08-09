<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AdminAuditMiddleware
{
    private const MUTATING_METHODS = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), self::MUTATING_METHODS, true)) {
            return $next($request);
        }

        $user = $request->user();
        $startedAt = microtime(true);

        try {
            $response = $next($request);

            $this->writeLog(
                $request,
                $user?->id,
                $response->getStatusCode(),
                $response->getStatusCode() >= 400 ? 'failed' : 'success',
                $startedAt
            );

            return $response;
        } catch (Throwable $exception) {
            $statusCode = $this->exceptionStatusCode($exception);

            $this->writeLog(
                $request,
                $user?->id,
                $statusCode,
                'failed',
                $startedAt,
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    private function exceptionStatusCode(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof ValidationException => 422,
            $exception instanceof AuthorizationException => 403,
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof HttpExceptionInterface =>
                $exception->getStatusCode(),
            default => 500,
        };
    }

    private function writeLog(
        Request $request,
        ?int $userId,
        int $statusCode,
        string $outcome,
        float $startedAt,
        ?string $exceptionMessage = null
    ): void {
        try {
            [$auditableType, $auditableId] = $this->auditable($request);
            $routeName = $request->route()?->getName();
            $description = $this->description(
                $request,
                $routeName,
                $outcome,
                $exceptionMessage
            );

            AdminAuditLog::create([
                'user_id' => $userId,
                'action' => $this->action($request, $routeName),
                'route_name' => $routeName,
                'method' => $request->method(),
                'url' => $request->url(),
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'description' => $description,
                'request_data' => [
                    'input' => $this->sanitize($request->all()),
                    'duration_ms' => round(
                        (microtime(true) - $startedAt) * 1000,
                        2
                    ),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit(
                    (string) $request->userAgent(),
                    1000,
                    ''
                ),
                'status_code' => $statusCode,
                'outcome' => $outcome,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            /* Audit logging must never break the administrator's action. */
        }
    }

    private function sanitize(array $data, string $prefix = ''): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if ($this->isSensitive($path)) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            if ($value instanceof UploadedFile) {
                $sanitized[$key] = [
                    'file_name' => $value->getClientOriginalName(),
                    'mime_type' => $value->getClientMimeType(),
                    'size' => $value->getSize(),
                ];
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value, $path);
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = Str::limit($value, 1000, '…');
                continue;
            }

            $sanitized[$key] = $value;
        }

        return Arr::except($sanitized, ['_token', '_method']);
    }

    private function isSensitive(string $key): bool
    {
        return Str::contains(Str::lower($key), [
            'password',
            'token',
            'secret',
            'authorization',
            'card_number',
            'card-number',
            'cvv',
            'cvc',
            'account_number',
            'iban',
            'swift_code',
        ]);
    }

    private function auditable(Request $request): array
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return [
                    $parameter::class,
                    (string) $parameter->getKey(),
                ];
            }
        }

        return [null, null];
    }

    private function action(Request $request, ?string $routeName): string
    {
        if ($routeName) {
            return Str::after($routeName, 'admin.');
        }

        return Str::lower($request->method()) . ':' . $request->path();
    }

    private function description(
        Request $request,
        ?string $routeName,
        string $outcome,
        ?string $exceptionMessage
    ): string {
        $action = $routeName
            ? Str::headline(Str::after($routeName, 'admin.'))
            : Str::headline($request->method() . ' ' . $request->path());

        $description = $action . ' — ' . Str::headline($outcome);

        if ($exceptionMessage) {
            $description .= ': ' . Str::limit(
                $exceptionMessage,
                300,
                '…'
            );
        }

        return Str::limit($description, 500, '…');
    }
}
