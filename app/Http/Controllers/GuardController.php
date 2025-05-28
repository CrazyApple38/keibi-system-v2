<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * 警備員管理Controller
 * 
 * 警備員の人材管理機能の中核を提供
 * CRUD操作、スキル管理、勤怠管理、パフォーマンス分析を統合
 */
class GuardController extends Controller
{
    /**
     * 警備員一覧を表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Guard::with(['user', 'company', 'projects'])
            ->withCount(['shifts', 'attendances'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            });

        // 検索条件
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('employee_id', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('emergency_contact', 'like', "%{$search}%");
            });
        }

        // ステータスフィルター
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 会社フィルター
        if ($request->filled('company_id') && $user->role === 'admin') {
            $query->where('company_id', $request->company_id);
        }

        // スキルフィルター
        if ($request->filled('skills')) {
            $skills = is_array($request->skills) ? $request->skills : [$request->skills];
            $query->where(function($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhereJsonContains('skills', $skill);
                }
            });
        }

        // 資格フィルター
        if ($request->filled('qualifications')) {
            $qualifications = is_array($request->qualifications) ? $request->qualifications : [$request->qualifications];
            $query->where(function($q) use ($qualifications) {
                foreach ($qualifications as $qualification) {
                    $q->orWhereJsonContains('qualifications', $qualification);
                }
            });
        }

        // 経験年数フィルター
        if ($request->filled('experience_years')) {
            $query->where('experience_years', '>=', $request->experience_years);
        }

        // ソート
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'name') {
            $query->join('users', 'guards.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('guards.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        if ($request->expectsJson()) {
            $guards = $query->paginate($request->get('per_page', 15));
            return $this->paginationResponse($guards, '警備員一覧を取得しました');
        }

        $guards = $query->paginate(15);
        $companies = Customer::where('status', 'active')->get();
        
        return view('guards.index', compact('guards', 'companies'));
    }

    /**
     * 警備員詳細を表示
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $user = Auth::user();
        
        $guard = Guard::with([
            'user',
            'company',
            'projects.customer',
            'shifts.project.customer',
            'attendances.shift.project'
        ])
        ->when($user->role !== 'admin', function($q) use ($user) {
            return $q->where('company_id', $user->company_id);
        })
        ->when($user->role === 'guard', function($q) use ($user) {
            return $q->where('user_id', $user->id);
        })
        ->findOrFail($id);

        // 警備員統計情報
        $statistics = $this->getGuardStatistics($guard);
        
        // 最近の勤怠履歴
        $recentAttendances = $this->getRecentAttendances($guard, 10);
        
        // パフォーマンス評価
        $performance = $this->getPerformanceMetrics($guard);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'guard' => $guard,
                'statistics' => $statistics,
                'recent_attendances' => $recentAttendances,
                'performance' => $performance
            ], '警備員詳細を取得しました');
        }

        return view('guards.show', compact('guard', 'statistics', 'recentAttendances', 'performance'));
    }

    /**
     * 警備員作成フォームを表示
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $companies = Customer::where('status', 'active')->get();
        $availableSkills = $this->getAvailableSkills();
        $availableQualifications = $this->getAvailableQualifications();
        
        return view('guards.create', compact('companies', 'availableSkills', 'availableQualifications'));
    }

    /**
     * 新規警備員を作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // ユーザー情報
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            
            // 警備員基本情報
            'employee_id' => 'required|string|max:50|unique:guards',
            'company_id' => 'required|exists:customers,id',
            'phone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'hire_date' => 'required|date|before_or_equal:today',
            'address' => 'required|string|max:500',
            
            // 勤務情報
            'hourly_wage' => 'required|numeric|min:0',
            'experience_years' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,suspended,retired',
            
            // スキル・資格
            'skills' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'certifications' => 'nullable|array',
            
            // その他
            'notes' => 'nullable|string',
            'health_status' => 'nullable|string|max:500',
            'uniform_size' => 'nullable|string|max:10',
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
            'company_id.required' => '会社は必須です',
            'company_id.exists' => '存在しない会社です',
            'phone.required' => '電話番号は必須です',
            'date_of_birth.required' => '生年月日は必須です',
            'date_of_birth.before' => '生年月日は今日より前の日付を入力してください',
            'hire_date.required' => '入社日は必須です',
            'hire_date.before_or_equal' => '入社日は今日以前の日付を入力してください',
            'address.required' => '住所は必須です',
            'hourly_wage.required' => '時給は必須です',
            'hourly_wage.numeric' => '時給は数値で入力してください',
            'hourly_wage.min' => '時給は0以上で入力してください',
            'experience_years.required' => '経験年数は必須です',
            'experience_years.integer' => '経験年数は整数で入力してください',
            'experience_years.min' => '経験年数は0以上で入力してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '無効なステータスです',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // ユーザーアカウント作成
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guard',
                'employee_id' => $request->employee_id,
                'company_id' => $request->company_id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // 警備員プロファイル作成
            $guard = Guard::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'company_id' => $request->company_id,
                'phone' => $request->phone,
                'emergency_contact' => $request->emergency_contact,
                'date_of_birth' => $request->date_of_birth,
                'hire_date' => $request->hire_date,
                'address' => $request->address,
                'hourly_wage' => $request->hourly_wage,
                'experience_years' => $request->experience_years,
                'status' => $request->status,
                'skills' => $request->skills ?? [],
                'qualifications' => $request->qualifications ?? [],
                'certifications' => $request->certifications ?? [],
                'notes' => $request->notes,
                'health_status' => $request->health_status,
                'uniform_size' => $request->uniform_size,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($guard->load(['user', 'company']), '警備員を作成しました', 201);
            }

            return redirect()->route('guards.show', $guard)
                           ->with('success', '警備員を作成しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('警備員の作成に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', '警備員の作成に失敗しました');
        }
    }

    /**
     * 警備員編集フォームを表示
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $guard = Guard::with(['user', 'company'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            })
            ->findOrFail($id);

        $companies = Customer::where('status', 'active')->get();
        $availableSkills = $this->getAvailableSkills();
        $availableQualifications = $this->getAvailableQualifications();
        
        return view('guards.edit', compact('guard', 'companies', 'availableSkills', 'availableQualifications'));
    }

    /**
     * 警備員情報を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $guard = Guard::with('user')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            // ユーザー情報
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $guard->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            
            // 警備員基本情報
            'employee_id' => 'required|string|max:50|unique:guards,employee_id,' . $id,
            'company_id' => 'required|exists:customers,id',
            'phone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'hire_date' => 'required|date|before_or_equal:today',
            'address' => 'required|string|max:500',
            
            // 勤務情報
            'hourly_wage' => 'required|numeric|min:0',
            'experience_years' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,suspended,retired',
            
            // スキル・資格
            'skills' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'certifications' => 'nullable|array',
            
            // その他
            'notes' => 'nullable|string',
            'health_status' => 'nullable|string|max:500',
            'uniform_size' => 'nullable|string|max:10',
        ], [
            'name.required' => '名前は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
            'employee_id.required' => '社員IDは必須です',
            'employee_id.unique' => 'この社員IDは既に使用されています',
            'company_id.required' => '会社は必須です',
            'company_id.exists' => '存在しない会社です',
            'phone.required' => '電話番号は必須です',
            'date_of_birth.required' => '生年月日は必須です',
            'date_of_birth.before' => '生年月日は今日より前の日付を入力してください',
            'hire_date.required' => '入社日は必須です',
            'hire_date.before_or_equal' => '入社日は今日以前の日付を入力してください',
            'address.required' => '住所は必須です',
            'hourly_wage.required' => '時給は必須です',
            'hourly_wage.numeric' => '時給は数値で入力してください',
            'hourly_wage.min' => '時給は0以上で入力してください',
            'experience_years.required' => '経験年数は必須です',
            'experience_years.integer' => '経験年数は整数で入力してください',
            'experience_years.min' => '経験年数は0以上で入力してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '無効なステータスです',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // ユーザーアカウント更新
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'company_id' => $request->company_id,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
                $userData['password_changed_at'] = now();
            }

            $guard->user->update($userData);

            // 警備員プロファイル更新
            $guard->update([
                'employee_id' => $request->employee_id,
                'company_id' => $request->company_id,
                'phone' => $request->phone,
                'emergency_contact' => $request->emergency_contact,
                'date_of_birth' => $request->date_of_birth,
                'hire_date' => $request->hire_date,
                'address' => $request->address,
                'hourly_wage' => $request->hourly_wage,
                'experience_years' => $request->experience_years,
                'status' => $request->status,
                'skills' => $request->skills ?? [],
                'qualifications' => $request->qualifications ?? [],
                'certifications' => $request->certifications ?? [],
                'notes' => $request->notes,
                'health_status' => $request->health_status,
                'uniform_size' => $request->uniform_size,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($guard->load(['user', 'company']), '警備員情報を更新しました');
            }

            return redirect()->route('guards.show', $guard)
                           ->with('success', '警備員情報を更新しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('警備員情報の更新に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', '警備員情報の更新に失敗しました');
        }
    }

    /**
     * 警備員を削除
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        $user = Auth::user();
        
        // 管理者のみ削除可能
        if ($user->role !== 'admin') {
            if ($request->expectsJson()) {
                return $this->errorResponse('権限がありません', 403);
            }
            return back()->with('error', '権限がありません');
        }

        $guard = Guard::with('user')->findOrFail($id);

        // 関連データの確認
        if ($guard->shifts()->exists()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('関連するシフトが存在するため削除できません', 400);
            }
            return back()->with('error', '関連するシフトが存在するため削除できません');
        }

        DB::beginTransaction();
        try {
            // プロジェクトアサインを削除
            $guard->projects()->detach();
            
            // 警備員プロファイルを削除
            $guard->delete();
            
            // ユーザーアカウントを削除
            $guard->user->delete();
            
            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse(null, '警備員を削除しました');
            }

            return redirect()->route('guards.index')
                           ->with('success', '警備員を削除しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('警備員の削除に失敗しました', 500);
            }
            
            return back()->with('error', '警備員の削除に失敗しました');
        }
    }

    /**
     * 警備員統計情報を取得
     * 
     * @param Guard $guard
     * @return array
     */
    private function getGuardStatistics(Guard $guard): array
    {
        $totalShifts = $guard->shifts()->count();
        $completedShifts = $guard->shifts()->where('status', 'completed')->count();
        $completionRate = $totalShifts > 0 ? ($completedShifts / $totalShifts) * 100 : 0;

        $totalHours = $guard->attendances()->sum('worked_hours');
        $averageHoursPerShift = $totalShifts > 0 ? $totalHours / $totalShifts : 0;

        $currentMonth = Carbon::now()->startOfMonth();
        $monthlyHours = $guard->attendances()
            ->where('date', '>=', $currentMonth)
            ->sum('worked_hours');

        $monthlyEarnings = $monthlyHours * $guard->hourly_wage;

        return [
            'total_shifts' => $totalShifts,
            'completed_shifts' => $completedShifts,
            'completion_rate' => round($completionRate, 1),
            'total_hours' => $totalHours,
            'average_hours_per_shift' => round($averageHoursPerShift, 1),
            'monthly_hours' => $monthlyHours,
            'monthly_earnings' => $monthlyEarnings,
            'total_projects' => $guard->projects()->count(),
            'active_projects' => $guard->projects()->where('status', 'active')->count(),
            'experience_months' => Carbon::parse($guard->hire_date)->diffInMonths(Carbon::now()),
            'attendance_rate' => $this->calculateAttendanceRate($guard),
        ];
    }

