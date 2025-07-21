var b=(s,e)=>()=>(e||s((e={exports:{}}).exports,e),e.exports);var f=b((u,c)=>{function d(s){const e=new Date(s);return isNaN(e)?"":e.toLocaleDateString("en-US",{month:"short",day:"numeric",year:"numeric"})}function l(s,e){return s.length?`<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Initiative</th><th class="text-center">Total Programs</th><th>Timeline</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>${s.map(t=>`<tr data-initiative-id="${t[e.id]}">
            <td>
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1">
                            ${t[e.number]?`<span class="badge bg-primary me-2">${t[e.number]}</span>`:""}
                            ${t[e.name]||""}
                        </div>
                        ${t[e.description]?`<div class="text-muted small" style="line-height: 1.4;">${t[e.description].length>120?t[e.description].substring(0,120)+"...":t[e.description]}</div>`:""}
                    </div>
                </div>
            </td>
            <td class="text-center"><span class="badge bg-secondary">${t.program_count||0} total</span></td>
            <td>${t[e.start_date]||t[e.end_date]?`<div class="small">${t[e.start_date]&&t[e.end_date]?`<i class='fas fa-calendar-alt me-1 text-muted'></i>${d(t[e.start_date])} - ${d(t[e.end_date])}`:t[e.start_date]?`<i class='fas fa-play me-1 text-success'></i>Started: ${d(t[e.start_date])}`:`<i class='fas fa-flag-checkered me-1 text-warning'></i>Due: ${d(t[e.end_date])}`}</div>`:'<span class="text-muted small"><i class="fas fa-calendar-times me-1"></i>No timeline</span>'}</td>
            <td>${t[e.is_active]?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>'}</td>
            <td class="text-center"><a href="edit.php?id=${t[e.id]}" class="btn btn-outline-primary btn-sm me-1" title="Edit Initiative"><i class="fas fa-edit"></i></a><a href="view_initiative.php?id=${t[e.id]}" class="btn btn-outline-primary btn-sm" title="View Initiative Details"><i class="fas fa-eye"></i></a></td>
        </tr>`).join("")}</tbody></table></div>`:`<div class="text-center py-5">
            <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No initiatives found</h5>
            <p class="text-muted">No initiatives match your search criteria.</p>
            <a href="initiatives.php" class="btn btn-outline-primary">
                <i class="fas fa-undo me-1"></i>Clear Filters
            </a>
        </div>`}document.addEventListener("DOMContentLoaded",function(){const s=document.querySelector("form"),e=document.querySelector(".card-body.p-0"),r=document.createElement("div");r.className="text-center py-4",r.innerHTML='<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';function t(){return{id:document.body.dataset.initiativeIdCol||"id",name:document.body.dataset.initiativeNameCol||"name",number:document.body.dataset.initiativeNumberCol||"number",description:document.body.dataset.initiativeDescriptionCol||"description",start_date:document.body.dataset.startDateCol||"start_date",end_date:document.body.dataset.endDateCol||"end_date",is_active:document.body.dataset.isActiveCol||"is_active"}}function o(n){e.innerHTML="",e.appendChild(r);const i=new URL("/app/ajax/admin_manage_initiatives.php",window.location.origin);Object.keys(n).forEach(a=>i.searchParams.append(a,n[a])),fetch(i).then(a=>a.json()).then(a=>{e.innerHTML="",a.success?e.innerHTML=l(a.initiatives,t()):e.innerHTML=`<div class='alert alert-danger'>${a.error||"Failed to load initiatives."}</div>`}).catch(()=>{e.innerHTML="<div class='alert alert-danger'>Server error. Please try again.';</div>"})}s&&s.addEventListener("submit",function(n){n.preventDefault();const i=new FormData(s),a={};for(const[p,m]of i.entries())a[p]=m;o(a)})});typeof c<"u"&&c.exports&&(c.exports={formatDate:d,renderTable:l})});export default f();
