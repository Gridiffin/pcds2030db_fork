# Chapter 2: Background Study

## 2.1 Post COVID-19 Development Strategy 2030 (PCDS 2030)

The Post COVID-19 Development Strategy 2030 (PCDS 2030) is Sarawak's strategic blueprint launched in July 2021, outlining the state's vision to become a thriving, developed society driven by data and innovation by 2030, where all Sarawakians enjoy economic prosperity, social inclusivity, and environmental sustainability. The strategy is anchored upon six key economic sectors—Manufacturing, Commercial Agriculture, Tourism, Forestry, Mining, and Social Services—supported by seven enablers including Digital Transformation, Innovation, and Basic Infrastructure.

For the forestry sector, PCDS 2030 aims for Sarawak to be globally recognised in sustainable management of tropical forests and biodiversity conservation while enhancing the rapid growth of the timber industry, with a target of RM8 billion in annual export value. Key forestry initiatives under PCDS 2030 include the quantification of forest carbon stock, establishment of a Centre of Excellence for Planted Forest R&D, upgrading and management of Totally Protected Areas, and the Sustainable Forest Management Centre.

Digital transformation serves as a crucial enabler and catalyst for PCDS 2030, empowering all economic sectors—including forestry—to increase efficiencies and productivity through data-driven approaches and technology adoption. The Forest Department Sarawak (FDS) aligns its strategic direction with PCDS 2030 principles, integrating sustainability into development plans consistent with the Sustainable Development Goals (SDGs) and Environmental, Social, and Governance (ESG) criteria. Performance monitoring and delivery of PCDS 2030 priority projects are tracked by the Performance Management & Delivery Sector (PMD) within the Economic Planning Unit Sarawak, with annual progress reports published to ensure transparency and accountability.

## 2.2 Overview of Existing System: PCDS2030 Dashboard (Forestry Sector)

To support the digital transformation enabler of the PCDS 2030, the PCDS2030 Dashboard was developed as the primary digital solution for managing forestry sector programs under the Sarawak Ministry of Natural Resources and Urban Development. Developed to replace traditional manual reporting processes, the system integrates data from three key agencies: the Forestry Department Sarawak (FDS), Sarawak Forestry Corporation (SFC), and the Sarawak Timber Industry Development Corporation (STIDC).

The system functions as a centralized platform for monitoring the implementation of the PCDS 2030 initiatives within the forestry sector. It facilitates the transition from decentralized Excel-based reporting to a unified web-based architecture, enabling real-time tracking of program performance, financial expenditure, and outcome realization.

## 2.3 Functional Capabilities

The existing system offers a comprehensive suite of features designed to support both agency-level data entry and ministry-level oversight:

### 2.3.1 Program Management and Reporting
Agencies utilize the system to register and update forestry programs. The core functionality revolves around a quarterly and half-yearly reporting cycle where users submit progress updates. Key data points include:
*   **Physical Progress**: Tracking actual vs. planned implementation status.
*   **Outcome Indicators**: Measuring specific sector outcomes such as timber export values, forest conservation areas, and certification status.

### 2.3.2 Dashble oard and Visualization
The system employs a dynamic dashboard interface powered by Chart.js to provide visual analytics. Stakeholders can view:
*   **Program Status Distribution**: Visual breakdowns of programs that are "On Track," "Delayed," or "Completed."
*   **Submission Status**: Real-time monitoring of agency reporting compliance during active windows.
*   **Sector Performance**: Aggregated metrics across the forestry sector.

### 2.3.3 Automated Report Generation
A critical feature of the system is its ability to automate the generation of official reports. It supports the creation of PowerPoint (PPTX) and PDF reports, significantly reducing the manual effort required to compile data for ministry meetings. This feature ensures consistency in formatting and data accuracy across different reporting periods.

### 2.3.4 Security and Access Control
The application implements a Role-Based Access Control (RBAC) system to ensure data integrity and security.
*   **Agency Users**: Restricted to viewing and editing data relevant to their specific organization.
*   **Admin Users**: Possess oversight capabilities, including user management, reporting period configuration, and cross-agency data analysis.
*   **Audit Trails**: Comprehensive logging of user activities, including login history and data modifications, ensures accountability and compliance with government standards.

## 2.4 Technical Architecture

The PCDS2030 Dashboard is built on a robust web-based architecture utilizing open-source technologies, specifically the **Apache**, **MySQL**, and **PHP** stack.

*   **Backend**: The core logic is powered by **PHP 8.x**, utilizing a custom Model-View-Controller (MVC) architecture. This ensures separation of concerns between data handling, user interface, and business logic.
*   **Database**: **MySQL** serves as the relational database management system, storing structured data for users, programs, and submissions. It also employs JSON storage for flexible content management, allowing the system to adapt to evolving reporting requirements without frequent schema changes.
*   **Frontend**: The user interface is built with **HTML5**, **CSS3**, and **JavaScript (ES6+)**. **Bootstrap 5** provides the responsive layout framework, ensuring accessibility across different devices. **Vite** is used for modern asset bundling and optimization.
*   **Infrastructure**: The system is designed to be cross-platform. While currently developed in a Windows environment (using tools like Laragon/XAMPP), it is deployed on standard web hosting environments (typically cPanel) for production.

## 2.5 Critical Analysis and Limitations

Despite the successful deployment of the PCDS2030 Dashboard, several critical limitations have emerged during its operational phase. These issues primarily relate to the system's architecture, user experience, and design consistency, which necessitate a comprehensive refactoring and redesign.

1.  **Codebase Architecture and Maintainability**: The current system, while functional, relies on a generic MVC structure that has become difficult to maintain as features expanded. The code organization lacks a modular, **feature-based architecture**, leading to tight coupling between components. This makes debugging and implementing new features risky and complex. A restructuring is required to transition from an "internship-level" codebase to a professional, maintainable standard.

2.  **Complex Navigation and Workflow**: The user journey for managing data—specifically navigating from **Programs** to **Initiatives** and finally to **Submissions**—is convoluted. Users are often required to click through multiple layers of the hierarchy to perform routine tasks. This deep nesting creates a confusing workflow that hinders efficiency and increases the learning curve for new agency officers.

3.  **Inconsistent User Interface (UI)**: The application lacks a unified design system. Visual elements such as buttons, typography, and spacing are inconsistent across different pages, resulting in a disjointed aesthetic that does not reflect a premium, professional standard. The current design "vibe" varies significantly between modules, reducing user confidence and satisfaction.

4.  **Mobile Usability**: While the application is responsive, the mobile experience is suboptimal. Key features, particularly data tables and complex forms, are difficult to navigate on smaller screens. The lack of a mobile-optimized interface limits the ability of officers to effectively access or update data while in the field.

## 2.6 Conclusion

The existing PCDS2030 Dashboard has served its purpose as a proof-of-concept and initial digital solution for the forestry sector. However, the identified issues in **architectural structure, navigation complexity, and UI consistency** present significant barriers to its long-term viability. The proposed project aims to address these specific gaps by refactoring the backend into a modular, feature-based architecture and completely redesigning the frontend to offer a streamlined, intuitive, and visually consistent user experience. This evolution will transform the system from a functional prototype into a robust, professional-grade enterprise application.
