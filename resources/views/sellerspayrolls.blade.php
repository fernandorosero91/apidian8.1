@extends('layouts.app', ['is_seller' => true])
@section('content')
<div class="fade-in">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-money-check-alt mr-2"></i> Nóminas y Ajustes - {{ $company_idnumber }}</span>
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $documents->total() ?? $documents->count() }}</span>
        </div>
        <div class="card-body p-0">
            @include('partials.payrolls.table')
        </div>
    </div>
</div>
@endsection