    /**
     * 最近の勤怠履歴を取得
     * 
     * @param Guard $guard
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentAttendances(Guard $guard, int $limit = 10)
    {
        return $guard->attendances()
            ->with(['shift.project.customer'])
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * パフォーマンス評価を取得
     * 
     * @param Guard $guard
     * @return array
     */
    private function getPerformanceMetrics(Guard $guard): array
    {
        $last3Months = Carbon::now()->subMonths(3);
        
        return [
            'punctuality_score' => $this->calculatePunctualityScore($guard, $last3Months),
            'reliability_score' => $this->calculateReliabilityScore($guard, $last3Months),
            'quality_score' => $this->calculateQualityScore($guard, $last3Months),
            'overall_rating' => $this->calculateOverallRating($guard, $last3Months),
        ];
    }

    /**
     * 出席率を計算
     * 
     * @param Guard $guard
     * @return float
     */
    private function calculateAttendanceRate(Guard $guard): float
    {
        $scheduledShifts = $guard->shifts()->count();
        $attendedShifts = $guard->attendances()->count();

        return $scheduledShifts > 0 ? ($attendedShifts / $scheduledShifts) * 100 : 0;
    }

    /**
     * 時間厳守スコアを計算
     * 
     * @param Guard $guard
     * @param Carbon $since
     * @return float
     */
    private function calculatePunctualityScore(Guard $guard, Carbon $since): float
    {
        $attendances = $guard->attendances()->where('date', '>=', $since)->get();
        
        if ($attendances->isEmpty()) {
            return 0;
        }

        $onTimeCount = $attendances->where('on_time', true)->count();
        return ($onTimeCount / $attendances->count()) * 100;
    }

