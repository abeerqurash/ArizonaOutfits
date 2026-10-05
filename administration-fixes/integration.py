# Executed by build.py with shared helpers.
p='app/Services/AdminBackupService.php'
change(p,"$stamp = now()->format('Y-m-d_H-i-s');", "$stamp = now()->format('Y-m-d_H-i-s') . '_' . \\Illuminate\\Support\\Str::uuid();\n        if (!in_array($type, ['database','full'], true)) throw new RuntimeException('Invalid backup type.');")
s=files[p];a=s.index('        $temporarySql = storage_path(');b=s.index('\n\n        try {',a);s=s[:a]+"        $temporarySql = Storage::disk('local')->path('admin-backups/temp-' . $backup->id . '-' . $stamp . '.sql.gz');"+s[b:];put(p,s)
method(p,'writeDatabaseDump',r'''private function writeDatabaseDump(string $path): array
    {
        $source=DB::connection();
        if(!in_array($source->getDriverName(),['mysql','mariadb'],true))throw new RuntimeException('The backup exporter supports MySQL and MariaDB.');
        $name='administration_backup_snapshot';config(['database.connections.'.$name=>$source->getConfig()]);DB::purge($name);
        $connection=DB::connection($name);$pdo=$connection->getPdo();$stream=gzopen($path,'wb9');
        if($stream===false)throw new RuntimeException('Unable to open the backup file.');
        $write=function(string $sql)use($stream):void{if(gzwrite($stream,$sql)!==strlen($sql))throw new RuntimeException('Unable to finish writing the backup.');};
        $quote=fn(string $identifier):string=>'`'.str_replace('`','``',$identifier).'`';$rows=0;$count=0;
        try{
            $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
            $tables=$connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $sqlMode=(string)$pdo->query('SELECT @@SESSION.sql_mode')->fetchColumn();
            $sqlMode=implode(',',array_unique(array_filter(array_merge(explode(',',$sqlMode),['NO_AUTO_VALUE_ON_ZERO']))));
            $write("-- Arizona Outfits database backup\n-- Created: ".now()->toDateTimeString()."\nSET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\nSET SQL_MODE=".$pdo->quote($sqlMode).";\n\n");
            foreach($tables as $record){
                $table=array_values((array)$record)[0];$quoted=$quote($table);
                $create=array_values((array)$connection->select('SHOW CREATE TABLE '.$quoted)[0])[1];
                $columns=collect($connection->select('SHOW FULL COLUMNS FROM '.$quoted))->filter(fn($column)=>!preg_match('/(?:VIRTUAL|STORED) GENERATED/i',$column->Extra))->pluck('Field')->all();
                $primary=collect($connection->select('SHOW KEYS FROM '.$quoted." WHERE Key_name = 'PRIMARY'"))->sortBy('Seq_in_index')->pluck('Column_name')->all();
                $list=implode(',',array_map($quote,$columns));
                $write('DROP TABLE IF EXISTS '.$quoted.";\n".$create.";\n\n");
                // Use a dedicated unbuffered connection: no live business transaction is changed.
                $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
                $statement=$pdo->query('SELECT '.$list.' FROM '.$quoted.($primary?' ORDER BY '.implode(',',array_map($quote,$primary)):''));
                while($row=$statement->fetch(\PDO::FETCH_NUM)){
                    $values=array_map(fn($value)=>$value===null?'NULL':$pdo->quote((string)$value),$row);
                    $write('INSERT INTO '.$quoted.' ('.$list.') VALUES ('.implode(',',$values).");\n");$rows++;
                }
                $statement->closeCursor();$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);$count++;$write("\n");
            }
            $write("SET FOREIGN_KEY_CHECKS=1;\n");$pdo->exec('COMMIT');
            if(!gzclose($stream))throw new RuntimeException('Unable to close the backup stream.');$stream=null;
            return [$count,$rows];
        }catch(Throwable $exception){try{$pdo->exec('ROLLBACK');}catch(Throwable){}throw $exception;}
        finally{if(is_resource($stream))gzclose($stream);DB::purge($name);}
    }''')
change(p,"        $zip->close();", "        if (!$zip->close()) throw new RuntimeException('Unable to finish the backup archive.');")
change(p,"            $zip->addFile(\n                $file->getPathname(),\n                $archiveRoot . '/' . $relative\n            );", "            if (!$zip->addFile($file->getPathname(), $archiveRoot . '/' . $relative)) throw new RuntimeException('Unable to add an uploaded file to the backup.');")
p='app/Http/Controllers/Admin/AdminBackupController.php'
change(p,"$backup->status === 'completed'", "$this->safePath($backup) && $backup->status === 'completed'")
method(p,'destroy',r'''public function destroy(AdminBackup $backup): RedirectResponse
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
    }''')
method(p,'cleanup',r'''public function cleanup(Request $request): RedirectResponse
    {
        $data=$request->validate(['days'=>['required','integer','in:7,14,30,60,90']]);$deleted=0;$skipped=0;
        foreach(AdminBackup::where('created_at','<',now()->subDays((int)$data['days']))->whereIn('status',['completed','failed'])->get()as $backup){
            if(!$this->safePath($backup)){ $skipped++;continue; }
            $disk=Storage::disk('local');if($disk->exists($backup->file_path)&&!$disk->delete($backup->file_path)){$skipped++;continue;}
            $backup->delete();$deleted++;
        }
        return back()->with($skipped?'error':'success',$deleted.' old backup(s) deleted.'.($skipped?' '.$skipped.' could not be removed.':''));
    }''')

p='app/Http/Controllers/Admin/AdminAnalyticsController.php'
change(p,"private const PAID_STATUSES", "private string $currency = 'USD';\n    private const PAID_STATUSES")
change(p,"[$start, $end] = $this->dateRange($request);", "[$start, $end] = $this->dateRange($request);\n        $currencies = Order::withTrashed()->whereNotNull('currency')->distinct()->pluck('currency')->map(fn($value)=>strtoupper($value))->unique()->sort()->values();\n        $currency = strtoupper($request->string('currency')->value() ?: \\App\\Models\\EcommerceSetting::current()->currency);\n        if (!preg_match('/^[A-Z]{3}$/D',$currency)) throw \\Illuminate\\Validation\\ValidationException::withMessages(['currency'=>'Choose a three-letter currency code.']);\n        $this->currency = $currency;\n        $currencyTotals = Order::withTrashed()->whereIn('payment_status', self::PAID_STATUSES)->whereNotIn('order_status', self::EXCLUDED_ORDER_STATUSES)->whereBetween('created_at', [$start,$end])->selectRaw('UPPER(currency) as currency, SUM(total) as revenue')->groupByRaw('UPPER(currency)')->get();")
change(p,"$days = $start->diffInDays($end) + 1;", "$days = (int)$start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;")
change(p,"'start', 'end', 'metrics', 'chartData', 'topProducts', 'topCountries'", "'start', 'end', 'metrics', 'chartData', 'topProducts', 'topCountries', 'currency', 'currencies', 'currencyTotals'")
change(p,"function () use ($start, $end, $summary, $daily, $products)","function () use ($start, $end, $summary, $daily, $products, $currency)")
change(p,"fputcsv($output, ['Arizona Outfits Analytics Export']);", "fputcsv($output, ['Arizona Outfits Analytics Export']);\n            fputcsv($output, ['Currency', $currency]);")
change(p,"return Order::query()->whereIn", "return Order::withTrashed()->whereRaw('UPPER(currency) = ?', [$this->currency])->whereIn")
change(p,"$rows = Order::query()", "$rows = Order::withTrashed()->whereRaw('UPPER(currency) = ?', [$this->currency])")
change(p,"->whereBetween('orders.created_at', [$start, $end])", "->whereBetween('orders.created_at', [$start, $end])->whereRaw('UPPER(orders.currency) = ?', [$this->currency])")
change(p,"'nullable', 'date'", "'nullable', 'date_format:Y-m-d'")
change(p,"abort(422, 'The start date must be before the end date.')", "throw \\Illuminate\\Validation\\ValidationException::withMessages(['start_date'=>'The start date must be before the end date.'])")
change(p,"if ($start->diffInDays($end) > 366) abort(422, 'Analytics can cover a maximum of 366 days at once.');", "if ((int)$start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1 > 366) throw \\Illuminate\\Validation\\ValidationException::withMessages(['end_date'=>'Analytics can cover a maximum of 366 days at once.']);")
p='resources/views/admin/analytics/index.blade.php'
change(p,"<button type=\"submit\">", "<div><label for=\"analytics-currency\">Currency</label><select id=\"analytics-currency\" name=\"currency\">@foreach($currencies->push($currency)->unique() as $code)<option value=\"{{ $code }}\" @selected($code === $currency)>{{ $code }}</option>@endforeach</select></div><button type=\"submit\">")
change(p,"'$'.number_format", "$currency.' '.number_format")
change(p,"<strong>${{", "<strong>{{ $currency }} {{")
change(p,"label:'Revenue ($)'", "label:'Revenue ('+data.currency+')'")
change(p,'@json($chartData)',"@json(array_merge($chartData,['currency'=>$currency]))")
change(p,'<section class="metric-grid">','<section class="analytics-panel" style="padding:15px"><p>Figures below are in {{ $currency }} and include archived paid orders. New customer counts cover all currencies.</p>@foreach($currencyTotals as $row)<span style="margin-right:20px">{{ $row->currency }} {{ number_format($row->revenue,2) }}</span>@endforeach<p>Amounts in different currencies are reported separately.</p></section>\n    <section class="metric-grid">')

