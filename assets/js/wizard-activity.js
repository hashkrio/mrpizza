// /**
//  * Activity Wizard
//  */
// let activityWizardStep = 1;
// let activityWizardPayload = {};
// let activityDatePickerInstance = null;
// let activityTimePickerInstance = null;
// let wizardMode = 'activity';

// const wizardStepsMap = {
//     activity: [1, 2, 3, 4, 5],
//     call_result: [1, 4, 5]
// };

// function initActivityDatePicker(selector, parent) {
//     return flatpickr(selector, {
//         altInput: true,
//         altFormat: "d-m-Y",
//         dateFormat: "Y-m-d",
//         appendTo: parent,
//         static: true,
//         minDate: "today",
//         locale: {
//             firstDayOfWeek: 1
//         }
//     });
// }

// function initActivityTimePicker(selector, parent = null) {
//     const $element = $(selector);

//     if ($element.length && typeof $element.inputmask === 'function') {
//         $element.inputmask("99:99", {
//             placeholder: "HH:MM"
//         });
//     }

//     const options = {
//         timeFormat: 'HH:mm',
//         interval: 30,
//         minTime: '00:00',
//         maxTime: '23:59',
//         dynamic: false,
//         dropdown: true,
//         scrollbar: true
//     };

//     if (parent) {
//         options.appendTo = parent;
//     }

//     return $element.timepicker(options);
// }

// function syncActivityWizard(step) {
//     activityWizardStep = step;
//     let visibleSteps = wizardStepsMap[wizardMode];
//     $('.wizard-panel').addClass('d-none');
//     $(`.wizard-panel[data-panel="${step}"]`).removeClass('d-none');

//     let currentIndex = visibleSteps.indexOf(step);
//     let percentage = 0;

//     if (visibleSteps.length > 1) {
//         percentage = (currentIndex / (visibleSteps.length - 1)) * 100;
//     }

//     $('#activityWizardProgress').css('width', percentage + '%');

//     $('.step-dot').hide();

//     visibleSteps.forEach((stepNo, index) => {
//         let dot = $(`.step-dot[data-step="${stepNo}"]`);
//         dot.show();

//         if (stepNo !== 5) {
//             dot.text(index + 1);
//         } else {
//             dot.html('<i class="ti tabler-check"></i>');
//         }

//         if (index < currentIndex) {
//             dot.addClass('passed-step');
//         } else if (index === currentIndex) {
//             dot.addClass('active-step');
//         }
//     });


// if (step === 1 || step === 5) {

//     $('#activityWizardBackBtn').hide();
//     $('#activityWizardNextBtn').hide();

// } else {

//     $('#activityWizardBackBtn').show();
//     $('#activityWizardNextBtn').show();
//     if (step === visibleSteps[visibleSteps.length - 2]) {

//         $('#activityWizardNextBtn')
//             .removeClass('btn-primary')
//             .addClass('btn-success')
//             .html(`
//                 Save
//                 <i class="ti tabler-device-floppy ms-1"></i>
//             `);

//     } else {

//         $('#activityWizardNextBtn')
//             .removeClass('btn-success')
//             .addClass('btn-primary')
//             .html(`
//                 Continue
//                 <i class="ti tabler-arrow-right ms-1"></i>
//             `);
//     }
// }
// }


// function openActivityWizard(pipelineId) {
//     if (!pipelineId) {
//         Swal.fire({
//             icon: 'error',
//             title: window.translations.pipeline_missing
//         });
//         return;
//     }

//     let url = window.callActivityWizardDataUrl.replace('__ID__', pipelineId);

//     $.ajax({
//         url: url,
//         type: 'GET',
//         success: function(res) {

//             // Check next status options before opening wizard
//             if (
//                 !res.next_status_step ||
//                 !res.next_status_step.statuses ||
//                 res.next_status_step.statuses.length === 0
//             ) {
//                 Swal.fire({
//                     icon: 'warning',
//                     title: 'No Further Action Configured',
//                     text: 'Please complete this activity manually.',
//                     confirmButtonText: 'OK'
//                 });
//                 return;
//             }
            
//             activityWizardPayload = res;
//             $('#wizardPipelineId').val(res.pipeline_id);
//             renderActivityWizard(res);
//             syncActivityWizard(1);
//             $('#activityWizardModal').modal('show');
//         },
//         error: function() {
//             Swal.fire({
//                 icon: 'error',
//                 title: window.translations.failed,
//                 text: window.translations.unable_load
//             });
//         }
//     });
// }

