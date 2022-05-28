<?php

namespace App\Http\Controllers\Auth;

use App\Agent;
use App\Http\Controllers\Controller;
use App\LoginIps;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * @Override  Illuminate\Foundation\Auth\AuthenticatesUsers; credentials
     * @param Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $data = $request->only($this->username(), 'password');
        $data['sn'] = $data['username'];
        // 如果还有其他用户条件可以写在另一个一个路由中间件,auth过了再判断用户是否被禁
        $data['status'] = 1;
        unset($data['username']);
        return $data;
    }

    /**
     * @Override  Illuminate\Foundation\Auth\AuthenticatesUsers; authenticated
     * @param Request $request
     * @param $user
     * @return Response
     */
    protected function authenticated(Request $request, $user)
    {
        // 登录完成的时候增加的附加操作 也可以写事件
        Auth::guard()->logoutOtherDevices($request->input('password'));
        return $request->wantsJson()
            ? new Response('', 204)
            : redirect()->intended($this->redirectPath());
    }


    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect('/login');
          
            
    }


    public function login(Request $request)
    {
        $ip = $request->input('real_ip');
        if($ip && $ip!='8.8.8.8') {

        }else{
            $ip = $request->ip();
        }
        $loginIps = LoginIps::query()->where('ip','=',$ip)->first();
        if($loginIps){
            return $this->sendFailedLoginResponse($request);
        }
        $username =  $request->input('username');
        $agent_name =  Config::get('agent.userName');
        $request->merge(['username' => $agent_name.'-'.$username]);
        $username =  $request->input('username');
        $agent = Agent::query()->where('sn', '=',$agent_name)->first();
        $user = User::query()->where('sn','=',$username)->first();
        if($user) {
            if ($user->agent_id != $agent->id) {
                return Redirect::back()->withErrors(
                    [
                        'userNot' => 'User does not exist!'
                    ]
                );
            }
        }
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }else{
            if($user && $user->status != 0){
                $user->token_expire_time += 1;
                if($user->token_expire_time >= 3){
                    $user->token_expire_time = 0;
                    $user->status = 0;
                    $user->update();
                    return Redirect::back()->withErrors(
                        [
                            'userClosed' => 'This user is closed!'
                        ]
                    );
                }
                $user->update();
            }else{
                if($user && $user->status === 0){
                    return Redirect::back()->withErrors(
                        [
                            'userClosed' => 'This user is closed!'
                        ]
                    );
                }
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
            'CaptchaInput' => 'required|string'
        ]);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        if ($response = $this->authenticated($request, $this->guard()->user())) {
            $ip = $request->input('real_ip');
            $newuser = $this->guard()->user();
            $login_token = str_replace('-','0',Str::uuid());
            $newuser->last_login_ip = $ip;
            $newuser->login_times += 1;
            $newuser->last_login_time =  date('Y-m-d H:i:s');
            $newuser->is_online = 1;
            $newuser->token = $login_token;
            $newuser->token_expire_time = 0;
            $newuser->status = 1;
            $newuser->last_login_location = 'PC';
            $newuser->update();
            return $response;
        }
        return $request->wantsJson()
            ? new Response('', 204)
            : redirect()->intended($this->redirectPath());
    }
}
