<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;

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
    protected $redirectTo = '/';
    
    /**
     * @return string
     */
    protected function redirectTo()
    {
        return url()->previous();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    // Illuminate\Foundation\Auth\AuthenticatesUsers의 username()
    // 로그인 ID를 이메일에서 로그인ID로
    public function username()
    {
        return 'name';
    }
    
    // Illuminate\Foundation\Auth\AuthenticatesUsers의 attemptLogin()
    protected function attemptLogin(Request $request)
    {
	    $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
        
		// 암호화된 아이디로 로그인이 가능하도록 함
        if(!\Auth::check()){
	        $id=$this->username();
			$request->merge(['name'=>\App\Encryption::rt_encrypt($request->$id)]);
		    $this->guard()->attempt(
	            $this->credentials($request), $request->filled('remember')
	        );
        }
	    
        return \Auth::check();
    }
    
    protected function authenticated(Request $request, $user)
    {
	    // 상태가 200이 아닌 미승인 회원 처리
		if ($user->state!=200){ 
			\Auth::logout($request);
			
			$message='로그인 에러';
			if($user->state==100)
				$message='아직 승인되지 않은 계정입니다.';
			
			return redirect()->back()
				->withInput($request->only($this->username(),'remember'))
				->withErrors([
					$this->username()=>$message,
				]);
		}
    }
    
    protected function validateLogin(Request $request)
    {
		Controller::logActivity('USR');
        $this->validate($request,[
            $this->username() =>'required', 
            'password'=>'required',
        ]);
	}
	
	public function logout(Request $request)
	{
		Controller::logActivity('USR');
		
		$this->guard()->logout();
		$request->session()->flush();
		$request->session()->regenerate();
		
		return redirect($this->redirectTo);
	}

}