    /**
     * 信頼性スコアを計算
     * 
     * @param Guard $guard
     * @param Carbon $since
     * @return float
     */
    private function calculateReliabilityScore(Guard $guard, Carbon $since): float
    {
        $scheduledShifts = $guard->shifts()->where('shift_date', '>=', $since)->count();
        $attendedShifts = $guard->attendances()->where('date', '>=', $since)->count();

        return $scheduledShifts > 0 ? ($attendedShifts / $scheduledShifts) * 100 : 0;
    }

    /**
     * 品質スコアを計算
     * 
     * @param Guard $guard
     * @param Carbon $since
     * @return float
     */
    private function calculateQualityScore(Guard $guard, Carbon $since): float
    {
        // 日報の品質、報告書の完成度等から算出
        // 現在は仮実装として固定値を返す
        return 85.0;
    }

    /**
     * 総合評価を計算
     * 
     * @param Guard $guard
     * @param Carbon $since
     * @return float
     */
    private function calculateOverallRating(Guard $guard, Carbon $since): float
    {
        $punctuality = $this->calculatePunctualityScore($guard, $since);
        $reliability = $this->calculateReliabilityScore($guard, $since);
        $quality = $this->calculateQualityScore($guard, $since);

        return ($punctuality * 0.3 + $reliability * 0.4 + $quality * 0.3);
    }

