# 警備グループ会社 受注管理・シフト管理統合システム 仕様書

## 1. システム概要

### 1.1 システム名
警備グループ会社 受注管理・シフト管理統合システム（Security Management System）

### 1.2 目的
警備業務における受注管理からシフト管理までを統合的に管理し、業務効率化と情報の一元化を実現する。

### 1.3 対象業務
- 顧客・案件管理
- 受注管理（見積・契約）
- 警備員管理
- シフト管理・配置管理
- 売上・請求管理
- 報告書管理

## 2. システム要件

### 2.1 機能要件

#### 2.1.1 顧客管理機能
- **顧客基本情報管理**
  - 顧客ID（自動採番）
  - 顧客名（企業名・個人名）
  - 住所（郵便番号、都道府県、市区町村、番地、建物名）
  - 電話番号（複数登録可）
  - メールアドレス（複数登録可）
  - 担当者情報（部署、役職、氏名）
  - 契約開始日、契約終了日
  - 顧客区分（法人・個人）
  - 信用情報・特記事項

- **顧客検索・一覧表示**
  - 顧客名、電話番号、メールアドレスでの検索
  - 契約状況でのフィルタリング
  - 五十音順、登録日順での並び替え

#### 2.1.2 案件管理機能
- **案件基本情報管理**
  - 案件ID（自動採番）
  - 案件名
  - 顧客ID（顧客管理との連携）
  - 警備場所（住所詳細、GPS座標）
  - 警備種別（施設警備、交通誘導、イベント警備、身辺警備等）
  - 警備期間（開始日時、終了日時）
  - 必要人数・配置人数
  - 時給・日給情報
  - 特記事項・注意事項

- **見積管理**
  - 見積番号（自動採番）
  - 見積作成日
  - 見積金額計算（人件費、諸経費、消費税）
  - 見積書印刷機能
  - 見積状況管理（作成中、提出済み、承認、却下）

- **契約管理**
  - 契約番号（自動採番）
  - 契約締結日
  - 契約内容変更履歴
  - 契約書類管理

#### 2.1.3 警備員管理機能
- **警備員基本情報管理**
  - 警備員ID（自動採番）
  - 氏名（フリガナ）
  - 生年月日・年齢
  - 住所・連絡先
  - 緊急連絡先
  - 入社日・退社日
  - 雇用形態（正社員、契約社員、アルバイト）
  - 所持資格（警備業検定、各種免許等）
  - 健康状態・制限事項

- **スキル・資格管理**
  - 警備業検定レベル
  - 特殊技能（語学、IT、運転等）
  - 勤務可能エリア
  - 勤務可能時間帯
  - 警備種別適性

- **勤務実績管理**
  - 月間勤務日数・時間
  - 勤務評価履歴
  - 研修受講履歴

#### 2.1.4 シフト管理機能
- **シフト作成・編集**
  - 案件別シフト表作成
  - ドラッグ&ドロップでの直感的操作
  - 警備員の勤務可能時間との照合
  - 人員配置の最適化提案
  - シフト変更履歴管理

- **シフト表示・検索**
  - カレンダー形式表示
  - 警備員別、案件別、日付別でのフィルタリング
  - 月間・週間・日間ビュー切り替え
  - シフト確定・仮配置状態管理

- **シフト通知機能**
  - 警備員へのシフト確定通知
  - シフト変更時の緊急連絡
  - 前日確認通知

#### 2.1.5 勤怠管理機能
- **出退勤記録**
  - 案件現場での出退勤記録
  - 遅刻・早退・欠勤管理
  - 休憩時間管理
  - GPS位置情報記録

- **勤怠集計**
  - 警備員別月間勤怠集計
  - 案件別勤怠集計
  - 残業時間計算
  - 深夜勤務・休日勤務手当計算

#### 2.1.6 売上・請求管理機能
- **売上管理**
  - 案件別売上計算
  - 月間・年間売上集計
  - 顧客別売上分析
  - 警備種別売上分析

- **請求書管理**
  - 請求書自動作成
  - 請求書番号管理
  - 入金管理・督促管理
  - 消費税計算

#### 2.1.7 報告書管理機能
- **日報管理**
  - 警備員による現場報告書作成
  - 写真・動画添付機能
  - 異常事項報告
  - 報告書承認ワークフロー

- **月報・各種レポート**
  - 案件別月次レポート
  - 警備員稼働状況レポート
  - 売上分析レポート
  - 顧客満足度レポート

#### 2.1.8 ユーザー管理・権限管理機能
- **ユーザー管理**
  - 管理者、営業、現場責任者、警備員の役割別管理
  - ログイン認証・パスワード管理
  - 操作ログ記録

