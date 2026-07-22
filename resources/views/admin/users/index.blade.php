@extends('layouts.app')
@section('title', __('Users Management') . ' - ' . company_name())

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row g-6 mb-6">
            <div class="col-md">
                <div class="card">
                    <div class="row card-header flex-column flex-md-row border-bottom mx-0 px-3">
                        <div class="d-md-flex justify-content-between align-items-center col-md-auto me-auto mt-0">
                            <h5 class="card-title mb-0 text-md-start text-center pb-md-0 pb-6">
                                {{ __('Users Management') }}</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success mt-6">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table datatable" id="user_table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Sr No.') }}</th>
                                        <th>{{ __('Profile') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Mobile No.') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created At') }}</th>
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
            var table = $('#user_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.users.data') }}",
                order: [
                    [6, 'desc']
                ],
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
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
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
                    },
                ]
            });

            // AJAX Toggle Active/Inactive
            $(document).on("change", ".toggleStatus", function() {
                let id = $(this).data("id");
                let checkbox = $(this);
                let isChecked = checkbox.prop("checked");

                Swal.fire({
                    title: @json(__('Are you sure?')),
                    text: isChecked ?
                        @json(__('Do you want to activate this user?')) : @json(__('Do you want to deactivate this user?')),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: @json(__('Yes, do it!')),
                    cancelButtonText: @json(__('Cancel'))
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/users') }}" + "/" + id + "/toggle-status",
                            type: "PATCH",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                Swal.fire({
                                    title: @json(__('Updated!')),
                                    text: res.message,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(err) {
                                // Revert the switch on failure
                                checkbox.prop("checked", !isChecked);
                                Swal.fire({
                                    title: @json(__('Error!')),
                                    text: @json(__('Something went wrong while updating status.')),
                                    icon: "error"
                                });
                            }
                        });
                    } else {
                        // Cancelled — revert switch to original position
                        checkbox.prop("checked", !isChecked);
                    }
                });
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
                            url: "{{ url('admin/users') }}" + "/" + id,
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    title: @json(__('Deleted!')),
                                    text: res.message ||
                                        @json(__('User has been deleted.')),
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
