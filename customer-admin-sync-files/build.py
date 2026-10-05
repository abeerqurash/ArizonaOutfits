from pathlib import Path
import json, hashlib, shutil

root=Path(__file__).resolve().parent.parent
out=Path(__file__).resolve().parent
stage=out/'_staged'
manifest=[]
for entry in json.loads((root/'administration-fixes/manifest.json').read_text()):
    destination=entry['destination']
    target=stage/destination
    target.parent.mkdir(parents=True,exist_ok=True)
    shutil.copyfile(root/'administration-fixes'/entry['file'],target)
    manifest.append({'destination':destination})

def put(destination, text):
    p=stage/destination
    p.parent.mkdir(parents=True,exist_ok=True)
    p.write_text(text,encoding='utf-8')
    if not any(x['destination']==destination for x in manifest):
        manifest.append({'destination':destination})

destination='app/Http/Controllers/Customer/AccountSecurityController.php'
s=(root/destination).read_text(encoding='utf-8-sig')
start=s.index('    public function verifyEmailChange(')
end=s.index('    public function cancelEmailChange(',start)
part=s[start:end]
needle="                $lockedUser->forceFill(["
addition="""                abort_unless($lockedUser->status === 'active', 403);
                abort_if($lockedUser->is_admin || $lockedUser->is_super_admin, 403);
                $identityClass = \\App\\Models\\CustomerEmailIdentity::class;
                $identityConflict = $identityClass::query()
                    ->where('source', $identityClass::SOURCE_CUSTOM)
                    ->where('normalized_email', $signedEmail)
                    ->where('user_id', '!=', $lockedUser->id)
                    ->whereNull('disconnected_at')
                    ->exists();
                if ($identityConflict) {
                    throw ValidationException::withMessages([
                        'email' => 'This email address is already connected to another account.',
                    ]);
                }
                $identityClass::updateOrCreate([
                    'user_id' => $lockedUser->id,
                    'source' => $identityClass::SOURCE_CUSTOM,
                ], [
                    'email' => $signedEmail,
                    'normalized_email' => $signedEmail,
                    'connected_at' => now(),
                    'disconnected_at' => null,
                    'email_verified_at' => now(),
                    'email_verification_pending_at' => null,
                    'verified_at' => now(),
                    'verification_pending_at' => null,
                    'is_login_enabled' => true,
                ]);

"""
assert part.count(needle)==1
part=part.replace(needle,addition+needle)
part=part.replace("                    'email' =>\n                    $signedEmail,", "                    'email' =>\n                    $signedEmail,\n                    'email_login_enabled_at' => now(),")
s=s[:start]+part+s[end:]
put(destination,s)

put('app/Http/Controllers/Auth/PasswordController.php', '''<?php

namespace App\\Http\\Controllers\\Auth;

use App\\Http\\Controllers\\Controller;
use App\\Models\\User;
use App\\Services\\PasswordSecurityService;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Str;
use Illuminate\\Validation\\Rules\\Password;
use Illuminate\\Validation\\ValidationException;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user('web');
        abort_unless($user instanceof User, 401);
        abort_if($user->is_admin || $user->is_super_admin || $user->status !== 'active', 403);
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
        DB::transaction(function () use ($user, $validated) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_if($locked->is_admin || $locked->is_super_admin || $locked->status !== 'active', 403);
            if (!$locked->hasPassword() || !Hash::check($validated['current_password'], $locked->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The current password you entered is incorrect.',
                ])->errorBag('updatePassword');
            }
            $security = app(PasswordSecurityService::class);
            if ($security->isRecentlyUsed($locked, $validated['password'])) {
                throw ValidationException::withMessages([
                    'password' => 'Choose a password different from your current and previous password.',
                ])->errorBag('updatePassword');
            }
            $security->rememberCurrentPassword($locked);
            $locked->forceFill([
                'password' => Hash::make($validated['password']),
                'password_set_at' => now(),
                'remember_token' => Str::random(60),
                'security_reminder_shown_at' => null,
            ])->save();
        });
        return back()->with('status', 'password-updated');
    }
}
''')

destination='app/Http/Controllers/ProfileController.php'
s=(root/destination).read_text(encoding='utf-8-sig')
start=s.index('        Auth::logout();')
end=s.index("        return Redirect::to('/');",start)
s=s[:start]+'''        \\Illuminate\\Support\\Facades\\DB::transaction(function () use ($user, $request) {
            $locked = \\App\\Models\\User::query()->lockForUpdate()->findOrFail($user->id);
            abort_if($locked->is_admin || $locked->is_super_admin || $locked->status !== 'active', 403);
            if (!Hash::check((string) $request->password, (string) $locked->password)) {
                throw ValidationException::withMessages([
                    'password' => 'The password you entered is incorrect.',
                ])->errorBag('userDeletion');
            }
            if ($locked->orders()->withTrashed()->exists()) {
                throw ValidationException::withMessages([
                    'password' => 'This account has current or archived orders and cannot be deleted. Contact the store for assistance.',
                ])->errorBag('userDeletion');
            }
            $locked->delete();
        });
        Auth::guard('web')->logout();
        $request->session()->forget([
            'customer.url.intended', 'url.intended', 'social_link',
            'security_phone_verification', 'phone_login', 'phone_registration',
        ]);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

'''+s[end:]
put(destination,s)

destination='app/Http/Controllers/CheckoutController.php'
s=(stage/destination).read_text(encoding='utf-8-sig')
start=s.index('    public function thankYou(')
part=s[start:]
assert 'Order::query()' in part
part=part.replace('Order::query()', 'Order::withTrashed()',1)
put(destination,s[:start]+part)

for i, entry in enumerate(manifest,1):
    entry['file']=f"{i:02d}_{Path(entry['destination']).name}.txt"
    payload=(stage/entry['destination']).read_bytes()
    (out/entry['file']).write_bytes(payload)
    entry['sha256']=hashlib.sha256(payload).hexdigest()
(out/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
print(f'Prepared {len(manifest)} separate replacements.')