// function renderActivityWizard(data) {
//     $('#activityWizardTitle').text(data.title || 'Complete Activity');
//     $('#activityWizardDescription')
//         .text(data.description || '')
//         .removeClass('d-none');

//     let step1 = data.call_result_step || {};
//     $('#step1Title').text(step1.title || 'Call Result');
//     $('#step1Description').text(step1.description || '');

//     let step1Html = '';
//     (step1.options || []).forEach(option => {
//         let color = option.color || 'primary';
//         let icon = option.icon || 'circle';
//         step1Html += `
//             <div 
//                 class="wizard-option-card call-result-option p-3 rounded"
//                 data-id="${option.id}"
//                 data-label="${option.label}"
//             >
//                 <div class="d-flex align-items-center justify-content-between">
//                     <div class="d-flex align-items-center">
//                         <div class="wizard-option-icon bg-${color}-subtle text-${color} me-3">
//                             <i class="ti tabler-${icon}"></i>
//                         </div>
//                         <div>
//                             <div class="fw-bold text-dark lh-sm mb-1">
//                                 ${option.label}
//                             </div>
//                         </div>
//                     </div>
//                     <i class="ti tabler-chevron-right"></i>
//                 </div>
//             </div>
//         `;
//     });
//     $('#wizardCallResultContainer').html(step1Html);

//     let notesStep = data.notes_step || {};
//     $('#step2Title').text(notesStep.title || 'Notes');
//     $('#step2Description').text(notesStep.description || '');

//     let nextStatusStep = data.next_status_step || {};
//     $('#step3Title').text(nextStatusStep.title || 'Next Status');
//     $('#step3Description').text(nextStatusStep.description || '');

//     let statusHtml = '';
//     (nextStatusStep.statuses || []).forEach(status => {
//         let color = status.color || 'primary';
//         let icon = status.icon || 'circle';
//         statusHtml += `
//             <div 
//                 class="wizard-option-card next-status-option p-3 rounded"
//                 data-id="${status.id}"
//                 data-label="${status.label}"
//                 data-config='${JSON.stringify(status)}'
//             >
//                 <div class="d-flex align-items-center justify-content-between">
//                     <div class="d-flex align-items-center">
//                         <div class="wizard-option-icon bg-${color}-subtle text-${color} me-3">
//                             <i class="ti tabler-${icon}"></i>
//                         </div>
//                         <div>
//                             <div class="fw-bold text-dark lh-sm mb-1">
//                                 ${status.label}
//                             </div>
//                         </div>
//                     </div>
//                     <i class="ti tabler-chevron-right"></i>
//                 </div>
//             </div>
//         `;
//     });
//     $('#wizardNextStatusContainer').html(statusHtml);
// }

// $(document).on('click', '.call-result-option', function() {
//     $('.call-result-option').removeClass('active');
//     $(this).addClass('active');
//     $('#wizardSelectedCallResult').val($(this).attr('data-id'));
//     $('#selectedResultLabel').text($(this).attr('data-label'));
//     syncActivityWizard(2);
// });

// $(document).on('click', '.next-status-option', function() {
//     $('.next-status-option').removeClass('active');
//     $(this).addClass('active');

//     let config = JSON.parse($(this).attr('data-config'));
//     $('#wizardSelectedNextStatus').val(config.id);
//     renderDynamicActivityFields(config);
//     syncActivityWizard(4);
// });

// $(document).on('click', '#activityWizardNextBtn', function() {
//     let visibleSteps = wizardStepsMap[wizardMode];
//     let currentIndex = visibleSteps.indexOf(activityWizardStep);
//     let nextStep = visibleSteps[currentIndex + 1];

//     if (nextStep && activityWizardStep !== 4) {
//         syncActivityWizard(nextStep);
//         return;
//     }

//     if (activityWizardStep === 4) {
//         if (wizardMode === 'call_result') {
//             saveCallResultWizard();
//         } else {
//             saveActivityWizard();
//         }
//     }
// });

// $(document).on('click', '#activityWizardBackBtn', function() {
//     if (activityWizardStep > 1) {
//         let visibleSteps = wizardStepsMap[wizardMode];
//         let currentIndex = visibleSteps.indexOf(activityWizardStep);
//         let prevStep = visibleSteps[currentIndex - 1];

//         if (prevStep) {
//             syncActivityWizard(prevStep);
//         }
//     }
// });

// function renderDynamicActivityFields(config) {
//     let activity = config.activity || {};

