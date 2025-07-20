class l{formatDate(e){if(!e)return"N/A";try{return new Date(e).toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"})}catch{return console.warn("Invalid date string:",e),"Invalid Date"}}formatDateTime(e){if(!e)return"N/A";try{return new Date(e).toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric",hour:"2-digit",minute:"2-digit"})}catch{return console.warn("Invalid date string:",e),"Invalid Date"}}getReportTypeBadgeClass(e){return{program:"bg-primary",sector:"bg-info",public:"bg-secondary",agency:"bg-success"}[e]||"bg-secondary"}getFileTypeIcon(e){return{pdf:"fas fa-file-pdf",pptx:"fas fa-file-powerpoint",xlsx:"fas fa-file-excel",docx:"fas fa-file-word"}[e]||"fas fa-file"}validatePeriodSelection(e){return e&&!isNaN(parseInt(e))&&parseInt(e)>0}generateDownloadUrl(e){return e?`${window.APP_URL||""}/reports/${e}`:"#"}isRecentReport(e){if(!e)return!1;try{const t=new Date(e),r=new Date;return r.setDate(r.getDate()-7),t>=r}catch{return!1}}filterReportsByType(e,t){return!t||t==="all"?e:e.filter(r=>r.report_type===t)}sortReportsByDate(e,t="desc"){return[...e].sort((r,s)=>{const o=new Date(r.generated_at),a=new Date(s.generated_at);return t==="desc"?a-o:o-a})}getReportStatistics(e){const t={total:e.length,byType:{},recent:0,thisMonth:0},r=new Date,s=new Date(r.getFullYear(),r.getMonth(),1),o=new Date;return o.setDate(o.getDate()-7),e.forEach(a=>{const n=a.report_type||"unknown";t.byType[n]=(t.byType[n]||0)+1;const c=new Date(a.generated_at);c>=o&&t.recent++,c>=s&&t.thisMonth++}),t}truncateText(e,t=50){return!e||e.length<=t?e||"":e.substring(0,t-3)+"..."}}class d{constructor(){this.baseUrl=this.getBaseUrl()}getBaseUrl(){const t=window.location.pathname.split("/"),r=t.findIndex(s=>s==="app");return r>=0?t.slice(0,r+1).join("/"):"/app"}async loadReportsForPeriod(e){try{const t=await fetch(`${this.baseUrl}/ajax/get_reports.php?period_id=${e}`,{method:"GET",headers:{"Content-Type":"application/json"}});if(!t.ok)throw new Error(`HTTP error! status: ${t.status}`);const r=await t.json();if(r.error)throw new Error(r.error);return r}catch(t){throw console.error("Error loading reports:",t),t}}async loadPublicReports(){try{const e=await fetch(`${this.baseUrl}/ajax/get_public_reports.php`,{method:"GET",headers:{"Content-Type":"application/json"}});if(!e.ok)throw new Error(`HTTP error! status: ${e.status}`);const t=await e.json();if(t.error)throw new Error(t.error);return t}catch(e){throw console.error("Error loading public reports:",e),e}}async downloadReport(e,t="pdf"){try{const r=await fetch(`${this.baseUrl}/ajax/download_report.php`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({report_id:e,file_type:t})});if(!r.ok)throw new Error(`HTTP error! status: ${r.status}`);const s=await r.blob(),o=window.URL.createObjectURL(s),a=document.createElement("a");return a.href=o,a.download=`report_${e}.${t}`,document.body.appendChild(a),a.click(),window.URL.revokeObjectURL(o),document.body.removeChild(a),!0}catch(r){throw console.error("Error downloading report:",r),r}}async getReportStatistics(){try{const e=await fetch(`${this.baseUrl}/ajax/get_report_stats.php`,{method:"GET",headers:{"Content-Type":"application/json"}});if(!e.ok)throw new Error(`HTTP error! status: ${e.status}`);const t=await e.json();if(t.error)throw new Error(t.error);return t}catch(e){throw console.error("Error loading report statistics:",e),e}}async requestReportGeneration(e){try{const t=await fetch(`${this.baseUrl}/ajax/generate_report.php`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)});if(!t.ok)throw new Error(`HTTP error! status: ${t.status}`);const r=await t.json();if(r.error)throw new Error(r.error);return r}catch(t){throw console.error("Error requesting report generation:",t),t}}async checkReportStatus(e){try{const t=await fetch(`${this.baseUrl}/ajax/check_report_status.php?job_id=${e}`,{method:"GET",headers:{"Content-Type":"application/json"}});if(!t.ok)throw new Error(`HTTP error! status: ${t.status}`);const r=await t.json();if(r.error)throw new Error(r.error);return r}catch(t){throw console.error("Error checking report status:",t),t}}handleError(e,t="operation"){console.error(`Error during ${t}:`,e),typeof window.showToast=="function"?window.showToast("Error",`Failed to ${t}. Please try again.`,"danger"):alert(`Error: Failed to ${t}. Please try again.`)}showLoading(e){e&&(e.innerHTML='<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>')}hideLoading(e){if(e){const t=e.querySelector(".fa-spinner");t&&t.closest(".text-center").remove()}}}class p{constructor(e,t){this.logic=e,this.ajax=t,this.currentPeriod=null,this.reports=[]}init(){this.attachEventListeners(),this.initializeFilters(),this.loadInitialData(),console.log("View Reports page initialized")}attachEventListeners(){const e=document.querySelector("#period-filter-form");e&&e.addEventListener("submit",s=>{s.preventDefault(),this.handlePeriodFilter()});const t=document.querySelector("#period_id");t&&t.addEventListener("change",()=>{this.handlePeriodChange()});const r=document.querySelector(".clear-filter-btn");r&&r.addEventListener("click",()=>{this.clearFilters()}),this.attachDownloadListeners(),this.attachViewListeners()}initializeFilters(){const t=new URLSearchParams(window.location.search).get("period_id");if(t){const r=document.querySelector("#period_id");r&&(r.value=t,this.currentPeriod=parseInt(t))}}async loadInitialData(){this.currentPeriod&&await this.loadReportsForPeriod(this.currentPeriod)}async handlePeriodFilter(){const e=document.querySelector("#period_id"),t=e?parseInt(e.value):null;if(!this.logic.validatePeriodSelection(t)){this.showError("Please select a valid reporting period.");return}const r=new URL(window.location);r.searchParams.set("period_id",t),window.history.pushState({},"",r),this.currentPeriod=t,await this.loadReportsForPeriod(t)}handlePeriodChange(){const e=document.querySelector("#period_id"),t=e?parseInt(e.value):null,r=document.querySelector(".filter-btn");r&&(r.disabled=!this.logic.validatePeriodSelection(t))}clearFilters(){const e=document.querySelector("#period_id");e&&(e.value="");const t=new URL(window.location);t.searchParams.delete("period_id"),window.history.pushState({},"",t),this.currentPeriod=null,this.clearReportsList(),this.showSelectPeriodMessage()}async loadReportsForPeriod(e){try{const t=document.querySelector("#reports-container");t&&this.ajax.showLoading(t);const r=await this.ajax.loadReportsForPeriod(e);this.reports=r.reports||[],this.renderReportsList(),this.updateReportsCount()}catch(t){this.ajax.handleError(t,"load reports"),this.showError("Failed to load reports. Please try again.")}}renderReportsList(){const e=document.querySelector("#reports-container");if(!e)return;if(this.reports.length===0){this.showEmptyState();return}const t=this.reports.map(r=>this.renderReportRow(r)).join("");e.innerHTML=`
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
        `,this.attachDownloadListeners(),this.attachViewListeners()}renderReportRow(e){const t=this.logic.getReportTypeBadgeClass(e.report_type),r=this.logic.generateDownloadUrl(e.file_path),s=this.logic.formatDate(e.generated_at),o=this.logic.isRecentReport(e.generated_at);return`
            <tr ${o?'class="table-warning"':""}>
                <td>
                    <div class="report-name">${e.report_name||"Untitled Report"}</div>
                    ${o?'<small class="text-warning"><i class="fas fa-star"></i> New</small>':""}
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
            `)}updateReportsCount(){const e=document.querySelector(".reports-count-badge");e&&(e.textContent=`${this.reports.length} Reports`)}attachDownloadListeners(){document.querySelectorAll(".download-report-btn").forEach(e=>{e.addEventListener("click",async t=>{t.preventDefault();const r=e.dataset.reportId,s=e.dataset.fileType||"pdf";try{e.disabled=!0,e.innerHTML='<i class="fas fa-spinner fa-spin"></i>',await this.ajax.downloadReport(r,s),typeof window.showToast=="function"&&window.showToast("Success","Report downloaded successfully","success")}catch(o){this.ajax.handleError(o,"download report")}finally{e.disabled=!1,e.innerHTML='<i class="fas fa-download"></i>'}})})}attachViewListeners(){document.querySelectorAll(".view-report-btn").forEach(e=>{e.addEventListener("click",t=>{typeof gtag<"u"&&gtag("event","view_report",{report_type:"agency_report"})})})}showError(e){typeof window.showToast=="function"?window.showToast("Error",e,"danger"):alert(`Error: ${e}`)}clearReportsList(){const e=document.querySelector("#reports-container");e&&(e.innerHTML="")}}class h{constructor(e,t){this.logic=e,this.ajax=t,this.reports=[],this.filteredReports=[]}init(){this.attachEventListeners(),this.loadPublicReports(),console.log("Public Reports page initialized")}attachEventListeners(){this.attachDownloadListeners(),this.attachViewListeners(),this.attachSearchListeners(),this.attachFilterListeners();const e=document.querySelector(".refresh-reports-btn");e&&e.addEventListener("click",()=>{this.refreshReports()})}async loadPublicReports(){try{const e=document.querySelector("#public-reports-container");e&&this.ajax.showLoading(e);const t=await this.ajax.loadPublicReports();this.reports=t.reports||[],this.filteredReports=[...this.reports],this.renderReportsList(),this.updateReportsCount()}catch(e){this.ajax.handleError(e,"load public reports"),this.showError("Failed to load public reports. Please try again.")}}async refreshReports(){const e=document.querySelector(".refresh-reports-btn");if(e){e.disabled=!0;const t=e.innerHTML;e.innerHTML='<i class="fas fa-spinner fa-spin"></i> Refreshing...';try{await this.loadPublicReports(),typeof window.showToast=="function"&&window.showToast("Success","Reports refreshed successfully","success")}catch{}finally{e.disabled=!1,e.innerHTML=t}}else await this.loadPublicReports()}renderReportsList(){const e=document.querySelector("#public-reports-container");if(!e)return;if(this.filteredReports.length===0){this.showEmptyState();return}const t=this.filteredReports.map(r=>this.renderReportCard(r)).join("");e.innerHTML=`
            <div class="row">
                ${t}
            </div>
        `,this.attachDownloadListeners(),this.attachViewListeners()}renderReportCard(e){const t=this.logic.generateDownloadUrl(e.file_path),r=this.logic.formatDate(e.generated_at),s=this.logic.getFileTypeIcon(e.file_type||"pdf"),o=this.logic.isRecentReport(e.generated_at);return`
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 report-card ${o?"recent-report":""}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <i class="${s} fa-2x text-primary"></i>
                        ${o?'<span class="badge bg-warning text-dark">New</span>':""}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${e.report_name||"Untitled Report"}</h6>
                        <p class="card-text flex-grow-1">${this.logic.truncateText(e.description||"No description available",100)}</p>
                        <div class="mt-auto">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-calendar-alt"></i> ${r}
                            </small>
                            <div class="d-flex gap-2">
                                <a href="${t}" class="btn btn-outline-primary btn-sm view-report-btn flex-grow-1" 
                                   target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button class="btn btn-success btn-sm download-report-btn" 
                                        data-report-id="${e.report_id}" 
                                        data-file-type="${e.file_type||"pdf"}"
                                        title="Download Report">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `}showEmptyState(){const e=document.querySelector("#public-reports-container");if(e){const t=this.filteredReports.length!==this.reports.length,r=t?"No reports match your current filters.":"No public reports are currently available for download.";e.innerHTML=`
                <div class="col-12">
                    <div class="text-center py-5 reports-empty-state">
                        <i class="fas fa-file-alt"></i>
                        <p>${r}</p>
                        ${t?'<button class="btn btn-outline-primary clear-filters-btn">Clear Filters</button>':""}
                    </div>
                </div>
            `;const s=e.querySelector(".clear-filters-btn");s&&s.addEventListener("click",()=>{this.clearFilters()})}}updateReportsCount(){const e=document.querySelector(".reports-count");if(e){const t=this.filteredReports.length,r=this.reports.length;t===r?e.textContent=`${r} Reports`:e.textContent=`Showing ${t} of ${r} Reports`}}attachDownloadListeners(){document.querySelectorAll(".download-report-btn").forEach(e=>{e.addEventListener("click",async t=>{t.preventDefault();const r=e.dataset.reportId,s=e.dataset.fileType||"pdf";try{e.disabled=!0;const o=e.innerHTML;e.innerHTML='<i class="fas fa-spinner fa-spin"></i>',await this.ajax.downloadReport(r,s),typeof window.showToast=="function"&&window.showToast("Success","Report downloaded successfully","success"),typeof gtag<"u"&&gtag("event","download",{event_category:"Public Reports",event_label:`Report ${r}`})}catch(o){this.ajax.handleError(o,"download report")}finally{e.disabled=!1,e.innerHTML='<i class="fas fa-download"></i>'}})})}attachViewListeners(){document.querySelectorAll(".view-report-btn").forEach(e=>{e.addEventListener("click",t=>{typeof gtag<"u"&&gtag("event","view_report",{report_type:"public_report"})})})}attachSearchListeners(){const e=document.querySelector("#reports-search");if(e){let t;e.addEventListener("input",r=>{clearTimeout(t),t=setTimeout(()=>{this.filterReports()},300)})}}attachFilterListeners(){const e=document.querySelector("#report-type-filter");e&&e.addEventListener("change",()=>{this.filterReports()})}filterReports(){const e=document.querySelector("#reports-search"),t=document.querySelector("#report-type-filter"),r=e?e.value.toLowerCase().trim():"",s=t?t.value:"all";this.filteredReports=this.reports.filter(o=>{const a=!r||o.report_name&&o.report_name.toLowerCase().includes(r)||o.description&&o.description.toLowerCase().includes(r),n=s==="all"||o.report_type===s;return a&&n}),this.renderReportsList(),this.updateReportsCount()}clearFilters(){const e=document.querySelector("#reports-search"),t=document.querySelector("#report-type-filter");e&&(e.value=""),t&&(t.value="all"),this.filteredReports=[...this.reports],this.renderReportsList(),this.updateReportsCount()}showError(e){typeof window.showToast=="function"?window.showToast("Error",e,"danger"):alert(`Error: ${e}`)}}document.addEventListener("DOMContentLoaded",function(){const i=window.location.pathname.split("/").pop(),e=new l,t=new d;switch(i){case"view_reports.php":new p(e,t).init();break;case"public_reports.php":new h(e,t).init();break}});window.ReportsModule={ReportsLogic:l,ReportsAjax:d,ViewReports:p,PublicReports:h};
