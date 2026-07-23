@extends('layouts.app')

@section('title', __('Orders'))

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Orders') }}</h5>
            </div>

            <div class="card-datatable table-responsive p-3">
                <table class="table table-bordered" id="ordersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Order No') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Payment') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js_script')
    <script>
        $(function() {
            $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [6, 'desc']
                ],
                ajax: '{{ route('admin.orders.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_no',
                        name: 'order_no'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'payment',
                        name: 'payment_status'
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
@endpush