    /**
     * 利用可能なスキル一覧を取得
     * 
     * @return array
     */
    private function getAvailableSkills(): array
    {
        return [
            '施設警備', '交通誘導', '駐車場管理', '巡回警備', '機械警備',
            '身辺警備', 'イベント警備', '運搬警備', '空港保安', 'ビル管理',
            '防犯カメラ操作', '無線操作', '応急手当', '消防設備操作', 'PC操作'
        ];
    }

    /**
     * 利用可能な資格一覧を取得
     * 
     * @return array
     */
    private function getAvailableQualifications(): array
    {
        return [
            '警備員検定1級', '警備員検定2級', '警備業務検定1級', '警備業務検定2級',
            '防火管理者', '防災管理者', '普通救命講習', '上級救命講習',
            '警備員指導教育責任者', '機械警備業務管理者', '交通誘導警備業務検定',
            '雑踏警備業務検定', '施設警備業務検定', '貴重品運搬警備業務検定'
        ];
    }

    /**
     * 警備員のステータスを更新
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,suspended,retired',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator);
        }

        $guard = Guard::findOrFail($id);
        
        $guard->update([
            'status' => $request->status,
            'status_updated_at' => now(),
            'status_update_reason' => $request->reason,
            'updated_by' => Auth::id(),
        ]);

        // ユーザーアカウントのステータスも更新
        $userStatus = $request->status === 'active' ? 'active' : 'inactive';
        $guard->user->update(['status' => $userStatus]);

        if ($request->expectsJson()) {
            return $this->successResponse($guard, '警備員ステータスを更新しました');
        }

        return back()->with('success', '警備員ステータスを更新しました');
    }

    /**
     * 警備員のスキル・資格を更新
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateSkills($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'skills' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'certifications' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator);
        }

        $guard = Guard::findOrFail($id);
        
        $guard->update([
            'skills' => $request->skills ?? [],
            'qualifications' => $request->qualifications ?? [],
            'certifications' => $request->certifications ?? [],
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return $this->successResponse($guard, 'スキル・資格情報を更新しました');
        }

        return back()->with('success', 'スキル・資格情報を更新しました');
    }

    /**
     * 警備員の給与情報を更新
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateWage($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hourly_wage' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator);
        }

        $guard = Guard::findOrFail($id);
        
        $guard->update([
            'hourly_wage' => $request->hourly_wage,
            'wage_updated_at' => $request->effective_date,
            'wage_update_reason' => $request->reason,
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return $this->successResponse($guard, '給与情報を更新しました');
        }

        return back()->with('success', '給与情報を更新しました');
    }

    /*
    |--------------------------------------------------------------------------
    | Google Maps API連携機能
    |--------------------------------------------------------------------------
    */

