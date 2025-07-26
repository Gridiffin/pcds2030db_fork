- in programs overview in admin side, dont separate programs according to "ASsigned and "agency create" anymore, just show recent 5 programs in one list
- rewrite the entire outcomes overview section to fit with current deisgn of outcomes (e.g. outcomes are now cannot be created by anyone so the button creat new outcome should be removed)
- remove recent submissions section
- remove assign programs button from quick actions section.
- view and edit outcome are using a common.bundle.css should be using its own edit outcome bundle
- the copy email button in manage users doesnt work
- remove bulk assign initiatives from programs page
- reporting period page doesnt show anything aside from the header, footer and navbar 
17:29:22.691 Uncaught TypeError: can't access property "innerHTML", tableContainer is null
    loadPeriods http://localhost/pcds2030_dashboard_fork/assets/js/admin/periods-management.js:77
    <anonymous> http://localhost/pcds2030_dashboard_fork/assets/js/admin/periods-management.js:8
    jQuery 13
periods-management.js:77:5
    loadPeriods http://localhost/pcds2030_dashboard_fork/assets/js/admin/periods-management.js:77
    <anonymous> http://localhost/pcds2030_dashboard_fork/assets/js/admin/periods-management.js:8
    jQuery 13


- separate all the programs modules to use their own bundle per page just like how it is in agency side. (e.g view programs has its own bundle, edit program has its own bundle, etc)
- remove refresh data button in all header of admin pages. keep the other buttons and elements intact
- remove system settings page out of admin views