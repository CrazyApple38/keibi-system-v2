# 開発ルール・ガイドライン

## Git管理ルール

### ブランチ戦略: GitHub Flow
- **main**: 本番環境用の安定ブランチ
- **feature/[機能名]**: 機能開発用ブランチ
- **hotfix/[修正内容]**: 緊急修正用ブランチ

### ブランチ命名規則
```
feature/user-authentication
feature/shift-management
feature/order-calendar
hotfix/login-bug-fix
hotfix/database-connection-error
```

### コミットメッセージ規則
**言語**: 日本語
**頻度**: 作業単位
**形式**: `[カテゴリ] 変更内容の簡潔な説明`

#### カテゴリ一覧
- `[機能]` - 新機能の追加
- `[修正]` - バグ修正
- `[更新]` - 既存機能の改善・変更
- `[削除]` - ファイルや機能の削除
- `[ドキュメント]` - ドキュメントの追加・更新
- `[設定]` - 設定ファイルの変更
- `[リファクタ]` - コードの整理・改善
- `[テスト]` - テストコードの追加・修正
- `[環境]` - 開発環境の設定変更

#### コミットメッセージ例
```
[機能] ユーザー認証機能の実装
[修正] ログイン時のバリデーションエラーを修正
[更新] データベース接続設定を本番環境用に変更
[ドキュメント] API設計書を追加
[設定] Laravel環境設定ファイルを追加
```

### Pull Request ルール
1. **作成タイミング**: 機能完成時
2. **レビュー必須**: Yes（セルフレビュー可）
3. **テスト必須**: 基本的な動作確認
4. **マージ方法**: Squash and Merge（履歴の整理）

### 作業フロー
1. `main`から`feature/[機能名]`ブランチを作成
2. 作業単位で日本語コミット
3. 機能完成時にPull Request作成
4. レビュー・テスト実施
5. `main`にマージ

## コーディング規約

### PHP/Laravel コーディング規約
**基準**: PSR-12 + Laravel規約 + 冗長性確保

#### 基本原則
- **可読性重視**: 冗長でも理解しやすいコードを書く
- **明示的記述**: 型宣言、戻り値型、プロパティ型を必須
- **完全性**: null許可、例外処理、バリデーションを厳格に実装

#### 具体的ルール

##### 1. 型宣言（厳格）
```php
// 良い例：完全な型宣言
public function calculateWorkingHours(
    Employee $employee, 
    DateTime $startDate, 
    DateTime $endDate
): float {
    // 実装
}

// 悪い例：型宣言なし
public function calculateWorkingHours($employee, $start, $end) {
    // 実装
}
```

##### 2. メソッド・変数命名（英語 + 冗長性）
```php
// 良い例：意図が明確
$availableEmployeeListForShiftAssignment = [];
$totalWorkingHoursInCurrentMonth = 0;

public function getAvailableEmployeesForSpecificShift(): Collection
{
    // 実装
}

// 悪い例：短縮形
$empList = [];
$hours = 0;
public function getEmps(): Collection {}
```

##### 3. コメント（日本語 + 冗長）
```php
/**
 * 指定された日付における社員のシフト配置を実行する
 * 
 * このメソッドは以下の処理を行う：
 * 1. 社員の勤務可能状態をチェック
 * 2. 現場の必要人員数を確認
 * 3. 社員のスキル・資格要件をマッチング
 * 4. 組み合わせ制限を考慮した配置実行
 * 
 * @param Employee $employee 配置対象の社員オブジェクト
 * @param Workplace $workplace 配置先の現場オブジェクト
 * @param DateTime $assignmentDate 配置予定日
 * @return ShiftAssignmentResult 配置結果（成功/失敗とその理由）
 * @throws EmployeeNotAvailableException 社員が勤務不可の場合
 * @throws WorkplaceCapacityExceededException 現場の定員超過の場合
 */
public function assignEmployeeToWorkplaceShift(
    Employee $employee,
    Workplace $workplace, 
    DateTime $assignmentDate
): ShiftAssignmentResult {
    // メソッド内の重要な処理ステップにもコメント
    // 社員の勤務可能状態をチェック
    if (!$this->isEmployeeAvailableForDate($employee, $assignmentDate)) {
        throw new EmployeeNotAvailableException(
            "社員ID {$employee->id} は {$assignmentDate->format('Y-m-d')} に勤務不可です"
        );
    }
    
    // 現場の空き状況を確認
    $availableCapacity = $this->getWorkplaceAvailableCapacity($workplace, $assignmentDate);
    if ($availableCapacity <= 0) {
        throw new WorkplaceCapacityExceededException(
            "現場ID {$workplace->id} は {$assignmentDate->format('Y-m-d')} に定員に達しています"
        );
    }
    
    // 実際の配置処理を実行
    return $this->executeShiftAssignment($employee, $workplace, $assignmentDate);
}
```