//     $('#step4Title').text(config.step4_title || 'Schedule Activity');
//     $('#step4Description').text(config.step4_description || 'Configure next follow-up activity');

//        if (activity.check_contact === 'check_contact') {
//         let html = `
//             <div class="text-center py-5">
//                 <div class="mb-4">
//                     <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
//                         style="width:90px; height:90px;">
//                         <i class="ti tabler-alert-triangle text-warning"
//                             style="font-size:50px;"></i>
//                     </div>
//                 </div>
//                 <h4 class="fw-bold mb-3">
//                     Please Check Contact Number
//                 </h4>
//                 <p class="text-muted mb-3">
//                     The contact number is missing or invalid.
//                 </p>
//                 <div class="alert alert-warning fw-bold mb-0">
//                     <i class="ti tabler-info-circle me-2"></i>
//                     Please verify and update the contact details before proceeding.
//                 </div>
//             </div>
//         `;

//         $('#wizardDynamicFields').html(html);
//         return; 
//     }

//     if (wizardMode === 'call_result') {
//         let isDirectStatusChange = activity.move_to_status &&
//             activity.hide_activity_type &&
//             activity.hide_due_date;

//         if (isDirectStatusChange) {
//             let selectedLabel = $('.call-result-action-option.active')
//                 .find('.fw-bold')
//                 .text();

//             $('#wizardDynamicFields').html(`
//                 <div class="text-center py-5">
//                     <div class="mb-4">
//                         <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
//                             style="width:90px; height:90px;">
//                             <i class="ti tabler-refresh text-warning"
//                                 style="font-size:50px;"></i>
//                         </div>
//                     </div>
//                     <h4 class="fw-bold mb-3">
//                         Change Pipeline Status
//                     </h4>
//                     <p class="text-muted mb-3">
//                         This action will change pipeline status to:
//                     </p>
//                     <div class="alert alert-primary fw-bold">
//                         ${selectedLabel}
//                     </div>
//                 </div>
//             `);
//             return;
//         }
//     }

//     let html = '';
//     let isDirectStatusChange = activity.move_to_status &&
//         activity.hide_activity_type &&
//         activity.hide_due_date;

//     if (activity.mark_deal_as_won == 1) {
//         html = `
//             <div class="text-center py-4">
//                 <div class="mb-4">
//                     <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
//                         style="width:90px; height:90px;"> 
//                         <i class="ti tabler-trophy text-success" style="font-size:50px;"></i> 
//                     </div>
//                 </div>
//                 <h4 class="fw-bold mb-2">
//                     ${window.translations.mark_deal_as_won}
//                 </h4>
//                 <p class="text-muted mb-0">
//                     ${window.translations.mark_the_pipeline_as_won}
//                 </p>
//             </div>
//         `;
//         $('#wizardDynamicFields').html(html);
//         return;
//     }

//     let dueDays = config.due_days || 0;
//     let futureDate = new Date();
//     futureDate.setDate(futureDate.getDate() + dueDays);

//     let yyyy = futureDate.getFullYear();
//     let mm = String(futureDate.getMonth() + 1).padStart(2, '0');
//     let dd = String(futureDate.getDate()).padStart(2, '0');
//     let formattedDate = `${yyyy}-${mm}-${dd}`;

//     if (isDirectStatusChange) {
//         let selectedLabel = $('.call-result-action-option.active')
//             .find('.fw-bold')
//             .text();

//         html = `
//             <div class="text-center py-5">
//                 <div class="mb-4">
//                     <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
//                         style="width:90px; height:90px;">
//                         <i class="ti tabler-refresh text-warning"
//                             style="font-size:50px;"></i>
//                     </div>
//                 </div>
//                 <h4 class="fw-bold mb-3">
//                     Change Pipeline Status
//                 </h4>
//                 <p class="text-muted mb-0">
//                     This action will update pipeline status to:
//                 </p>
//                 <div class="alert alert-primary mt-4 mb-0 fw-bold">
//                     ${selectedLabel}
//                 </div>
//             </div>
//         `;
//         $('#wizardDynamicFields').html(html);
//         return;
//     }

//     if (!activity.hide_activity_type) {
//         let options = '';
//         (activityWizardPayload.call_types || []).forEach(type => {
//             let selected = type.slug === activity.call_type_slug
//                 ? 'selected'
//                 : '';
//             options += `
//                 <option value="${type.slug}" ${selected}>
//                     ${type.name}
//                 </option>
//             `;
//         });

