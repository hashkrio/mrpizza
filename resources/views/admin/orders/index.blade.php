@extends('layouts.app')

@section('title', __('Orders'))

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row g-6 mb-6">
            <div class="col-md">
                <div class="card">
                    <div class="row card-header flex-column flex-md-row border-bottom mx-0 px-3">
                        <div class="d-md-flex justify-content-between align-items-center col-md-auto me-auto mt-0">
                            <h5 class="card-title mb-0 text-md-start text-center pb-md-0 pb-6"> {{ __('Orders') }}</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success mt-6">{{ session('success') }}</div>
                        @endif
                        <div class="table-responsive">
                            <table border="1" class="table datatable" id="ordersTable">
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
                responsive: true,
                order: [
                    [6, 'desc']
                ],
                ajax: '{{ route('admin.orders.data') }}',
                language: {
                    search: "{{ __('Search') }}:",
                    searchPlaceholder: "{{ __('Type to search') }}",
                    lengthMenu: "{{ __('Show _MENU_ entries per page') }}",
                    info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
                    infoEmpty: "{{ __('No records found') }}",
                    infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
                    emptyTable: "{{ __('No matching records found') }}",
                    zeroRecords: "{{ __('No matching records found') }}",
                },

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
