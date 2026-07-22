let vfPayload = {};
let vfSelectedScheduleStatus = null;
let vfSelectedNextPipelineStatus = null;
let vfDatePickerInstance = null;
let vfTimePickerInstance = null;

function vfInitDatePicker(selector, parent = null) {
    return flatpickr(selector, {
        altInput: true,
        altFormat: "d-m-Y",
        dateFormat: "Y-m-d",
        appendTo: parent,
        static: true,
        minDate: "today",
        locale: { firstDayOfWeek: 1 }
    });
}

function vfInitTimePicker(selector, parent = null) {
    const $element = $(selector);
    if ($element.length && typeof $element.inputmask === 'function') {
        $element.inputmask("99:99", { placeholder: "HH:MM" });
    }

    const options = {
        timeFormat: 'HH:mm',
        interval: 30,
        minTime: '00:00',
        maxTime: '23:59',
        dropdown: true,
        scrollbar: true
    };

    if (parent) options.appendTo = parent;
    return $element.timepicker(options);
}

function loadPipelineForm(pipelineId) {
    if (!pipelineId) {
        return;
    }

    $('#vfPipelineId').val(pipelineId);

    if (!window.wizardDataUrl) {
        return;
    }

    let ajaxUrl = window.wizardDataUrl.replace('__ID__', pipelineId);

    $.ajax({
        url: ajaxUrl,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            vfPayload = res || {};
            $('.vf-form-variant').addClass('d-none');
            $('#vfTargetStatus').val('');
            $('#vfFieldType').val('');
            vfSelectedScheduleStatus = null;
            vfSelectedNextPipelineStatus = null;
            $('#vfNextPipelineStatusContainer').closest('.vf-form-section').find('.vf-section-title').addClass('d-none');
            $('#vfStep4SelectedStatusReview').closest('.vf-form-section').find('.vf-section-title').addClass('d-none');
            $('#vfStep2SelectedStatusReview').html('');
            $('#vfStep4SelectedStatusReview').html('');
            $('#vfNextPipelineStatusContainer').html('');
            
            // Forced priority key alignment
            const optionsList = res.schedule_status_options || [];
            const skippedSteps = res.skip_steps || [];

            if (skippedSteps.includes('notes')) {
                $('#vfWizardComments').closest('.vf-field-group').addClass('d-none');
                if (skippedSteps.includes('schedule_status')) {
                    $('#vfWizardComments').closest('.vf-form-section').find('.vf-section-title').addClass('d-none');
                }
            } else {
                $('#vfWizardComments').closest('.vf-field-group').removeClass('d-none');
                $('#vfWizardComments').closest('.vf-form-section').find('.vf-section-title').removeClass('d-none');
            }

            if (skippedSteps.includes('schedule_status') && optionsList.length > 0) {
                $('#vfStep1OptionsContainer').closest('.vf-form-section').addClass('d-none');
                $('#vfStep2SelectedStatusReview').addClass('d-none');

                if (!skippedSteps.includes('notes')) {
                    $('#vfWizardComments').closest('.vf-form-section').find('.vf-section-title').addClass('d-none');
                }

                const firstOption = optionsList[0];
                vfSelectedScheduleStatus = { slug: firstOption.slug, label: firstOption.label };
                vfRenderSelectedStatus(vfSelectedScheduleStatus);

                let mappedRules = res.schedule_status_rules?.[firstOption.slug] || [];
                
                vfRenderNextPipelineStatuses(mappedRules);
            } else {
                $('#vfStep1OptionsContainer').closest('.vf-form-section').removeClass('d-none');
                $('#vfStep2SelectedStatusReview').removeClass('d-none');
                
                if (!skippedSteps.includes('notes')) {
                    $('#vfWizardComments').closest('.vf-form-section').find('.vf-section-title').removeClass('d-none');
                }
                vfRenderStep1Options(optionsList);
            }

            vfFillDropdowns(res);

            const form = $('#vfScheduleForm');
            if (vfDatePickerInstance) {
                vfDatePickerInstance.destroy();
            }

            vfDatePickerInstance = vfInitDatePicker('#vfScheduleDueDate', form[0]);

            if (vfTimePickerInstance) {
                try {
                    vfTimePickerInstance.timepicker('destroy');
                } catch (e) {}
            }

            vfTimePickerInstance = vfInitTimePicker('#vfScheduleDueTime', form[0]);
            $('#vfScheduleDueTime').val('09:00');
        },
        error: function (xhr) {
            console.error('Failed loading form configuration', xhr);
            Swal.fire({
                icon: 'error',
                title: window.translations.failed || 'Failed',
                text: window.translations.unable_to_load_config || 'Unable to load workflow configuration'
            });
        }
    });
}