##### 4. 例外処理（厳格）
```php
// 全てのメソッドで適切な例外処理を実装
try {
    $assignmentResult = $this->assignEmployeeToWorkplaceShift(
        $employee, 
        $workplace, 
        $assignmentDate
    );
} catch (EmployeeNotAvailableException $e) {
    // ログ出力と適切なエラーレスポンス
    Log::warning('社員配置失敗', [
        'employee_id' => $employee->id,
        'workplace_id' => $workplace->id,
        'date' => $assignmentDate->format('Y-m-d'),
        'reason' => $e->getMessage()
    ]);
    
    return response()->json([
        'success' => false,
        'error_type' => 'employee_not_available',
        'message' => '指定された社員は選択日に勤務できません'
    ], 400);
}
```

### JavaScript/React コーディング規約

#### 自動整形ツール設定
- **ESLint**: 構文チェック・コード品質
- **Prettier**: コード整形
- **Husky**: コミット前の自動チェック

#### 設定ファイル例（.eslintrc.js）
```javascript
module.exports = {
  extends: [
    'eslint:recommended',
    '@typescript-eslint/recommended',
    'plugin:react/recommended',
    'plugin:react-hooks/recommended'
  ],
  rules: {
    // 冗長性を許可するルール
    'max-len': ['warn', { code: 120 }], // 長い行を許可
    'no-console': 'warn', // console.logは警告のみ
    'prefer-const': 'error', // const使用を強制
    // コメント必須ルール
    'require-jsdoc': ['warn', {
      require: {
        FunctionDeclaration: true,
        MethodDefinition: true
      }
    }]
  }
};
```

#### React コンポーネント規約
```javascript
/**
 * 社員シフト表示コンポーネント
 * 
 * 指定された期間の社員シフト情報を表形式で表示する
 * ドラッグアンドドロップによるシフト変更機能を提供
 */
const EmployeeShiftCalendarComponent = ({
  employeeList,
  shiftAssignmentList,
  selectedDateRange,
  onShiftAssignmentChange
}) => {
  // 状態管理：選択中のシフト配置情報
  const [selectedShiftAssignment, setSelectedShiftAssignment] = useState(null);
  
  // シフト配置変更時のハンドラー関数
  const handleShiftAssignmentUpdate = useCallback((
    employeeId,
    workplaceId, 
    newAssignmentDate
  ) => {
    // 変更処理の実装
    onShiftAssignmentChange({
      employeeId,
      workplaceId,
      assignmentDate: newAssignmentDate
    });
  }, [onShiftAssignmentChange]);

  return (
    <div className="employee-shift-calendar-container">
      {/* コンポーネントの実装 */}
    </div>
  );
};
```

### ディレクトリ・ファイル命名規則

#### Laravel ディレクトリ拡張構造
```
app/
├── Modules/                    # 機能別モジュール
│   ├── Authentication/         # 認証機能
│   ├── OrderManagement/        # 受注管理
│   ├── ShiftManagement/        # シフト管理
│   └── MasterDataManagement/   # マスタ管理
├── Services/                   # ビジネスロジック
├── Repositories/               # データアクセス層
├── ValueObjects/              # 値オブジェクト
└── Exceptions/                # カスタム例外
```

#### ファイル命名規則
- **Controller**: `EmployeeShiftManagementController.php`
- **Model**: `EmployeeShiftAssignment.php`
- **Service**: `ShiftAssignmentCalculationService.php`
- **Repository**: `EmployeeAvailabilityRepository.php`
- **Component**: `EmployeeShiftCalendarComponent.jsx`

## プロジェクト構造・モジュール分割

### モジュール分割方針：小分類（機能ごとに細分化）

#### 分割粒度の基本原則
- **単一責任原則**: 1つのモジュールは1つの明確な責任を持つ
- **高凝集・低結合**: モジュール内の機能は密接に関連し、他モジュールとの依存は最小化
- **テスト容易性**: 各モジュールは独立してテスト可能