//         html += `
//             <div class="mb-4">
//                 <label class="form-label fw-semibold">
//                     ${window.translations.activity_type}
//                 </label>
//                 <select
//                     class="form-select rounded-3 shadow-none"
//                     id="activityCallType"
//                 >
//                     ${options}
//                 </select>
//             </div>
//         `;
//     }

//     if (!activity.hide_due_date) {
//         html += `
//             <div class="row">
//                 <div class="col-md-6 mb-4">
//                     <label class="form-label fw-semibold">
//                         ${window.translations.due_date}
//                     </label>
//                     <input
//                         type="text"
//                         class="form-control rounded-3 shadow-none flatpicker-date"
//                         id="activityDueDate"
//                         value="${formattedDate}"
//                     >
//                 </div>
//                 <div class="col-md-6 mb-4">
//                     <label class="form-label fw-semibold">
//                         ${window.translations.due_time}
//                     </label>
//                     <input
//                         type="text"
//                         class="form-control rounded-3 shadow-none flatpicker-time"
//                         id="activityDueTime"
//                         value="09:00"
//                     >
//                 </div>
//             </div>
//         `;
//     }

//     if (activity.type === 'email') {
//         html += `
//             <div class="mb-4">
//                 <label class="form-label fw-semibold">
//                     Email Notes
//                 </label>
//                 <textarea
//                     class="form-control rounded-3 shadow-none"
//                     id="activityEmailNotes"
//                     rows="5"
//                     placeholder="${activity.email_description || 'Write email notes'}"
//                 ></textarea>
//             </div>
//         `;
//     }
    
//     if (activity.field_type === 'dropdown') {
//         let options = `
//             <option value="">
//                 ${window.translations.select_lost_reason || 'Select Lost Reason'}
//             </option>
//         `;

//         // console.log('Lost Reasons:', activity.reason_dropdown);

//         (activity.reason_dropdown || []).forEach(item => {
//             options += `
//                 <option value="${item.id}">
//                     ${item.name}
//                 </option>
//             `;
//         });

//         html += `
//             <div class="mb-4">
//                 <label class="form-label fw-semibold">
//                     ${window.translations.lost_reason}
//                 </label>
//                 <select
//                     class="form-select rounded-3 shadow-none"
//                     id="activityLostReason"
//                 >
//                     ${options}
//                 </select>
//             </div>
//         `;
//     }

//     $('#wizardDynamicFields').html(html);

//     const modalElement = document.getElementById('activityWizardModal');

//     if ($('#activityDueDate').length) {
//         if (activityDatePickerInstance) {
//             activityDatePickerInstance.destroy();
//         }
//         activityDatePickerInstance = initActivityDatePicker(
//             '#activityDueDate',
//             modalElement
//         );
//         activityDatePickerInstance.setDate(formattedDate, true);
//     }

//     if ($('#activityDueTime').length) {
//         $('#activityDueTime').val('09:00');
//         activityTimePickerInstance = initActivityTimePicker(
//             '#activityDueTime',
//             modalElement
//         );
//     }
// }

// function showWizardResult(type, title, subtitle) {
//     let selectedStatusLabel = '';

//     if (wizardMode === 'call_result') {

//         selectedStatusLabel =
//             $('.call-result-action-option.active')
//                 .find('.fw-bold')
//                 .text()
//                 .trim();

//     } else {

//         selectedStatusLabel =
//             $('.next-status-option.active')
//                 .attr('data-label') || '';
//     }
//         let dueDate = $('#activityDueDate').val() || '';
//         let dueTime = $('#activityDueTime').val() || '';
//         let activityType = $('#activityCallType option:selected').text() || '';
//         let summaryText = '';

//     if (selectedStatusLabel) {

//         if (wizardMode === 'call_result') {

//             summaryText += `Pipeline status changed to "${selectedStatusLabel}"`;

//         } else {

//             summaryText += `Next activity status set to "${selectedStatusLabel}"`;
//         }
//     }

//     if (activityType) {
//         summaryText += summaryText
//             ? ` with ${activityType.toLowerCase()} activity`
//             : `${activityType} activity created`;
//     }

//     if (dueDate && dueTime) {
//         summaryText += ` scheduled on ${dueDate} at ${dueTime}`;
//     } else if (dueDate) {
//         summaryText += ` scheduled on ${dueDate}`;
//     }

//     summaryText += '.';

//     if (type === 'success') {
//         $('#activitySuccessMessageTitle')
//             .text(title || window.translations.activity_processed);

