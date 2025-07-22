const y="modulepreload",v=function(n){return"/"+n},h={},f=function(e,t,r){let s=Promise.resolve();if(t&&t.length>0){let u=function(o){return Promise.all(o.map(d=>Promise.resolve(d).then(p=>({status:"fulfilled",value:p}),p=>({status:"rejected",reason:p}))))};var R=u;document.getElementsByTagName("link");const l=document.querySelector("meta[property=csp-nonce]"),i=l?.nonce||l?.getAttribute("nonce");s=u(t.map(o=>{if(o=v(o),o in h)return;h[o]=!0;const d=o.endsWith(".css"),p=d?'[rel="stylesheet"]':"";if(document.querySelector(`link[href="${o}"]${p}`))return;const a=document.createElement("link");if(a.rel=d?"stylesheet":y,d||(a.as="script"),a.crossOrigin="",a.href=o,i&&a.setAttribute("nonce",i),document.head.appendChild(a),d)return new Promise((w,m)=>{a.addEventListener("load",w),a.addEventListener("error",()=>m(new Error(`Unable to preload CSS for ${o}`)))})}))}function c(l){const i=new Event("vite:preloadError",{cancelable:!0});if(i.payload=l,window.dispatchEvent(i),!i.defaultPrevented)throw l}return s.then(l=>{for(const i of l||[])i.status==="rejected"&&c(i.reason);return e().catch(c)})};class g{constructor(e,t){this.logic=e,this.ajax=t,this.currentPeriod=null,this.reports=[]}init(){this.attachEventListeners(),this.initializeFilters(),this.loadInitialData()}attachEventListeners(){const e=document.querySelector("#period-filter-form");e&&e.addEventListener("submit",s=>{s.preventDefault(),this.handlePeriodFilter()});const t=document.querySelector("#period_id");t&&t.addEventListener("change",()=>{this.handlePeriodChange()});const r=document.querySelector(".clear-filter-btn");r&&r.addEventListener("click",()=>{this.clearFilters()}),this.attachDownloadListeners(),this.attachViewListeners()}initializeFilters(){const t=new URLSearchParams(window.location.search).get("period_id");if(t){const r=document.querySelector("#period_id");r&&(r.value=t,this.currentPeriod=parseInt(t))}}async loadInitialData(){this.currentPeriod&&await this.loadReportsForPeriod(this.currentPeriod)}async handlePeriodFilter(){const e=document.querySelector("#period_id"),t=e?parseInt(e.value):null;if(!this.logic.validatePeriodSelection(t)){this.showError("Please select a valid reporting period.");return}const r=new URL(window.location);r.searchParams.set("period_id",t),window.history.pushState({},"",r),this.currentPeriod=t,await this.loadReportsForPeriod(t)}handlePeriodChange(){const e=document.querySelector("#period_id"),t=e?parseInt(e.value):null,r=document.querySelector(".filter-btn");r&&(r.disabled=!this.logic.validatePeriodSelection(t))}clearFilters(){const e=document.querySelector("#period_id");e&&(e.value="");const t=new URL(window.location);t.searchParams.delete("period_id"),window.history.pushState({},"",t),this.currentPeriod=null,this.clearReportsList(),this.showSelectPeriodMessage()}async loadReportsForPeriod(e){try{const t=document.querySelector("#reports-container");t&&this.ajax.showLoading(t);const r=await this.ajax.loadReportsForPeriod(e);this.reports=r.reports||[],this.renderReportsList(),this.updateReportsCount()}catch(t){this.ajax.handleError(t,"load reports"),this.showError("Failed to load reports. Please try again.")}}renderReportsList(){const e=document.querySelector("#reports-container");if(!e)return;if(this.reports.length===0){this.showEmptyState();return}const t=this.reports.map(r=>this.renderReportRow(r)).join("");e.innerHTML=`
            <div class="card reports-list">
                <div class="card-header">
                    <h5 class="card-title">Available Reports</h5>
                    <span class="reports-count-badge">${this.reports.length} Reports</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table reports-table">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${t}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `,this.attachDownloadListeners(),this.attachViewListeners()}renderReportRow(e){const t=this.logic.getReportTypeBadgeClass(e.report_type),r=this.logic.generateDownloadUrl(e.file_path),s=this.logic.formatDate(e.generated_at),c=this.logic.isRecentReport(e.generated_at);return`
            <tr ${c?'class="table-warning"':""}>
                <td>
                    <div class="report-name">${e.report_name||"Untitled Report"}</div>
                    ${c?'<small class="text-warning"><i class="fas fa-star"></i> New</small>':""}
                </td>
                <td>${this.logic.truncateText(e.description||"No description available",80)}</td>
                <td>
                    <span class="badge ${t} report-type-badge">
                        ${(e.report_type||"general").charAt(0).toUpperCase()+(e.report_type||"general").slice(1)}
                    </span>
                </td>
                <td>${s}</td>
                <td>
                    <div class="reports-actions">
                        <a href="${r}" class="btn btn-outline-primary btn-sm view-report-btn" 
                           target="_blank" title="View Report">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-outline-success btn-sm download-report-btn" 
                                data-report-id="${e.report_id}" 
                                data-file-type="${e.file_type||"pdf"}"
                                title="Download Report">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `}showEmptyState(){const e=document.querySelector("#reports-container");e&&(e.innerHTML=`
                <div class="alert alert-info reports-alert">
                    <i class="fas fa-info-circle"></i>
                    No reports found for the selected reporting period.
                </div>
            `)}showSelectPeriodMessage(){const e=document.querySelector("#reports-container");e&&(e.innerHTML=`
                <div class="alert alert-info reports-alert">
                    <i class="fas fa-info-circle"></i>
                    Please select a reporting period to view available reports.
                </div>
            `)}updateReportsCount(){const e=document.querySelector(".reports-count-badge");e&&(e.textContent=`${this.reports.length} Reports`)}attachDownloadListeners(){document.querySelectorAll(".download-report-btn").forEach(e=>{e.addEventListener("click",async t=>{t.preventDefault();const r=e.dataset.reportId,s=e.dataset.fileType||"pdf";try{e.disabled=!0,e.innerHTML='<i class="fas fa-spinner fa-spin"></i>',await this.ajax.downloadReport(r,s),typeof window.showToast=="function"&&window.showToast("Success","Report downloaded successfully","success")}catch(c){this.ajax.handleError(c,"download report")}finally{e.disabled=!1,e.innerHTML='<i class="fas fa-download"></i>'}})})}attachViewListeners(){document.querySelectorAll(".view-report-btn").forEach(e=>{e.addEventListener("click",t=>{typeof gtag<"u"&&gtag("event","view_report",{report_type:"agency_report"})})})}showError(e){typeof window.showToast=="function"&&window.showToast("Error",e,"danger")}clearReportsList(){const e=document.querySelector("#reports-container");e&&(e.innerHTML="")}}document.addEventListener("DOMContentLoaded",function(){(document.querySelector("#reports-container")||document.querySelector(".reports-page"))&&f(async()=>{const{ReportsLogic:n}=await import("../assets/logic-BoJl2MKg.js");return{ReportsLogic:n}},[]).then(({ReportsLogic:n})=>{f(async()=>{const{ReportsAjax:e}=await import("../assets/ajax-DkYBT2Kz.js");return{ReportsAjax:e}},[]).then(({ReportsAjax:e})=>{const t=new e,r=new n;new g(r,t).init()}).catch(e=>console.log("Reports modules not found, using basic functionality"))}).catch(n=>console.log("Reports modules not found, using basic functionality"))});
