@extends('layouts.app')
@section('title', __('Translations Management') . ' - ' . company_name())
@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">{{ __('Manage Translations') }}</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                <i class="icon-base ti tabler-plus me-1"></i> {{ __('Add Key') }}
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                {{-- Search --}}
                <form method="GET" action="{{ route('admin.translations.index') }}" class="mb-3">
                    <div class="input-group" style="max-width:420px;">
                        <input type="text" name="q" value="{{ $search }}" class="form-control"
                            placeholder="{{ __('Search key or value...') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="icon-base ti tabler-search"></i>
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('admin.translations.index') }}" class="btn btn-outline-secondary">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width:220px;">{{ __('Key') }}</th>
                                @foreach ($locales as $loc)
                                    <th>{{ \App\Support\LangHelper::localeName($loc) }}</th>
                                @endforeach
                                <th style="width:120px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $key => $values)
                                <tr data-key="{{ $key }}">
                                    <td>
                                        <code class="key-label">{{ $key }}</code>
                                    </td>
                                    @foreach ($locales as $loc)
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm trans-input"
                                                data-locale="{{ $loc }}"
                                                value="{{ $values[$loc] ?? '' }}">
                                        </td>
                                    @endforeach
                                    <td class="text-nowrap">
                                        <button type="button"
                                            class="btn btn-sm btn-text-secondary rounded-pill saveRow"
                                            title="{{ __('Save') }}">
                                            <i class="icon-base ti tabler-device-floppy"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm btn-text-secondary rounded-pill deleteRow"
                                            title="{{ __('Delete') }}">
                                            <i class="icon-base ti tabler-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($locales) + 2 }}" class="text-center text-muted">
                                        {{ __('No translations found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Key Modal --}}
    <div class="modal fade" id="addKeyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.translations.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Translation Key') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Key') }} <span class="text-danger">*</span></label>
                        <input type="text" name="key" class="form-control @error('key') is-invalid @enderror"
                            value="{{ old('key') }}" placeholder="{{ __('e.g. Save Changes') }}">
                        @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">{{ __('For JSON translations the key is usually the English text itself.') }}</small>
                    </div>

                    @foreach ($locales as $loc)
                        <div class="mb-3">
                            <label class="form-label">{{ \App\Support\LangHelper::localeName($loc) }}</label>
                            <input type="text" name="values[{{ $loc }}]" class="form-control"
                                value="{{ old('values.'.$loc) }}"
                                placeholder="{{ __('Value for') }} {{ \App\Support\LangHelper::localeName($loc) }}">
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js_script')
<script>
    const ROUTES = {
        update: "{{ route('admin.translations.update') }}",
        destroy: "{{ route('admin.translations.destroy') }}",
    };
    const CSRF = "{{ csrf_token() }}";

    $(function () {
        // Save a row (inline update)
        $('.saveRow').on('click', function () {
            const $row = $(this).closest('tr');
            const key = $row.data('key');

            const values = {};
            $row.find('.trans-input').each(function () {
                values[$(this).data('locale')] = $(this).val();
            });

            $.ajax({
                url: ROUTES.update,
                method: 'PUT',
                data: { _token: CSRF, key: key, values: values },
                success: function (res) {
                    showToast(res.message || 'Saved', 'success');
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Error saving', 'danger');
                }
            });
        });

        // Delete a row
        $('.deleteRow').on('click', function () {
            const $row = $(this).closest('tr');
            const key = $row.data('key');

            if (!confirm("{{ __('Delete this translation key from all languages?') }}")) return;

            $.ajax({
                url: ROUTES.destroy,
                method: 'DELETE',
                data: { _token: CSRF, key: key },
                success: function (res) {
                    $row.fadeOut(200, () => $row.remove());
                    showToast(res.message || 'Deleted', 'success');
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Error deleting', 'danger');
                }
            });
        });

        function showToast(message, type) {
            // Minimal fallback — swap for your project's toast/notify system
            const el = $('<div class="alert alert-' + type + ' position-fixed top-0 end-0 m-3" style="z-index:2000;">' + message + '</div>');
            $('body').append(el);
            setTimeout(() => el.fadeOut(300, () => el.remove()), 2500);
        }
    });
</script>
@endpush