//         $('#activitySuccessMessageSubtitle')
//             .html(`
//                 ${subtitle || 'The follow-up action has been completed successfully.'}
//                 <br><br>
//                 ${summaryText}
//             `);

//         $('.tabler-circle-check-filled')
//             .removeClass('text-danger')
//             .addClass('text-success');
//     } else {
//         $('#activitySuccessMessageTitle')
//             .text(title || window.translations.activity_failed);

//         $('#activitySuccessMessageSubtitle')
//             .html(`
//                 ${subtitle || 'Something went wrong while processing activity.'}
//                 <br><br>
//                 ${summaryText}
//             `);

//         $('.tabler-circle-check-filled')
//             .removeClass('text-success')
//             .addClass('text-danger');
//     }

//     syncActivityWizard(5);
// }

// function saveActivityWizard() {
//     let selectedConfig = JSON.parse(
//         $('.next-status-option.active')
//             .attr('data-config') || '{}'
//     );

//     let payload = {
//         _token: $('meta[name="csrf-token"]').attr('content'),
//         pipeline_id: $('#wizardPipelineId').val(),
//         call_result: $('#wizardSelectedCallResult').val(),
//         next_status: $('#wizardSelectedNextStatus').val(),
//         call_type_slug: $('#activityCallType').val(),
//         notes: $('#wizardNotes').val(),
//         due_date: $('#activityDueDate').val(),
//         due_time: $('#activityDueTime').val(),
//         lost_reason: $('#activityLostReason').val(),
//         mark_deal_as_won: selectedConfig.activity?.mark_deal_as_won || 0,
//         mark_deal_as_lost: selectedConfig.activity?.mark_deal_as_lost || 0,
//         mark_deal_as_unqualified: selectedConfig.activity?.mark_deal_as_unqualified || 0,
//     };

//     $.ajax({
//         url: window.storeCallActivityUrl,
//         type: 'POST',
//         data: payload,
//         beforeSend: function() {
//             $('#activityWizardNextBtn')
//                 .prop('disabled', true)
//                 .html(`Saving...`);
//         },
//         success: function(res) {
//             showWizardResult(
//                 'success',
//                 res.title || window.translations.activity_saved,
//                 res.message || 'Activity saved successfully'
//             );
//             const pipelineId = $('#wizardPipelineId').val();
            
//             setTimeout(function() {
//                 const modalElement = document.getElementById('activityWizardModal');
//                 if (modalElement) {
//                     const modal = bootstrap.Modal.getInstance(modalElement);
//                     if (modal) {
//                         modal.hide();
//                     }
//                 }
                
//                 setTimeout(function() {
//                     const offcanvasElement = document.getElementById('viewPipelineCanvas');
//                     if (offcanvasElement) {
//                         const existingOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
//                         if (existingOffcanvas) {
//                             existingOffcanvas.hide();
//                         }
//                     }
                    
//                         if (typeof viewRecord === 'function') {
//                             viewRecord(pipelineId);
//                         }
//                 }, 300);
//             }, 5000);
//         },
//         error: function(xhr) {
//             showWizardResult(
//                 'error',
//                 window.translations.validation_failed,
//                 xhr.responseJSON?.message || 'Something went wrong'
//             );
//         },
//         complete: function() {
//             $('#activityWizardNextBtn')
//                 .prop('disabled', false)
//                 .html(`
//                     ${window.translations.save_activity}
//                     <i class="ti tabler-device-floppy ms-1"></i>
//                 `);
//         }
//     });
// }

// $('#activityWizardModal').on('hidden.bs.modal', function() {
//     wizardMode = 'activity';
//     $('#activityWizardForm')[0].reset();
//     $('.call-result-option').removeClass('active');
//     $('.next-status-option').removeClass('active');
//     $('#wizardDynamicFields').html('');

//     if (activityDatePickerInstance) {
//         activityDatePickerInstance.destroy();
//     }

//     syncActivityWizard(1);
// });

// function openCallResultWizard(data) {
//     wizardMode = 'call_result';

//     $.ajax({
//         url: window.callResultWizardDataUrl,
//         type: "POST",
//         data: {
//             pipeline_id: data.pipelineId,
//             call_result_slug: data.callResultSlug,
//             activity_id: data.activityId,
//             _token: $('meta[name="csrf-token"]').attr('content')
//         },
//         success: function(res) {
//             activityWizardPayload = res;
//             let wizard = res.wizard || {};

