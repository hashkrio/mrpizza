let feWizardStep = 1;
let feWizardPayload = {};
let feSelectedScheduleStatus = null;    
let feSelectedNextPipelineStatus = null;  
let feWizardTotalSteps = 0;              
let feActiveWorkflowPanels = [];         
let feDatePickerInstance = null;
let feTimePickerInstance = null;

function initDatePicker(selector, parent) {
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

function initTimePicker(selector, parent = null) {
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

function feBuildDynamicStepIndicators() {
    const $panels = $('.fe-wizard-panel');
    const skippedSteps = feWizardPayload.skip_steps || feWizardPayload.wizard?.skip_steps || [];
    
    feActiveWorkflowPanels = [];
    let stepsContainerHtml = '';
    let visualIndex = 1;

    $panels.each(function () {
        const stepKey = $(this).attr('data-step-key');
        if (skippedSteps.includes(stepKey)) {
            $(this).addClass('d-none').attr('data-skipped', 'true').removeAttr('data-panel');
            return;
        }
        $(this).removeAttr('data-skipped');
        $(this).attr('data-panel', visualIndex);
        feActiveWorkflowPanels.push(this);
        const stepTitle = $(this).attr('data-step-title') || `Step ${visualIndex}`;
        stepsContainerHtml += `
            <div class="fe-step-dot" data-step="${visualIndex}" title="${stepTitle}">
                ${visualIndex}
            </div>`;
        visualIndex++;
    });

    feWizardTotalSteps = feActiveWorkflowPanels.length;
    $('#feWizardStepsContainer').html(stepsContainerHtml);
}

function feSyncWizard(step) {
    feWizardStep = step;
    $('.fe-wizard-panel').addClass('d-none');
    $(`.fe-wizard-panel[data-panel="${step}"]:not([data-skipped="true"])`).removeClass('d-none');
    const percentage = feWizardTotalSteps > 1 ? ((step - 1) / (feWizardTotalSteps - 1)) * 100 : 0;
    $('#feWizardProgressLine').css('width', percentage + '%');
    $('.fe-step-dot').removeClass('active-step passed-step');
    $('.fe-step-dot').each(function() {
        let dotStep = Number($(this).data('step'));
        if (dotStep < step) {
            $(this).addClass('passed-step');
        } else if (dotStep === step) {
            $(this).addClass('active-step');
        }
    });

    if (step === feWizardTotalSteps) {
        $('#feBtnSaveSchedule').addClass('d-none');
        $('#feWizardBackBtn').addClass('d-none');
    } else {
        $('#feBtnSaveSchedule').removeClass('d-none').html(
            step === (feWizardTotalSteps - 1) ? `${window.translations.save_finish_text} <i class="ti tabler-check ms-1"></i>` : `${window.translations.continue_text} <i class="ti tabler-arrow-right ms-1"></i>`
        );
        if (step > 1) {
            $('#feWizardBackBtn').removeClass('d-none');
        } else {
            $('#feWizardBackBtn').addClass('d-none');
        }
    }
}

// Step 1 Card Renderer: Handles Primary Action Options
function feRenderStep1Options(options) {
    const $container = $('#feStep1OptionsContainer');
    if (!options || !options.length) {
        $container.html(`<div class="alert alert-warning mb-0">${window.translations.no_primary_configuration_statuses}</div>`);
        return;
    }

    let html = '';
    options.forEach(option => {
        const label = option.label || '-';
        const icon = option.icon || 'phone';
        const color = option.color || 'primary';
        const slug = option.slug || '';

        html += `
            <div class="fe-wizard-status-card p-3 rounded mb-2 cp" data-slug="${slug}" data-label="${label}">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-${color} bg-opacity-10 text-${color} rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="ti tabler-${icon} fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark lh-sm">${label}</div>
                        </div>
                    </div>
                    <i class="ti tabler-chevron-right text-secondary fs-5"></i>
                </div>
            </div>
        `;
    });

    $container.html(html);
}

// Step 2 Visual Review Generator
function feRenderStep2Review(status) {
    const $reviewBadge = $('#feStep2SelectedStatusReview');
    if ($reviewBadge.length && status) {
        $reviewBadge.html(`
            <div class="card bg-success-subtle border-2 border-success p-3 mb-3">
                <div class="d-flex align-items-center gap-2 text-dark">
                    <i class="ti tabler-circle-check text-success fs-4"></i>
                    <span><strong class="text-dark">${status.label}</strong></span>
                </div>
            </div>
        `);
    }
}

// Step 3 Card Renderer: Loads dynamic next pipelines based on Step 1 rules
function feRenderStep3Options(options) {
    const $container = $('#feNextPipelineStatusContainer');
    if (!options || !options.length) {
        $container.html(`<div class="alert alert-warning mb-0">${window.translations.no_pipeline_paths_found}</div>`);
        return;
    }

    let html = '';
    options.forEach(item => {
        const ruleJson = JSON.stringify(item.form_rule || {}).replace(/'/g, "&apos;");
        const label = item.label || '-';
        const desc = item.description || '';
        const icon = item.icon || 'git-commit';
        const color = item.color || 'primary';
        const isReschedule = item.form_rule?.is_reschedule ? 1 : 0; 

        html += `
            <div class="fe-wizard-next-status-card p-3 rounded mb-2 cp" 
                 data-status="${item.status}" 
                 data-label="${label}"
                 data-is-reschedule="${isReschedule}"
                 data-form-rule='${ruleJson}'>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-${color} bg-opacity-10 text-${color} rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="ti tabler-${icon} fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark lh-sm mb-1">${label}</div>
                            ${desc ? `<small class="text-muted d-block lh-sm">${desc}</small>` : ''}
                        </div>
                    </div>
                    <i class="ti tabler-chevron-right text-secondary fs-5"></i>
                </div>
            </div>
        `;
    });

    $container.html(html);
}

// Step 4 Selection Summary Info Header
function feRenderStep4Review(status) {
    const $reviewBadge = $('#feStep4SelectedStatusReview');
    if ($reviewBadge.length && status) {
        const messageText = status.is_reschedule 
            ? `<strong class="text-dark">${status.label}</strong>`
            : `<strong class="text-dark">${status.label}</strong>`;

        $reviewBadge.html(`
            <div class="alert alert-success fw-bold text-dark border-2 border-success py-2 px-3 mb-3 d-flex align-items-center gap-2">
                <i class="ti tabler-${status.is_reschedule ? 'refresh' : 'arrows-up-down'} fs-4"></i>
                <span>${messageText}</span>
            </div>
        `);
    }
}

function feFillGlobalDropdowns(res) {
    let typeHtml = '';
    if (res.schedule_types?.length) {
        res.schedule_types.forEach(item => {
            typeHtml += `<option value="${item.slug}">${item.name}</option>`;
        });
    } else {
        typeHtml = `<option value="">${window.translations.no_active_schedule_types}</option>`;
    }
    $('#feScheduleTypeSlug').html(typeHtml);
}

// Helper to programmatically offset execution dates via rule payloads
function feSetDefaultDate($input, pickerInstance, days) {
    if (days === undefined || days === null) return;
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + parseInt(days, 10));
    const yyyy = dueDate.getFullYear();
    const mm = String(dueDate.getMonth() + 1).padStart(2, '0');
    const dd = String(dueDate.getDate()).padStart(2, '0');
    const formattedDate = `${yyyy}-${mm}-${dd}`;
    $input.val(formattedDate);
    if (pickerInstance) pickerInstance.setDate(formattedDate, true);
}

// Parses and applies context rules on Form Configuration components
function feApplyFormRules(formRule = {}, nextStatusLabel = '') {
    $('.fe-form-variant').addClass('d-none');
    $('#feWizardFieldType').val(formRule.field_type || '');
    const $typeWrapper = $('#feScheduleTypeSlug').closest('.mb-3, .form-group, div');
    const $timeWrapper = $('#feScheduleTimeContainer');
    const $dateWrapper = $('#feScheduleDateContainer');
    $typeWrapper.removeClass('d-none');
    $timeWrapper.removeClass('d-none');
    $dateWrapper.removeClass('d-none');

    if (formRule.field_type === 'activity_form' || formRule.field_type === 'calculation_form') {
        $('#feVariantActivityForm').removeClass('d-none');

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
            $('#feScheduleTypeSlug').val(formRule.schedule_type_slug);
        }

        if (formRule.default_due_days !== undefined) {
            feSetDefaultDate($('#feScheduleDueDate'), feDatePickerInstance, formRule.default_due_days);
        }
        $('#feScheduleDueTime').val('09:00');

        if (formRule.field_type === 'calculation_form' && formRule.show_button === true) {
            $('#feCalculationActionButtonContainer').removeClass('d-none');
            $('#feCalculationActionButton').text(formRule.button_label || 'Calculation');
        } else {
            $('#feCalculationActionButtonContainer').addClass('d-none');
        }
    }   else if (formRule.field_type === 'status_change_form') {
        $typeWrapper.addClass('d-none');
        $timeWrapper.addClass('d-none');
        $dateWrapper.addClass('d-none');
        $('#feVariantActivityForm').addClass('d-none');

    }   else if (formRule.field_type === 'dropdown_form') {
            $('#feVariantDropdownForm').removeClass('d-none');
            let reasonHtml = '<option value="">'+window.translations.select_reason+'</option>';

            if (formRule.reason_dropdown && formRule.reason_dropdown.length > 0) {
                formRule.reason_dropdown.forEach(reason => {
                    reasonHtml += `<option value="${reason.id}">${reason.name}</option>`;
                });
            }
            $('#feReasonDropdownId').html(reasonHtml);
        }
    }

function openScheduleWizard(pipelineId) {
    if (!pipelineId) return;

    $('#feWizardPipelineId').val(pipelineId);
    const ajaxUrl = window.wizardDataUrl.replace('__ID__', pipelineId);

    $.ajax({
        url: ajaxUrl,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            feWizardPayload = res || {};
            $('#feWizardTitle').text(res.wizard?.title || window.translations.complete_activity);
            $('#feWizardSubtitle').text(res.wizard?.description || window.translations.choose_how_to_proceed);
            feBuildDynamicStepIndicators();
            const optionsList = res.schedule_status_options || res.wizard?.steps?.[0]?.fields?.[0]?.options || [];
            feRenderStep1Options(optionsList);
            feFillGlobalDropdowns(res);
            const skippedSteps = res.skip_steps || res.wizard?.skip_steps || [];
            if (skippedSteps.includes('schedule_status')) {
                if (optionsList.length > 0) {
                    const firstOption = optionsList[0];
                    feSelectedScheduleStatus = { slug: firstOption.slug, label: firstOption.label };
                    feRenderStep2Review(feSelectedScheduleStatus);
                    let mappedRules = res.schedule_status_rules?.[firstOption.slug];
                    if (!mappedRules && res.schedule_status_rules) {
                        mappedRules = res.schedule_status_rules;
                    }
                    if (!mappedRules) {
                        mappedRules = firstOption.next_statuses || [];
                    }
                    feRenderStep3Options(mappedRules);
                }
            }

            const modalElement = document.getElementById('feScheduleWizardModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();

            setTimeout(function () {
                try {
                    if (feDatePickerInstance) feDatePickerInstance.destroy();
                    feDatePickerInstance = initDatePicker('#feScheduleDueDate', modalElement);
                } catch (e) { console.error(e); }

                try {
                    if (feTimePickerInstance) feTimePickerInstance.timepicker('destroy');
                    feTimePickerInstance = initTimePicker('#feScheduleDueTime', modalElement);
                } catch (e) { console.error(e); }
            }, 300);

            feSyncWizard(1);
        },
        error: function (xhr) {
            console.error('Failed to parse wizard data structural contract.', xhr);
        }
    });
}

$(document).on('click', '#feStep1OptionsContainer .fe-wizard-status-card', function () {
    $('#feStep1OptionsContainer .fe-wizard-status-card').removeClass('selected border-primary bg-light');
    $(this).addClass('selected border-primary bg-light');

    const slug = $(this).data('slug');
    const label = $(this).data('label');

    feSelectedScheduleStatus = { slug: slug, label: label };
    feRenderStep2Review(feSelectedScheduleStatus);

    let mappedRules = feWizardPayload.schedule_status_rules?.[slug];
    if (!mappedRules && feWizardPayload.wizard?.steps) {
        const step1 = feWizardPayload.wizard.steps.find(s => s.key === 'schedule_status');
        const opt = step1?.fields?.[0]?.options?.find(o => o.slug === slug);
        mappedRules = opt?.next_statuses || [];
    }
    feRenderStep3Options(mappedRules || []);

    const currentStep = parseInt($(this).closest('.fe-wizard-panel').attr('data-panel')) || 1;
    feSyncWizard(currentStep + 1);
});

$(document).on('click', '#feNextPipelineStatusContainer .fe-wizard-next-status-card', function () {
    $('#feNextPipelineStatusContainer .fe-wizard-next-status-card').removeClass('selected border-primary bg-light');
    $(this).addClass('selected border-primary bg-light');

    const statusId = $(this).data('status');
    const label = $(this).data('label');
    const formRule = $(this).data('form-rule') || {};
    
    let isReschedule = 0;
    if (formRule.is_reschedule === true || formRule.is_reschedule === 1) {
        isReschedule = 1;
    } else {
        isReschedule = parseInt($(this).data('is-reschedule')) || 0;
    }

    feSelectedNextPipelineStatus = { 
        id: statusId, 
        label: label, 
        is_reschedule: isReschedule, 
         schedule_status_slug_to_set: formRule.schedule_status_slug_to_set || null,
        form_rule: formRule 
    };

    feRenderStep4Review(feSelectedNextPipelineStatus);
    feApplyFormRules(formRule, label);

    const currentStep = parseInt($(this).closest('.fe-wizard-panel').attr('data-panel')) || 1;
    feSyncWizard(currentStep + 1);
});

$(document).on('click', '#feCalculationActionButton', function () {
    const pipelineId = $('#feWizardPipelineId').val();
    if (!pipelineId || !window.storeScheduleUrl) return;

    const payload = {
        _token: $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'),
        pipeline_id: pipelineId,
        status: feSelectedNextPipelineStatus?.id,
        field_type: 'calculation_form',
        schedule_type_slug: 'calculation',
        due_date: $('#feScheduleDueDate').val(),
        comments: $('#feWizardComments').val(),
        schedule_status_slug: feSelectedScheduleStatus?.slug,
        schedule_status_slug_to_set: feSelectedNextPipelineStatus?.schedule_status_slug_to_set || null,
        is_reschedule: feSelectedNextPipelineStatus?.is_reschedule || 0,
    };

    $.ajax({
        url: window.storeScheduleUrl,
        type: 'POST',
        data: payload,
        beforeSend: function () {
            $('#feCalculationActionButton').prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2"></span> ${window.translations.system_syncing}`);
        },
        success: function (res) {
            if (window.quotationCreateRoute) {
                window.location.href = window.quotationCreateRoute.replace('__ID__', pipelineId);
            }
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Calculation Error',
                text: xhr.responseJSON?.message || 'Could not map calculations parameters.'
            });
        },
        complete: function () {
            $('#feCalculationActionButton').prop('disabled', false).html(`<i class="ti tabler-calculator me-2"></i> ${window.translations.calculation}`);
        }
    });
});