#### 詳細モジュール構造
```
app/Modules/
├── Authentication/
│   ├── UserLogin/              # ユーザーログイン機能
│   ├── UserRegistration/       # ユーザー登録機能
│   ├── PasswordReset/          # パスワードリセット機能
│   ├── TwoFactorAuth/          # 二要素認証機能
│   └── SessionManagement/      # セッション管理機能
├── UserManagement/
│   ├── UserProfileManagement/  # ユーザープロファイル管理
│   ├── UserRoleAssignment/     # ユーザー権限割り当て
│   ├── UserStatusManagement/   # ユーザー状態管理
│   └── UserAuditLog/          # ユーザー操作ログ
├── MasterDataManagement/
│   ├── CompanyManagement/      # 会社管理
│   ├── ClientManagement/       # 契約先管理
│   ├── WorkplaceManagement/    # 現場管理
│   ├── EmployeeManagement/     # 社員管理
│   └── HolidayManagement/      # 休暇管理
├── OrderManagement/
│   ├── OrderRegistration/      # 受注登録
│   ├── OrderListing/           # 受注一覧
│   ├── OrderSearch/            # 受注検索
│   ├── OrderStatusManagement/  # 受注ステータス管理
│   ├── OrderCalendar/          # 受注カレンダー
│   └── OrderReporting/         # 受注レポート
├── ShiftManagement/
│   ├── ShiftAssignment/        # シフト配置
│   ├── ShiftCalendar/          # シフトカレンダー
│   ├── EmployeeAvailability/   # 社員空き状況管理
│   ├── WorkplaceScheduling/    # 現場スケジューリング
│   ├── ShiftOptimization/      # シフト最適化
│   └── ShiftReporting/         # シフトレポート
├── Dashboard/
│   ├── MainDashboard/          # メインダッシュボード
│   ├── ProgressMonitoring/     # 進捗監視
│   ├── AlertManagement/        # アラート管理
│   └── WeatherIntegration/     # 天気予報連携
└── MobileSupport/
    ├── MobileAuthentication/   # モバイル認証
    ├── MobileShiftView/        # モバイルシフト表示
    ├── MobilePushNotification/ # プッシュ通知
    └── MobileOfflineSupport/   # オフライン対応
```

### ファイルサイズ制限

#### 厳格なファイルサイズ制限
- **PHPファイル**: 最大1000行
- **JavaScriptファイル**: 最大800行
- **Bladeテンプレート**: 最大500行
- **CSSファイル**: 最大1000行

#### ファイル分割の必須基準
```php
// 悪い例：1つのファイルに全機能
class EmployeeManagementController extends Controller 
{
    // 1200行のメソッドが詰め込まれている
    public function index() { /* 100行 */ }
    public function create() { /* 150行 */ }
    public function store() { /* 200行 */ }
    public function show() { /* 80行 */ }
    public function edit() { /* 120行 */ }
    public function update() { /* 250行 */ }
    public function destroy() { /* 100行 */ }
    public function bulkImport() { /* 300行 */ }
    public function exportToCsv() { /* 200行 */ }
    // → 合計1500行：制限違反
}

// 良い例：機能別に分割
// EmployeeBasicManagementController.php (500行)
class EmployeeBasicManagementController extends Controller 
{
    public function index() { /* 100行 */ }
    public function create() { /* 150行 */ }
    public function store() { /* 150行 */ }
    public function show() { /* 100行 */ }
}

// EmployeeDataOperationController.php (600行)
class EmployeeDataOperationController extends Controller 
{
    public function bulkImport() { /* 300行 */ }
    public function exportToCsv() { /* 200行 */ }
    public function validateImportData() { /* 100行 */ }
}
```

#### メソッド・関数サイズ制限
- **1メソッド**: 最大100行
- **1関数**: 最大80行
- **超過時の分割必須**

