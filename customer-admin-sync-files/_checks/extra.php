<?php
require __DIR__.'/bootstrap.php';
use Illuminate\Support\Facades\Auth;
run('Successful customer deletion',function(){
 $u=customerFixture();$r=customerRequest($u,['password'=>'FixturePassword123!'],'profile.destroy','DELETE');$key=Auth::guard('admin')->getName();$r->session()->put($key,99);
 (new App\Http\Controllers\ProfileController)->destroy($r);
 check('Unreferenced customer can self-delete',App\Models\User::find($u->id)===null);
 check('Successful self-delete preserves admin login session',$r->session()->get($key)===99);
 check('Successful self-delete removes web login',Auth::guard('web')->guest());
});
run('Both password routes reject current password',function(){
 $u=customerFixture();$r=customerRequest($u,['current_password'=>'FixturePassword123!','password'=>'FixturePassword123!','password_confirmation'=>'FixturePassword123!'],'password.update','PUT');
 check('Standard route refuses current password reuse',validationBlocked(fn()=>(new App\Http\Controllers\Auth\PasswordController)->update($r)));
});
file_put_contents(__DIR__.'/../extra-checks.json',json_encode($results,JSON_PRETTY_PRINT));