$(document).on('click', '#feWizardBackBtn', function () {
    if (feWizardStep <= 1) return;
    const targetPreviousStep = feWizardStep - 1;
    const $prevPanel = $(`.fe-wizard-panel[data-panel="${targetPreviousStep}"]`);
    const prevStepKey = $prevPanel.attr('data-step-key');
    if (prevStepKey === 'next_status') {
        feSelectedNextPipelineStatus = null;
        $('#feNextPipelineStatusContainer .fe-wizard-next-status-card').removeClass('selected border-primary bg-light');
    } else if (prevStepKey === 'schedule_status') {
        feSelectedScheduleStatus = null;
        $('#feStep1OptionsContainer .fe-wizard-status-card').removeClass('selected border-primary bg-light');
    }
    feSyncWizard(targetPreviousStep);
});

function feValidateWorkflowData(fieldType) {
    const $currentPanel = $(`.fe-wizard-panel[data-panel="${feWizardStep}"]`);
    const stepKey = $currentPanel.attr('data-step-key');
    const skippedSteps = feWizardPayload.skip_steps || feWizardPayload.wizard?.skip_steps || [];

    if (stepKey === 'schedule_status' && !feSelectedScheduleStatus && !skippedSteps.includes('schedule_status')) {
        return window.translations.please_choose_schedule_outcome;
    }
    if (stepKey === 'notes' && !feSelectedScheduleStatus && !skippedSteps.includes('schedule_status')) {
        return window.translations.step1_selection_missing;
    }
    if (stepKey === 'next_status' && !feSelectedNextPipelineStatus) {
        return window.translations.please_select_option_continue;
    }
    
    if (stepKey === 'activity_configuration') {
        if (!feSelectedScheduleStatus && !skippedSteps.includes('schedule_status')) return window.translations.please_select_status_outcome;
        if (!feSelectedNextPipelineStatus) return window.translations.please_assign_target_pipeline;

        if (fieldType === 'activity_form') {
            const $typeWrapper = $('#feScheduleTypeSlug').closest('.mb-3, .form-group, div');
            const $timeWrapper = $('#feScheduleDueTime').closest('.mb-3, .form-group, div');
            const $dateWrapper = $('#feScheduleDueDate').closest('.mb-3, .form-group, div');

            if (!$dateWrapper.hasClass('d-none') && !$('#feScheduleDueDate').val()) return window.translations.execution_date_missing;
            if (!$timeWrapper.hasClass('d-none') && !$('#feScheduleDueTime').val()) return window.translations.target_time_missing;
            if (!$typeWrapper.hasClass('d-none') && !$('#feScheduleTypeSlug').val()) return window.translations.schedule_category_missing;
        } else if (fieldType === 'dropdown_form') {
            if (!$('#feReasonDropdownId').val()) return window.translations.select_reason_required;
        } else if (fieldType === 'calculation_form') {
            return null;
        } else if (fieldType === 'status_change_form') {
            return null;
        }
    }
    return null;
}