```php
// 悪い例：長すぎるメソッド（150行）
public function calculateOptimalShiftAssignment($parameters) 
{
    // 150行の複雑なロジック
}

// 良い例：適切に分割（各メソッド50行以下）
public function calculateOptimalShiftAssignment($parameters): ShiftAssignmentResult 
{
    $availableEmployees = $this->getAvailableEmployeesForDate($parameters['date']);
    $workplaceRequirements = $this->getWorkplaceRequirements($parameters['workplace_id']);
    $optimizedAssignments = $this->optimizeEmployeeAssignments($availableEmployees, $workplaceRequirements);
    
    return $this->createShiftAssignmentResult($optimizedAssignments);
}

private function getAvailableEmployeesForDate(DateTime $date): Collection { /* 40行 */ }
private function getWorkplaceRequirements(int $workplaceId): WorkplaceRequirement { /* 30行 */ }
private function optimizeEmployeeAssignments(Collection $employees, WorkplaceRequirement $requirements): array { /* 50行 */ }
private function createShiftAssignmentResult(array $assignments): ShiftAssignmentResult { /* 20行 */ }
```

### ファイルサイズ監視・自動チェック

#### Git Pre-commit Hook設定
```bash
#!/bin/sh
# .git/hooks/pre-commit

echo "ファイルサイズチェックを実行中..."

# PHPファイルの行数チェック
find . -name "*.php" -not -path "./vendor/*" | while read file; do
    lines=$(wc -l < "$file")
    if [ $lines -gt 1000 ]; then
        echo "❌ エラー: $file は $lines 行で制限（1000行）を超えています"
        exit 1
    fi
done

# JavaScriptファイルの行数チェック
find . -name "*.js" -o -name "*.jsx" | while read file; do
    lines=$(wc -l < "$file")
    if [ $lines -gt 800 ]; then
        echo "❌ エラー: $file は $lines 行で制限（800行）を超えています"
        exit 1
    fi
done

echo "✅ ファイルサイズチェック完了"
```

### テストデータ管理：ダミーデータ生成

#### Factory/Seeder 構造
```
database/
├── factories/
│   ├── CompanyFactory.php          # 会社ダミーデータ
│   ├── EmployeeFactory.php         # 社員ダミーデータ
│   ├── WorkplaceFactory.php        # 現場ダミーデータ
│   ├── OrderFactory.php            # 受注ダミーデータ
│   └── ShiftAssignmentFactory.php  # シフト配置ダミーデータ
├── seeders/
│   ├── DevelopmentDataSeeder.php   # 開発用データ
│   ├── TestingDataSeeder.php       # テスト用データ
│   ├── DemoDataSeeder.php          # デモ用データ
│   └── ProductionInitSeeder.php    # 本番初期データ
└── samples/
    ├── employees_sample.csv        # サンプルCSVデータ
    ├── workplaces_sample.csv       # サンプルCSVデータ
    └── orders_sample.csv           # サンプルCSVデータ
```

#### ダミーデータ生成例
```php
// EmployeeFactory.php
class EmployeeFactory extends Factory
{
    /**
     * 社員のダミーデータ定義
     * 
     * 現実的な日本の名前、住所、電話番号を生成
     * 各社員には適切なスキル・資格をランダム割り当て
     */
    public function definition(): array
    {
        return [
            'employee_number' => $this->faker->unique()->numerify('EMP####'),
            'company_id' => Company::factory(),
            'last_name' => $this->faker->lastKanaName(),
            'first_name' => $this->faker->firstKanaName(),
            'last_name_kana' => $this->faker->lastKanaName(),
            'first_name_kana' => $this->faker->firstKanaName(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birth_date' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'employment_status' => 'active',
            'hire_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'driving_license_type' => $this->faker->randomElement(['none', 'regular', 'medium', 'large']),
            'certification_traffic_1st' => $this->faker->boolean(30),
            'certification_traffic_2nd' => $this->faker->boolean(50),
            'certification_crowd_1st' => $this->faker->boolean(20),
            'certification_crowd_2nd' => $this->faker->boolean(40),
            'certification_facility_1st' => $this->faker->boolean(25),
            'certification_facility_2nd' => $this->faker->boolean(45),
        ];
    }
}
```

#### データ生成コマンド
```bash
# 開発環境用ダミーデータ生成
php artisan db:seed --class=DevelopmentDataSeeder

# テスト環境用ダミーデータ生成  
php artisan db:seed --class=TestingDataSeeder

# 大量データでのパフォーマンステスト用
php artisan db:seed --class=PerformanceTestDataSeeder
```

## テスト・品質管理

### テスト戦略

#### カバレッジ目標：60%以上（標準）
- **Unit Test**: 40%以上
- **Feature Test**: 80%以上（主要機能）
- **Browser Test**: 重要なユーザーフロー