function vfRenderStep1Options(options) {
    const container = $('#vfStep1OptionsContainer');

    if (!options || !options.length) {
        container.html(`
            <div class="alert alert-warning">
                ${window.translations.no_result_options || 'No result options available.'}
            </div>
        `);
        return;
    }

    let html = '';
    options.forEach(option => {
        const cardColor = option.color || 'primary';
        const cardIcon = option.icon || 'point-filled';

        html += `
        <div class="vf-status-card cp p-3 rounded mb-2"
             data-slug="${option.slug}"
             data-label="${option.label}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-${cardColor} bg-opacity-10 text-${cardColor} rounded-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px; flex-shrink:0;">
                        <i class="ti tabler-${cardIcon} fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark lh-sm">${option.label}</div>
                    </div>
                </div>
                <i class="ti tabler-chevron-down text-secondary fs-5"></i>
            </div>
        </div>
        `;
    });

    container.html(html);
}

function vfRenderSelectedStatus(status) {
    $('#vfStep2SelectedStatusReview').html(`
        <div class="alert alert-success border-2 border-success py-2 px-3 mb-3 small d-flex align-items-center gap-2">
            <i class="ti tabler-circle-check text-success fs-5"></i>
            <span><strong class="text-dark">${status.label}</strong></span>
        </div>
    `);
}

function vfRenderNextPipelineStatuses(options) {
    const container = $('#vfNextPipelineStatusContainer');
    const $sectionTitle = container.closest('.vf-form-section').find('.vf-section-title');

    if (!options || !options.length) {
        $sectionTitle.addClass('d-none');
        container.html(`
            <div class="alert alert-warning">
                ${window.translations.no_next_actions || 'No next actions configured.'}
            </div>
        `);
        return;
    }

    $sectionTitle.removeClass('d-none');

    let html = '';
    options.forEach(item => {
        const cardColor = item.color || 'primary';
        const cardIcon = item.icon || 'git-commit';

        html += `
        <div class="vf-next-status-card cp p-3 rounded mb-2"
             data-status="${item.status}"
             data-label="${item.label}"
             data-form-rule='${JSON.stringify(item.form_rule || {})}'>
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-${cardColor} bg-opacity-10 text-${cardColor} rounded-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px; flex-shrink:0;">
                        <i class="ti tabler-${cardIcon} fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark lh-sm mb-1">${item.label}</div>
                        ${item.description ? `<small class="text-muted d-block lh-sm">${item.description}</small>` : ''}
                    </div>
                </div>
                <i class="ti tabler-chevron-down text-secondary fs-5"></i>
            </div>
        </div>
        `;
    });

    container.html(html);
}

function vfFillDropdowns(res) {
    let html = '';
    if (res.schedule_types && res.schedule_types.length) {
        res.schedule_types.forEach(item => {
            html += `
            <option value="${item.slug}">
                ${item.name}
            </option>
            `;
        });
    } else {
        html = `
        <option value="">
            ${window.translations.no_schedule_type || 'No schedule type available'}
        </option>
        `;
    }
    $('#vfScheduleTypeSlug').html(html);
}

function vfSetDefaultDate($input, picker, days) {
    if (days === undefined || days === null) {
        return;
    }
    let date = new Date();
    date.setDate(date.getDate() + parseInt(days));

    let yyyy = date.getFullYear();
    let mm = String(date.getMonth() + 1).padStart(2, '0');
    let dd = String(date.getDate()).padStart(2, '0');

    let formatted = `${yyyy}-${mm}-${dd}`;
    $input.val(formatted);
    if (picker) {
        picker.setDate(formatted, true);
    }
}