$(document).on('click', '#feBtnSaveSchedule', function () {
    const fieldType = $('#feWizardFieldType').val();

    const structuralError = feValidateWorkflowData(fieldType);
    if (structuralError) {
        Swal.fire({ 
            icon: 'warning', 
            title: window.translations.action_required, 
            text: structuralError, 
            confirmButtonColor: '#6f42c1' 
        });
        return;
    }
    if (feWizardStep < (feWizardTotalSteps - 1)) {
        feSyncWizard(feWizardStep + 1);
        return;
    }
    const $saveBtn = $(this);
    const payload = {
        _token: $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'),
        pipeline_id: $('#feWizardPipelineId').val(),
        status: feSelectedNextPipelineStatus?.id,
        field_type: fieldType,
        comments: $('#feWizardComments').val(), 
        schedule_status_slug: feSelectedScheduleStatus?.slug,
        schedule_status_slug_to_set: feSelectedNextPipelineStatus?.schedule_status_slug_to_set || null,
        is_reschedule: feSelectedNextPipelineStatus?.is_reschedule || 0
    };

    if (fieldType === 'activity_form' || fieldType === 'calculation_form') {
        payload.schedule_type_slug = $('#feScheduleTypeSlug').val();
        payload.due_date = $('#feScheduleDueDate').val();
        payload.due_time = $('#feScheduleDueTime').val();
    } 
    else if(fieldType === 'status_change_form') {
        payload.status_change_only = 1;
    } 
    else if (fieldType === 'dropdown_form') {
        payload.lost_reason = $('#feReasonDropdownId').val();
        payload.lost_reason_label = $('#feReasonDropdownId option:selected').text();
    }

    $.ajax({
        url: window.storeScheduleUrl,
        type: 'POST',
        data: payload,
        beforeSend: function () {
            $saveBtn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2"></span> ${window.translations.storing}`);
        },
        success: function (res) {
            let successTitle = window.translations.pipeline_updated;
            let successSubtitle = '';
            const successTick = `<div class="text-center mb-3"><i class="ti tabler-circle-check text-success" style="font-size: 3.5rem;"></i></div>`;

            if (fieldType === 'activity_form') {
                const activityType = res.schedule_type || window.translations.activity;
                const finalDate = res.schedule_date || payload.due_date || window.translations.n_a;
                const finalTime = res.schedule_time || payload.due_time || '';
                
                const timeString = finalTime 
                    ? ` at <span class="text-primary fw-bold">${finalTime}</span>` 
                    : '';

                successTitle = window.translations.activity_scheduled_successfully;
                let subtitleTemplate = window.translations.activity_scheduled_subtitle || "The next :type is scheduled for :date:time.";
                subtitleTemplate = subtitleTemplate
                    .replace(':type', `<span class="fw-bold text-dark">${activityType}</span>`)
                    .replace(':date', `<span class="text-primary fw-bold">${finalDate}</span>`)
                    .replace(':time', timeString);

                successSubtitle = `<p class="text-muted mb-0">${subtitleTemplate}</p>`;
            } 
            else if (fieldType === 'dropdown_form') {
                const statusLabel = feSelectedNextPipelineStatus?.label || window.translations.updated_status;
                const lostReason = payload.lost_reason_label || window.translations.no_reason_provided;
                let titleTemplate = window.translations.dropdown_form_success_title || "Deal Marked as :status";
                successTitle = titleTemplate.replace(':status', statusLabel);
                let subtitleTemplate = window.translations.dropdown_form_success_subtitle || "The deal has been successfully marked as :status because of :reason.";
                subtitleTemplate = subtitleTemplate
                    .replace(':status', `<strong>${statusLabel}</strong>`)
                    .replace(':reason', `<span class="fw-bold text-danger">${lostReason}</span>`);

                successSubtitle = `<p class="text-muted mb-0">${subtitleTemplate}</p>`;
            } 
            else if (fieldType === 'calculation_form') {
                successTitle = window.translations.moved_to_calculation;
                successSubtitle = `
                    <p class="text-muted mb-0">
                        ${window.translations.moved_to_calculation_subtitle || 'The deal has been moved to the calculation stage and saved successfully.'}
                    </p>`;
            } 
            else if (fieldType === 'status_change_form') {
                const statusLabel = feSelectedNextPipelineStatus?.label || 'New Status';
                
                successTitle = window.translations.status_updated_successfully;
                
                let subtitleTemplate = window.translations.status_updated_subtitle || "Pipeline status updated to :status.";
                subtitleTemplate = subtitleTemplate.replace(':status', `<span class="fw-bold text-success">${statusLabel}</span>`);

                successSubtitle = `<p class="text-muted mb-0">${subtitleTemplate}</p>`;
            }

            $('#feSuccessMessageTitle').text(successTitle);
            $('#feSuccessMessageSubtitle').html(successSubtitle);

            feSyncWizard(feWizardTotalSteps);

            const pipelineId = $('#feWizardPipelineId').val();
            setTimeout(function () {
                const modalElement = document.getElementById('feScheduleWizardModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                }

                setTimeout(function () {
                    if (typeof viewRecord === 'function') {
                        const existingOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('viewRecordOffcanvas'));
                        if (existingOffcanvas) existingOffcanvas.hide();
                        viewRecord(pipelineId);
                    }
                }, 2000);
            }, 3000);
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: xhr.responseJSON?.message || 'A validation runtime error occurred processing tracking parameters.'
            });
        },
        complete: function () {
            $saveBtn.prop('disabled', false).html(`Continue <i class="ti tabler-arrow-right ms-1"></i>`);
        }
    });
});

