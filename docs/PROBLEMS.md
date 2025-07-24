
### 1) Problems with the "Continue" button in delete modal

Description: The button doesnt give any feedback when clicked, ultimately meaning the delete funcitonality doesnt work
Page/File: view_programs.php
Assumptions/Suspection: check the eventlistener, the button might be not connected to any js functionality

======= EXTRA INFO ========
''console-log 
17:51:23.207 [DEBUG] Delete button clicked 
Object { programId: "15", programName: "program testing 2", button: button.btn.btn-outline-danger.flex-fill.trigger-delete-modal
 }
​
button: <button class="btn btn-outline-danger f…ll trigger-delete-modal" type="button" data-id="15" data-name="program testing 2" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Delete this program and all its submissions" data-bs-original-title="Delete this program and all its submissions">
​
programId: "15"
​
programName: "program testing 2"
​
<prototype>: Object { … }

### 2) Problems with the "Change Status" button in program details

Description: The button doesnt give any feedback when clicked, ultimately meaning the delete funcitonality doesnt work. same with problem 1
Page/File: programs_details.php
Assumptions/Suspection: check the eventlistener, the button might be not connected to any js functionality

======= EXTRA INFO ========
-

### 3) Problems with the deleting a submission from quick actions in program details

Description: the deleting of the submission shouldnt be shown here. it should work like this:
            - the button should be changed to deleting the program not deleting a submission.
Page/File: programs_details.php
Assumptions/Suspection: a wrong implementation of functions

======= EXTRA INFO ========

### 4) Problems with view submission in program details

Description: the full flow should be:
            - user click on the view submissions button
            - modal to choose which quarter to view
            - user click on view details and then directly be redirected to the view submission page of the respective period
Page/File: programs_details.php
Assumptions/Suspection: a wrong implementation of functions

======= EXTRA INFO ========

### 5) Problems with "save and exit" button in edit submission

Description: users should be redirected back to view_programs.php not the dashboard.
Page/File: view_programs.php
Assumptions/Suspection: a wrong redirect

======= EXTRA INFO ========