    /**
     * 警備員位置管理地図を表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function mapView(Request $request)
    {
        $user = Auth::user();
        
        $guards = Guard::with(['user', 'company', 'currentShift'])
            ->where('status', 'active')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            })
            ->get();

        // 現在勤務中の警備員の位置情報を取得
        $activeGuards = [];
        foreach ($guards as $guard) {
            if ($guard->currentShift && $guard->location_lat && $guard->location_lng) {
                $activeGuards[] = [
                    'id' => $guard->id,
                    'name' => $guard->user->name,
                    'employee_id' => $guard->employee_id,
                    'company' => $guard->company->name,
                    'latitude' => $guard->location_lat,
                    'longitude' => $guard->location_lng,
                    'last_update' => $guard->location_updated_at,
                    'shift_info' => [
                        'project_name' => $guard->currentShift->project->name ?? '',
                        'start_time' => $guard->currentShift->start_time ?? '',
                        'end_time' => $guard->currentShift->end_time ?? '',
                        'status' => $guard->currentShift->status ?? '',
                    ],
                ];
            }
        }

        if ($request->expectsJson()) {
            return $this->successResponse([
                'guards' => $activeGuards,
                'center' => [
                    'lat' => config('services.google_maps.default_lat'),
                    'lng' => config('services.google_maps.default_lng'),
                ],
                'zoom' => config('services.google_maps.default_zoom'),
            ], '警備員位置情報を取得しました');
        }

        return view('guards.map', compact('activeGuards'));
    }

    /**
     * 警備員の位置情報を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('位置情報が無効です', 422, $validator->errors());
        }

        $user = Auth::user();
        
        $guard = Guard::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->where('user_id', $user->id);
            })
            ->findOrFail($id);

        $guard->update([
            'location_lat' => $request->latitude,
            'location_lng' => $request->longitude,
            'location_accuracy' => $request->accuracy,
            'location_address' => $request->address,
            'location_updated_at' => now(),
        ]);

        return $this->successResponse([
            'guard_id' => $guard->id,
            'location' => [
                'latitude' => $guard->location_lat,
                'longitude' => $guard->location_lng,
                'accuracy' => $guard->location_accuracy,
                'address' => $guard->location_address,
                'updated_at' => $guard->location_updated_at,
            ]
        ], '位置情報を更新しました');
    }

    /**
     * 警備員の位置履歴を取得
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocationHistory(Request $request, $id)
    {
        $user = Auth::user();
        
        $guard = Guard::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->where('user_id', $user->id);
            })
            ->findOrFail($id);

        $dateFrom = $request->get('date_from', Carbon::today()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));

        // 位置履歴データの取得（実際の実装では位置履歴テーブルを作成することを推奨）
        $history = collect([
            [
                'datetime' => now()->subHours(1),
                'latitude' => $guard->location_lat + rand(-10, 10) / 10000,
                'longitude' => $guard->location_lng + rand(-10, 10) / 10000,
                'accuracy' => rand(5, 50),
                'address' => '移動中',
            ],
            [
                'datetime' => now()->subHours(2),
                'latitude' => $guard->location_lat + rand(-10, 10) / 10000,
                'longitude' => $guard->location_lng + rand(-10, 10) / 10000,
                'accuracy' => rand(5, 50),
                'address' => '現場到着',
            ],
        ]);

        return $this->successResponse([
            'guard' => [
                'id' => $guard->id,
                'name' => $guard->user->name,
                'employee_id' => $guard->employee_id,
            ],
            'history' => $history,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ]
        ], '位置履歴を取得しました');
    }

    /**
     * ルート最適化計算
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateOptimizedRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|array',
            'origin.lat' => 'required|numeric|between:-90,90',
            'origin.lng' => 'required|numeric|between:-180,180',
            'destinations' => 'required|array|min:1',
            'destinations.*.lat' => 'required|numeric|between:-90,90',
            'destinations.*.lng' => 'required|numeric|between:-180,180',
            'destinations.*.name' => 'nullable|string|max:255',
            'mode' => 'nullable|in:driving,walking,transit',
            'avoid' => 'nullable|array',
            'avoid.*' => 'in:tolls,highways,ferries',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('ルート計算データが無効です', 422, $validator->errors());
        }

        $origin = $request->origin;
        $destinations = $request->destinations;
        $mode = $request->get('mode', 'driving');
        $avoid = $request->get('avoid', []);

        // Google Maps Directions APIを使用したルート最適化
        // 実際の実装では外部APIコールを行う
        $optimizedRoute = $this->performRouteOptimization($origin, $destinations, $mode, $avoid);

        return $this->successResponse([
            'origin' => $origin,
            'destinations' => $destinations,
            'optimized_route' => $optimizedRoute,
            'total_distance' => $optimizedRoute['total_distance'] ?? 0,
            'total_duration' => $optimizedRoute['total_duration'] ?? 0,
            'mode' => $mode,
            'calculated_at' => now(),
        ], 'ルート最適化を完了しました');
    }

    /**
     * 現場間の距離・時間を計算
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateDistance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|array',
            'origin.lat' => 'required|numeric|between:-90,90',
            'origin.lng' => 'required|numeric|between:-180,180',
            'destination' => 'required|array',
            'destination.lat' => 'required|numeric|between:-90,90',
            'destination.lng' => 'required|numeric|between:-180,180',
            'mode' => 'nullable|in:driving,walking,transit',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('距離計算データが無効です', 422, $validator->errors());
        }

        $origin = $request->origin;
        $destination = $request->destination;
        $mode = $request->get('mode', 'driving');

        // Google Maps Distance Matrix APIを使用した距離・時間計算
        $result = $this->calculateDistanceAndTime($origin, $destination, $mode);

        return $this->successResponse([
            'origin' => $origin,
            'destination' => $destination,
            'distance' => $result['distance'],
            'duration' => $result['duration'],
            'mode' => $mode,
            'calculated_at' => now(),
        ], '距離・時間を計算しました');
    }

    /**
     * 警備員の現在地から最寄りの現場を検索
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function findNearbyProjects(Request $request, $id)
    {
        $user = Auth::user();
        
        $guard = Guard::with(['user', 'company'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('company_id', $user->company_id);
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->where('user_id', $user->id);
            })
            ->findOrFail($id);

        if (!$guard->location_lat || !$guard->location_lng) {
            return $this->errorResponse('警備員の位置情報が取得できません', 400);
        }

        $radius = $request->get('radius', 10); // デフォルト10km圏内
        
        // 現在地から指定範囲内のプロジェクトを検索
        $nearbyProjects = Project::where('status', 'active')
            ->where('company_id', $guard->company_id)
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->selectRaw('*, 
                (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * 
                cos(radians(location_lng) - radians(?)) + sin(radians(?)) * 
                sin(radians(location_lat)))) AS distance', 
                [$guard->location_lat, $guard->location_lng, $guard->location_lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->with(['customer'])
            ->get();

        $projects = $nearbyProjects->map(function($project) use ($guard) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'customer' => $project->customer->name ?? '',
                'location' => [
                    'latitude' => $project->location_lat,
                    'longitude' => $project->location_lng,
                    'address' => $project->location_address,
                ],
                'distance' => round($project->distance, 2),
                'estimated_travel_time' => $this->estimateTravelTime($project->distance),
                'status' => $project->status,
                'priority' => $project->priority ?? 'normal',
            ];
        });

        return $this->successResponse([
            'guard' => [
                'id' => $guard->id,
                'name' => $guard->user->name,
                'employee_id' => $guard->employee_id,
                'current_location' => [
                    'latitude' => $guard->location_lat,
                    'longitude' => $guard->location_lng,
                    'address' => $guard->location_address,
                ],
            ],
            'nearby_projects' => $projects,
            'search_radius' => $radius,
            'total_found' => $projects->count(),
        ], '最寄りの現場情報を取得しました');
    }

    /**
     * ジオコーディング（住所→緯度経度変換）
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function geocodeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('住所が無効です', 422, $validator->errors());
        }

        $address = $request->address;
        
        // Google Maps Geocoding APIを使用した住所→座標変換
        $result = $this->performGeocoding($address);

        if (!$result) {
            return $this->errorResponse('住所から座標を取得できませんでした', 400);
        }

        return $this->successResponse([
            'address' => $address,
            'location' => [
                'latitude' => $result['lat'],
                'longitude' => $result['lng'],
            ],
            'formatted_address' => $result['formatted_address'] ?? $address,
            'place_id' => $result['place_id'] ?? null,
        ], 'ジオコーディングを完了しました');
    }

    /**
     * 逆ジオコーディング（緯度経度→住所変換）
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reverseGeocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('座標が無効です', 422, $validator->errors());
        }

        $lat = $request->latitude;
        $lng = $request->longitude;
        
        // Google Maps Reverse Geocoding APIを使用した座標→住所変換
        $result = $this->performReverseGeocoding($lat, $lng);

        if (!$result) {
            return $this->errorResponse('座標から住所を取得できませんでした', 400);
        }

        return $this->successResponse([
            'location' => [
                'latitude' => $lat,
                'longitude' => $lng,
            ],
            'address' => $result['formatted_address'] ?? '',
            'components' => $result['address_components'] ?? [],
            'place_id' => $result['place_id'] ?? null,
        ], '逆ジオコーディングを完了しました');
    }

    /*
    |--------------------------------------------------------------------------
    | Google Maps API Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * ルート最適化の実行
     * 
     * @param array $origin
     * @param array $destinations
     * @param string $mode
     * @param array $avoid
     * @return array
     */
    private function performRouteOptimization(array $origin, array $destinations, string $mode, array $avoid): array
    {
        // 実際の実装では Google Maps Directions API を呼び出す
        // ここでは仮データを返す
        return [
            'waypoints' => $destinations,
            'total_distance' => rand(10, 50) . 'km',
            'total_duration' => rand(30, 120) . '分',
            'route_points' => [
                $origin,
                ...$destinations
            ],
        ];
    }

