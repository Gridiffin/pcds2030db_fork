- this is an intern project so i need to create documentations for the next developer that would continue the development of this project.
- so i need your help to generate the documentation based on this whole codebase.
- NOTE: focus solely on Windows as the environment
-  put everything in a new directory called devs docs. for readme, update the one that is in the root directory

- start with readme. 
	📋 README.md (The Hub):

    What: First file any developer opens. Be brutally clear.

    How: Use Markdown in your repo root.

    Content:

        Project Name & Brief Purpose (1 sentence).

        🔥 Critical: Local Setup Guide (commands, env vars, dependencies).

        How to build/run the project (npm start, docker-compose up, etc.).

        Key technology stack (Languages, Frameworks, DBs, major libraries).

        Link to main architecture diagram (if exists).

        Link to other crucial docs (API Spec, Deployment Guide).

        Optional: Badges (build status, coverage), very brief contribution hints.

- Setup & Installation Guide:

    What: Detailed, step-by-step instructions for getting the project running locally.

    How: Can be in the README or a separate SETUP.md/INSTALL.md. Use numbered steps.

    Content:

        Prerequisites (specific OS, Docker, Node/Python/Java version, DB engine).

        Exact commands for installing dependencies (pip install -r requirements.txt, npm install, bundle install).

        Database setup (schema creation, seed data, migrations).

        Configuration (environment variables - .env.example file is MANDATORY).

        How to run tests.

        Common setup pitfalls & solutions. 

- Code Structure & Key Concepts:

    What: A map to navigate the codebase and understand core ideas.

    How: ARCHITECTURE.md or STRUCTURE.md. Diagrams are gold! (Use Mermaid, Draw.io, Excalidraw).

    Content:

        High-level directory structure explanation (/src, /config, /tests, /scripts - what each holds).

        Core architectural patterns (MVC, Microservices, Serverless, Event-Driven?).

        Key Abstractions: Explain the 3-5 most important classes/modules/services and their responsibilities.

        Data Flow: How does data move through the system? (e.g., API -> Service -> DB -> Response).

        Decision Log: Briefly note why major tech choices were made (e.g., "Chose Redis for caching due to low latency needs").