function vfApplyFormRules(formRule = {}) {
    $('.vf-form-variant').addClass('d-none');
    $('#vfFieldType').val(formRule.field_type || '');

    const $typeWrapper = $('#vfScheduleTypeSlug').closest('.vf-field-group');
    const $dateWrapper = $('#vfScheduleDateContainer');
    const $timeWrapper = $('#vfScheduleTimeContainer');
    const $step4Title = $('#vfStep4SelectedStatusReview').closest('.vf-form-section').find('.vf-section-title');
    
    $typeWrapper.removeClass('d-none');
    $dateWrapper.removeClass('d-none');
    $timeWrapper.removeClass('d-none');

    if (formRule.field_type) {
        $step4Title.removeClass('d-none');
    } else {
        $step4Title.addClass('d-none');
    }

    if (formRule.field_type === 'activity_form') {
        $('#vfVariantActivityForm').removeClass('d-none');

        if (formRule.hide_schedule_type === true || String(formRule.hide_schedule_type).toLowerCase() === 'true') {
            $typeWrapper.addClass('d-none');
        }
        if (formRule.hide_time === true || String(formRule.hide_time).toLowerCase() === 'true') {
            $timeWrapper.addClass('d-none');
        }
        if (formRule.hide_due_date === true || String(formRule.hide_due_date).toLowerCase() === 'true') {
            $dateWrapper.addClass('d-none');
        }
        if (formRule.schedule_type_slug) {
            $('#vfScheduleTypeSlug').val(formRule.schedule_type_slug);
        }

        vfSetDefaultDate($('#vfScheduleDueDate'), vfDatePickerInstance, formRule.default_due_days);
        $('#vfScheduleDueTime').val('09:00');
    } 
    else if (formRule.field_type === 'dropdown_form') {
        $('#vfVariantDropdownForm').removeClass('d-none');
        let html = `<option value=""> ${window.translations.select_reason || 'Select Reason'} </option>`;

        (formRule.reason_dropdown || []).forEach(reason => {
            html += `
            <option value="${reason.id}">
                ${reason.name}
            </option>
            `;
        });

        $('#vfReasonDropdownId').html(html);
    } 
    else if (formRule.field_type === 'calculation_form') {
        $('#vfCalculationActionButtonContainer').removeClass('d-none');
        if (formRule.button_label) {
            $('#vfCalculationActionButton').html(
                `<i class="ti tabler-calculator me-2"></i> ${formRule.button_label}`
            );
        }

        vfSetDefaultDate($('#vfScheduleDueDate'), vfDatePickerInstance, formRule.default_due_days);
    } 
    else if (formRule.field_type === 'status_change_form') {
        $('.vf-form-variant').addClass('d-none');
    }
}

$(document).on('click', '.vf-status-card', function () {
    $('.vf-status-card').removeClass('selected border-primary bg-light');
    $(this).addClass('selected border-primary bg-light');
    
    let slug = $(this).data('slug');

    vfSelectedScheduleStatus = {
        slug: slug,
        label: $(this).data('label')
    };

    vfRenderSelectedStatus(vfSelectedScheduleStatus);
    
    $('#vfStep4SelectedStatusReview').closest('.vf-form-section').find('.vf-section-title').addClass('d-none');
    $('#vfStep4SelectedStatusReview').html('');
    $('.vf-form-variant').addClass('d-none');
    $('#vfTargetStatus').val('');
    $('#vfFieldType').val('');

    let nextStatuses = vfPayload.schedule_status_rules?.[slug] || [];
    vfRenderNextPipelineStatuses(nextStatuses);
});

$(document).on('click', '.vf-next-status-card', function () {
    $('.vf-next-status-card').removeClass('selected border-primary bg-light');
    $(this).addClass('selected border-primary bg-light');
    
    let rule = $(this).data('form-rule');

    vfSelectedNextPipelineStatus = {
        id: $(this).data('status'),
        label: $(this).data('label'),
        form_rule: rule
    };
    
    $('#vfTargetStatus').val(vfSelectedNextPipelineStatus.id);
    
    $('#vfStep4SelectedStatusReview').html(`
        <div class="alert alert-success border-2 border-success py-2 px-3 mb-3 d-flex align-items-center gap-2 small">
            <i class="ti tabler-arrows-up-down fs-5"></i>
            <span><strong>${vfSelectedNextPipelineStatus.label}</strong></span>
        </div>
    `);
    
    vfApplyFormRules(rule);
});