    /**
     * 距離・時間の計算
     * 
     * @param array $origin
     * @param array $destination
     * @param string $mode
     * @return array
     */
    private function calculateDistanceAndTime(array $origin, array $destination, string $mode): array
    {
        // 実際の実装では Google Maps Distance Matrix API を呼び出す
        // ここでは仮データを返す
        return [
            'distance' => rand(1, 20) . 'km',
            'duration' => rand(5, 60) . '分',
        ];
    }

    /**
     * 移動時間の推定
     * 
     * @param float $distance
     * @return string
     */
    private function estimateTravelTime(float $distance): string
    {
        // 平均時速30kmで計算
        $hours = $distance / 30;
        $minutes = round($hours * 60);
        
        if ($minutes < 60) {
            return $minutes . '分';
        } else {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return $hours . '時間' . ($remainingMinutes > 0 ? $remainingMinutes . '分' : '');
        }
    }

    /**
     * ジオコーディングの実行
     * 
     * @param string $address
     * @return array|null
     */
    private function performGeocoding(string $address): ?array
    {
        // 実際の実装では Google Maps Geocoding API を呼び出す
        // ここでは仮データを返す
        return [
            'lat' => 35.6762 + (rand(-1000, 1000) / 10000),
            'lng' => 139.6503 + (rand(-1000, 1000) / 10000),
            'formatted_address' => $address,
            'place_id' => 'ChIJ' . bin2hex(random_bytes(16)),
        ];
    }

    /**
     * 逆ジオコーディングの実行
     * 
     * @param float $lat
     * @param float $lng
     * @return array|null
     */
    private function performReverseGeocoding(float $lat, float $lng): ?array
    {
        // 実際の実装では Google Maps Reverse Geocoding API を呼び出す
        // ここでは仮データを返す
        return [
            'formatted_address' => '東京都千代田区丸の内1-1-1',
            'address_components' => [
                ['long_name' => '東京都', 'types' => ['administrative_area_level_1']],
                ['long_name' => '千代田区', 'types' => ['locality']],
                ['long_name' => '丸の内', 'types' => ['sublocality']],
            ],
            'place_id' => 'ChIJ' . bin2hex(random_bytes(16)),
        ];
    }
}
