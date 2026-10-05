<?php
namespace App\Http\Controllers\Admin;
use App\Models\AdminBackup;
use App\Services\AdminBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
class AdminBackupController extends AdminController
{
    public function index(): View
    {
        $backups = AdminBackup::query()
            ->with([
                'creator:id,name,email',
                'legacyCreator:id,name,email',
            ])
            ->latest()
            ->paginate(25);
        $stats = [
            'count' => AdminBackup::where('status', 'completed')->count(),
            'size' => (int) AdminBackup::where('status', 'completed')->sum('file_size'),
            'latest' => AdminBackup::where('status', 'completed')->latest('completed_at')->first(),
            'failed' => AdminBackup::where('status', 'failed')->count(),
        ];
        return view('admin.backups.index', compact('backups', 'stats'));
    }
    public function store(
        Request $request,
        AdminBackupService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'type' => ['required', 'in:database,full'],
        ]);
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 403);
        try {
            $backup = $service->create(
                $validated['type'],
                (int) $admin->getAuthIdentifier()
            );
            return back()->with(
                'success',
                'Backup created successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);
            return back()->with(
                'error',
                'Backup failed: ' . $exception->getMessage()
            );
        }
    }
    public function download(AdminBackup $backup): StreamedResponse
    {
        abort_unless(
            $this->safePath($backup) && $backup->status === 'completed'
            && Storage::disk($backup->disk)->exists($backup->file_path),
            404
        );
        return Storage::disk($backup->disk)
            ->download($backup->file_path, $backup->name);
    }
    public function destroy(AdminBackup $backup): RedirectResponse
    {
        if($backup->status==='processing')return back()->with('error','This backup is still processing.');
        if(!$this->safePath($backup))return back()->with('error','The backup path is invalid.');
        $disk=Storage::disk('local');
        if($disk->exists($backup->file_path)&&!$disk->delete($backup->file_path))return back()->with('error','The backup file could not be deleted.');
        $backup->delete();return back()->with('success','Backup deleted permanently.');
    }
    private function safePath(AdminBackup $backup): bool
    {
        return $backup->disk==='local' && (bool)preg_match('/^admin-backups\/[a-zA-Z0-9_.-]+\.(?:zip|sql\.gz)$/D',(string)$backup->file_path);
    }
    public function cleanup(Request $request): RedirectResponse
    {
        $data=$request->validate(['days'=>['required','integer','in:7,14,30,60,90']]);$deleted=0;$skipped=0;
        foreach(AdminBackup::where('created_at','<',now()->subDays((int)$data['days']))->whereIn('status',['completed','failed'])->get()as $backup){
            if(!$this->safePath($backup)){ $skipped++;continue; }
            $disk=Storage::disk('local');if($disk->exists($backup->file_path)&&!$disk->delete($backup->file_path)){$skipped++;continue;}
            $backup->delete();$deleted++;
        }
        return back()->with($skipped?'error':'success',$deleted.' old backup(s) deleted.'.($skipped?' '.$skipped.' could not be removed.':''));
    }
    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
}