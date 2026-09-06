# Grading Dashboard for Moodle

[![Moodle 5.2](https://img.shields.io/badge/Moodle-5.2-orange.svg)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-Moodle%205.2%20compatible-blue.svg)](https://php.net)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-green.svg)](http://www.gnu.org/copyleft/gpl.html)

**Grading Dashboard** (`block_gradingdashboard`) is a Moodle 5.2 block plugin that gives teachers and administrators a centralized view of assignment submissions awaiting grading.

Teachers see pending grading activity only for courses where they hold Moodle's `mod/assign:grade` capability, while administrators can monitor applicable pending assignments across courses. The dashboard organizes workload by **course, section, assignment, and student**, with direct access to Moodle's grading workflow.

---

## Key Features

- **Centralized Grading Overview**  
  Displays assignment submissions awaiting grading in one structured dashboard.

- **Role-Aware Visibility**  
  Uses Moodle's core `mod/assign:grade` capability so teachers see only authorized grading work while administrators can monitor applicable courses more broadly.

- **Hierarchical Navigation**  
  Organizes pending work by:
  - Course
  - Section
  - Assignment
  - Student submission

- **Pending Grading Indicators**  
  Shows pending counts and grading-age indicators to help identify work that needs attention.

- **Direct Grading Access**  
  Provides quick links to Moodle's native assignment grading interface.

- **Lazy-Loaded Student Details**  
  Loads student-level submission information only when an assignment is expanded.

- **Optimized Moodle DML Queries**  
  Uses aggregate queries and avoids N+1 query patterns when calculating pending grading counts.

- **Moodle-Native Architecture**  
  Uses Moodle capabilities, contexts, DML, External API/AJAX, renderables, renderers, Mustache templates, AMD JavaScript, and the Privacy API.

- **Responsive Interface**  
  Designed to integrate cleanly with Moodle themes and remain usable across different screen sizes.

- **Accessible Controls**  
  Uses semantic controls, keyboard-accessible interactions, ARIA attributes, and accessible status information.

- **No Additional Database Tables**  
  Operates using existing Moodle course, assignment, enrolment, user, and submission data.

- **No Third-Party Dependencies**  
  Relies on Moodle core APIs and functionality.

---

## Requirements

| Component | Requirement |
|---|---|
| **Moodle** | Moodle 5.2 |
| **Plugin type** | Block |
| **Component** | `block_gradingdashboard` |
| **PHP** | A PHP version supported by Moodle 5.2 |
| **Assignment activity** | Moodle core `mod_assign` |
| **Additional database tables** | None |
| **External dependencies** | None |

---

## Installation

### Option 1 — Manual Installation

1. Download or clone the plugin.
2. Ensure the plugin directory is named:

   ```text
   gradingdashboard
   ```

3. Place it in:

   ```text
   blocks/gradingdashboard
   ```

4. Log in to Moodle as an administrator.
5. Navigate to:

   **Site administration → Notifications**

6. Complete the plugin installation or upgrade process.

### Option 2 — Install from ZIP

1. Navigate to:

   **Site administration → Plugins → Install plugins**

2. Upload the Grading Dashboard plugin ZIP.
3. Follow Moodle's installation prompts.
4. Complete the installation process.

---

## Add Grading Dashboard to Moodle

After installation:

1. Navigate to the Moodle page where the block should be displayed.
2. Enable **Edit mode**.
3. Select **Add a block**.
4. Choose **Grading Dashboard**.

The block displays pending grading information according to the current user's Moodle permissions.

---

## Permissions and Access Control

Grading Dashboard uses Moodle's existing assignment grading capability:

```text
mod/assign:grade
```

Access is determined using the appropriate Moodle context.

- **Administrators** can monitor applicable pending grading activity across courses.
- **Teachers** see only courses and assignments where they are authorized to grade.
- **Unauthorized users** cannot retrieve protected grading details through the AJAX endpoint.

The plugin does not introduce a parallel grading-permission model; it relies on Moodle's native authorization system.

---

## Dashboard Structure

Pending grading activity is presented using an expandable hierarchy:

```text
Course
└── Section
    └── Assignment
        └── Student Submission
```

Each level provides pending grading counts and status information so teachers and administrators can quickly identify where grading attention is required.

---

## Technical Architecture

Grading Dashboard uses a modular architecture that separates data access, business logic, presentation, and asynchronous interactions.

### Core Components

- **Main block class** — `block_gradingdashboard.php`  
  Initializes the block and coordinates dashboard rendering.

- **Data provider** — `classes/data_provider.php`  
  Prepares the data required by the dashboard.

- **Repository layer** — `classes/repository/grading_repository.php`  
  Handles Moodle DML queries for courses, assignments, enrolments, submissions, and pending grading counts.

- **Service layer** — `classes/service/grading_dashboard_service.php`  
  Coordinates dashboard business logic between the repository and presentation layers.

- **External API** — `classes/external/get_assignment_students.php`  
  Retrieves student submission details through Moodle's External API/AJAX system with context and capability validation.

- **Output classes** — `classes/output/`  
  Prepare renderable data for courses, sections, assignments, students, and the main dashboard.

- **Renderer** — `classes/output/renderer.php`  
  Connects structured output data with Moodle Mustache templates.

- **Mustache templates** — `templates/`  
  Keep HTML presentation separate from PHP logic and use Moodle-native escaping behavior.

- **AMD JavaScript** — `amd/`  
  Handles expandable dashboard interactions and asynchronous loading of student details.

---

## Performance

Grading Dashboard is designed to minimize unnecessary processing during the initial dashboard load.

Key performance considerations include:

- Aggregate queries for pending grading counts.
- Avoidance of N+1 query patterns.
- Lazy loading of student-level details.
- Parameterized Moodle DML queries.
- No additional plugin database tables.
- Student lists loaded only when an assignment is expanded.

This keeps the initial dashboard focused while still allowing detailed grading information to be loaded when needed.

---

## Privacy

Grading Dashboard does not maintain its own user-data tables or separate tracking database.

It reads existing Moodle course, assignment, enrolment, user, and submission information only as needed to present authorized grading information.

The plugin implements Moodle's Privacy API through its privacy provider.

---

## Security

Grading Dashboard follows Moodle's core security model, including:

- Moodle authentication.
- Context validation.
- Capability-based authorization.
- Parameter validation.
- Parameterized Moodle DML queries.
- Moodle External API protections.
- Mustache output escaping.
- Moodle-native AJAX integration.

Sensitive grading information is available only to users with the appropriate Moodle grading capability.

---

## Accessibility

The interface is designed to support accessible navigation and interaction through:

- Semantic interactive controls.
- Keyboard-accessible expand/collapse behavior.
- ARIA attributes for expandable elements.
- Accessible status information.
- Responsive layouts compatible with Moodle themes.

---

## Changelog

### v1.0.1

Moodle Plugin Directory review remediation:

- Added complete Moodle GPL boilerplate headers across PHP, AMD JavaScript, Mustache, and CSS sources.
- Moved hard-coded pending badge / tooltip / aria-label text into language strings (`pendingcount`, `pendingtooltip`).
- Switched AMD empty-state string loading to Moodle `core/str`.
- Rebuilt `amd/build/tree.min.js` with Moodle 5.2 official `npx grunt amd` tooling.

### v1.0.0

Initial stable release.

Included:

- Centralized pending-grading dashboard.
- Administrator-wide and teacher-specific course visibility.
- Course, section, assignment, and student hierarchy.
- Pending grading counts.
- Grading-age status indicators.
- Direct grading access.
- Lazy-loaded student submission details.
- Moodle-native AJAX integration.
- Moodle Privacy API support.
- Accessible expandable controls.
- Query optimizations to avoid N+1 patterns.
- Responsive Moodle-compatible styling.
- Moodle 5.2 support.

---

## Support

For bug reports, feature requests, or technical questions, use the issue tracker associated with the public source repository for this plugin.

When reporting an issue, include:

- Moodle version.
- PHP version.
- Plugin version.
- Relevant error message or reproduction steps.

---

## License

Grading Dashboard is licensed under the **GNU General Public License v3.0 or later (GPL-3.0-or-later)**.

See the included `LICENSE` file for details.
