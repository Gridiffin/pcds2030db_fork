document.addEventListener("DOMContentLoaded",function(){const c=document.getElementById("addSubmissionForm");if(!c)return;const f=document.getElementById("period_id"),r=document.getElementById("targets-container"),p=document.getElementById("add-target-btn"),g=c.dataset.programNumber||"";f&&Array.from(f.options).forEach(e=>{e.dataset.status==="open"&&e.classList.add("text-success","fw-bold")});let m=0;const v=()=>{m++;const e=document.createElement("div");e.className="target-entry border rounded p-2 mb-2 position-relative",e.innerHTML=`
            <button type="button" class="btn-close remove-target" aria-label="Remove target" style="position: absolute; top: 5px; right: 5px;"></button>
            <div class="mb-2">
                <label class="form-label small">Target ${m}</label>
                <textarea class="form-control form-control-sm" name="target_text[]" rows="2" placeholder="Define a measurable target" required></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small">Target Number</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">${g}.</span>
                        <input type="number" min="1" class="form-control form-control-sm target-counter-input" 
                               name="target_counter[]" placeholder="Counter (e.g., 1)">
                    </div>
                    <input type="hidden" name="target_number[]" value="">
                </div>
                <div class="col-6">
                    <label class="form-label small">Status Indicator</label>
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1" placeholder="Achievements/Status"></textarea>
            </div>
        `,r&&r.appendChild(e),e.querySelector(".remove-target").addEventListener("click",()=>{e.remove(),N()});const n=e.querySelector(".target-counter-input");n.addEventListener("blur",()=>{b(n)})},N=()=>{if(!r)return;const e=r.querySelectorAll(".target-entry");e.forEach((a,n)=>{const t=a.querySelector("label");t&&(t.textContent=`Target ${n+1}`)}),m=e.length},b=e=>{const a=e.value.trim(),n=e.closest(".target-entry"),t=n.querySelector('input[name="target_number[]"]');e.classList.remove("is-valid","is-invalid");const l=n.querySelector(".invalid-feedback");if(l&&l.remove(),a==="")return t.value="",!0;const E=parseInt(a,10);if(isNaN(E)||E<1){e.classList.add("is-invalid");const d=document.createElement("div");return d.className="invalid-feedback",d.textContent="Please enter a positive number",e.parentNode.appendChild(d),!1}if(r){const d=r.querySelectorAll(".target-counter-input");let L=0;if(d.forEach(i=>{i!==e&&i.value.trim()===a&&L++}),L>0){e.classList.add("is-invalid");const i=document.createElement("div");return i.className="invalid-feedback",i.textContent="This target number is already used",e.parentNode.appendChild(i),!1}}const C=`${g}.${a}`;return t.value=C,e.classList.add("is-valid"),!0};p&&p.addEventListener("click",v),r&&v();const h=document.getElementById("add-attachment-btn"),s=document.getElementById("attachments"),u=document.getElementById("attachments-list");let o=[];function y(){u&&(u.innerHTML="",o.forEach((e,a)=>{const n=document.createElement("li");n.className="d-flex align-items-center justify-content-between mb-1",n.innerHTML=`<span>${e.name}</span>`;const t=document.createElement("button");t.type="button",t.className="btn btn-sm btn-link text-danger p-0 ms-2",t.innerHTML='<i class="fas fa-times"></i>',t.addEventListener("click",function(){o.splice(a,1),y()}),n.appendChild(t),u.appendChild(n)}))}h&&s&&(h.addEventListener("click",function(){s.value="",s.click()}),s.addEventListener("change",function(){s.files.length>0&&(Array.from(s.files).forEach(e=>{o.some(a=>a.name===e.name&&a.size===e.size&&a.lastModified===e.lastModified)||o.push(e)}),y())})),c.addEventListener("submit",function(e){if(r){const a=r.querySelectorAll(".target-counter-input");let n=!1;if(a.forEach(t=>{b(t)||(n=!0)}),n){e.preventDefault(),typeof showToast=="function"&&showToast("Error","Please fix the target number validation errors before submitting.","danger");return}}if(o.length>0){c.querySelectorAll('input[type="file"][name="attachments[]"]').forEach(l=>l.remove());const n=new DataTransfer;o.forEach(l=>n.items.add(l));const t=document.createElement("input");t.type="file",t.name="attachments[]",t.multiple=!0,t.className="d-none",t.files=n.files,c.appendChild(t)}})});
