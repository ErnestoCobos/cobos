<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Contracts\Auth\StatefulGuard;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;
    use Illuminate\Foundation\Validation\ValidatesRequests;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\Auth;
    use Laravel\Nova\Nova;

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

        use AuthenticatesUsers, ValidatesRequests;

        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->middleware('nova.guest:' . config('nova.guard'))->except('logout');
        }

        /**
         * Show the application's login form.
         *
         * @return Response
         */
        public function showLoginForm()
        {
            return view('nova::auth.login');
        }

        /**
         * Log the user out of the application.
         *
         * @param Request $request
         * @return Response
         */
        public function logout(Request $request)
        {
            $this->guard()->logout();

            $request->session()->invalidate();

            return redirect($this->redirectPath());
        }

        /**
         * Get the guard to be used during authentication.
         *
         * @return StatefulGuard
         */
        protected function guard()
        {
            return Auth::guard(config('nova.guard'));
        }

        /**
         * Get the post register / login redirect path.
         *
         * @return string
         */
        public function redirectPath()
        {
            return Nova::path();
        }
    }