#### テスト分類と優先度

##### 高優先度（必須テスト）- 90%以上
```php
// 認証関連
- ログイン・ログアウト機能
- 権限チェック機能
- セッション管理

// データ整合性関連
- シフト配置の競合チェック
- 社員の重複配置防止
- 勤務時間の計算ロジック

// 基幹業務機能
- 受注登録・更新
- シフト作成・変更
- 社員管理（登録・更新・削除）
```

##### 中優先度（推奨テスト）- 60%以上
```php
// 検索・フィルタ機能
- 複合条件での検索
- 日付範囲でのフィルタ
- 社員ステータスでの絞り込み

// レポート・集計機能
- 月次レポート生成
- 勤務時間集計
- 受注実績集計

// 外部連携機能
- Google Maps API連携
- 天気予報API連携
- メール送信機能
```

##### 低優先度（任意テスト）- 30%以上
```php
// UI/UX関連
- レスポンシブデザイン
- アクセシビリティ
- ブラウザ互換性

// パフォーマンス関連
- 大量データ処理
- 同時アクセス処理
- キャッシュ効率
```

### テストファイル構造
```
tests/
├── Unit/                           # 単体テスト
│   ├── Authentication/
│   │   ├── UserLoginServiceTest.php
│   │   ├── PasswordResetServiceTest.php
│   │   └── SessionManagementTest.php
│   ├── ShiftManagement/
│   │   ├── ShiftAssignmentServiceTest.php
│   │   ├── EmployeeAvailabilityTest.php
│   │   └── ShiftOptimizationTest.php
│   └── OrderManagement/
│       ├── OrderRegistrationServiceTest.php
│       ├── OrderSearchServiceTest.php
│       └── OrderValidationTest.php
├── Feature/                        # 機能テスト
│   ├── Authentication/
│   │   ├── LoginFeatureTest.php
│   │   ├── UserRegistrationTest.php
│   │   └── PasswordResetTest.php
│   ├── ShiftManagement/
│   │   ├── ShiftAssignmentTest.php
│   │   ├── ShiftCalendarTest.php
│   │   └── ShiftReportingTest.php
│   └── OrderManagement/
│       ├── OrderCrudTest.php
│       ├── OrderSearchTest.php
│       └── OrderCalendarTest.php
└── Browser/                        # ブラウザテスト（E2E）
    ├── LoginFlowTest.php
    ├── ShiftManagementFlowTest.php
    ├── OrderManagementFlowTest.php
    └── MobileResponsivenessTest.php
```

### 静的解析：Level 6（標準）

#### PHPStan設定（phpstan.neon）
```neon
parameters:
    level: 6
    paths:
        - app
        - database
        - routes
    excludePaths:
        - vendor
        - storage
        - bootstrap/cache
    
    # Level 6で有効なチェック項目
    checkMissingIterableValueType: true
    checkGenericClassInNonGenericObjectType: false
    checkMissingCallableSignature: true
    
    # プロジェクト固有の除外設定
    ignoreErrors:
        - '#Call to an undefined method Illuminate\\Database\\Eloquent\\Builder::#'
        - '#Property .* does not accept default value of type null#'
    
    # Laravel固有の設定
    bootstrapFiles:
        - %currentWorkingDirectory%/bootstrap/app.php
```

#### ESLint設定（.eslintrc.js）
```javascript
module.exports = {
  extends: [
    'eslint:recommended',
    '@typescript-eslint/recommended',
    'plugin:react/recommended'
  ],
  rules: {
    // Level 6相当の中程度の厳格さ
    'no-unused-vars': 'warn',           // 未使用変数は警告
    'no-undef': 'error',                // 未定義変数はエラー
    'prefer-const': 'warn',             // const推奨は警告
    'no-console': 'warn',               // console.logは警告
    'max-len': ['warn', { code: 120 }], // 120文字制限
    
    // React固有ルール
    'react/prop-types': 'warn',         // PropTypes推奨
    'react/jsx-uses-vars': 'error',     // JSX変数使用チェック
    'react-hooks/exhaustive-deps': 'warn' // useEffect依存配列チェック
  }
};
```

#### 自動チェック実行コマンド
```bash
# PHPStan実行
./vendor/bin/phpstan analyse --memory-limit=2G

# ESLint実行
npx eslint resources/js --ext .js,.jsx,.ts,.tsx

# 両方を一括実行
npm run lint:all
```

