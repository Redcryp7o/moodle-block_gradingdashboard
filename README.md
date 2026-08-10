# Grading Dashboard Block for Moodle

The **Grading Dashboard Block** is a production-ready dashboard block designed for teachers and administrators. It consolidates all course assignments requiring grading into a single, high-performance UI block on the user's dashboard.

## Key Features

- **Consolidated Dashboard**: Displays active courses with assignment submissions pending grading.
- **Strict Capability Checking**: Respects core Moodle permissions (`mod/assign:grade`). Admins see all courses, while teachers only see courses they have grading capabilities in.
- **N+1 Avoidance**: The database queries are optimized to fetch course and pending submission counts in a single query.
- **Premium Aesthetics**: Follows Moodle Core UI guidelines, styled using `styles.css` with smooth transitions, modern styling, and micro-animations.

---

## Technical Architecture

The plugin is architected around SOLID principles to allow easy extension (e.g., adding lazy-loading student lists or course section navigation).

### Core Components

- **`block_gradingdashboard`** (`block_gradingdashboard.php`): Entry point for the block. Instantiates the data provider and triggers rendering.
- **`block_gradingdashboard\data_provider`** (`classes/data_provider.php`): Fetches courses and submission count using Moodle DML. Decoupled from the output layer to support future caching.
- **`block_gradingdashboard\output\block`** (`classes/output/block.php`): Main renderable representation of the block.
- **`block_gradingdashboard\output\course_card`** (`classes/output/course_card.php`): Individual course card data wrapper.
- **`block_gradingdashboard\output\renderer`** (`classes/output/renderer.php`): Custom plugin renderer that delegates output to Mustache templates.
- **Mustache Templates** (`templates/`): Mustache template files that separate HTML markup from backend code.

---

## Installation

1. Copy the `gradingdashboard` folder into your Moodle site's `blocks/` directory:
   ```bash
   cp -r block_gradingdashboard moodle/blocks/gradingdashboard
   ```
2. Log in to your Moodle site as an administrator and go to **Site administration > Notifications** to trigger the installation.
3. Turn editing mode on on your **Dashboard** or **Site Home**, and add the **Grading Dashboard** block.

## Requirements

- **Moodle**: 5.2+
- **PHP**: 8.3+
- **Database**: MySQL, MariaDB, PostgreSQL, MSSQL, Oracle (full cross-database compatibility)

---

## Changelog

### v1.0.0
- Initial release candidate for Moodle Plugins Directory.
- Rebranded and refactored completely to `block_gradingdashboard`.
- Implemented modern Slate/Navy premium design theme.
- Fixed layout, chevrons, count badges, and dynamic course shortname icons.
- Implemented Moodle Privacy API compliance.
- Optimized performance with caching and N+1 query prevention.