# Limit delegated administrators to permissions they already possess.
put('app/Services/AdminAccessPolicy.php',r'''<?php
namespace App\Services;
use App\Models\{Admin,AdminPermission,AdminRole};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AdminAccessPolicy
{
    public function permissions(array $ids): void
    {
        $actor=Auth::guard('admin')->user();abort_unless($actor,403);if($actor->is_super_admin)return;
        foreach(AdminPermission::whereIn('id',$ids)->pluck('slug')as $slug)if(!$actor->hasAdminPermission($slug))throw ValidationException::withMessages(['permission_ids'=>'You cannot grant permissions you do not have.']);
    }
    public function team(bool $super, array $roles, ?Admin $target=null): void
    {
        $actor=Auth::guard('admin')->user();abort_unless($actor,403);
        if(!$actor->is_super_admin && ($super || $target?->is_super_admin))throw ValidationException::withMessages(['is_super_admin'=>'Only a Super Administrator can manage Super Administrator access.']);
        $this->permissions(AdminRole::with('permissions')->whereIn('id',$roles)->get()->flatMap(fn($role)=>$role->permissions->pluck('id'))->unique()->all());
    }
}
''')
p='app/Http/Controllers/Admin/AdminRoleController.php'
change(p,"$validated = $this->validatedData($request);", "$validated = $this->validatedData($request);\n        app(\\App\\Services\\AdminAccessPolicy::class)->permissions($validated['permission_ids']);")
change(p,"$validated = $this->validatedData($request, $adminRole);", "$validated = $this->validatedData($request, $adminRole);\n        $policy=app(\\App\\Services\\AdminAccessPolicy::class);\n        $policy->permissions($adminRole->permissions()->pluck('admin_permissions.id')->all());\n        $policy->permissions($validated['permission_ids']);")
change(p,"if ($adminRole->is_system) {", "app(\\App\\Services\\AdminAccessPolicy::class)->permissions($adminRole->permissions()->pluck('admin_permissions.id')->all());\n        if ($adminRole->is_system) {")
p='app/Http/Controllers/Admin/AdminUserController.php'
change(p,"$roleIds = $this->roleIds($validated, $isSuperAdmin);", "$roleIds = $this->roleIds($validated, $isSuperAdmin);\n        app(\\App\\Services\\AdminAccessPolicy::class)->team($isSuperAdmin, $roleIds, $adminUser ?? null);")
change(p,"|| !$isSuperAdmin", "|| ($adminUser->is_super_admin && !$isSuperAdmin)")
# Replace last-super check outside transactions with fresh, consistently ordered row locks inside.
s=files[p];a=s.index('        $this->protectLastSuperAdministrator(\n',s.index('public function update'));b=s.index('        DB::transaction',a);s=s[:a]+s[b:];put(p,s)
change(p,"        ): void {\n            $data = [", "        ): void {\n            Admin::orderBy('id')->lockForUpdate()->get();\n            $adminUser->refresh();\n            $this->protectLastSuperAdministrator($adminUser, $isSuperAdmin, $validated['status']);\n            app(\\App\\Services\\AdminAccessPolicy::class)->team($isSuperAdmin, $roleIds, $adminUser);\n            $data = [")
s=files[p];a=s.index('        $this->protectLastSuperAdministrator(\n',s.index('public function destroy'));b=s.index('        $name =',a);s=s[:a]+s[b:];put(p,s)
change(p,"DB::transaction(function () use ($adminUser): void {", "DB::transaction(function () use ($adminUser): void {\n            Admin::orderBy('id')->lockForUpdate()->get();\n            $adminUser->refresh();\n            app(\\App\\Services\\AdminAccessPolicy::class)->team(false, [], $adminUser);\n            $this->protectLastSuperAdministrator($adminUser, false, $adminUser->status);")

if (OUT/'pricing.py').exists():exec((OUT/'pricing.py').read_text(encoding='utf-8'))
