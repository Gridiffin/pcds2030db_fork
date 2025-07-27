- i need your help to completely overhaul admin side of programs module
- i need:
    a. view programs page (currently called programs.php) - functions same as view porgrams in agency side but it will only display finalized submissions by programs. use the agency side of view programs as your reference. both should functions the same except for the fact that admin side checks for is_admin and agency side checks for regular user, focal, editors etc
    b. program details page, the exact same function from agency except it is special for admin
    c. view submissions page, again the same as agency except for admin
    d. edit submission
    e. edit program.
- one thing to note is that admin has the absolute permissions and they have access to EVERY agencies' programs including editing them so adding an agency information into the programs modules would be good
- another information is that admin CANNOT create programs, or assign any agencies to programs.
- admins also cannot view unfinalized submissions or draft submissions. (is_draft= 1)
- remove any unused files after implementing bcs the current system stil using the old design of programs module.
- put in program details which focal finalized the submission and and record the date and time. 
- simply said, admin side has the most "overview" feeling. they can edit but most of the time their job is to review submissions and generate reports not editing or deleting or creating a new program bcs all these basic CRUD is all done by the agency users themselves.
- DONT FORGET: implement the cs and js correctly so that we can bundle them later. use agency side as a reference bcs the main concept for both sides are the same. we dont need any checking for focal or regular users in this side bcs everything is admin. unless for certain stuff like checking which agency owns this program. DONT USE the same modern box as programs, ill design a new one later so keep on using modern tables.css
- also make sure all components in admin side is using the new modern components just like agency. if needed you can move the shared components between both sides into a new directory "shared" in assets.