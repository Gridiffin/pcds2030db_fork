---
applyTo: '**'
---
Coding standards, domain knowledge, and preferences that AI should follow.

- Follow established coding standards and best practices.
- Incorporate domain knowledge relevant to the project.
- Consider user preferences and feedback in the development process.
- Ensure code is well-documented and maintainable (modular)
- Use meaningful variable and function names.
- always use the latest version of the libraries and frameworks.
- remember that this project is going to be hosted on cPanel.
- Use a consistent coding style throughout the project (refer to /layouts/headers.php)
- remember to INCLUDE all files that is related to any functions that is specified. For example: if login functionality is being edited then scan the whole codebase to find anything related to login, including their styles.
- suggest improvements to the codebase, including refactoring, optimization, and performance enhancements.
- for any database operations please use DBCode extension to help you.
- Ensure that all database queries are properly parameterized to prevent SQL injection attacks.
- make sure that you understand how the files are referenced across the project. For example: in layouts/headers.php, there are base.css (or main.css) file that is referenced so you need to use these files as a centralized referencer file for all the css styles that is being used in the project. So if you are going to add a new css file, make sure to import them in main.css. (or base.css)
- whenever "continue" is used, you have to summarize the previous conversation history and then continue from there. So for example, if the previous conversation was about fixing a bug in the login functionality, you should summarize that conversation and then continue from there.

## Project Structure
- Follow the project structure and organization as outlined in the project documentation.
- Use the provided folder structure for organizing files and directories.
- Keep the project organized and maintainable by following the established conventions.
- Ensure that all files are properly named and located in the appropriate directories.

## System Context
- Understand the system context and how the code fits into the larger architecture.
- Refer to system_context.txt
- Consider the interactions between different components and modules.
- 

## Outputs
- Before solving a problem/implementing a feature or just doing anything in general, create a .md file that describes the problem and how to solve it step by step.
- So, for example, if you are going to add a new feature, create a file called ".github/implementations/feature_name.md" and describe the feature in detail.
- Format the file like how you would for a TODO list.
- Mark complete the tasks in the .md file as you complete them.
- always choose for the best simplest way to solve problems.
- if the user's way of coding is not a good practice, give suggestions for improvements before coding anything.
- delete all test files after the implementation is complete.