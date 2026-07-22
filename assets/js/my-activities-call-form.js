/**
 * Call Activity Form (Optimized & Cleaned)
 */

let callActivityPayload = {};
let callDatePickerInstance = null;
let callTimePickerInstance = null;

/* =========================
   PICKERS (DATE & TIME)
========================= */
function initCallDatePicker(selector, parent = null) {
    const el = document.querySelector(selector);
    if (!el) return console.warn("[DATEPICKER] Not found:", selector), null;

    return flatpickr(el, {
        altInput: true,
        altFormat: "d-m-Y",
        dateFormat: "Y-m-d",
        appendTo: parent ? parent[0] : undefined,
        static: true,
        minDate: "today",
        locale: { firstDayOfWeek: 1 }
    });
}

function initCallTimePicker(selector, parent = null) {
    const $el = $(selector);
    if (!$el.length) return console.warn("[TIMEPICKER] Not found:", selector), null;

    if (typeof $el.inputmask === 'function') {
        $el.inputmask("99:99", { placeholder: "HH:MM" });
    }

    const options = { timeFormat: 'HH:mm', interval: 30, minTime: '00:00', maxTime: '23:59', dropdown: true, scrollbar: true };
    if (parent) options.appendTo = parent;

    return $el.timepicker(options);
}

function destroyDatePicker(instance) {
    if (instance && typeof instance.destroy === 'function') instance.destroy();
}

/* =========================
   FORM ACTIONS & RENDERING
========================= */
function openCallActivityForm(pipelineId) {
    const url = window.callActivityWizardDataUrl.replace('__ID__', pipelineId);

    $.ajax({
        url,
        type: 'GET',
        success: function (res) {
            callActivityPayload = res;
            $('#feCallPipelineId').val(res.pipeline_id);
            renderCallActivityForm(res);
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('completeActivityOffcanvas')).show();
        },
        error: function (xhr) {
            console.error("[OPEN FORM] FAILED:", xhr);
            Swal.fire({ icon: 'error', title: window.translations.failed_to_load_data });
        }
    });
}

function renderCallActivityForm(data) {
    renderCallResults(data?.call_result_step || {});
    renderNextStatuses(data?.next_status_step || {});
    resetCallActivityForm();
}

/* =========================
   UI CARDS GENERATORS
========================= */
function renderCallResults(step) {
    const options = step?.options || [];
    if (!options.length) return $('#feCallResultContainer').html('<small class="text-muted">' + window.translations.no_call_results_found + '</small>');

    const html = options.map(opt => `
        <div class="fe-call-result-card" data-id="${opt.id}" data-label="${opt.label}">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="wizard-option-icon bg-${opt.color || 'primary'}-subtle text-${opt.color || 'primary'} me-3">
                        <i class="ti tabler-${opt.icon || 'circle'}"></i>
                    </div>
                    <div class="fw-bold">${opt.label}</div>
                </div>
                <i class="ti tabler-chevron-right"></i>
            </div>
        </div>
    `).join('');
    $('#feCallResultContainer').html(html);
}

function renderNextStatuses(step) {
    const statuses = step?.statuses || step?.next_statuses || step?.data || [];
    if (!statuses.length) return $('#feCallNextStatusContainer').html('<small class="text-muted">' + window.translations.no_next_status_options_found + '</small>');

    const html = statuses.map(status => {
        const config = encodeURIComponent(JSON.stringify(status));
        return `
            <div class="fe-next-status-card" data-id="${status.id}" data-label="${status.label}" data-config="${config}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="wizard-option-icon bg-${status.color || 'primary'}-subtle text-${status.color || 'primary'} me-3">
                            <i class="ti tabler-${status.icon || 'circle'}"></i>
                        </div>
                        <div class="fw-bold">${status.label}</div>
                    </div>
                    <i class="ti tabler-chevron-right"></i>
                </div>
            </div>
        `;
    }).join('');
    $('#feCallNextStatusContainer').html(html);
}

/* =========================
   SELECTIONS & INTERACTIONS
========================= */
$(document).on('click', '.fe-call-result-card', function () {
    $('.fe-call-result-card').removeClass('selected');
    $(this).addClass('selected');
    $('#feCallSelectedResult').val($(this).data('id'));
    $('#feCallSelectedResultLabel').text($(this).data('label'));
});