function vfValidate() {
    let fieldType = $('#vfFieldType').val();
    
    const skippedSteps = vfPayload.skip_steps || [];
    if (!vfSelectedScheduleStatus && !skippedSteps.includes('schedule_status')) { 
        return window.translations.val_select_result || 'Please select what the result is.'; 
    }
    
    if (!vfSelectedNextPipelineStatus) { return window.translations.val_select_route || 'Please select the next step route.'; }
    if (!fieldType) { return window.translations.val_select_rule || 'Please select next step rule configuration.'; }
    
    if (fieldType === 'activity_form') {
        if ($('#vfScheduleTypeSlug').closest('.vf-field-group').is(':visible') && !$('#vfScheduleTypeSlug').val()) { return window.translations.val_select_type || 'Please select activity type'; }
        if ($('#vfScheduleDateContainer').is(':visible') && !$('#vfScheduleDueDate').val()) { return window.translations.val_select_date || 'Please select due date'; }
        if ($('#vfScheduleTimeContainer').is(':visible') && !$('#vfScheduleDueTime').val()) { return window.translations.val_select_time || 'Please select due time'; }
    }

    if (fieldType === 'dropdown_form') {
        if (!$('#vfReasonDropdownId').val()) { return window.translations.val_select_reason || 'Please select reason'; }
    }
    return null;
}

$(document).on('click', '#vfCalculationActionButton', function () {
    const pipelineId = $('#vfPipelineId').val();
    if (!pipelineId || !window.storeScheduleUrl) return;

    const ruleObj = vfSelectedNextPipelineStatus?.form_rule || {};
    const targetStatusSlug = ruleObj.schedule_status_slug_to_set || ruleObj.schedule_status_slug || null;

    const payload = {
        _token: $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'),
        pipeline_id: pipelineId,
        status: vfSelectedNextPipelineStatus?.id,
        field_type: 'calculation_form',
        schedule_type_slug: 'calculation',
        due_date: $('#vfScheduleDueDate').val(),
        comments: $('#vfWizardComments').val(),
        schedule_status_slug: vfSelectedScheduleStatus?.slug,
        schedule_status_slug_to_set: targetStatusSlug,
        is_reschedule: ruleObj.is_reschedule ? 1 : 0,
    };

    $.ajax({
        url: window.storeScheduleUrl,
        type: 'POST',
        data: payload,
        beforeSend: function () {
            $('#vfCalculationActionButton')
                .prop('disabled', true)
                .html(`<span class="spinner-border spinner-border-sm me-2"></span> ${window.translations.system_syncing || 'System Syncing...'}`);
        },
        success: function (res) {
            if (window.quotationCreateRoute) {
                window.location.href = window.quotationCreateRoute.replace('__ID__', pipelineId);
            }
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: window.translations.calculation_error || 'Calculation Error',
                text: xhr.responseJSON?.message || window.translations.could_not_map_calc || 'Could not map calculations parameters.'
            });
        },
        complete: function () {
            $('#vfCalculationActionButton')
                .prop('disabled', false)
                .html(`<i class="ti tabler-calculator me-2"></i> ${window.translations.calculation_btn || 'Calculation'}`);
        }
    });
});

