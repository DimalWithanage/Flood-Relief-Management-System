# Flood Relief Management System

## 1. Introduction

### 1.1 Project Description
The Flood Relief Management System is a web-based application developed as the final group project for CSC 1051 – Full-Stack Fundamentals. The system is designed to support the collection and management of relief data during flood situations in Sri Lanka. It provides a structured platform for flood-affected individuals to register, submit relief requests, and for administrators to manage users and generate summarized reports.

This project was implemented entirely using the mandatory technology stack covered in the module, comprising HTML, CSS, JavaScript for the frontend; PHP for the backend; and MySQL as the relational database, managed via phpMyAdmin. No PHP frameworks were used, reflecting a fundamental understanding of full-stack web development from the ground up.

### 1.2 Objectives of the System
The primary objectives of the Flood Relief Management System are:
- To provide a secure user authentication system with role-based access control (Admin and Affected Person).
- To allow flood-affected individuals to submit, view, update, and delete their relief requests.
- To enable administrators to view and manage all registered users and their relief requests.
- To generate dynamic, filtered summary reports from database queries to support relief decision-making.
- To deliver a clean, user-friendly interface that is accessible and intuitive for all user types.
- To implement all functionality using the specified technology stack without relying on external frameworks.

## 2. System Overview

### 2.1 Overall System Description
The Flood Relief Management System is a multi-user web application that operates on a client-server architecture. The frontend, built with HTML, CSS, and JavaScript, communicates with a PHP backend through asynchronous HTTP requests. The PHP scripts interact with a MySQL database to perform all Create, Read, Update, and Delete (CRUD) operations.

The system consists of the following main modules:
- User Authentication Module – handles registration, login, session management, and logout.
- Relief Request Module – allows affected persons to submit and manage their relief requests.
- Admin Management Module – gives administrators full control over user accounts and request data.
- Reporting Module – generates dynamic, filtered summary reports from database queries.

### 2.2 Target Users

| Role | Description |
| ---- | ----------- |
| Admin | A system administrator who manages registered users, monitors all relief requests, and generates filtered summary reports. |
| Affected Person | A flood-affected individual who registers on the system, submits relief requests, and manages their own requests. |

### 2.3 Technologies Used
The following technologies were used and integrated in the project:

| Layer | Technology | Purpose |
| ----- | ---------- | ------- |
| Frontend | HTML, CSS, JavaScript | Structure, styling, and dynamic client-side behavior |
| Backend | PHP | Server-side logic, session handling, API endpoints |
| Database | MySQL via phpMyAdmin | Persistent data storage for users and requests |
| Integration | Fetch API (AJAX) | Asynchronous communication between frontend and PHP |
| Environment | XAMPP / Apache | Local development server |

### 2.4 Database Design
The MySQL database is named `flood_relief_db` and consists of two primary tables that store all system data. All database interactions are handled through parameterized PHP queries to prevent SQL injection.

#### 2.4.1 Table: users
Stores the credentials and profile information for all registered users.

| Column | Type | Constraint | Description |
| ------ | ---- | ---------- | ----------- |
| id | INT | PK, AUTO_INCREMENT | Unique user identifier |
| full_name | VARCHAR(100) | NOT NULL | Login username |
| email | VARCHAR(100) | NOT NULL, UNIQUE | User email address |
| password | VARCHAR(255) | NOT NULL | User password |
| phone | VARCHAR(20) | NOT NULL | User phone number |
| address | TEXT | NOT NULL | User address |
| role | ENUM | NOT NULL, DEFAULT 'user' | User role |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account created time |

#### 2.4.2 Table: relief_requests
Stores all flood relief requests submitted by affected persons.

| Column | Type | Constraint | Description |
| ------ | ---- | ---------- | ----------- |
| id | INT | PK, AUTO_INCREMENT | Unique request identifier |
| user_id | INT | FK → users.id | Owner of the request |
| relief_type | ENUM | NOT NULL | Food / Water / Medicine / Shelter |
| district | VARCHAR(100) | NOT NULL | Affected district |
| divisional_secretariat | VARCHAR(100) | NOT NULL | Divisional Secretariat |
| gn_division | VARCHAR(100) | NOT NULL | GN Division |
| contact_person | VARCHAR(150) | NOT NULL | Contact person name |
| contact_number | VARCHAR(20) | NOT NULL | Contact phone number |
| address | TEXT | NOT NULL | Full address |
| family_members | INT | NOT NULL | Number of family members |
| severity | ENUM | NOT NULL | Low / Medium / High |
| description | TEXT | NULLABLE | Additional requirements |
| created_at | TIMESTAMP | DEFAULT NOW() | Request submission time |

### 2.5 Backend Logic
The backend is implemented entirely in plain PHP, with each PHP file acting as a dedicated API endpoint. All endpoints respond in JSON format and are consumed by the JavaScript frontend via the Fetch API. Session-based authentication is used to maintain user state across requests.

The PHP file structure and responsibilities are as follows:

| PHP File | Responsibility |
| -------- | -------------- |
| db_connect.php | Establishes the MySQLi database connection. Shared by all other endpoints via require. |
| register.php | Handles new user registration. Validates input and inserts into the users table. |
| login.php | Authenticates credentials using password, starts a PHP session, and returns role information to the frontend. |
| logout.php | Destroys the active PHP session and redirects the user to the login/index page. |
| create_request.php | Accepts POST data from the dashboard form and inserts a new relief request into the relief_requests table. |
| get_requests.php | Fetches all relief requests belonging to the currently logged-in user and returns them as a JSON array. |
| update_request.php | Updates an existing relief request. Verifies the request belongs to the session user before updating. |
| delete_request.php | Deletes a specific relief request. Validates ownership before deletion. |
| get_users.php | Admin-only endpoint. Returns a list of all registered users. |
| delete_user.php | Admin-only endpoint. Deletes a registered user account from the system. |
| view_user_report.php | Admin-only endpoint. Returns a detailed summary report for a selected user and all their relief requests. |
| get_reports.php | Admin-only endpoint. Accepts filter parameters (area, relief type) and returns aggregated summary statistics using MySQL GROUP BY queries. |

### 2.6 Frontend Functionality
The frontend is built with semantic HTML, custom CSS3, and vanilla JavaScript (ES6+). The application is structured across multiple HTML pages, each serving a distinct role in the user workflow.

| Page / File | Functionality |
| ----------- | ------------- |
| index.html | The main landing/login page. Displays the login form and links to the registration page. |
| register.html | New user registration form with client-side validation before submitting to register.php. |
| dashboard.html | The authenticated user's main interface. Allows submission of new relief requests, viewing existing requests, editing request details, and deleting requests. |
| admin.html | The administrator's control panel. Displays all registered users, enables deletion of users, and provides access to detailed user reports and filtered summary reports. |
| app.js | Central JavaScript file handling all Fetch API calls, DOM manipulation, form submissions, dynamic table rendering, modal management, and pop-up confirmation messages. |
| style.css | Global stylesheet providing consistent typography, color scheme, form styling, table layout, responsive design, and modal/popup styling. |

All CRUD operations are handled asynchronously using the Fetch API. After each successful operation, a pop-up confirmation message is displayed to the user (e.g., "Your relief request has been created successfully.").