$(document).on('click', '.fe-next-status-card', function () {
    $('.fe-next-status-card').removeClass('selected');
    $(this).addClass('selected');
    try {
        const config = JSON.parse(decodeURIComponent($(this).attr('data-config')));
        $('#feCallSelectedNextStatus').val(config.id);
        renderCallDynamicFields(config);
    } catch (e) {
        console.error("[CONFIG PARSE ERROR]", e);
    }
});

/* =========================
   DYNAMIC FIELDS LOGIC
========================= */
function renderCallDynamicFields(config) {
    const activity = config?.activity || {};
    const dueDays = config?.due_days || 0;

    // Date Calculation
    const date = new Date();
    date.setDate(date.getDate() + dueDays);
    const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

    let html = '';

    // Call Type Field
    if (!activity.hide_activity_type) {
        const options = (callActivityPayload.call_types || []).map(type => 
            `<option value="${type.slug}" ${type.slug === activity.call_type_slug ? 'selected' : ''}>${type.name}</option>`
        ).join('');
        
        html += `
            <div class="mb-3">
                <label class="form-label fw-semibold">${window.translations.call_type}</label>
                <select id="feCallTypeSlug" class="form-select">${options}</select>
            </div>`;
    }

    // Special Case: Check Contact
    if (activity.check_contact === 'check_contact') {
        return $('#feDynamicFields').html(`
            <div class="text-center py-5">
                <div class="mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:90px; height:90px;">
                        <i class="ti tabler-alert-triangle text-warning" style="font-size:50px;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">${window.translations.please_check_contact_number}</h4>
                <p class="text-muted mb-3">${window.translations.the_contact_number_is_missing_or_invalid}</p>
                <div class="alert alert-warning fw-bold mb-0">
                    <i class="ti tabler-info-circle me-2"></i>${window.translations.please_verify_and_update_contact_details_before_proceeding}
                </div>
            </div>`);
    }

    // Special Case: Deal Won
    if (activity.mark_deal_as_won == 1) {
        return $('#feDynamicFields').html(`
            <div class="text-center py-4">
                <div class="mb-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:90px; height:90px;">
                        <i class="ti tabler-trophy text-success" style="font-size:50px;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-2">${window.translations.mark_deal_as_won}</h4>
                <p class="text-muted mb-0">${window.translations.mark_the_pipeline_as_won}</p>
            </div>`);
    }

    const isDropdownType = activity.field_type === 'dropdown';

    // Date & Time Fields
    if (!isDropdownType) {
        html += `
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-semibold">${window.translations.due_date}</label>
                    <input type="text" id="feCallDueDate" class="form-control flatpicker-date" value="${formattedDate}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-semibold">${window.translations.due_time}</label>
                    <input type="text" id="feCallDueTime" class="form-control flatpicker-time" value="09:00">
                </div>
            </div>`;
    }

    // Email Field
    if (activity.type === 'email') {
        html += `
            <div class="mb-4">
                <label class="form-label fw-semibold">${window.translations.notes}</label>
                <textarea class="form-control" id="activityEmailNotes" rows="5" placeholder="${activity.email_description || window.translations.write_email_notes}"></textarea>
            </div>`;
    }

    // Dropdown Field (Lost Reason)
    if (isDropdownType) {
        const options = (activity.reason_dropdown || []).map(item => 
            `<option value="${item.id}">${item.name}</option>`
        ).join('');

        html += `
            <div class="mb-4">
                <label class="form-label fw-semibold">${window.translations.lost_reason}</label>
                <select id="activityLostReason" class="form-select">${options}</select>
            </div>`;
    }

    // Inject generated HTML
    $('#feDynamicFields').html(html);
    const offcanvasElement = document.getElementById('completeActivityOffcanvas');

    // Destroy Old Pickers
    if (callDatePickerInstance) { callDatePickerInstance.destroy(); callDatePickerInstance = null; }
    if (callTimePickerInstance?.destroy) { callTimePickerInstance.destroy(); callTimePickerInstance = null; }

    // Init New Pickers
    if (!isDropdownType) {
        if ($('#feCallDueDate').length) {
            callDatePickerInstance = initCallDatePicker('#feCallDueDate', offcanvasElement);
            callDatePickerInstance.setDate(formattedDate, true);
        }
        if ($('#feCallDueTime').length) {
            callTimePickerInstance = initCallTimePicker('#feCallDueTime', offcanvasElement);
        }
    }
}

