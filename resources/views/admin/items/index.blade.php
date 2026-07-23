{{-- resources/views/admin/items/index.blade.php --}}
@extends('layouts.app')
@section('title', __('Item Management') . ' - ' . company_name())

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row g-6 mb-6">
            <div class="col-md">
                <div class="card">
                    <div class="row card-header flex-column flex-md-row border-bottom mx-0 px-3">
                        <div class="d-md-flex justify-content-between align-items-center col-md-auto me-auto mt-0">
                            <h5 class="card-title mb-0 text-md-start text-center pb-md-0 pb-6">
                                {{ __('Item Management') }}</h5>
                        </div>
                        <div class="d-md-flex justify-content-between align-items-center col-md-auto ms-auto mt-0">
                            <div class="btn-group flex-wrap mb-0">
                                <div class="ms-1">
                                    <a href="{{ route('admin.items.create') }}"
                                        class="btn btn-primary">{{ __('Add Item') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success mt-6">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table border="1" class="table datatable" id="item_table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Sr No.') }}</th>
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js_script')
    <script type="text/javascript">
        $(function() {
            var table = $('#item_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.items.data') }}",
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
                columnDefs: [
                    { width: '450px', targets: 4 },
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image_col',
                        name: 'image_col',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'category_name',
                        name: 'category.name'
                    },
                    {
                        data: 'price_col',
                        name: 'price_col',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // AJAX Delete with SweetAlert2
            $(document).on("click", ".deleteRow", function(e) {
                e.stopPropagation();
                let id = $(this).data("id");

                Swal.fire({
                    title: @json(__('Are you sure?')),
                    text: @json(__("You won't be able to revert this!")),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: @json(__('Yes, delete it!')),
                    cancelButtonText: @json(__('Cancel'))
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/items') }}" + "/" + id,
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    title: @json(__('Deleted!')),
                                    text: @json(__('Item has been deleted.')),
                                    icon: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(err) {
                                Swal.fire({
                                    title: @json(__('Error!')),
                                    text: @json(__('Something went wrong while deleting.')),
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush