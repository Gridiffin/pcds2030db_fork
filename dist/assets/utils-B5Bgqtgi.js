function r(){typeof bootstrap<"u"&&bootstrap.Tooltip&&[].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function(e){return new bootstrap.Tooltip(e)})}function l(i,e="info",o=5e3){document.querySelectorAll(".alert-toast").forEach(n=>n.remove());const t=document.createElement("div");return t.className=`alert alert-${e} alert-dismissible fade show alert-toast`,t.style.cssText=`
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
    `,t.innerHTML=`
        <div class="d-flex align-items-center">
            <i class="fas ${a(e)} me-2"></i>
            <span>${i}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `,document.body.appendChild(t),o>0&&setTimeout(()=>{t.parentNode&&t.remove()},o),t}function a(i){return{success:"fa-check-circle",error:"fa-exclamation-circle",danger:"fa-exclamation-triangle",warning:"fa-exclamation-triangle",info:"fa-info-circle",primary:"fa-info-circle",secondary:"fa-info-circle"}[i]||"fa-info-circle"}export{r as i,l as s};
