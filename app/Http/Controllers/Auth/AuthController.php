<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * 認証Controller
 * 
 * ユーザーの認証、ログイン、ログアウト、登録機能を提供
 * 警備グループシステムの認証フローを管理
 */
class AuthController extends Controller
{
    /**
     * ログインページを表示
     * 
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ユーザーログイン処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは6文字以上で入力してください',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        // ログイン試行
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // ログイン時刻を記録
            $user->update([
                'last_login_at' => now(),
                'login_count' => $user->login_count + 1
            ]);

            if ($request->expectsJson()) {
                return $this->successResponse([
                    'user' => $user,
                    'redirect_url' => route('dashboard')
                ], 'ログインしました');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        if ($request->expectsJson()) {
            return $this->errorResponse('メールアドレスまたはパスワードが間違っています', 401);
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが間違っています。',
        ])->withInput();
    }

    /**
     * ユーザー登録ページを表示
     * 
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * ユーザー登録処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:50|unique:users',
            'company_id' => 'required|exists:customers,id',
            'role' => 'required|in:admin,manager,guard,operator',
        ], [
            'name.required' => '名前は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
            'employee_id.required' => '社員IDは必須です',
            'employee_id.unique' => 'この社員IDは既に使用されています',
            'company_id.required' => '会社IDは必須です',
            'company_id.exists' => '存在しない会社IDです',
            'role.required' => '役割は必須です',
            'role.in' => '無効な役割です',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        // ユーザー作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'company_id' => $request->company_id,
            'role' => $request->role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 自動ログイン
        Auth::login($user);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'user' => $user,
                'redirect_url' => route('dashboard')
            ], 'ユーザー登録が完了しました', 201);
        }

        return redirect(route('dashboard'));
    }

    /**
     * ログアウト処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return $this->successResponse(null, 'ログアウトしました');
        }

        return redirect(route('login'));
    }

    /**
     * 現在認証されているユーザー情報を取得
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        if (!Auth::check()) {
            return $this->errorResponse('認証が必要です', 401);
        }

        $user = Auth::user()->load(['company', 'permissions']);

        return $this->successResponse($user, 'ユーザー情報を取得しました');
    }

    /**
     * パスワード変更処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => '現在のパスワードは必須です',
            'new_password.required' => '新しいパスワードは必須です',
            'new_password.min' => '新しいパスワードは8文字以上で入力してください',
            'new_password.confirmed' => '新しいパスワードが一致しません',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('入力データが無効です', 422, $validator->errors());
        }

        $user = Auth::user();

        // 現在のパスワード確認
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('現在のパスワードが間違っています', 400);
        }

        // パスワード更新
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);

        return $this->successResponse(null, 'パスワードを変更しました');
    }

    // =============================================================================
    // API専用メソッド（JSON レスポンス専用）
    // =============================================================================

    /**
     * API ログイン処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogin(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは6文字以上で入力してください',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('入力データが無効です', 422, $validator->errors());
        }

        // ログイン試行
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // ログイン時刻を記録
            $user->update([
                'last_login_at' => now(),
                'login_count' => $user->login_count + 1
            ]);

            // APIトークン生成（Sanctum使用）
            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse([
                'user' => $user->load(['company']),
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'ログインしました');
        }

        return $this->errorResponse('メールアドレスまたはパスワードが間違っています', 401);
    }

    /**
     * API ユーザー登録処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRegister(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:50|unique:users',
            'company_id' => 'required|exists:customers,id',
            'role' => 'required|in:admin,manager,guard,operator',
        ], [
            'name.required' => '名前は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
            'employee_id.required' => '社員IDは必須です',
            'employee_id.unique' => 'この社員IDは既に使用されています',
            'company_id.required' => '会社IDは必須です',
            'company_id.exists' => '存在しない会社IDです',
            'role.required' => '役割は必須です',
            'role.in' => '無効な役割です',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('入力データが無効です', 422, $validator->errors());
        }

        // ユーザー作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'company_id' => $request->company_id,
            'role' => $request->role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // APIトークン生成
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user->load(['company']),
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'ユーザー登録が完了しました', 201);
    }

    /**
     * API ログアウト処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogout(Request $request)
    {
        // 現在のトークンを削除
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'ログアウトしました');
    }

    /**
     * 認証されたユーザー情報を取得（API専用）
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUser()
    {
        $user = Auth::user()->load(['company', 'permissions']);

        return $this->successResponse($user, 'ユーザー情報を取得しました');
    }

    /**
     * API パスワード変更処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiChangePassword(Request $request)
    {
        return $this->changePassword($request);
    }

    /**
     * API パスワードリセット処理
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.exists' => '登録されていないメールアドレスです',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('入力データが無効です', 422, $validator->errors());
        }

        // パスワードリセット処理（実装は省略、実際にはメール送信等）
        return $this->successResponse(null, 'パスワードリセットメールを送信しました');
    }
}