### エラー処理・ログ管理：エラー・警告のみ

#### ログレベル定義
```php
// config/logging.php
'default' => env('LOG_CHANNEL', 'stack'),

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'warning'), // エラー・警告のみ
    ],

    'business' => [
        'driver' => 'daily',
        'path' => storage_path('logs/business.log'),
        'level' => 'warning',
        'days' => 30,
    ],
];
```

#### 標準的なログ出力パターン
```php
/**
 * ログ出力の標準パターン
 * 
 * エラー・警告のみをログ出力し、
 * 重要な業務処理の失敗を確実に記録する
 */
class ShiftAssignmentService 
{
    /**
     * シフト配置処理
     */
    public function assignEmployeeToShift(Employee $employee, Workplace $workplace, DateTime $date): ShiftAssignmentResult 
    {
        try {
            // 業務処理実行
            $result = $this->executeShiftAssignment($employee, $workplace, $date);
            
            // 成功時は通常ログ出力なし（デバッグ情報は除く）
            return $result;
            
        } catch (EmployeeNotAvailableException $e) {
            // 警告レベル：業務例外
            Log::warning('社員配置失敗：社員が利用不可', [
                'employee_id' => $employee->id,
                'workplace_id' => $workplace->id,
                'date' => $date->format('Y-m-d'),
                'reason' => $e->getMessage(),
                'context' => $this->getContextData($employee, $workplace, $date)
            ]);
            
            throw $e;
            
        } catch (DatabaseException $e) {
            // エラーレベル：システム例外
            Log::error('シフト配置処理でデータベースエラー', [
                'employee_id' => $employee->id,
                'workplace_id' => $workplace->id,
                'date' => $date->format('Y-m-d'),
                'error_message' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            throw new SystemErrorException('システムエラーが発生しました', 0, $e);
            
        } catch (\Exception $e) {
            // エラーレベル：予期しない例外
            Log::error('シフト配置処理で予期しないエラー', [
                'employee_id' => $employee->id,
                'workplace_id' => $workplace->id,
                'date' => $date->format('Y-m-d'),
                'error_class' => get_class($e),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            throw new SystemErrorException('予期しないエラーが発生しました', 0, $e);
        }
    }
}
```

#### ログ監視・アラート設定
```php
// app/Logging/BusinessLogHandler.php
class BusinessLogHandler
{
    /**
     * 重要なエラーが発生した場合の自動通知
     */
    public function handle(array $record): void
    {
        // エラーレベル以上の場合、管理者に通知
        if ($record['level'] >= Logger::ERROR) {
            $this->notifyAdministrators($record);
        }
        
        // データベースエラーの場合、即座に通知
        if (strpos($record['message'], 'データベースエラー') !== false) {
            $this->sendUrgentNotification($record);
        }
    }
}
```

### 品質管理自動化

#### GitHub Actions設定例
```yaml
# .github/workflows/quality-check.yml
name: Quality Check

on: [push, pull_request]

jobs:
  php-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
          
      - name: Install dependencies
        run: composer install
        
      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse --no-progress
        
      - name: Run PHPUnit
        run: ./vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml
        
      - name: Check coverage
        run: |
          COVERAGE=$(grep -o 'Lines:.*%' coverage.xml | grep -o '[0-9.]*%' | head -1 | tr -d '%')
          if (( $(echo "$COVERAGE < 60" | bc -l) )); then
            echo "カバレッジが60%を下回っています: $COVERAGE%"
            exit 1
          fi
```

## デプロイメント・環境管理

### 環境構成：3環境

#### 環境別責任と用途
```
Local Environment (開発者個人)
├── 目的：機能開発・単体テスト・デバッグ
├── データ：ダミーデータ・小規模サンプル
├── 設定：開発用設定・デバッグ有効
└── 責任者：各開発者

↓ (機能完成時)

Staging Environment (検証環境)
├── 目的：統合テスト・ユーザー受け入れテスト・本番前検証
├── データ：本番類似データ・完全なテストシナリオ
├── 設定：本番近似設定・ログ詳細化
└── 責任者：プロジェクト管理者

↓ (検証完了時)

Production Environment (本番環境)
├── 目的：実運用・エンドユーザー利用
├── データ：実データ・機密情報
├── 設定：本番用設定・セキュリティ最適化
└── 責任者：システム管理者
```