//             $('#wizardPipelineId').val(data.pipelineId);
//             $('#wizardSelectedCallResult').val(data.callResultSlug);
//             $('#activityWizardTitle').text(
//                 wizard.title || 'Call Follow-up'
//             );
//             $('#activityWizardDescription')
//                 .text(wizard.description || '')
//                 .removeClass('d-none');

//             renderCallResultOptions(
//                 wizard.call_result_options || {},
//                 wizard.default_option || null
//             );

//             syncActivityWizard(1);
//             $('#activityWizardModal').modal('show');
//         }
//     });
// }

// function renderCallResultOptions(options, defaultOption = null) {
//     let html = '';

//     Object.entries(options).forEach(([key, option]) => {
//         let active = key === defaultOption ? 'active' : '';
//         html += `
//             <div 
//                 class="wizard-option-card call-result-action-option ${active}"
//                 data-option-key="${key}"
//                 data-config='${JSON.stringify(option)}'
//             >
//                 <div class="d-flex align-items-center justify-content-between">
//                     <div class="d-flex align-items-center gap-3">
//                         <div class="wizard-option-icon bg-${option.color}-subtle text-${option.color}">
//                             <i class="ti tabler-${option.icon}"></i>
//                         </div>
//                         <div>
//                             <div class="fw-bold">
//                                 ${option.label}
//                             </div>
//                         </div>
//                     </div>
//                     <i class="ti tabler-chevron-right"></i>
//                 </div>
//             </div>
//         `;
//     });

//     $('#wizardCallResultContainer').html(html);
// }

// $(document).on('click', '.call-result-action-option', function() {
//     $('.call-result-action-option').removeClass('active');
//     $(this).addClass('active');

//     let config = JSON.parse($(this).attr('data-config'));
//     renderDynamicActivityFields({
//         activity: config,
//         due_days: config.due_days || 0,
//         move_to_status: config.move_to_status || null
//     });

//     syncActivityWizard(4);
// });

// function saveCallResultWizard() {
//     let selectedConfig = JSON.parse(
//         $('.call-result-action-option.active')
//             .attr('data-config') || '{}'
//     );

//     if (selectedConfig.check_contact === 'check_contact') {
//         Swal.fire({
//             icon: 'warning',
//             title: 'Please Check Contact Number',
//             text: 'The contact number is missing or invalid. Please verify and update the contact details before proceeding.',
//             confirmButtonText: 'OK',
//             confirmButtonColor: '#f59e0b'
//         });
//         return; 
//     }

//     let payload = {
//         _token: $('meta[name="csrf-token"]').attr('content'),
//         pipeline_id: $('#wizardPipelineId').val(),
//         call_result: $('#wizardSelectedCallResult').val(),
//         activity_type: selectedConfig.type || null,
//         call_type_slug: $('#activityCallType').val(),
//         due_date: $('#activityDueDate').val(),
//         due_time: $('#activityDueTime').val(),
//         move_to_status: selectedConfig.move_to_status || null,
//         email_notes: $('#activityEmailNotes').val()
//     };

//     $.ajax({
//         url: window.storeCallResultWizardUrl,
//         type: 'POST',
//         data: payload,
//         beforeSend: function() {
//             $('#activityWizardNextBtn')
//                 .prop('disabled', true)
//                 .html('Saving...');
//         },
//         success: function(res) {
//             showWizardResult(
//                 'success',
//                 res.title || 'Saved',
//                 res.message || 'Call follow-up saved'
//             );
//              const pipelineId = $('#wizardPipelineId').val();
            
//             setTimeout(function() {
//                 const modalElement = document.getElementById('activityWizardModal');
//                 if (modalElement) {
//                     const modal = bootstrap.Modal.getInstance(modalElement);
//                     if (modal) {
//                         modal.hide();
//                     }
//                 }
                
//                 setTimeout(function() {
//                     const offcanvasElement = document.getElementById('viewPipelineCanvas');
//                     if (offcanvasElement) {
//                         const existingOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
//                         if (existingOffcanvas) {
//                             existingOffcanvas.hide();
//                         }
//                     }
                    
//                         if (typeof viewRecord === 'function') {
//                             viewRecord(pipelineId);
//                         }
//                 }, 300);
//             }, 5000);
//         },
//         error: function(xhr) {
//             showWizardResult(
//                 'error',
//                 'Failed',
//                 xhr.responseJSON?.message || 'Something went wrong'
//             );
//         },
//         complete: function() {
//         $('#activityWizardNextBtn')
//             .prop('disabled', false)
//             .html(`
//                 Save
//                 <i class="ti tabler-device-floppy ms-1"></i>
//             `);
//         }
//     });
// }