$('#feScheduleWizardModal').on('hidden.bs.modal', function () {
    const $form = $('#feScheduleWizardForm');
    if ($form.length) $form[0].reset();
    $('#feWizardComments').val('');
    $('#feStep2SelectedStatusReview').html('');
    $('#feStep4SelectedStatusReview').html('');
    $('.fe-wizard-panel').removeAttr('data-skipped').removeAttr('data-panel').addClass('d-none');
    $('.fe-wizard-status-card, .fe-wizard-next-status-card').removeClass('selected border-primary bg-light');
    $('.fe-form-variant').addClass('d-none');
    $('#feCalculationActionButtonContainer').addClass('d-none');
    $('#feStep1OptionsContainer').html('');
    $('#feNextPipelineStatusContainer').html('');
    $('#feWizardTitle').text('Complete Activity');
    $('#feWizardSubtitle').text('Choose how to proceed');
    $('#feSuccessMessageTitle').text('-');
    $('#feSuccessMessageSubtitle').text('');
    $('#feWizardFieldType').val('');
    $('#feWizardPipelineId').val('');
    feSelectedScheduleStatus = null;
    feSelectedNextPipelineStatus = null;
    feActiveWorkflowPanels = [];
    if (feDatePickerInstance) feDatePickerInstance.clear();
    feSyncWizard(1);
});


