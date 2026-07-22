@extends('layouts.app')
@section('title', __('Dashboard') . ' - ' . company_name())
@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        Admin Dashboard
    </div>
@endsection
@push('js_script')
    <script></script>
@endpush