/* =========================
   RESET & SUBMIT CLEANUP
========================= */
function resetCallActivityForm() {

    const form = $('#feCallActivityForm')[0];

    if (form) {
        form.reset();
    }

    $('#feDynamicFields').empty();

    $('.fe-call-result-card').removeClass('selected');
    $('.fe-next-status-card').removeClass('selected');

    $('#feCallSelectedResult').val('');
    $('#feCallSelectedNextStatus').val('');
    $('#feCallSelectedResultLabel').text('-');
}

function validateCallActivityForm() {

    if (!$('#feCallSelectedResult').val()) {
        return window.translations.please_select_a_call_result;
    }

    if (!$('#feCallSelectedNextStatus').val()) {
        return window.translations.please_select_the_next_status;
    }

    if ($('#feCallTypeSlug').length && !$('#feCallTypeSlug').val()) {
        return window.translations.please_select_a_call_type;
    }

    if ($('#feCallDueDate').length && !$('#feCallDueDate').val()) {
        return window.translations.please_select_a_due_date;
    }

    if ($('#feCallDueTime').length && !$('#feCallDueTime').val()) {
        return window.translations.please_select_a_due_time;
    }

    if ($('#activityLostReason').length && !$('#activityLostReason').val()) {
        return window.translations.please_select_a_reason;
    }

    return null;
}


function showValidationAlert(message) {
    Swal.fire({
        icon: 'warning',
        title: window.translations.missing_information,
        text: message,
        confirmButtonText: window.translations.ok,
        confirmButtonColor: '#6f42c1'
    });
}


$('#feCallActivityForm').on('submit', function (e) {
    e.preventDefault();
        const validationError = validateCallActivityForm();

    if (validationError) {
        showValidationAlert(validationError);
        return;
    }

    const selectedConfig = JSON.parse($('.next-status-option.active').attr('data-config') || '{}');

    const payload = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        pipeline_id: $('#feCallPipelineId').val(),
        call_result: $('#feCallSelectedResult').val(),
        next_status: $('#feCallSelectedNextStatus').val(),
        call_type_slug: $('#feCallTypeSlug').val(),
        due_date: $('#feCallDueDate').val(),
        due_time: $('#feCallDueTime').val(),
        comments: $('#feCallComments').val(),
        lost_reason: $('#activityLostReason').val(),
        mark_deal_as_won: selectedConfig.activity?.mark_deal_as_won || 0
    };

    $.ajax({
        url: window.storeCallActivityUrl,
        type: 'POST',
        data: payload,
        beforeSend: () => $('#feBtnSubmitCallForm').prop('disabled', true).html(window.translations.saving),
        success: function (res) {
            Swal.fire({
                icon: 'success',
                title: window.translations.saved_successfully,
                text: window.translations.call_activity_planned_successfully,
                timer: 2000,
                showConfirmButton: false
            });

            const offcanvasElement = document.getElementById('completeActivityOffcanvas');
            if (offcanvasElement) {
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvas) {
                    offcanvas.hide();
                }
            }

            setTimeout(function () {
                window.location.reload();
            }, 2500);
        },
        error: function (xhr) {
            Swal.fire({ icon: 'error', title: window.translations.failed, text: xhr.responseJSON?.message || window.translations.something_went_wrong });
        },
        complete: () => $('#feBtnSubmitCallForm').prop('disabled', false).html(window.translations.save_activity)
    });
});

$('#completeActivityOffcanvas').on('hidden.bs.offcanvas', function () {
    resetCallActivityForm();
    if (callDatePickerInstance) { destroyDatePicker(callDatePickerInstance); callDatePickerInstance = null; }
    if (callTimePickerInstance?.destroy) { callTimePickerInstance.destroy(); callTimePickerInstance = null; }
});