<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends AdminController
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request)
            ->with('user:id,name,email');

        $logs = $query
            ->latest('created_at')
            ->paginate(40)
            ->withQueryString();

        $admins = User::query()
            ->where('is_admin', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $actions = AdminAuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $stats = [
            'today' => AdminAuditLog::query()
                ->whereDate('created_at', today())
                ->count(),
            'seven_days' => AdminAuditLog::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'failed' => AdminAuditLog::query()
                ->where('outcome', 'failed')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'active_admins' => AdminAuditLog::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id'),
        ];

        return view(
            'admin.audit-logs.index',
            compact('logs', 'admins', 'actions', 'stats')
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'admin-audit-logs-'
            . now()->format('Y-m-d-His')
            . '.csv';

        return response()->streamDownload(
            function () use ($request): void {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'Date and Time',
                    'Administrator',
                    'Email',
                    'Action',
                    'Method',
                    'Route',
                    'Outcome',
                    'Status Code',
                    'IP Address',
                    'Record Type',
                    'Record ID',
                    'Description',
                ]);

                $this->filteredQuery($request)
                    ->with('user:id,name,email')
                    ->reorder()
                    ->chunkById(
                        500,
                        function ($logs) use ($handle): void {
                            foreach ($logs as $log) {
                                fputcsv($handle, [
                                    optional($log->created_at)
                                        ->format('Y-m-d H:i:s'),
                                    $log->user?->name,
                                    $log->user?->email,
                                    $log->action,
                                    $log->method,
                                    $log->route_name,
                                    $log->outcome,
                                    $log->status_code,
                                    $log->ip_address,
                                    $log->auditable_type,
                                    $log->auditable_id,
                                    $log->description,
                                ]);
                            }
                        },
                        'id'
                    );

                fclose($handle);
            },
            $fileName,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function filteredQuery(Request $request): Builder
    {
        $search = trim($request->string('search')->value());
        $userId = $request->integer('user_id');
        $action = trim($request->string('action')->value());
        $outcome = trim($request->string('outcome')->value());
        $dateFrom = trim($request->string('date_from')->value());
        $dateTo = trim($request->string('date_to')->value());

        return AdminAuditLog::query()
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $searchQuery) use ($search): void {
                            $searchQuery
                                ->where('action', 'like', '%' . $search . '%')
                                ->orWhere('description', 'like', '%' . $search . '%')
                                ->orWhere('route_name', 'like', '%' . $search . '%')
                                ->orWhere('ip_address', 'like', '%' . $search . '%')
                                ->orWhereHas(
                                    'user',
                                    fn (Builder $userQuery) => $userQuery
                                        ->where('name', 'like', '%' . $search . '%')
                                        ->orWhere('email', 'like', '%' . $search . '%')
                                );
                        }
                    );
                }
            )
            ->when(
                $userId > 0,
                fn (Builder $query) => $query->where('user_id', $userId)
            )
            ->when(
                $action !== '',
                fn (Builder $query) => $query->where('action', $action)
            )
            ->when(
                in_array($outcome, ['success', 'failed'], true),
                fn (Builder $query) => $query->where('outcome', $outcome)
            )
            ->when(
                $this->validDate($dateFrom),
                fn (Builder $query) => $query->where(
                    'created_at',
                    '>=',
                    Carbon::parse($dateFrom)->startOfDay()
                )
            )
            ->when(
                $this->validDate($dateTo),
                fn (Builder $query) => $query->where(
                    'created_at',
                    '<=',
                    Carbon::parse($dateTo)->endOfDay()
                )
            );
    }

    private function validDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        try {
            Carbon::createFromFormat('Y-m-d', $value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