#### 環境別設定ファイル
```
config/
├── environments/
│   ├── local.php           # ローカル環境固有設定
│   ├── staging.php         # ステージング環境固有設定
│   └── production.php      # 本番環境固有設定
├── database.php            # 全環境共通DB設定
├── mail.php               # 全環境共通メール設定
└── app.php                # 全環境共通アプリ設定
```

#### 環境変数管理
```bash
# .env.local (ローカル環境)
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=keibi_system_local
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
LOG_LEVEL=debug

# .env.staging (ステージング環境)
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.keibi-system.com

DB_CONNECTION=mysql
DB_HOST=staging-db.internal
DB_PORT=3306
DB_DATABASE=keibi_system_staging
DB_USERNAME=staging_user
DB_PASSWORD=${DB_STAGING_PASSWORD}

MAIL_MAILER=smtp
LOG_LEVEL=warning

# .env.production (本番環境)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://keibi-system.com

DB_CONNECTION=mysql
DB_HOST=prod-db.internal
DB_PORT=3306
DB_DATABASE=keibi_system_production
DB_USERNAME=prod_user
DB_PASSWORD=${DB_PRODUCTION_PASSWORD}

MAIL_MAILER=smtp
LOG_LEVEL=error
```

### データベース管理：週次自動バックアップ

#### バックアップ戦略
```bash
# バックアップスケジュール
# - 本番環境：毎週日曜日 02:00 (完全バックアップ)
# - ステージング環境：毎週水曜日 03:00
# - 重要変更前：手動バックアップ必須

# crontab設定例
0 2 * * 0 /var/www/keibi-system/scripts/backup-production.sh
0 3 * * 3 /var/www/keibi-system/scripts/backup-staging.sh
```

#### 自動バックアップスクリプト
```bash
#!/bin/bash
# scripts/backup-production.sh

# 設定
BACKUP_DIR="/var/backups/keibi-system"
DB_NAME="keibi_system_production"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql"

# ディレクトリ作成
mkdir -p $BACKUP_DIR

# データベースバックアップ実行
mysqldump \
  --host=$DB_HOST \
  --user=$DB_USER \
  --password=$DB_PASSWORD \
  --single-transaction \
  --routines \
  --triggers \
  $DB_NAME > $BACKUP_FILE

# 圧縮
gzip $BACKUP_FILE

# 古いバックアップ削除（4週間より古いもの）
find $BACKUP_DIR -name "*.sql.gz" -mtime +28 -delete

# ログ出力
echo "$(date): バックアップ完了 - ${BACKUP_FILE}.gz" >> /var/log/keibi-system-backup.log

# 成功通知（重要なバックアップの場合）
if [ $? -eq 0 ]; then
    echo "データベースバックアップが正常に完了しました" | mail -s "バックアップ成功通知" admin@keibi-system.com
else
    echo "データベースバックアップでエラーが発生しました" | mail -s "バックアップ失敗通知" admin@keibi-system.com
fi
```

#### Migration管理の厳格化
```php
// database/migrations/verification_required/
// 本番適用前に必ずステージングで検証が必要なMigration

/**
 * 本番適用前チェックリスト
 * 
 * □ ローカル環境で正常動作確認済み
 * □ ステージング環境でテストデータを使用した検証完了
 * □ ロールバック手順の確認済み
 * □ 関係者レビュー完了
 * □ バックアップ取得済み
 */
class CreateEmployeeShiftAssignmentsTable extends Migration 
{
    /**
     * 危険度評価：中（既存データへの影響あり）
     * 推定実行時間：5分
     * ロールバック可能性：可能
     */
    public function up(): void
    {
        Schema::create('employee_shift_assignments', function (Blueprint $table) {
            // テーブル定義
        });
    }
}
```

### デプロイメント：機能完成時随時デプロイ

#### デプロイフロー
```
開発者作業完了
↓
Pull Request作成
↓
コードレビュー・テスト実行
↓
mainブランチにマージ
↓
自動的にステージング環境デプロイ
↓
ステージング環境での動作確認
↓ (確認完了後)
本番環境デプロイ実行
↓
本番環境での動作確認・ログ監視
```

