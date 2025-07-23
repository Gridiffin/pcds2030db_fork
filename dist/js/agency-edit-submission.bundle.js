document.addEventListener("DOMContentLoaded",function(){P(),D();const t=new URLSearchParams(window.location.search).get("period_id");t&&(document.getElementById("period_selector").value=t,T(t))});function P(){const e=document.getElementById("period_selector");e&&e.addEventListener("change",function(){const t=this.value;t?T(t):q()})}function D(){document.addEventListener("click",function(t){if(t.target.id==="add-new-submission-btn"&&H(),t.target.id==="add-target-btn"&&C(),t.target.classList.contains("remove-target-btn")||t.target.closest(".remove-target-btn")){t.preventDefault(),t.stopPropagation();const s=t.target.classList.contains("remove-target-btn")?t.target:t.target.closest(".remove-target-btn");O(s)}t.target.id==="add-attachment-btn"&&document.getElementById("attachments").click()}),document.addEventListener("click",function(t){if(t.target.classList.contains("remove-attachment-btn")||t.target.closest(".remove-attachment-btn")){t.preventDefault();const s=t.target.classList.contains("remove-attachment-btn")?t.target:t.target.closest(".remove-attachment-btn"),a=s.getAttribute("data-attachment-id");a&&(s.disabled=!0,s.innerHTML='<i class="fas fa-spinner fa-spin"></i>',fetch(`${window.APP_URL}/app/ajax/delete_program_attachment.php`,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:`attachment_id=${encodeURIComponent(a)}`}).then(n=>n.json()).then(n=>{if(n.success){showToast("Success","Attachment deleted successfully","success"),E();const o=document.querySelector('input[name="period_id"]').value;T(o)}else showToast("Error",n.error||"Failed to delete attachment","danger"),s.disabled=!1,s.innerHTML='<i class="fas fa-trash"></i>'}).catch(n=>{showToast("Error","Failed to delete attachment","danger"),s.disabled=!1,s.innerHTML='<i class="fas fa-trash"></i>'}))}}),document.addEventListener("change",function(t){t.target.id==="attachments"&&Y(t.target.files)});let e=null;document.addEventListener("click",function(t){t.target.type==="submit"&&t.target.form&&t.target.form.id==="submission-form"&&(e=t.target)}),document.addEventListener("submit",function(t){if(t.target.id==="submission-form"){t.preventDefault();let s=t.submitter||e;K(t.target,s)}})}function T(e){document.getElementById("dynamic-content"),E(),B(e);const t=new FormData;t.append("program_id",window.programId),t.append("period_id",e),fetch(`${window.APP_URL}/app/ajax/get_submission_by_period.php`,{method:"POST",body:t}).then(s=>s.json()).then(s=>{s.success?s.has_submission?N(s):F(s):I("Failed to load submission data: "+(s.error||"Unknown error"))}).catch(s=>{console.error("Error:",s),I("An error occurred while loading submission data.")})}function E(){const e=document.getElementById("dynamic-content"),t=document.getElementById("loading-template");e.innerHTML=t.innerHTML}function B(e){const s=document.getElementById("period_selector").querySelector(`option[value="${e}"]`),a=document.querySelector(".period-status-display");if(s&&a){const n=s.getAttribute("data-has-submission")==="true",o=s.getAttribute("data-status");let l="";if(o==="open"?l='<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Open</span>':l='<span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Closed</span>',n){const i=s.textContent.includes("Draft")?"Draft":"Finalized";l+=` <span class="badge bg-${i==="Draft"?"warning":"info"} ms-2"><i class="fas fa-file-alt me-1"></i>${i}</span>`}else l+=' <span class="badge bg-light text-dark ms-2"><i class="fas fa-plus me-1"></i>No Submission</span>';a.innerHTML=l}}function N(e){const t=document.getElementById("dynamic-content"),s=e.submission,a=e.period_info,n=e.attachments;window.targetIdToIndex={},Array.isArray(s.targets)&&s.targets.forEach((h,w)=>{h.target_id&&(window.targetIdToIndex[h.target_id]=w+1)});const o=window.currentUserRole==="focal",l=s.is_draft;let i="";o&&l&&(i=`
            <button type="submit" name="finalize_submission" value="1" class="btn btn-success ms-2">
                <i class="fas fa-lock me-2"></i> Finalize Submission
            </button>
        `);const c=`
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Submission - ${a.display_name}
                </h5>
                <div class="header-actions"></div>
            </div>
            <div class="card-body">
                <form id="submission-form" enctype="multipart/form-data">
                    <input type="hidden" name="program_id" value="${window.programId}">
                    <input type="hidden" name="period_id" value="${a.period_id}">
                    <input type="hidden" name="submission_id" value="${s.submission_id}">

                    <!-- Two-column area: Submission Info + Description -->
                    <div class="d-flex flex-row gap-4 info-description-row mb-4">
                        <div class="flex-shrink-0 info-section-card" style="width: 350px; max-width: 100%; min-width: 250px;">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Submission Info
                                    </h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-2">
                                            <i class="fas fa-calendar text-primary me-2"></i>
                                            Period: ${a.display_name}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-file-alt text-info me-2"></i>
                                            Status: ${s.is_draft?"Draft":"Finalized"}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-clock text-warning me-2"></i>
                                            Updated: ${S(s.updated_at)}
                                        </li>
                                        ${s.submission_date?`
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            Submitted: ${S(s.submission_date)}
                                        </li>
                                        `:""}
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 description-section-card">
                            <div class="mb-4 h-100 d-flex flex-column">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control flex-grow-1" id="description" name="description" rows="3"
                                          placeholder="Describe the submission for this period">${g(s.description)}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Section (full width) -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-2">
                                <i class="fas fa-paperclip me-2"></i>
                                Attachments
                            </h6>
                            <button type="button" id="add-attachment-btn" class="btn btn-outline-secondary btn-sm mb-2">
                                <i class="fas fa-plus me-1"></i> Add File(s)
                            </button>
                            <input type="file" class="form-control d-none" name="attachments[]" id="attachments" multiple>
                            <div class="form-text mt-1">
                                You can add files one by one or in batches.
                            </div>
                            <ul id="attachments-list" class="list-unstyled small mt-2">
                                ${U(n)}
                            </ul>
                        </div>
                    </div>

                    <!-- Two-column area: Targets + History Sidebar -->
                    <div class="d-flex flex-row gap-4 targets-history-row">
                        <div class="flex-grow-1 targets-section-card">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-bullseye me-2"></i>
                                        Targets
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="targets-container">
                                        ${j(s.targets)}
                                    </div>
                                    <button type="button" id="add-target-btn" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-plus-circle me-1"></i> Add Target
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div style="width: 350px; max-width: 100%;" id="history-sidebar-inside-card"></div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="view_programs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </a>
                        <div>
                            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary">
                                <i class="fas fa-save me-2"></i>
                                Save as Draft
                            </button>
                            
        <button type="submit" name="save_and_exit" value="1" class="btn btn-primary ms-2">
            <i class="fas fa-save me-2"></i> Save and Exit
        </button>
    
                            ${i}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;t.innerHTML=c;const u=document.querySelector(".col-md-8 .card.shadow-sm");u&&(u.style.maxHeight="600px",u.style.overflowY="auto"),s&&s.submission_id&&J(s.submission_id,"history-sidebar-inside-card")}function F(e){A(e.period_info.period_id).then(t=>{if(t.length>0)H();else{const s=document.getElementById("dynamic-content"),a=document.getElementById("no-submission-template");s.innerHTML=a.innerHTML;const n=document.getElementById("add-new-submission-btn");n&&n.setAttribute("data-period-id",e.period_info.period_id)}})}let b=[];function A(e){const t=new FormData;return t.append("program_id",window.programId),t.append("period_id",e),fetch(`${window.APP_URL}/app/ajax/get_incomplete_targets.php`,{method:"POST",body:t}).then(s=>s.json()).then(s=>(s.success?b=s.incomplete_targets||[]:(console.warn("Failed to fetch incomplete targets:",s.error),b=[]),b)).catch(s=>(console.error("Error fetching incomplete targets:",s),b=[],b))}function H(){const e=document.getElementById("period_selector").value;A(e).then(()=>{const s=document.getElementById("period_selector").querySelector(`option[value="${e}"]`),a=s?s.textContent.split(" - ")[0]:"Selected Period",n=`
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Add New Submission - ${a}
                    </h5>
                </div>
                <div class="card-body">
                    <form id="submission-form" enctype="multipart/form-data">
                        <input type="hidden" name="program_id" value="${window.programId}">
                        <input type="hidden" name="period_id" value="${e}">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                              placeholder="Describe the submission for this period"></textarea>
                                </div>
                                <!-- Targets Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-bullseye me-2"></i>
                                            Targets
                                            ${b.length>0?`<span class="badge bg-info ms-2">${b.length} auto-filled</span>`:""}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="targets-container">
                                            <!-- Targets will be added here -->
                                        </div>
                                        <button type="button" id="add-target-btn" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-plus-circle me-1"></i> Add Target
                                        </button>
                                    </div>
                                </div>
                                <!-- Rating and Remarks -->
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <label for="rating" class="form-label">Progress Rating</label>
                                        <select class="form-select" id="rating" name="rating">
                                            <option value="not-started">Not Started</option>
                                            <option value="on-track">On Track</option>
                                            <option value="on-track-yearly">On Track for Year</option>
                                            <option value="target-achieved">Target Achieved</option>
                                            <option value="delayed">Delayed</option>
                                            <option value="severe-delay">Severe Delays</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="remarks" class="form-label">Remarks</label>
                                        <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                                  placeholder="Additional remarks"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Submission Info -->
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Submission Info
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-plus text-primary me-2"></i>
                                                Creates a new submission for ${a}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-edit text-info me-2"></i>
                                                You can edit this submission later
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-paperclip text-warning me-2"></i>
                                                Add attachments after creating
                                            </li>
                                            <li>
                                                <i class="fas fa-save text-success me-2"></i>
                                                Save as draft or finalize when ready
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- Attachments Section -->
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">
                                            <i class="fas fa-paperclip me-2"></i>
                                            Attachments
                                        </h6>
                                        <button type="button" id="add-attachment-btn" class="btn btn-outline-secondary btn-sm mb-2">
                                            <i class="fas fa-plus me-1"></i> Add File(s)
                                        </button>
                                        <input type="file" class="form-control d-none" name="attachments[]" id="attachments" multiple>
                                        <div class="form-text mt-1">
                                            You can add files one by one or in batches.
                                        </div>
                                        <ul id="attachments-list" class="list-unstyled small mt-2"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="view_programs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <div>
                                <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Save as Draft
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;document.getElementById("dynamic-content").innerHTML=n,R()})}function R(){const e=document.getElementById("targets-container");e&&b.length!==0&&(e.innerHTML="",b.forEach((t,s)=>{C(t)}),b.length>0&&showToast("Info",`${b.length} incomplete targets from previous periods have been auto-filled. You can edit or remove them as needed.`,"info"))}function q(){const e=document.getElementById("dynamic-content");e.innerHTML=`
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted">Select a Reporting Period</h5>
            <p class="text-muted">Choose a reporting period from the dropdown above to view or edit submissions.</p>
        </div>
    `;const t=document.querySelector(".period-status-display");t&&(t.innerHTML="")}function j(e){if(!e||e.length===0)return'<div class="text-muted small mb-3">No targets added yet. Click "Add Target" to get started.</div>';let t="";const s=window.programNumber||"";return e.forEach((a,n)=>{const o=n+1,l=a.target_id?g(a.target_id):"";let i="";if(a.target_number){const r=a.target_number.match(/\.([^.]+)$/);i=r?r[1]:""}t+=`
            <div class="target-container card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2"></i>
                        Target #${o}
                    </h6>
                </div>
                <div class="card-body">
                    <input type="hidden" name="target_id[]" value="${l}">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small">Target Number</label>
                            <div class="input-group">
                                <span class="input-group-text">${s}.</span>
                                <input type="number" min="1" class="form-control form-control-sm target-counter-input" 
                                       name="target_counter[]" value="${g(i)}" placeholder="Counter (e.g., 1)">
                            </div>
                            <input type="hidden" name="target_number[]" value="${g(a.target_number||"")}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Target Description</label>
                            <textarea class="form-control" name="target_text[]" rows="2" required
                                      placeholder="Describe the target">${g(a.target_text||"")}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label small">Status Indicator</label>
                            <select class="form-select form-select-sm" name="target_status[]">
                                <option value="not_started" ${a.target_status==="not_started"?"selected":""}>Not Started</option>
                                <option value="in_progress" ${a.target_status==="in_progress"?"selected":""}>In Progress</option>
                                <option value="completed" ${a.target_status==="completed"?"selected":""}>Completed</option>
                                <option value="delayed" ${a.target_status==="delayed"?"selected":""}>Delayed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Remarks</label>
                            <textarea class="form-control form-control-sm" name="target_remarks[]" rows="2"
                                      placeholder="Additional remarks for this target">${g(a.remarks||"")}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label small">Achievements/Status</label>
                            <textarea class="form-control form-control-sm" name="target_status_description[]" rows="2"
                                      placeholder="Provide details about achievements and current status">${g(a.status_description||"")}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label small">Start Date</label>
                            <input type="date" class="form-control form-control-sm" name="target_start_date[]"
                                   value="${a.start_date||""}" placeholder="Select start date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">End Date</label>
                            <input type="date" class="form-control form-control-sm" name="target_end_date[]"
                                   value="${a.end_date||""}" placeholder="Select end date">
                        </div>
                    </div>
                </div>
            </div>
        `}),t}let _=[];function U(e){if(!e||e.length===0)return"";let t="";return e.forEach(s=>{const a=s.original_filename&&s.original_filename.trim()!==""?s.original_filename:"Unnamed file";t+=`
            <li class="mb-2 d-flex justify-content-between align-items-center attachment-item" data-attachment-id="${s.attachment_id}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file me-2 text-primary"></i>
                    <div>
                        <div class="fw-medium">${g(a)}</div>
                        <small class="text-muted">${s.file_size_formatted} • ${S(s.upload_date)}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="${window.APP_URL}/app/ajax/download_program_attachment.php?id=${s.attachment_id}" 
                       class="btn btn-sm btn-outline-primary me-1" target="_blank" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-attachment-btn" title="Remove" data-attachment-id="${s.attachment_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </li>
        `}),t}function L(){const e=document.getElementById("attachments-list");if(!e)return;e.querySelectorAll(".pending-attachment").forEach(n=>n.remove());const t=e.querySelector(".no-attachments-msg");t&&t.remove();const s=e.querySelectorAll(".attachment-item").length>0,a=_.length>0;if(!s&&!a){const n=document.createElement("li");n.className="text-muted no-attachments-msg",n.textContent="No attachments",e.appendChild(n)}_.forEach((n,o)=>{const l=document.createElement("li");l.className="mb-2 d-flex justify-content-between align-items-center pending-attachment",l.innerHTML=`
            <div class="d-flex align-items-center">
                <i class="fas fa-file me-2 text-primary"></i>
                <div>
                    <div class="fw-medium">${g(n.name)}</div>
                    <small class="text-muted">${G(n.size)} • Ready to upload</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-pending-file-btn" data-pending-idx="${o}" title="Remove">
                <i class="fas fa-trash"></i>
            </button>
        `,e.appendChild(l)})}document.addEventListener("click",function(e){if(e.target.classList.contains("remove-pending-file-btn")||e.target.closest(".remove-pending-file-btn")){const t=e.target.classList.contains("remove-pending-file-btn")?e.target:e.target.closest(".remove-pending-file-btn"),s=parseInt(t.getAttribute("data-pending-idx"),10);if(!isNaN(s)){_.splice(s,1),L();const a=document.getElementById("attachments");a&&(a.value="")}}});function C(e=null){const t=document.getElementById("targets-container");if(!t){console.error("Targets container not found");return}const a=t.querySelectorAll(".target-container").length+1;let n="",o="";if(e&&e.target_number){o=e.target_number;const i=e.target_number.match(/\.([^.]+)$/);n=i?i[1]:""}const l=document.createElement("div");l.className="target-container card shadow-sm mb-4",l.innerHTML=`
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-bullseye me-2"></i>
                Target #${a}
                ${e?'<span class="badge bg-info ms-2">Auto-filled</span>':""}
            </h6>
        </div>
        <div class="card-body">
            <input type="hidden" name="target_id[]" value="">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label small">Target Number</label>
                    <div class="input-group">
                        <span class="input-group-text">${programNumber}.</span>
                        <input type="number" min="1" class="form-control form-control-sm target-counter-input" 
                               name="target_counter[]" placeholder="Counter (e.g., 1)" value="${n}">
                    </div>
                    <input type="hidden" name="target_number[]" value="${o}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Target Description</label>
                    <textarea class="form-control" name="target_text[]" rows="2" required
                              placeholder="Describe the target">${e?g(e.target_text||""):""}</textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label small">Status Indicator</label>
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started" ${e&&e.target_status==="not_started"?"selected":""}>Not Started</option>
                        <option value="in_progress" ${e&&e.target_status==="in_progress"?"selected":""}>In Progress</option>
                        <option value="completed" ${e&&e.target_status==="completed"?"selected":""}>Completed</option>
                        <option value="delayed" ${e&&e.target_status==="delayed"?"selected":""}>Delayed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Remarks</label>
                    <textarea class="form-control form-control-sm" name="target_remarks[]" rows="2"
                              placeholder="Additional remarks for this target">${e?g(e.remarks||""):""}</textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="form-label small">Achievements/Status</label>
                    <textarea class="form-control form-control-sm" name="target_status_description[]" rows="2"
                              placeholder="Provide details about achievements and current status">${e?g(e.status_description||""):""}</textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label small">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="target_start_date[]"
                           placeholder="Select start date" value="${e&&e.start_date||""}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="target_end_date[]"
                           placeholder="Select end date" value="${e&&e.end_date||""}">
                </div>
            </div>
        </div>
    `,t.appendChild(l)}function O(e){const t=e.closest(".target-container");t?(e.disabled=!0,e.innerHTML='<i class="fas fa-spinner fa-spin"></i>',t.remove(),z()):console.error("Target container not found")}function z(){document.querySelectorAll(".target-container").forEach((t,s)=>{const a=s+1,n=t.querySelector(".card-title");n&&(n.innerHTML=`<i class="fas fa-bullseye me-2"></i>Target #${a}`)})}function Y(e){document.getElementById("attachments-list")&&(Array.from(e).forEach(s=>{_.push(s)}),L())}function K(e,t){const s=e.querySelectorAll(".target-container"),a=[],n=window.programNumber||"";s.forEach(i=>{const r=i.querySelector('[name="target_id[]"]'),c=i.querySelector('[name="target_counter[]"]'),u=i.querySelector('[name="target_text[]"]'),h=i.querySelector('[name="target_status[]"]'),w=i.querySelector('[name="target_status_description[]"]'),v=i.querySelector('[name="target_remarks[]"]'),m=i.querySelector('[name="target_start_date[]"]'),p=i.querySelector('[name="target_end_date[]"]');if(!u||!c){console.warn("Skipping target container with missing essential elements:",i);return}let f=r?r.value:"";f=f&&!isNaN(f)&&f!==""?parseInt(f,10):null;const x=c.value||"",d=x?`${n}.${x}`:"";a.push({target_id:f,target_number:d,target_text:u.value||"",target_status:h?h.value:"",status_description:w?w.value:"",remarks:v?v.value:"",start_date:m?m.value:"",end_date:p?p.value:""})});const o=new FormData(e);t&&t.name&&o.append(t.name,t.value),["target_id[]","target_number[]","target_text[]","target_status[]","target_status_description[]","target_remarks[]","target_start_date[]","target_end_date[]"].forEach(i=>o.delete(i)),_.forEach(i=>{o.append("attachments[]",i)}),o.append("targets_json",JSON.stringify(a));const l=e.querySelectorAll('button[type="submit"]');l.forEach(i=>{i.disabled=!0,i.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Processing...'}),fetch(`${window.APP_URL}/app/ajax/save_submission.php`,{method:"POST",body:o}).then(i=>i.json()).then(i=>{if(i.success){if(showToast("Success",i.message,"success"),V(),t&&t.name==="save_and_exit"){window.currentUserRole,window.location.href=window.APP_URL+"/app/views/admin/programs/programs.php";return}E(),setTimeout(()=>{T(o.get("period_id"))},1e3)}else showToast("Error",i.error||"An error occurred while saving the submission.","danger")}).catch(i=>{console.error("Error:",i),showToast("Error","An error occurred while saving the submission.","danger")}).finally(()=>{l.forEach(i=>{i.disabled=!1,i.name==="save_as_draft"?i.innerHTML='<i class="fas fa-save me-2"></i>Save as Draft':i.innerHTML='<i class="fas fa-check me-2"></i>Finalize Submission'}),_=[],L()})}function I(e){const t=document.getElementById("dynamic-content");t.innerHTML=`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${g(e)}
        </div>
    `}function g(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}function S(e){return e?new Date(e).toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric",hour:"2-digit",minute:"2-digit"}):"Not set"}function G(e){if(e===0)return"0 Bytes";const t=1024,s=["Bytes","KB","MB","GB"],a=Math.floor(Math.log(e)/Math.log(t));return parseFloat((e/Math.pow(t,a)).toFixed(2))+" "+s[a]}function V(){fetch(`${window.APP_URL}/app/ajax/get_reporting_periods.php?program_id=${window.programId}`).then(e=>e.json()).then(e=>{if(e.success){const t=document.getElementById("period_selector"),s=t.value;t.innerHTML='<option value="">Choose a reporting period...</option>',e.periods.forEach(a=>{let n=a.display_name;a.status==="open"&&(n+=" (Open)"),a.has_submission?n+=a.is_draft?" - Draft":" - Finalized":n+=" - No Submission",t.innerHTML+=`<option value="${a.period_id}" data-has-submission="${a.has_submission}" data-submission-id="${a.submission_id||""}" data-status="${a.status}">${n}</option>`}),t.value=s}})}function J(e,t="history-sidebar-container"){const s=document.getElementById(t);s&&(s.innerHTML='<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading change history...</p></div>',fetch(`${window.APP_URL}/app/ajax/get_submission_audit_history.php?submission_id=${e}`).then(a=>a.json()).then(a=>{if(!a.success||!a.data){s.innerHTML="<div class='alert alert-warning'>No change history found for this submission.</div>";return}let n=a.data.audit_history;if(!n||n.length===0){s.innerHTML="<div class='alert alert-info'>No changes have been made to this submission yet.</div>";return}const o=new Set;n.forEach(r=>{r.field_changes.forEach(c=>{o.add(c.field_name)})});const l=Array.from(o);let i="<div class='card shadow-sm'><div class='card-header d-flex justify-content-between align-items-center'><h6 class='mb-0'><i class='fas fa-history me-2'></i>View Field Change History</h6></div><div class='card-body' id='history-sidebar-body'>";i+="<div class='mb-3'><input type='text' class='form-control form-control-sm' id='history-field-search' placeholder='Search field...'></div>",i+="<ul class='list-group'>",l.forEach(r=>{const c=n.find(u=>u.field_changes.find(h=>h.field_name===r)).field_changes.find(u=>u.field_name===r).field_label||r;i+=`<li class='list-group-item list-group-item-action history-field-item' data-field='${r}'>
                    <i class='fas fa-angle-right me-2'></i>${c}
                </li>`}),i+="</ul></div></div>",s.innerHTML=i,document.getElementById("history-field-search").addEventListener("input",function(){const r=this.value.toLowerCase();document.querySelectorAll(".history-field-item").forEach(c=>{const u=c.textContent.toLowerCase().includes(r);c.style.display=u?"":"none"})}),document.querySelectorAll(".history-field-item").forEach(r=>{r.addEventListener("click",function(){const c=this.getAttribute("data-field");Q(e,c,t)})})}).catch(()=>{s.innerHTML="<div class='alert alert-danger'>Failed to load change history.</div>"}))}function Q(e,t,s,a){const n=document.getElementById(s);if(!n)return;n.innerHTML="<div class='text-center py-4'><div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div><p class='mt-3 text-muted'>Loading field history...</p></div>";const o=window.programId,l=document.querySelector('input[name="period_id"]').value;fetch(`${window.APP_URL}/app/ajax/get_field_history.php`,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:new URLSearchParams({program_id:o,period_id:l,field_name:t,offset:0,limit:20})}).then(i=>i.json()).then(i=>{if(i.error){n.innerHTML=`<div class='alert alert-danger'>${i.error}</div>`;return}if(!i.history||i.history.length===0){n.innerHTML=`
                <div class='text-center py-4'>
                    <i class='fas fa-history text-muted' style='font-size: 2rem;'></i>
                    <p class='mt-3 text-muted'>No history found for this field.</p>
                </div>
            `;return}const r={};i.history.forEach(v=>{const m=v.target_id||"no_target";r[m]||(r[m]=[]),r[m].push(v)});let c=`
            <div class='history-header mb-3'>
                <div class='d-flex justify-content-between align-items-center'>
                    <h6 class='mb-0'><i class='fas fa-history'></i> Field History: ${ee(t)}</h6>
                    <button class='btn btn-sm btn-outline-secondary back-to-fields-btn' onclick="renderHistorySidebar(${e}, '${s}')">
                        <i class='fas fa-arrow-left'></i> Back to Fields
                    </button>
                </div>
                <small class='text-muted'>Showing ${i.history.length} of ${i.total_count||i.history.length} changes</small>
            </div>
        `;const u=Object.keys(r).sort((v,m)=>{if(v==="no_target")return 1;if(m==="no_target")return-1;const p=r[v],f=r[m],x=Math.max(...p.map(y=>new Date(y.submitted_at).getTime()));return Math.max(...f.map(y=>new Date(y.submitted_at).getTime()))-x}),h=3;u.slice(0,h).forEach(v=>{const m=r[v],p=m[0];let f="Unknown Target";if(v==="no_target")f="General Changes";else if(window.targetIdToIndex&&p.target_id){const d=window.targetIdToIndex[p.target_id];d?f=`Target #${d}`:p.target_number&&p.target_number.trim()!==""?f=`Target #${p.target_number}`:f=`Target (ID: ${p.target_id})`}else p.target_number&&p.target_number.trim()!==""?f=`Target #${p.target_number}`:f=`Target (ID: ${p.target_id})`;c+=`
                <div class='target-history-group mb-3'>
                    <div class='target-header d-flex align-items-center mb-2'>
                        <span class='badge bg-primary me-2'>${f}</span>
                        <small class='text-muted'>${m.length} change${m.length>1?"s":""}</small>
                    </div>
                    <div class='target-changes'>
            `,m.sort((d,y)=>new Date(y.submitted_at)-new Date(d.submitted_at)),m.slice(0,5).forEach(d=>{const y=W(d.change_type),k=X(d.change_type),M=Z(d.change_type);let $="";d.change_type==="modified"?$=`
                        <div class='change-values'>
                            <div class='old-value'><strong>From:</strong> ${d.old_value||"<em>empty</em>"}</div>
                            <div class='new-value'><strong>To:</strong> ${d.new_value||"<em>empty</em>"}</div>
                        </div>
                    `:d.change_type==="added"?$=`<div class='new-value'><strong>Added:</strong> ${d.new_value||"<em>empty</em>"}</div>`:d.change_type==="removed"&&($=`<div class='old-value'><strong>Removed:</strong> ${d.old_value||"<em>empty</em>"}</div>`),c+=`
                    <div class='change-entry mb-2 p-2 border-start border-3 ${y}'>
                        <div class='change-header d-flex justify-content-between align-items-start'>
                            <div class='change-type'>
                                <i class='${k}'></i>
                                <span class='ms-1'>${M}</span>
                            </div>
                            <div class='change-meta text-end'>
                                <div class='change-date small'>${d.formatted_date}</div>
                                <div class='change-user small text-muted'>by ${d.submitted_by}</div>
                                ${d.is_draft?'<div class="draft-badge small text-warning">Draft</div>':""}
                            </div>
                        </div>
                        ${$}
                    </div>
                `}),m.length>5&&(c+=`
                    <div class='text-center mt-2'>
                        <small class='text-muted'>+${m.length-5} more changes</small>
                    </div>
                `),c+=`
                    </div>
                </div>
            `}),u.length>h&&(c+=`
                <div class='text-center mt-3'>
                    <small class='text-muted'>+${u.length-h} more targets</small>
                </div>
            `),n.innerHTML=c}).catch(i=>{console.error("Error loading field history:",i),n.innerHTML=`<div class='alert alert-danger'>Error loading history: ${i.message}</div>`})}function W(e){switch(e){case"added":return"border-success bg-light";case"modified":return"border-warning bg-light";case"removed":return"border-danger bg-light";default:return"border-secondary bg-light"}}function X(e){switch(e){case"added":return"fas fa-plus-circle text-success";case"modified":return"fas fa-edit text-warning";case"removed":return"fas fa-minus-circle text-danger";default:return"fas fa-circle text-secondary"}}function Z(e){switch(e){case"added":return"Added";case"modified":return"Modified";case"removed":return"Removed";default:return"Changed"}}function ee(e){return{target_number:"Target Number",target_description:"Target Description",status_indicator:"Status Indicator",status_description:"Achievements/Status",remarks:"Remarks",start_date:"Start Date",end_date:"End Date",description:"Description"}[e]||e.replace(/_/g," ").replace(/\b\w/g,s=>s.toUpperCase())}
