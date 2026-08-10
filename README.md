# Grading Dashboard Block for Moodle

The **Grading Dashboard Block** is a production-ready Moodle block designed for teachers and administrators to monitor assignment submissions awaiting grading from a single, centralized dashboard.

Teachers see pending grading activity only for courses where they have the required grading capability, while administrators can monitor pending assignments across all courses. The block organizes grading workload by **course, section, assignment, and student**, providing a clear overview with direct access to Moodle’s grading workflow.

## Key Features

- **Centralized Grading Overview** — Displays courses with assignment submissions awaiting grading in one structured dashboard.
- **Role-Aware Visibility** — Respects Moodle’s core `mod/assign:grade` capability. Administrators can monitor all applicable courses, while teachers see only courses they are authorized to grade.
- **Hierarchical Navigation** — Organizes pending work by course, section, assignment, and student for faster navigation.
- **Grading Status Indicators** — Highlights pending workload using visual status indicators based on submission age.
- **Direct Grading Access** — Provides quick access to Moodle’s assignment grading interface.
- **Lazy-Loaded Student Details** — Loads detailed student submission information only when required, reducing initial page workload.
- **Optimized Database Access** — Uses efficient Moodle DML queries and avoids N+1 query patterns when calculating pending grading counts.
- **Moodle-Native Architecture** — Uses Moodle capabilities, contexts, DML, External API/AJAX, renderables, renderers, Mustache templates, and the Privacy API.
- **Responsive Interface** — Designed to integrate cleanly with Moodle themes and remain usable across different screen sizes.
- **Accessible Controls** — Includes keyboard-friendly interactions, semantic controls, ARIA attributes, and accessible status information.
- **No Additional Database Tables** — Operates using existing Moodle course, assignment, enrolment, and submission data.
- **No Third-Party Dependencies** — Relies entirely on Moodle core APIs and functionality.

---

## Technical Architecture

The plugin uses a modular architecture with clear separation between data access, business logic, presentation, and asynchronous interactions.

### Core Components

- **`block_gradingdashboard`** (`block_gradingdashboard.php`)  
  Main block entry point responsible for initializing the dashboard and rendering its content.

- **Data Provider** (`classes/data_provider.php`)  
  Coordinates the data required by the dashboard while keeping data retrieval separate from the presentation layer.

- **Repository Layer** (`classes/repository/grading_repository.php`)  
  Handles Moodle DML queries for courses, assignments, enrolments, submissions, and pending grading counts.

- **Service Layer** (`classes/service/grading_dashboard_service.php`)  
  Coordinates dashboard business logic between the repository and presentation layers.

- **External API** (`classes/external/get_assignment_students.php`)  
  Provides secure AJAX-based retrieval of student submission details using Moodle context validation and capability checks.

- **Output Classes** (`classes/output/`)  
  Provide structured renderable data for courses, sections, assignments, students, and the main dashboard.

- **Custom Renderer** (`classes/output/renderer.php`)  
  Delegates presentation to Moodle Mustache templates.

- **Mustache Templates** (`templates/`)  
  Keep HTML presentation separate from PHP logic and provide Moodle-native output escaping.

- **AMD JavaScript** (`amd/`)  
  Handles expandable dashboard navigation and asynchronous loading of grading details.

---

## Installation

1. Download or clone the plugin and ensure the directory is named:

   ```text
   gradingdashboard
   ```

2. Place it inside your Moodle installation:

   ```text
   blocks/gradingdashboard
   ```

3. Log in as a Moodle administrator.

4. Navigate to:

   **Site administration → Notifications**

5. Complete the plugin installation process.

6. Enable editing on the required Moodle page and add the **Grading Dashboard** block.

---

## Requirements

- **Moodle:** 5.2
- **PHP:** A PHP version supported by Moodle 5.2
- **Assignment activity:** Moodle core `mod_assign`
- **External dependencies:** None
- **Additional database tables:** None

---

## Permissions

The plugin uses Moodle’s existing assignment grading capability:

```text
mod/assign:grade
```

Access is determined using the appropriate Moodle context.

- **Administrators** can view pending grading activity across applicable courses.
- **Teachers** see only courses and assignments where they have permission to grade.
- **Unauthorized users** cannot retrieve protected grading details through the AJAX endpoint.

---

## Performance

Grading Dashboard is designed to minimize unnecessary processing on dashboard load.

Key performance considerations include:

- Aggregate queries for pending grading counts
- Avoidance of N+1 query patterns
- Lazy loading of student-level details
- Parameterized Moodle DML queries
- No additional plugin database tables
- Student lists loaded only when an assignment is expanded

This keeps the initial dashboard lightweight while still providing detailed grading information when required.

---

## Privacy

The plugin does not maintain its own user-data tables or tracking system.

It reads existing Moodle course, assignment, enrolment, user, and submission information only as required to present the grading dashboard.

The plugin implements Moodle’s Privacy API through its privacy provider.

---

## Security

Grading Dashboard follows Moodle’s core security model, including:

- Moodle authentication
- Context validation
- Capability-based authorization
- Parameter validation
- Parameterized database queries
- Moodle External API protections
- Mustache output escaping
- Moodle-native AJAX integration

Sensitive grading information is available only to users with the appropriate Moodle capability.

---

## User Interface

The dashboard presents pending grading activity using an expandable hierarchy:

```text
Course
└── Section
    └── Assignment
        └── Student Submission
```

Each level provides pending grading counts and status indicators, allowing teachers and administrators to quickly identify where grading attention is required.

---

## License

This plugin is licensed under the **GNU General Public License v3 or later (GPL-3.0-or-later)**.

---

## Changelog

### v1.0.0

Initial stable release.

- Introduced centralized pending-grading dashboard
- Added administrator-wide and teacher-specific course visibility
- Added course, section, assignment, and student hierarchy
- Added pending grading counts
- Added grading-age status indicators
- Added direct grading access
- Added lazy-loaded student submission details
- Implemented Moodle-native AJAX integration
- Implemented Moodle Privacy API support
- Added accessible expandable controls
- Optimized database queries to avoid N+1 patterns
- Added responsive Moodle-compatible styling
- Added Moodle 5.2 support