#### 自動デプロイスクリプト（ステージング）
```bash
#!/bin/bash
# scripts/deploy-staging.sh

echo "=== ステージング環境デプロイ開始 ==="

# GitHubからの最新コード取得
cd /var/www/keibi-system-staging
git fetch origin
git reset --hard origin/main

# Composer依存関係更新
composer install --no-dev --optimize-autoloader

# NPM依存関係更新・ビルド
npm ci
npm run production

# データベースマイグレーション（ステージング環境）
php artisan migrate --force

# キャッシュクリア・最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ファイル権限設定
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "=== ステージング環境デプロイ完了 ==="

# 自動テスト実行
php artisan test --env=staging

# 成功通知
curl -X POST "https://hooks.slack.com/..." \
  -H 'Content-type: application/json' \
  --data '{"text":"ステージング環境デプロイ完了：機能テストを実施してください"}'
```

#### 手動デプロイスクリプト（本番）
```bash
#!/bin/bash
# scripts/deploy-production.sh

echo "=== 本番環境デプロイ開始 ==="
echo "本番環境デプロイを実行します。続行しますか？ (y/N)"
read confirmation

if [ "$confirmation" != "y" ]; then
    echo "デプロイを中止しました"
    exit 1
fi

# バックアップ実行
echo "デプロイ前バックアップを実行中..."
./scripts/backup-production.sh

# メンテナンスモード有効化
php artisan down --message="システムメンテナンス中です。しばらくお待ちください。"

# デプロイ処理実行
cd /var/www/keibi-system-production

git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader
npm ci && npm run production

# データベースマイグレーション（慎重に実行）
echo "データベースマイグレーションを実行しますか？ (y/N)"
read migrate_confirmation
if [ "$migrate_confirmation" = "y" ]; then
    php artisan migrate --force
fi

# キャッシュ更新
php artisan config:cache
php artisan route:cache  
php artisan view:cache

# ファイル権限設定
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# メンテナンスモード解除
php artisan up

echo "=== 本番環境デプロイ完了 ==="

# 成功通知
curl -X POST "https://hooks.slack.com/..." \
  -H 'Content-type: application/json' \
  --data '{"text":"🚀 本番環境デプロイ完了：システム稼働を確認してください"}'
```

#### デプロイチェックリスト
```markdown
## 本番デプロイ前チェックリスト

### 事前準備
- [ ] ステージング環境での動作確認完了
- [ ] 関係者への事前通知完了
- [ ] バックアップ取得確認
- [ ] ロールバック手順の確認
- [ ] 緊急連絡先の確認

### デプロイ実行
- [ ] メンテナンス画面の表示確認
- [ ] コードデプロイ完了
- [ ] データベースマイグレーション実行
- [ ] キャッシュクリア・再生成
- [ ] ファイル権限設定

### 事後確認
- [ ] 主要機能の動作確認
- [ ] エラーログの確認
- [ ] パフォーマンス確認
- [ ] 関係者への完了報告
```

---

## 開発ルール決定完了サマリー

### 決定事項一覧

| 分類 | 設定内容 | 詳細 |
|------|----------|------|
| **Git管理** | GitHub Flow + 日本語コミット + 作業単位 | シンプルなブランチ戦略 |
| **コーディング規約** | PSR-12 + 冗長性重視 + 日本語コメント + 英語命名 | 厳格だが理解しやすい |
| **ファイル制限** | PHP:1000行, JS:800行, メソッド:100行 | 保守性確保 |
| **モジュール分割** | 小分類（機能ごと細分化） | 高い保守性 |
| **テストデータ** | ダミーデータ生成 | 安全な開発環境 |
| **品質管理** | カバレッジ60%以上 + PHPStan Lv6 + エラー・警告ログ | バランス重視 |
| **環境構成** | 3環境（Local→Staging→Production） | 実用的構成 |
| **バックアップ** | 週次自動バックアップ | 継続可能な運用 |
| **デプロイ** | 機能完成時随時デプロイ | アジャイルな開発 |

### 次のアクション

1. **即座実行可能**
   - Laravelプロジェクト作成
   - 開発環境構築
   - データベース設計開始

2. **設定ファイル作成**
   - .eslintrc.js
   - phpstan.neon
   - GitHub Actions設定

3. **開発開始準備**
   - 基本的なプロジェクト構造作成
   - 認証機能の実装開始

開発ルールが確定しました。次はどの作業から始めますか？

---
**決定日**: 2025-05-22
**最終更新**: 2025-05-22

