document.addEventListener("DOMContentLoaded",function(){const a=document.getElementById("initiativeRatingChart"),o=document.getElementById("ratingData");if(!a){console.error('Canvas element with ID "initiativeRatingChart" not found');return}if(!o){console.error('Rating data element with ID "ratingData" not found');return}if(typeof Chart>"u"){console.error("Chart.js library not loaded");return}let c;try{const t=o.textContent||o.innerText;c=JSON.parse(t)}catch(t){console.error("Failed to parse rating data:",t);return}const l=[],i=[],s=[],d={"target-achieved":"#28a745",completed:"#28a745","on-track":"#ffc107","on-track-yearly":"#ffc107",delayed:"#dc3545","severe-delay":"#dc3545","not-started":"#6c757d"},u={"target-achieved":"Target Achieved",completed:"Completed","on-track":"On Track","on-track-yearly":"On Track (Yearly)",delayed:"Delayed","severe-delay":"Severe Delay","not-started":"Not Started"};for(const[t,e]of Object.entries(c))if(e>0){const r=u[t]||t,n=d[t]||"#6c757d";l.push(r),i.push(e),s.push(n)}if(i.length>0)try{new Chart(a,{type:"doughnut",data:{labels:l,datasets:[{data:i,backgroundColor:s,borderWidth:2,borderColor:"#ffffff"}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!0,position:"bottom",labels:{padding:20,usePointStyle:!0}},tooltip:{callbacks:{label:function(t){const e=t.label||"",r=t.raw||0,n=t.dataset.data.reduce((h,p)=>h+p,0),f=n>0?Math.round(r/n*100):0;return`${e}: ${r} (${f}%)`}}}},cutout:"70%"}})}catch(t){console.error("Error creating chart:",t);const e=a.parentElement;e.innerHTML=`
                <div class="text-muted text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                    <div>Error loading chart. Please refresh the page.</div>
                </div>
            `}else{const t=a.parentElement;t.innerHTML=`
            <div class="text-muted text-center py-4">
                <i class="fas fa-chart-pie fa-2x mb-3"></i>
                <div>No program rating data available for this initiative.</div>
            </div>
        `}});