$(document).ready(function () {
    $('#feScheduleDueDate').addClass('flatpicker-date');
    $('#feScheduleDueTime').addClass('flatpicker-time');
    const reloadPipelineId = sessionStorage.getItem('reloadPipelineId');
    if (reloadPipelineId) {
        sessionStorage.removeItem('reloadPipelineId');
        setTimeout(function () {
            if (typeof viewRecord === 'function') viewRecord(reloadPipelineId);
        }, 300);
    }
});

$(document).on('click', '.btn-complete-activity', function () {
    const pipelineId = $(this).attr('data-pipeline-id');
    const activityFunctionalityId = parseInt($(this).attr('data-activity-functionality-id')) || 0;
    const scheduleStop = parseInt($(this).attr('data-schedule-stop')) || 0;
    const activityStop = parseInt($(this).attr('data-activity-stop')) || 0;
    const isAutoSchedule = parseInt($(this).attr('data-is-auto-schedule')) || 0;

    if (scheduleStop == 1 || activityStop == 1) {
        Swal.fire({ icon: 'warning', title: window.translations.automation_stopped, text: window.translations.followup_automation_stopped });
        return;
    }

    if (activityFunctionalityId > 0) {
        if (isAutoSchedule === 0) {
            Swal.fire({ icon: 'warning', title: window.translations.auto_activity_not_found, text: window.translations.schedule_process_manual });
            return;
        }
        openScheduleWizard(pipelineId);
        return;
    }

    Swal.fire({ icon: 'info', title: window.translations.no_action_configured, text: window.translations.no_action_pipeline_status });
});