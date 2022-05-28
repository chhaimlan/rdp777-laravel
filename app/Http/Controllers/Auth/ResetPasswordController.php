<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());
        $password = $request->input('password');
        $passwordConf = $request->input('password_confirmation');
        $old_pwd = $request->input('passwordold');
        if($password!=$passwordConf){
            return $this->sendResetFailedResponse($request,'password');
        }
        $user = Auth::user();
        if (Hash::check($old_pwd, $user->password)) {
            $user->password = Hash::make($password);
            $user->save();
        }else{
            return $this->sendResetFailedResponse($request,'password');
        }
        return redirect()->intended($this->redirectPath());

    }
    protected function credentials(Request $request)
    {
        return $request->only(
            'password', 'password_confirmation'
        );
    }
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    protected function setUserPassword($user, $password)
    {
        $user->password = Hash::make($password);
    }
    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'password' => 'required|confirmed|min:6'
        ];
    }
    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
}