- **権限管理**
  - 機能別アクセス権限設定
  - データ閲覧・編集権限管理
  - 承認権限管理

### 2.2 非機能要件

#### 2.2.1 性能要件
- 同時接続ユーザー数：50名以上
- レスポンス時間：3秒以内
- データベース容量：初期10GB、年間増加5GB程度

#### 2.2.2 可用性要件
- システム稼働率：99.5%以上
- 保守時間：毎週日曜日深夜2時間以内

#### 2.2.3 セキュリティ要件
- SSL/TLS暗号化通信
- ユーザー認証（ID/パスワード）
- 個人情報保護対応
- 操作ログ記録・監査機能

#### 2.2.4 バックアップ・復旧要件
- 日次自動バックアップ
- 週次完全バックアップ
- 災害時復旧時間：24時間以内

## 3. システム構成

### 3.1 技術仕様
- **フレームワーク**: Laravel 10.x
- **プログラミング言語**: PHP 8.1以上
- **データベース**: MySQL 8.0
- **Webサーバー**: Apache 2.4
- **フロントエンド**: HTML5, CSS3, JavaScript, Bootstrap 5

### 3.2 開発環境
- **ローカル環境**: XAMPP
- **ステージング環境**: 本番同等環境
- **本番環境**: レンタルサーバーまたはクラウド

### 3.3 ディレクトリ構成
```
keibi-system/
├── app/
│   ├── Http/Controllers/     # コントローラー
│   ├── Models/              # Eloquentモデル
│   ├── Services/            # ビジネスロジック
│   └── Helpers/             # ヘルパー関数
├── database/
│   ├── migrations/          # データベースマイグレーション
│   ├── seeders/            # テストデータ
│   └── factories/          # ファクトリー
├── resources/
│   ├── views/              # Bladeテンプレート
│   ├── js/                 # JavaScript
│   └── css/                # スタイルシート
├── public/                 # 公開ディレクトリ
├── storage/                # ファイルストレージ
└── docs/                   # プロジェクト文書
```

## 4. データベース設計

### 4.1 実装状況
- **実装完了日**: 2025年5月22日
- **データベース名**: keibi_system
- **テーブル数**: 11テーブル
- **マイグレーション**: 全て実行完了
- **テストデータ**: 警備業界特有のリアルデータ投入完了
- **整合性**: 外部キー制約、インデックス、ユニーク制約完備

### 4.2 主要テーブル構成

#### customers（顧客マスタ）
- id (PK, AUTO_INCREMENT)
- customer_code (顧客コード, UNIQUE)
- company_name (会社名)
- representative_name (代表者名)
- postal_code (郵便番号)
- address1 (住所1)
- address2 (住所2)
- phone (電話番号)
- email (メールアドレス)
- customer_type (顧客区分)
- contract_start_date (契約開始日)
- contract_end_date (契約終了日)
- notes (備考)
- created_at, updated_at

#### projects（案件マスタ）
- id (PK, AUTO_INCREMENT)
- project_code (案件コード, UNIQUE)
- project_name (案件名)
- customer_id (FK: customers.id)
- security_type (警備種別)
- location_address (警備場所住所)
- location_latitude (緯度)
- location_longitude (経度)
- start_date (開始日)
- end_date (終了日)
- required_staff_count (必要人数)
- hourly_rate (時給)
- notes (特記事項)
- status (ステータス)
- created_at, updated_at

#### guards（警備員マスタ）
- id (PK, AUTO_INCREMENT)
- guard_code (警備員コード, UNIQUE)
- name (氏名)
- name_kana (フリガナ)
- birth_date (生年月日)
- postal_code (郵便番号)
- address (住所)
- phone (電話番号)
- email (メールアドレス)
- emergency_contact (緊急連絡先)
- hire_date (入社日)
- employment_type (雇用形態)
- qualifications (所持資格)
- skills (特殊技能)
- available_areas (勤務可能エリア)
- status (在籍状況)
- created_at, updated_at

#### shifts（シフトマスタ）
- id (PK, AUTO_INCREMENT)
- project_id (FK: projects.id)
- guard_id (FK: guards.id)
- shift_date (勤務日)
- start_time (開始時刻)
- end_time (終了時刻)
- break_time (休憩時間)
- status (確定/仮配置)
- notes (備考)
- created_at, updated_at

#### attendances（勤怠記録）
- id (PK, AUTO_INCREMENT)
- shift_id (FK: shifts.id)
- actual_start_time (実際の開始時刻)
- actual_end_time (実際の終了時刻)
- break_minutes (休憩時間(分))
- location_checkin (出勤位置情報)
- location_checkout (退勤位置情報)
- notes (備考)
- created_at, updated_at

