document.addEventListener("DOMContentLoaded",function(){const s=document.getElementById("refreshPage");s&&s.addEventListener("click",function(){this.classList.add("loading"),this.innerHTML,this.innerHTML='<i class="fas fa-sync-alt me-1"></i> Refreshing...',setTimeout(()=>{window.location.reload()},500)});const i=document.getElementById("refreshSubmissions");if(i&&i.addEventListener("click",function(){this.innerHTML='<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...',this.disabled=!0,setTimeout(()=>{this.innerHTML='<i class="fas fa-sync-alt"></i> Refresh',this.disabled=!1;const t=document.querySelector(".table-responsive").parentNode,e=document.createElement("div");e.className="alert alert-success alert-dismissible fade show mt-3",e.innerHTML=`
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>Data refreshed successfully!</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `,t.insertBefore(e,document.querySelector(".table-responsive")),setTimeout(()=>{e.classList.remove("show"),setTimeout(()=>e.remove(),300)},3e3)},1200)}),!hasActivePeriod){const t=document.querySelector(".quick-actions-container"),e=document.createElement("div");e.className="alert alert-info alert-dismissible fade show mb-4",e.innerHTML=`
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-3 fa-lg"></i>
                <div>
                    <strong>No active reporting period.</strong>
                    Start by <a href="reporting_periods.php" class="alert-link">creating a new reporting period</a> to begin collecting data.
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `,t.parentNode.insertBefore(e,t)}document.querySelectorAll(".stat-card").forEach((t,e)=>{setTimeout(()=>{t.classList.add("animate__animated","animate__fadeInUp")},e*100)})});
