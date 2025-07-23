class c{constructor(){this.programId=window.programId,this.isOwner=window.isOwner,this.currentUser=window.currentUser,this.APP_URL=window.APP_URL,this.init()}init(){this.bindEvents(),this.initializeComponents(),this.loadAdditionalData(),this.loadProgramStatus(),document.getElementById("edit-status-btn")?this.enableStatusEditing=!0:this.enableStatusEditing=!1}loadProgramStatus(){const t=this;fetch(`${this.APP_URL}/app/api/program_status.php?action=status&program_id=${this.programId}`).then(e=>e.json()).then(e=>{t.renderStatus(e)})}renderStatus(t){const e=document.getElementById("program-status-badge"),s=document.getElementById("hold-point-info"),a=document.getElementById("holdPointManagementSection");if(!e)return;let o=t.status||"active",i=o.charAt(0).toUpperCase()+o.slice(1).replace(/_/g," ");e.textContent=i;const n=this.getStatusInfo(o);e.className=`badge status-badge ${n.class} py-2 px-3`,e.innerHTML=`<i class="${n.icon} me-1"></i> ${i}`,o==="on_hold"&&t.hold_point?(s&&(s.innerHTML=`<i class='fas fa-pause-circle text-warning'></i> On Hold: <b>${t.hold_point.reason||""}</b> <span class='text-muted'>(${this.formatDate(t.hold_point.created_at)})</span> <span>${t.hold_point.remarks?" - "+t.hold_point.remarks:""}</span>`),a&&(a.style.display="block",document.getElementById("hold_reason").value=t.hold_point.reason||"",document.getElementById("hold_remarks").value=t.hold_point.remarks||"")):(s&&(s.innerHTML=""),a&&(a.style.display="none"))}bindEvents(){this.animateProgressBars(),this.bindTimelineEvents(),this.bindAttachmentEvents(),this.bindQuickActionEvents(),this.bindResponsiveEvents();const t=document.getElementById("edit-status-btn"),e=document.getElementById("view-status-history-btn");t&&this.enableStatusEditing&&t.addEventListener("click",()=>this.openEditStatusModal()),e&&e.addEventListener("click",()=>this.openStatusHistoryModal());const s=document.getElementById("updateHoldPointBtn");s&&s.addEventListener("click",()=>this.updateHoldPoint());const a=document.getElementById("endHoldPointBtn");a&&a.addEventListener("click",()=>this.endHoldPoint()),document.addEventListener("click",o=>{const i=o.target.closest(".delete-submission-btn");if(i){const n=i.getAttribute("data-submission-id");this.handleDeleteSubmission(n,i)}}),document.addEventListener("click",o=>{const i=o.target.closest(".submission-option");i&&this.handleSubmissionSelection(i)})}handleDeleteSubmission(t,e){t&&confirm("Are you sure you want to delete this submission? This action cannot be undone.")&&(e.disabled=!0,e.innerHTML='<i class="fas fa-spinner fa-spin"></i> Deleting...',fetch(`${this.APP_URL}/app/api/program_submissions.php`,{method:"DELETE",headers:{"Content-Type":"application/json"},body:JSON.stringify({submission_id:t})}).then(s=>s.json()).then(s=>{if(s.success){this.showToast("Deleted","Submission deleted successfully.","success");const a=e.closest(".modal");a&&typeof bootstrap<"u"&&bootstrap.Modal.getInstance(a)&&bootstrap.Modal.getInstance(a).hide(),setTimeout(()=>window.location.reload(),1200)}else this.showToast("Error",s.error||"Failed to delete submission.","danger"),e.disabled=!1,e.innerHTML='<i class="fas fa-trash"></i> Delete Submission'}).catch(()=>{this.showToast("Error","Failed to delete submission.","danger"),e.disabled=!1,e.innerHTML='<i class="fas fa-trash"></i> Delete Submission'}))}handleSubmissionSelection(t){const e=t.getAttribute("data-submission-id"),s=t.getAttribute("data-period-id"),a=t.getAttribute("data-period-display"),o=t.getAttribute("data-is-draft")==="true",i=t.getAttribute("data-submission-date"),n=bootstrap.Modal.getInstance(document.getElementById("selectSubmissionModal"));n&&n.hide(),this.loadSubmissionDetails(e,s,a,o,i)}loadSubmissionDetails(t,e,s,a,o){new bootstrap.Modal(document.getElementById("viewSubmissionModal")).show(),document.getElementById("viewSubmissionModalLabel").textContent=`${s} - Submission Details`;const n=document.getElementById("viewSubmissionModalBody");n.innerHTML=`
      <div class="text-center p-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading submission details...</p>
      </div>
    `,setTimeout(()=>{this.renderSubmissionDetails({rating:"in_progress",targets:[]},s,a,o,e)},500)}renderSubmissionDetails(t,e,s,a,o){const i=document.getElementById("viewSubmissionModalBody"),n=a?new Date(a).toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"}):"Not submitted";i.innerHTML=`
      <div class="row">
        <div class="col-md-6">
          <h6>Submission Information</h6>
          <table class="table table-sm">
            <tr>
              <td><strong>Period:</strong></td>
              <td>${e}</td>
            </tr>
            <tr>
              <td><strong>Status:</strong></td>
              <td>
                <span class="badge bg-${s?"warning":"success"}">
                  ${s?"Draft":"Finalized"}
                </span>
              </td>
            </tr>
            <tr>
              <td><strong>Submitted:</strong></td>
              <td>${n}</td>
            </tr>
            <tr>
              <td><strong>Rating:</strong></td>
              <td>
                <span class="badge rating-${(t.rating||"not_started").replace(/[^a-z0-9]/gi,"_").toLowerCase()}">
                  ${this.formatRating(t.rating||"not_started")}
                </span>
              </td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <h6>Quick Actions</h6>
          <div class="d-grid gap-2">
            <a href="view_submissions.php?program_id=${this.programId}&period_id=${o}" 
               class="btn btn-primary">
              <i class="fas fa-eye me-2"></i>View Full Details
            </a>
            ${s&&this.canEdit?`
            <a href="edit_submission.php?program_id=${this.programId}&period_id=${o}" 
               class="btn btn-warning">
              <i class="fas fa-edit me-2"></i>Edit Draft
            </a>
            `:""}
          </div>
        </div>
      </div>
      
      <div class="mt-3">
        <p class="text-muted">
          <i class="fas fa-info-circle me-1"></i>
          Click "View Full Details" to see complete submission information including targets and achievements.
        </p>
      </div>
    `}formatRating(t){return t.replace(/[_-]/g," ").replace(/\b\w/g,e=>e.toUpperCase())}initializeComponents(){this.initTooltips(),this.initAnimations(),this.initCharts()}loadAdditionalData(){this.loadProgramStats(),this.loadTargetProgress()}animateProgressBars(){const t=document.querySelectorAll(".progress-bar"),e=new IntersectionObserver(s=>{s.forEach(a=>{if(a.isIntersecting){const o=a.target,i=o.style.width;o.style.width="0%",setTimeout(()=>{o.style.transition="width 1s ease-in-out",o.style.width=i},100),e.unobserve(o)}})});t.forEach(s=>e.observe(s))}bindTimelineEvents(){document.querySelectorAll(".timeline-item").forEach(e=>{e.addEventListener("click",s=>{s.target.tagName==="A"||s.target.closest("a")||this.toggleTimelineDetails(e)}),e.addEventListener("mouseenter",()=>{e.classList.add("timeline-item-hover")}),e.addEventListener("mouseleave",()=>{e.classList.remove("timeline-item-hover")})})}toggleTimelineDetails(t){const e=t.querySelector(".timeline-content");t.classList.contains("expanded")?(t.classList.remove("expanded"),e.style.maxHeight="60px"):(t.classList.add("expanded"),e.style.maxHeight=e.scrollHeight+"px")}bindAttachmentEvents(){document.querySelectorAll(".attachment-item").forEach(e=>{const s=e.querySelector(".attachment-actions .btn");s&&s.addEventListener("click",a=>{a.preventDefault(),this.handleAttachmentDownload(s.href,e)})})}handleAttachmentDownload(t,e){const s=e.querySelector(".attachment-actions .btn"),a=s.innerHTML;s.innerHTML='<i class="fas fa-spinner fa-spin"></i>',s.disabled=!0,setTimeout(()=>{window.open(t,"_blank"),s.innerHTML=a,s.disabled=!1,this.showToast("Download Started","File download has been initiated.","success")},500)}bindQuickActionEvents(){document.querySelectorAll(".card-body .btn").forEach(e=>{e.addEventListener("click",s=>{e.classList.add("btn-clicked"),setTimeout(()=>{e.classList.remove("btn-clicked")},200)})})}bindResponsiveEvents(){const t=()=>{window.innerWidth<768?this.enableMobileView():this.enableDesktopView()};window.addEventListener("resize",t),t()}enableMobileView(){document.querySelectorAll(".card").forEach(e=>{e.classList.add("mobile-optimized")})}enableDesktopView(){document.querySelectorAll(".card").forEach(e=>{e.classList.remove("mobile-optimized")})}initTooltips(){typeof bootstrap<"u"&&bootstrap.Tooltip&&[].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function(e){return new bootstrap.Tooltip(e)})}initAnimations(){const t=document.querySelectorAll(".card"),e=new IntersectionObserver(s=>{s.forEach(a=>{a.isIntersecting&&a.target.classList.add("animate-in")})},{threshold:.1});t.forEach(s=>e.observe(s))}initCharts(){}loadProgramStats(){this.programId&&fetch(`${this.APP_URL}/app/ajax/get_program_stats.php?program_id=${this.programId}`).then(t=>t.json()).then(t=>{t.success&&this.updateProgramStats(t.stats)}).catch(t=>{console.error("Error loading program stats:",t)})}updateProgramStats(t){if(document.querySelectorAll(".stat-item .badge"),t.total_submissions!==void 0){const s=document.querySelector(".stat-item:first-child .badge");s&&(s.textContent=t.total_submissions)}t.completion_rate!==void 0&&document.querySelectorAll(".target-progress .progress-bar").forEach(a=>{a.style.width=`${t.completion_rate}%`});const e=document.getElementById("last-activity-value");if(e)if(t.last_activity_date){const s=new Date(t.last_activity_date);e.textContent=isNaN(s.getTime())?"Never":s.toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"})}else e.textContent="Never"}loadTargetProgress(){this.programId&&fetch(`${this.APP_URL}/app/ajax/get_target_progress.php?program_id=${this.programId}`).then(t=>t.json()).then(t=>{t.success&&this.updateTargetProgress(t.progress)}).catch(t=>{console.error("Error loading target progress:",t)})}updateTargetProgress(t){t.forEach(e=>{const s=document.querySelector(`[data-target-id="${e.target_id}"]`);if(s){const a=s.querySelector(".progress-bar"),o=s.querySelector(".text-muted");a&&(a.style.width=`${e.percentage}%`),o&&(o.textContent=`${e.percentage}% Complete`)}})}showToast(t,e,s="info",a=5e3){typeof showToast=="function"&&showToast(t,e,s,a)}formatFileSize(t){if(t===0)return"0 Bytes";const e=1024,s=["Bytes","KB","MB","GB"],a=Math.floor(Math.log(t)/Math.log(e));return parseFloat((t/Math.pow(e,a)).toFixed(2))+" "+s[a]}formatDate(t){return new Date(t).toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"})}openEditStatusModal(){this.enableStatusEditing&&fetch(`${this.APP_URL}/app/api/program_status.php?action=status&program_id=${this.programId}`).then(t=>t.json()).then(t=>{this.renderEditStatusForm(t),new bootstrap.Modal(document.getElementById("editStatusModal")).show()})}renderEditStatusForm(t){if(!this.enableStatusEditing)return;const e=document.getElementById("edit-status-modal-body");if(!e)return;let s=t.status||"active",a=t.hold_point||{},i=`<form id='edit-status-form'>
            <div class='mb-3'>
                <label for='status-select' class='form-label'>Status</label>
                <select class='form-select' id='status-select' name='status'>
                    ${[{value:"active",label:"Active"},{value:"on_hold",label:"On Hold"},{value:"completed",label:"Completed"},{value:"delayed",label:"Delayed"},{value:"cancelled",label:"Cancelled"}].map(r=>`<option value='${r.value}' ${r.value===s?"selected":""}>${r.label}</option>`).join("")}
                </select>
            </div>
            <div class='mb-3'>
                <label for='status-remarks' class='form-label'>Remarks (optional)</label>
                <textarea class='form-control' id='status-remarks' name='remarks' rows='2'></textarea>
            </div>`;(s==="on_hold"||a)&&(i+=`<div id='hold-point-fields'>
                <div class='mb-3'>
                    <label for='hold-reason' class='form-label'>Hold Reason</label>
                    <input type='text' class='form-control' id='hold-reason' name='reason' value='${a.reason||""}' required />
                </div>
                <div class='mb-3'>
                    <label for='hold-remarks' class='form-label'>Hold Remarks (optional)</label>
                    <textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'>${a.remarks||""}</textarea>
                </div>
            </div>`),i+=`<button type='submit' class='btn btn-primary'>Save</button>
        </form>`,e.innerHTML=i;const n=document.getElementById("status-select");n.addEventListener("change",r=>{const d=document.getElementById("hold-point-fields");if(r.target.value==="on_hold")if(d)d.style.display="";else{const l=document.createElement("div");l.id="hold-point-fields",l.innerHTML="<div class='mb-3'><label for='hold-reason' class='form-label'>Hold Reason</label><input type='text' class='form-control' id='hold-reason' name='reason' required /></div><div class='mb-3'><label for='hold-remarks' class='form-label'>Hold Remarks (optional)</label><textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'></textarea></div>",n.parentNode.parentNode.appendChild(l)}else d&&(d.style.display="none")}),document.getElementById("edit-status-form").addEventListener("submit",r=>{r.preventDefault(),this.submitStatusForm()})}submitStatusForm(){if(!this.enableStatusEditing)return;const t=document.getElementById("edit-status-form"),e=new FormData(t);e.append("action","set_status"),e.append("program_id",this.programId),fetch(`${this.APP_URL}/app/api/program_status.php`,{method:"POST",body:e}).then(s=>s.json()).then(s=>{s.success?(this.loadProgramStatus(),bootstrap.Modal.getInstance(document.getElementById("editStatusModal")).hide(),this.showToast("Status Updated","Program status updated successfully.","success")):this.showToast("Error",s.error||"Failed to update status.","danger")})}openStatusHistoryModal(){fetch(`${this.APP_URL}/app/api/program_status.php?action=status_history&program_id=${this.programId}`).then(t=>t.json()).then(t=>{this.renderStatusHistory(t),new bootstrap.Modal(document.getElementById("statusHistoryModal")).show()})}renderStatusHistory(t){const e=document.getElementById("status-history-modal-body");if(!e)return;let s='<h6>Status Changes</h6><ul class="list-group mb-3">';(t.status_history||[]).forEach(a=>{s+=`<li class="list-group-item"><b>${a.status}</b> by User #${a.changed_by} <span class="text-muted">(${this.formatDate(a.changed_at)})</span> ${a.remarks?" - "+a.remarks:""}</li>`}),s+='</ul><h6>Hold Points</h6><ul class="list-group">',(t.hold_points||[]).forEach(a=>{s+=`<li class="list-group-item"><i class='fas fa-pause-circle text-warning'></i> <b>${a.reason}</b> (${this.formatDate(a.created_at)})${a.ended_at?" - Ended: "+this.formatDate(a.ended_at):""} ${a.remarks?" - "+a.remarks:""}</li>`}),s+="</ul>",e.innerHTML=s}}document.addEventListener("DOMContentLoaded",function(){new c});