#### quotations（見積管理）
- id (PK, AUTO_INCREMENT)
- quotation_number (見積番号, UNIQUE)
- project_id (FK: projects.id)
- quotation_date (見積日)
- total_amount (見積金額)
- tax_amount (消費税額)
- status (見積状況)
- valid_until (有効期限)
- created_at, updated_at

#### contracts（契約管理）
- id (PK, AUTO_INCREMENT)
- contract_number (契約番号, UNIQUE)
- project_id (FK: projects.id)
- contract_date (契約日)
- contract_amount (契約金額)
- payment_terms (支払条件)
- status (契約状況)
- created_at, updated_at

#### invoices（請求管理）
- id (PK, AUTO_INCREMENT)
- invoice_number (請求書番号, UNIQUE)
- contract_id (FK: contracts.id)
- invoice_date (請求日)
- due_date (支払期限)
- total_amount (請求金額)
- tax_amount (消費税額)
- payment_status (支払状況)
- payment_date (入金日)
- created_at, updated_at

#### daily_reports（日報管理）
- id (PK, AUTO_INCREMENT)
- shift_id (FK: shifts.id)
- report_date (報告日)
- weather (天候)
- incident_count (異常件数)
- patrol_count (巡回回数)
- visitor_count (来訪者数)
- special_notes (特記事項)
- photo_paths (写真パス)
- approval_status (承認状況)
- approved_by (承認者)
- approved_at (承認日時)
- created_at, updated_at

### 4.3 実装済みテーブル詳細

#### users（ユーザーマスタ）- **実装完了**
- Laravel標準認証テーブルを拡張
- employee_id, company_id, permissions等を追加
- 3社統合管理のための会社別権限管理対応

#### shift_guard_assignments（シフト配置管理）- **実装完了**
- シフトと警備員の多対多関係管理
- 配置時間、役割、ステータス管理
- 柔軟なシフト配置に対応

### 4.4 Eloquentモデル実装状況
全11個のモデルクラス実装完了：
- **User**: 認証・権限管理
- **Customer**: 顧客管理・関連プロジェクト
- **Project**: 案件管理・見積・契約連携
- **Guard**: 警備員管理・スキル・勤務実績
- **Shift**: シフト管理・配置・勤怠連携
- **Attendance**: 勤怠記録・自動計算機能
- **Quotation**: 見積管理・自動計算・承認フロー
- **Contract**: 契約管理・ステータス管理
- **Invoice**: 請求管理・入金管理・督促機能
- **DailyReport**: 日報管理・承認ワークフロー
- **ShiftGuardAssignment**: シフト配置管理

### 4.5 データベース特徴
- **JSON型活用**: qualifications, skills, permissions等
- **enum型ステータス管理**: 各テーブルで適切な状態管理
- **外部キー制約**: データ整合性確保
- **インデックス最適化**: パフォーマンス向上
- **日本語コメント**: 保守性向上
- **SoftDeletes対応**: 論理削除による履歴保持

### 4.6 テストデータ詳細
警備業界特有のリアルなダミーデータ投入済み：
- **顧客**: 10社（大手企業、中小企業、個人事業主等）
- **ユーザー**: 8名（管理者、営業、現場責任者、警備員）
- **プロジェクト**: 10件（施設警備、交通誘導、イベント警備等）
- **警備員**: 10名（多様な資格・スキル・経験）
- **シフト**: 15種（昼夜勤、週末、長期短期等）
- **その他**: 勤怠、見積、契約、請求、日報等の関連データ

## 5. 開発スケジュール

### フェーズ1: 基盤構築・マスタ管理機能（4週間）
1. 顧客管理機能
2. 案件管理機能
3. 警備員管理機能
4. ユーザー管理・権限管理機能

### フェーズ2: シフト・勤怠管理機能（3週間）
1. シフト管理機能
2. 勤怠管理機能

### フェーズ3: 売上・請求管理機能（3週間）
1. 見積・契約管理機能
2. 売上・請求管理機能

### フェーズ4: 報告書・分析機能（2週間）
1. 日報管理機能
2. 各種レポート機能

### フェーズ5: テスト・運用準備（2週間）
1. 結合テスト
2. 性能テスト
3. セキュリティテスト
4. 運用マニュアル作成

## 6. 運用・保守

### 6.1 保守項目
- システム監視
- バックアップ管理
- セキュリティパッチ適用
- 機能追加・改修

### 6.2 サポート体制
- 平日 9:00-18:00 電話・メールサポート
- 緊急時24時間対応
- 月次定期メンテナンス

---

**作成日**: 2025年5月22日  
**バージョン**: 1.0  
**作成者**: Claude AI Assistant  