$(document).on('submit', '#vfScheduleForm', function (e) {
    e.preventDefault();
    let error = vfValidate();
    if (error) {
        Swal.fire({
            icon: 'warning',
            title: window.translations.action_required || 'Action Required',
            text: error,
            confirmButtonColor: '#6f42c1'
        });
        return;
    }

    let fieldType = $('#vfFieldType').val();
    const ruleObj = vfSelectedNextPipelineStatus?.form_rule || {};
    const targetStatusSlug = ruleObj.schedule_status_slug_to_set || ruleObj.schedule_status_slug || null;

    let payload = {
        _token: $('input[name="_token"]').val(),
        pipeline_id: $('#vfPipelineId').val(),
        status: $('#vfTargetStatus').val(),
        field_type: fieldType,
        comments: $('#vfWizardComments').val(),
        schedule_status_slug: vfSelectedScheduleStatus?.slug || null,
        schedule_status_slug_to_set: targetStatusSlug,
        is_reschedule: ruleObj.is_reschedule ? 1 : 0
    };

    if (fieldType === 'activity_form') {
        payload.schedule_type_slug = $('#vfScheduleTypeSlug').val();
        payload.due_date = $('#vfScheduleDueDate').val();
        payload.due_time = $('#vfScheduleDueTime').val();
    } 
    else if (fieldType === 'dropdown_form') {
        payload.lost_reason = $('#vfReasonDropdownId').val();
        payload.lost_reason_label = $('#vfReasonDropdownId option:selected').text();
    } 
    else if (fieldType === 'calculation_form') {
        payload.schedule_type_slug = 'calculation';
        payload.due_date = $('#vfScheduleDueDate').val();
        payload.due_time = '';
    } 
    else if (fieldType === 'status_change_form') {
        payload.status_change_only = 1;
    }

    $.ajax({
        url: window.storeScheduleUrl,
        type: 'POST',
        data: payload,
        beforeSend: function () {
            $('.vf-submit-button')
                .prop('disabled', true)
                .html(`<span class="spinner-border spinner-border-sm me-2"></span> ${window.translations.saving || 'Saving...'}`);
        },
        success: function (res) {
            let successTitle = window.translations.saved_successfully || 'Saved Successfully';
            let successSubtitle = '';

            if (fieldType === 'activity_form') {
                const activityType = res.schedule_type || 'Activity';
                const finalDate = res.schedule_date || payload.due_date || 'N/A';
                const finalTime = res.schedule_time || payload.due_time || '';
                const timeString = finalTime ? ` at <span class="text-primary fw-bold">${finalTime}</span>` : '';

                successTitle = window.translations.activity_scheduled_successfully;
                let template = window.translations.activity_scheduled_subtitle || "The next :type is scheduled for :date:time.";
                successSubtitle = template
                    .replace(':type', `<span class="fw-bold text-dark">${activityType}</span>`)
                    .replace(':date', `<span class="text-primary fw-bold">${finalDate}</span>`)
                    .replace(':time', timeString);
                successSubtitle = `<p class="text-muted mb-0">${successSubtitle}</p>`;
            } 
            else if (fieldType === 'dropdown_form') {
                const statusLabel = vfSelectedNextPipelineStatus?.label || 'Updated Status';
                const lostReason = payload.lost_reason_label || 'No reason provided';

                let titleTemplate = window.translations.dropdown_form_success_title || "Deal Marked as :status";
                successTitle = titleTemplate.replace(':status', statusLabel);

                let subtitleTemplate = window.translations.dropdown_form_success_subtitle || "The deal has been successfully marked as :status because of :reason.";
                successSubtitle = subtitleTemplate
                    .replace(':status', `<strong>${statusLabel}</strong>`)
                    .replace(':reason', `<span class="fw-bold text-danger">${lostReason}</span>`);
                successSubtitle = `<p class="text-muted mb-0">${successSubtitle}</p>`;
            } 
            else if (fieldType === 'calculation_form') {
                successTitle = window.translations.moved_to_calculation;
                successSubtitle = `<p class="text-muted mb-0">${window.translations.moved_to_calculation_subtitle || 'The deal has been moved to the calculation stage and saved successfully.'}</p>`;
            } 
            else if (fieldType === 'status_change_form') {
                const statusLabel = vfSelectedNextPipelineStatus?.label || 'New Status';
                successTitle = window.translations.status_updated_successfully;
                
                let subtitleTemplate = window.translations.status_updated_subtitle || "Pipeline status updated to :status.";
                successSubtitle = subtitleTemplate.replace(':status', `<span class="fw-bold text-success">${statusLabel}</span>`);
                successSubtitle = `<p class="text-muted mb-0">${successSubtitle}</p>`;
            }

            Swal.fire({
                icon: 'success',
                title: successTitle,
                html: successSubtitle,
                timer: 2500,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 2500);
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: window.translations.failed || 'Failed',
                text: xhr.responseJSON?.message || window.translations.something_went_wrong || 'Something went wrong'
            });
        },
        complete: function () {
            $('.vf-submit-button')
                .prop('disabled', false)
                .html(`${window.translations.submit_btn || 'Submit'} <i class="ti tabler-arrow-right ms-1"></i>`);
        }
    });
});

$(document).ready(function () {
    $('#vfScheduleDueDate').addClass('flatpicker-date');
    $('#vfScheduleDueTime').addClass('flatpicker-time');
});