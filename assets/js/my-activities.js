/**
 * Activity Manager Form Wizard Flow
 */

$(function () {
    initActivityTables();
    bindAccordionEvents();
    bindCompleteActivityButton();
    bindOffcanvasEvents();
});

function initActivityTables() {
    $('.activity_table').each(function () {
        const $table = $(this);
        $table.DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            lengthChange: false,
            responsive: true,
            autoWidth: false,
            ajax: { url: window.activityDataUrl.replace(':type', $table.data('type')) },
            columns: [
                { data: 'activity_type' },
                { data: 'offer_no' },
                { data: 'customer_name' },
                { data: 'due_on' },
                { data: 'priority' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });
}

function bindAccordionEvents() {
    $('.accordion-collapse').on('shown.bs.collapse', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
}

/* =========================
   MAIN OPERATION CLICK HANDLER
========================= */
function bindCompleteActivityButton() {
    $(document).on('click', '.complete-activity-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);

        if (!validateActivity($btn)) return;

        fillHiddenFields($btn);

        const sourceType = $btn.data('type');
        const pipelineId = $btn.data('pipeline-id');
        const funcId = parseInt($btn.data('activity-functionality-id'), 10) || 0;

        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('completeActivityOffcanvas'));

        /**
         *  SCHEDULE TRANSITION FLOW
         */
        if ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10].includes(funcId)) {
            if (sourceType === 'activity') return Swal.fire('Warning', 'Cannot complete schedule as activity', 'warning');
            if (!parseInt($btn.data('is-auto-schedule'), 10)) return Swal.fire('Info', 'No auto schedule found', 'info');
            if (parseInt($btn.data('schedule-stop'), 10) === 1) return Swal.fire('Warning', 'Schedule automation stopped', 'warning');

            $('#callActivityFormContainer').addClass('d-none');
            $('#scheduleFormContainer').removeClass('d-none');

            offcanvas.show();

            loadPipelineForm(pipelineId);
            loadActivityDetails($btn.data('id'), sourceType);
            return;
        }

        /**
         * ACTIVITY WORKFLOW FLOW
         */
        // if ([4, 5, 6, 7].includes(funcId)) {
        //     if (sourceType === 'schedule') return Swal.fire('Warning', 'Cannot complete activity as schedule', 'warning');
        //     if (!parseInt($btn.data('is-auto-activity'), 10)) return Swal.fire('Info', 'No auto activity found', 'info');
        //     if (parseInt($btn.data('activity-stop'), 10) === 1) return Swal.fire('Warning', 'Activity automation stopped', 'warning');

        //     openCompleteActivityOffcanvas($btn);
        //     return;
        // }

        Swal.fire('Info', 'No action configured', 'info');
    });
}

// function openCompleteActivityOffcanvas($btn) {
//     $('#scheduleFormContainer').addClass('d-none');
//     $('#callActivityFormContainer').removeClass('d-none');

//     bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('completeActivityOffcanvas')).show();

//     loadActivityDetails($btn.data('id'), $btn.data('type'));
//     openCallActivityForm($btn.data('pipeline-id'));
// }

/* =========================
   VALIDATION & DOM POPULATION
========================= */
function validateActivity($btn) {
    if (!parseInt($btn.data('is-auto-created'), 10)) {
        Swal.fire('Warning', 'Not auto created', 'warning');
        return false;
    }
    if (!parseInt($btn.data('activity-functionality-id'), 10)) {
        Swal.fire('Error', 'Invalid functionality', 'error');
        return false;
    }
    return true;
}

function fillHiddenFields($btn) {
    $('#complete_activity_id').val($btn.data('id'));
    $('#complete_activity_type_source').val($btn.data('type'));
    $('#complete_activity_pipeline_id').val($btn.data('pipeline-id'));
    $('#complete_activity_stop_automation_schedule').val($btn.data('is-stop-automation-schedule'));
    $('#complete_activity_doc_status_id').val($btn.data('doc-status-id'));
    $('#complete_activity_activity_functionality_id').val($btn.data('activity-functionality-id'));
}

/* =========================
   ASYNCHRONOUS DETAILS HANDLER
========================= */
function loadActivityDetails(id, type) {
    $.ajax({
        url: window.activityDetailUrl.replace('__ID__', id).replace('__TYPE__', type),
        type: 'GET',
        success: function (res) {
            if (res.success) populateActivityHeader(res.data);
        },
        error: () => Swal.fire('Error', 'Failed to load activity details', 'error')
    });
}

function populateActivityHeader(d) {
    $('#activity_type_label').text(d.type_label || '');
    $('#activity_offer_no').text(d.offer_no || '-');
    $('#activity_customer_name').text(d.customer_name ? `| ${d.customer_name}` : '| -');
    $('#activity_due_date').text(`Due on: ${d.date_full || '-'}`);
    $('#activity_date_label').text(d.date_label || '');

    $('#activity_icon').removeClass().addClass(`ti ${d.icon} fs-5`);
    $('#activity_icon_wrapper').removeClass().addClass(`rounded-circle d-flex align-items-center justify-content-center bg-${d.color}-subtle text-${d.color}`);
}

function bindOffcanvasEvents() {
    $('#completeActivityOffcanvas').on('hidden.bs.offcanvas', function () {
        $('#scheduleFormContainer, #callActivityFormContainer').addClass('d-none');
    });
}