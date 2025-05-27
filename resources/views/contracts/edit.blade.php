@extends('layouts.app')

@section('title', '契約編集 - ' . $contract->contract_number)

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">契約編集</h1>
                    <p class="text-muted mb-0">Edit Contract - {{ $contract->contract_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('contracts.show', $contract) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 詳細に戻る
                    </a>
                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> 一覧に戻る
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="showChangeHistory()">
                        <i class="fas fa-history"></i> 変更履歴
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 編集制限アラート -->
    @if($contract->status === 'active')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            この契約は有効状態です。重要な変更を行う場合は承認が必要になる場合があります。
        </div>
    @elseif($contract->status === 'completed' || $contract->status === 'terminated')
        <div class="alert alert-danger">
            <i class="fas fa-ban"></i> 
            この契約は{{ $contract->status === 'completed' ? '完了' : '解約' }}状態のため、編集できません。
        </div>
    @endif

    <!-- 変更前後比較エリア -->
    <div class="card mb-4" id="comparison-area" style="display: none;">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-exchange-alt"></i> 変更内容プレビュー
            </h5>
        </div>
        <div class="card-body">
            <div id="changes-preview">
                <!-- 変更内容がここに表示される -->
            </div>
        </div>
    </div>

    <!-- 契約編集フォーム -->
    <form id="contract-edit-form" method="POST" action="{{ route('contracts.update', $contract) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- 基本情報 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> 基本情報
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">契約番号</label>
                        <input type="text" class="form-control @error('contract_number') is-invalid @enderror" 
                               name="contract_number" value="{{ old('contract_number', $contract->contract_number) }}" 
                               data-original="{{ $contract->contract_number }}" readonly>
                        @error('contract_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">契約番号は変更できません</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label required">顧客</label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" name="customer_id" required
                                data-original="{{ $contract->customer_id }}" onchange="trackChange(this)">
                            <option value="">顧客を選択</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $contract->customer_id) == $customer->id ? 'selected' : '' }}
                                        data-projects="{{ $customer->projects->pluck('name', 'id')->toJson() }}">
                                    {{ $customer->name }} ({{ $customer->company_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($contract->status === 'active')
                            <small class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> 顧客変更は承認が必要です
                            </small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">案件</label>
                        <select class="form-select @error('project_id') is-invalid @enderror" name="project_id" required
                                data-original="{{ $contract->project_id }}" onchange="trackChange(this)">
                            <option value="">案件を選択</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" 
                                        {{ old('project_id', $contract->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label required">契約件名</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title', $contract->title) }}" required
                               data-original="{{ $contract->title }}" onchange="trackChange(this)"
                               placeholder="契約の件名を入力">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">契約種別</label>
                        <select class="form-select @error('contract_type') is-invalid @enderror" name="contract_type"
                                data-original="{{ $contract->contract_type }}" onchange="trackChange(this)">
                            <option value="">契約種別を選択</option>
                            <option value="security_guard" {{ old('contract_type', $contract->contract_type) === 'security_guard' ? 'selected' : '' }}>警備業務</option>
                            <option value="facility_management" {{ old('contract_type', $contract->contract_type) === 'facility_management' ? 'selected' : '' }}>施設管理</option>
                            <option value="event_security" {{ old('contract_type', $contract->contract_type) === 'event_security' ? 'selected' : '' }}>イベント警備</option>
                            <option value="traffic_control" {{ old('contract_type', $contract->contract_type) === 'traffic_control' ? 'selected' : '' }}>交通誘導</option>
                            <option value="other" {{ old('contract_type', $contract->contract_type) === 'other' ? 'selected' : '' }}>その他</option>
                        </select>
                        @error('contract_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- 契約期間・条件 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt"></i> 契約期間・条件
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">契約開始日</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                               name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required
                               data-original="{{ $contract->start_date->format('Y-m-d') }}" onchange="trackChange(this); calculateDuration()">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($contract->status === 'active' && $contract->start_date->isPast())
                            <small class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> 開始済み契約の開始日変更は注意が必要です
                            </small>
                        @endif
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">契約終了日</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                               name="end_date" value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}" required
                               data-original="{{ $contract->end_date->format('Y-m-d') }}" onchange="trackChange(this); calculateDuration()">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">契約期間（自動計算）</label>
                        <input type="text" class="form-control" id="contract-duration" readonly 
                               value="{{ $contract->start_date->diffInDays($contract->end_date) }}日間">
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_auto_renewal" value="1" 
                                   {{ old('is_auto_renewal', $contract->is_auto_renewal) ? 'checked' : '' }} id="auto-renewal"
                                   data-original="{{ $contract->is_auto_renewal ? '1' : '0' }}" onchange="trackChange(this)">
                            <label class="form-check-label" for="auto-renewal">
                                自動更新契約
                            </label>
                        </div>
                        <small class="form-text text-muted">期限前に自動的に契約を更新</small>
                    </div>

                    <div class="col-md-4" id="renewal-period" style="{{ $contract->is_auto_renewal ? '' : 'display: none;' }}">
                        <label class="form-label">更新期間</label>
                        <select class="form-select" name="renewal_period"
                                data-original="{{ $contract->renewal_period }}" onchange="trackChange(this)">
                            <option value="1_month" {{ old('renewal_period', $contract->renewal_period) === '1_month' ? 'selected' : '' }}>1ヶ月</option>
                            <option value="3_months" {{ old('renewal_period', $contract->renewal_period) === '3_months' ? 'selected' : '' }}>3ヶ月</option>
                            <option value="6_months" {{ old('renewal_period', $contract->renewal_period) === '6_months' ? 'selected' : '' }}>6ヶ月</option>
                            <option value="1_year" {{ old('renewal_period', $contract->renewal_period) === '1_year' ? 'selected' : '' }}>1年</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="renewal-notice" style="{{ $contract->is_auto_renewal ? '' : 'display: none;' }}">
                        <label class="form-label">更新通知期間</label>
                        <select class="form-select" name="renewal_notice_period"
                                data-original="{{ $contract->renewal_notice_period }}" onchange="trackChange(this)">
                            <option value="30_days" {{ old('renewal_notice_period', $contract->renewal_notice_period) === '30_days' ? 'selected' : '' }}>30日前</option>
                            <option value="60_days" {{ old('renewal_notice_period', $contract->renewal_notice_period) === '60_days' ? 'selected' : '' }}>60日前</option>
                            <option value="90_days" {{ old('renewal_notice_period', $contract->renewal_notice_period) === '90_days' ? 'selected' : '' }}>90日前</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">支払い条件</label>
                        <select class="form-select @error('payment_terms') is-invalid @enderror" name="payment_terms"
                                data-original="{{ $contract->payment_terms }}" onchange="trackChange(this)">
                            <option value="">支払い条件を選択</option>
                            <option value="monthly_end" {{ old('payment_terms', $contract->payment_terms) === 'monthly_end' ? 'selected' : '' }}>月末締め翌月末払い</option>
                            <option value="monthly_25" {{ old('payment_terms', $contract->payment_terms) === 'monthly_25' ? 'selected' : '' }}>25日締め翌月25日払い</option>
                            <option value="bi_monthly" {{ old('payment_terms', $contract->payment_terms) === 'bi_monthly' ? 'selected' : '' }}>隔月払い</option>
                            <option value="quarterly" {{ old('payment_terms', $contract->payment_terms) === 'quarterly' ? 'selected' : '' }}>四半期払い</option>
                            <option value="upfront" {{ old('payment_terms', $contract->payment_terms) === 'upfront' ? 'selected' : '' }}>前払い</option>
                            <option value="other" {{ old('payment_terms', $contract->payment_terms) === 'other' ? 'selected' : '' }}>その他</option>
                        </select>
                        @error('payment_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">支払い方法</label>
                        <select class="form-select" name="payment_method"
                                data-original="{{ $contract->payment_method }}" onchange="trackChange(this)">
                            <option value="">支払い方法を選択</option>
                            <option value="bank_transfer" {{ old('payment_method', $contract->payment_method) === 'bank_transfer' ? 'selected' : '' }}>銀行振込</option>
                            <option value="cash" {{ old('payment_method', $contract->payment_method) === 'cash' ? 'selected' : '' }}>現金</option>
                            <option value="check" {{ old('payment_method', $contract->payment_method) === 'check' ? 'selected' : '' }}>小切手</option>
                            <option value="credit_card" {{ old('payment_method', $contract->payment_method) === 'credit_card' ? 'selected' : '' }}>クレジットカード</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 契約金額 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-yen-sign"></i> 契約金額
                    @if($contract->status === 'active')
                        <span class="badge bg-warning ms-2">金額変更は承認必須</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">基本契約金額</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control @error('base_amount') is-invalid @enderror" 
                                   name="base_amount" value="{{ old('base_amount', $contract->base_amount) }}" required
                                   step="1" min="0" id="base-amount"
                                   data-original="{{ $contract->base_amount }}" onchange="trackChange(this); calculateTotals()">
                        </div>
                        @error('base_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($contract->status === 'active')
                            <small class="form-text text-warning">
                                現在: ¥{{ number_format($contract->base_amount) }}
                            </small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">消費税額</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control" name="tax_amount" 
                                   value="{{ old('tax_amount', $contract->tax_amount) }}" step="1" min="0" id="tax-amount" readonly>
                        </div>
                        <small class="form-text text-muted">基本金額の10%で自動計算</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">総契約金額</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control fw-bold" name="total_amount" 
                                   value="{{ old('total_amount', $contract->total_amount) }}" step="1" min="0" id="total-amount" readonly>
                        </div>
                        @if($contract->status === 'active')
                            <small class="form-text text-info">
                                変更差額: <span id="amount-diff">¥0</span>
                            </small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">単価形態</label>
                        <select class="form-select" name="price_type"
                                data-original="{{ $contract->price_type }}" onchange="trackChange(this); updateUnitSuffix()">
                            <option value="fixed" {{ old('price_type', $contract->price_type) === 'fixed' ? 'selected' : '' }}>固定金額</option>
                            <option value="hourly" {{ old('price_type', $contract->price_type) === 'hourly' ? 'selected' : '' }}>時間単価</option>
                            <option value="daily" {{ old('price_type', $contract->price_type) === 'daily' ? 'selected' : '' }}>日単価</option>
                            <option value="monthly" {{ old('price_type', $contract->price_type) === 'monthly' ? 'selected' : '' }}>月額</option>
                            <option value="per_person" {{ old('price_type', $contract->price_type) === 'per_person' ? 'selected' : '' }}>人数単価</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">単価</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control" name="unit_price" 
                                   value="{{ old('unit_price', $contract->unit_price) }}" step="1" min="0"
                                   data-original="{{ $contract->unit_price }}" onchange="trackChange(this)">
                            <span class="input-group-text" id="unit-suffix">/固定</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 契約条件・特記事項 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-contract"></i> 契約条件・特記事項
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">契約条件</label>
                        <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                  name="terms_conditions" rows="5" 
                                  data-original="{{ $contract->terms_conditions }}" onchange="trackChange(this)"
                                  placeholder="契約に関する条件を記載してください">{{ old('terms_conditions', $contract->terms_conditions) }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">特記事項</label>
                        <textarea class="form-control" name="special_notes" rows="3" 
                                  data-original="{{ $contract->special_notes }}" onchange="trackChange(this)"
                                  placeholder="特別な条件や注意事項があれば記載してください">{{ old('special_notes', $contract->special_notes) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">業務内容</label>
                        <textarea class="form-control" name="work_content" rows="4" 
                                  data-original="{{ $contract->work_content }}" onchange="trackChange(this)"
                                  placeholder="具体的な業務内容を記載">{{ old('work_content', $contract->work_content) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">履行場所</label>
                        <textarea class="form-control" name="work_location" rows="4" 
                                  data-original="{{ $contract->work_location }}" onchange="trackChange(this)"
                                  placeholder="業務を履行する場所">{{ old('work_location', $contract->work_location) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 責任者・担当者 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users"></i> 責任者・担当者
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">契約責任者</label>
                        <select class="form-select" name="manager_id"
                                data-original="{{ $contract->manager_id }}" onchange="trackChange(this)">
                            <option value="">責任者を選択</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id', $contract->manager_id) == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->department ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">営業担当者</label>
                        <select class="form-select" name="sales_person_id"
                                data-original="{{ $contract->sales_person_id }}" onchange="trackChange(this)">
                            <option value="">担当者を選択</option>
                            @foreach($salesPersons as $person)
                                <option value="{{ $person->id }}" {{ old('sales_person_id', $contract->sales_person_id) == $person->id ? 'selected' : '' }}>
                                    {{ $person->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">顧客担当者名</label>
                        <input type="text" class="form-control" name="customer_contact_name" 
                               value="{{ old('customer_contact_name', $contract->customer_contact_name) }}" 
                               data-original="{{ $contract->customer_contact_name }}" onchange="trackChange(this)"
                               placeholder="顧客側の担当者名">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">顧客担当者連絡先</label>
                        <input type="text" class="form-control" name="customer_contact_info" 
                               value="{{ old('customer_contact_info', $contract->customer_contact_info) }}" 
                               data-original="{{ $contract->customer_contact_info }}" onchange="trackChange(this)"
                               placeholder="電話番号・メールアドレス等">
                    </div>
                </div>
            </div>
        </div>

        <!-- 変更理由 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit"></i> 変更理由
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label required">変更理由</label>
                        <textarea class="form-control @error('change_reason') is-invalid @enderror" 
                                  name="change_reason" rows="3" required
                                  placeholder="契約内容を変更する理由を記載してください">{{ old('change_reason') }}</textarea>
                        @error('change_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">変更履歴として記録されます</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 追加ファイル -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-paperclip"></i> 追加ファイル
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">追加ファイル</label>
                        <input type="file" class="form-control" name="additional_files[]" multiple 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                        <small class="form-text text-muted">
                            PDF、Word、画像ファイル対応。複数ファイル選択可能。最大10MB/ファイル
                        </small>
                    </div>
                    
                    @if($contract->attachments && count($contract->attachments) > 0)
                        <div class="col-md-12">
                            <label class="form-label">既存ファイル</label>
                            <div class="row g-2">
                                @foreach($contract->attachments as $index => $attachment)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-2 border rounded">
                                            <i class="fas fa-file me-2"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $attachment['name'] }}</div>
                                                <small class="text-muted">{{ $attachment['size'] ?? '' }}</small>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="remove_files[]" value="{{ $index }}" 
                                                       id="remove-file-{{ $index }}">
                                                <label class="form-check-label text-danger" for="remove-file-{{ $index }}">
                                                    削除
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- フォームアクション -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i> リセット
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="previewChanges()">
                            <i class="fas fa-eye"></i> 変更プレビュー
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('contracts.show', $contract) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> キャンセル
                        </a>
                        @if($contract->status === 'draft')
                            <button type="submit" class="btn btn-primary" name="action" value="update">
                                <i class="fas fa-save"></i> 更新
                            </button>
                        @else
                            <button type="submit" class="btn btn-warning" name="action" value="update">
                                <i class="fas fa-save"></i> 更新（承認待ち）
                            </button>
                        @endif
                        <button type="submit" class="btn btn-success" name="action" value="update_and_approve">
                            <i class="fas fa-check"></i> 更新・承認
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 変更履歴モーダル -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">変更履歴</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="history-content">
                    <!-- 変更履歴がここに表示される -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.input-group-text {
    min-width: 50px;
}

.card-header {
    background-color: #f8f9fa;
}

.change-highlight {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 0.5rem;
    margin: 0.25rem 0;
}

.change-item {
    padding: 0.5rem;
    margin: 0.25rem 0;
    border-radius: 0.25rem;
}

.change-item.added {
    background-color: #d1e7dd;
    border-left: 4px solid #198754;
}

.change-item.removed {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
}

.change-item.modified {
    background-color: #d1ecf1;
    border-left: 4px solid #0dcaf0;
}

#amount-diff {
    font-weight: bold;
}

#amount-diff.positive {
    color: #dc3545;
}

#amount-diff.negative {
    color: #198754;
}
</style>
@endpush

@push('scripts')
<script>
let originalData = {};
let hasChanges = false;

$(document).ready(function() {
    // 元データを保存
    storeOriginalData();
    
    // 金額自動計算
    calculateTotals();
    
    // 契約期間自動計算
    calculateDuration();
    
    // 自動更新チェックボックス
    $('#auto-renewal').change(function() {
        if (this.checked) {
            $('#renewal-period, #renewal-notice').slideDown();
        } else {
            $('#renewal-period, #renewal-notice').slideUp();
        }
        trackChange(this);
    });
    
    // 顧客選択時の案件リスト更新
    $('select[name="customer_id"]').change(function() {
        updateProjectList();
    });
    
    // 単価形態変更時の表示更新
    updateUnitSuffix();
    
    // フォーム離脱警告
    $(window).on('beforeunload', function() {
        if (hasChanges) {
            return '変更内容が保存されていません。ページを離れますか？';
        }
    });
    
    // フォーム送信時は警告無効
    $('#contract-edit-form').on('submit', function() {
        $(window).off('beforeunload');
    });
});

/**
 * 元データを保存
 */
function storeOriginalData() {
    $('[data-original]').each(function() {
        const field = $(this).attr('name') || $(this).attr('id');
        if (field) {
            originalData[field] = $(this).data('original');
        }
    });
}

/**
 * 変更を追跡
 */
function trackChange(element) {
    const $element = $(element);
    const fieldName = $element.attr('name') || $element.attr('id');
    const currentValue = $element.is(':checkbox') ? ($element.is(':checked') ? '1' : '0') : $element.val();
    const originalValue = String($element.data('original') || '');
    
    if (currentValue !== originalValue) {
        $element.addClass('change-highlight');
        hasChanges = true;
    } else {
        $element.removeClass('change-highlight');
    }
    
    // 金額変更の場合は差額表示
    if (fieldName === 'base_amount') {
        updateAmountDiff();
    }
    
    // 変更があれば比較エリアを表示
    if (hasChanges) {
        $('#comparison-area').slideDown();
        updateChangesPreview();
    }
}

/**
 * 金額自動計算
 */
function calculateTotals() {
    const baseAmount = parseFloat($('#base-amount').val()) || 0;
    const taxAmount = Math.floor(baseAmount * 0.1);
    const totalAmount = baseAmount + taxAmount;
    
    $('#tax-amount').val(taxAmount);
    $('#total-amount').val(totalAmount);
    
    updateAmountDiff();
}

/**
 * 金額差額更新
 */
function updateAmountDiff() {
    const currentAmount = parseFloat($('#total-amount').val()) || 0;
    const originalAmount = parseFloat('{{ $contract->total_amount }}') || 0;
    const diff = currentAmount - originalAmount;
    
    const $diffElement = $('#amount-diff');
    if (diff > 0) {
        $diffElement.text('+¥' + new Intl.NumberFormat('ja-JP').format(diff))
                   .removeClass('negative').addClass('positive');
    } else if (diff < 0) {
        $diffElement.text('¥' + new Intl.NumberFormat('ja-JP').format(diff))
                   .removeClass('positive').addClass('negative');
    } else {
        $diffElement.text('¥0').removeClass('positive negative');
    }
}

/**
 * 契約期間自動計算
 */
function calculateDuration() {
    const startDate = new Date($('input[name="start_date"]').val());
    const endDate = new Date($('input[name="end_date"]').val());
    
    if (startDate && endDate && endDate > startDate) {
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const months = Math.floor(diffDays / 30);
        const days = diffDays % 30;
        
        let duration = '';
        if (months > 0) {
            duration += months + 'ヶ月';
        }
        if (days > 0) {
            duration += (duration ? ' ' : '') + days + '日';
        }
        
        $('#contract-duration').val(duration || diffDays + '日間');
    }
}

/**
 * 案件リスト更新
 */
function updateProjectList() {
    const customerId = $('select[name="customer_id"]').val();
    const projectSelect = $('select[name="project_id"]');
    const currentProjectId = '{{ $contract->project_id }}';
    
    projectSelect.html('<option value="">案件を選択</option>');
    
    if (customerId) {
        const selectedOption = $('select[name="customer_id"] option:selected');
        const projects = selectedOption.data('projects');
        
        if (projects) {
            Object.entries(projects).forEach(([id, name]) => {
                const selected = id == currentProjectId ? 'selected' : '';
                projectSelect.append(`<option value="${id}" ${selected}>${name}</option>`);
            });
        }
    }
}

/**
 * 単価表示更新
 */
function updateUnitSuffix() {
    const priceType = $('select[name="price_type"]').val();
    const suffixMap = {
        'fixed': '/固定',
        'hourly': '/時間',
        'daily': '/日',
        'monthly': '/月',
        'per_person': '/人'
    };
    
    $('#unit-suffix').text(suffixMap[priceType] || '/固定');
}

/**
 * 変更プレビュー更新
 */
function updateChangesPreview() {
    let changes = [];
    
    $('[data-original]').each(function() {
        const $element = $(this);
        const fieldName = $element.attr('name') || $element.attr('id');
        const currentValue = $element.is(':checkbox') ? ($element.is(':checked') ? '1' : '0') : $element.val();
        const originalValue = String($element.data('original') || '');
        
        if (currentValue !== originalValue) {
            const label = $element.closest('.col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-md-12')
                                .find('label').first().text().replace(' *', '');
            
            changes.push({
                field: label || fieldName,
                original: originalValue,
                current: currentValue
            });
        }
    });
    
    let html = '';
    if (changes.length > 0) {
        html = '<h6>変更項目:</h6>';
        changes.forEach(change => {
            html += `
                <div class="change-item modified">
                    <strong>${change.field}:</strong><br>
                    <span class="text-muted">変更前:</span> ${change.original}<br>
                    <span class="text-primary">変更後:</span> ${change.current}
                </div>
            `;
        });
    } else {
        html = '<p class="text-muted">変更はありません。</p>';
    }
    
    $('#changes-preview').html(html);
}

/**
 * 変更プレビュー表示
 */
function previewChanges() {
    updateChangesPreview();
    $('#comparison-area').slideDown();
}

/**
 * 変更履歴表示
 */
function showChangeHistory() {
    $.ajax({
        url: '{{ route("contracts.history", $contract) }}',
        method: 'GET',
        success: function(data) {
            $('#history-content').html(data);
            $('#historyModal').modal('show');
        },
        error: function() {
            alert('変更履歴の取得に失敗しました。');
        }
    });
}

/**
 * フォームリセット
 */
function resetForm() {
    if (confirm('すべての変更をリセットしますか？')) {
        location.reload();
    }
}
</script>
@endpush