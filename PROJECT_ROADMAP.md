# SkillSprint — Project Bible

**Accessible Micro-Learning Web App for Lifelong Skills**

> SDG 4 · Quality Education | SDG 10 · Reduced Inequalities

---

# Table of Contents

1. [Project Overview](#1-project-overview)
2. [Tech Stack](#2-tech-stack)
3. [Current State Audit](#3-current-state-audit)
4. [Screen Inventory](#4-screen-inventory)
5. [Database Schema](#5-database-schema)
6. [Implementation Roadmap](#6-implementation-roadmap)
7. [AI Integration Strategy](#7-ai-integration-strategy)
8. [Accessibility Commitments](#8-accessibility-commitments)
9. [File & Folder Conventions](#9-file--folder-conventions)
10. [Milestone Checklist](#10-milestone-checklist)

---

# 1. Project Overview

**SkillSprint** is an AI-powered, accessible micro-learning web application that transforms complex topics — textbook chapters, technical manuals, and pasted notes — into bite-sized interactive lessons.

A student pastes intimidating material; the AI returns:

* A visual timeline
* Extracted key terms
* A custom 5-question MCQ quiz

—all in under 30 seconds.

## Core Value Proposition

| Problem                                                               | SkillSprint Solution                                 |
| --------------------------------------------------------------------- | ---------------------------------------------------- |
| Long video courses overwhelm working students                         | Bite-sized lessons consumable in 5–20 minute bursts  |
| Dense PDFs are inaccessible to learners with dyslexia or low literacy | AI summary + dyslexia font + TTS narration           |
| No personalized feedback on comprehension                             | Adaptive quiz engine adjusts difficulty per response |
| Gamification gaps cause drop-off                                      | XP, streaks, badges, and daily challenges            |

## Target Users

### Primary

* Working students
* Underprivileged youth seeking digital skills
* Beginner IT learners
* Learners pursuing bookkeeping or encoding skills

### Secondary

* Self-directed adult learners
* People with learning disabilities

---

# 2. Tech Stack

| Layer              | Technology                           | Version   |
| ------------------ | ------------------------------------ | --------- |
| Backend Framework  | Laravel                              | 13.x      |
| Auth Scaffolding   | laravel/ui                           | 4.x       |
| Frontend Framework | Vue 3 (Composition API)              | 3.5.x     |
| Build Tool         | Vite + laravel-vite-plugin           | 8.x / 3.x |
| CSS Utilities      | Tailwind CSS v4                      | 4.x       |
| Component Library  | Bootstrap 5                          | 5.2.x     |
| Database           | SQLite → MySQL/PostgreSQL            | —         |
| AI API             | Google Gemini API (gemini-2.0-flash) | —         |
| HTTP Client        | Guzzle                               | —         |
| Queue Driver       | Database Queue                       | —         |
| Language           | PHP 8.3 / TypeScript                 | —         |

> **AI API Choice Rationale:** Gemini 2.0 Flash offers a generous free tier suitable for a capstone prototype, supports long-context documents well, and integrates easily through PHP REST APIs.

---

# 3. Current State Audit

## Existing Components

* `routes/web.php`

  * Landing page route
  * Laravel UI auth routes
  * `/home`

* `resources/views/landing.blade.php`

  * Hero
  * Features
  * How It Works
  * Accessibility
  * UI States
  * Footer

* `resources/css/landing.css`

* `resources/css/auth.css`

* `resources/js/components/ExampleComponent.vue`

* `database/migrations/`

  * Default Laravel migrations only

* `app/Http/Controllers/HomeController.php`

* `app/Models/User.php`

## Missing Components

### Backend

* Domain migrations
* Controllers
* Services
* API routes
* AI integration layer
* Gamification engine
* Accessibility persistence

### Frontend

* Vue pages
* Shared UI components
* Dashboard
* Quiz engine
* Flashcard system
* Analytics pages

---

# 4. Screen Inventory

| #  | Screen           | Route                       | Auth Required |
| -- | ---------------- | --------------------------- | ------------- |
| 1  | Onboarding       | `/onboarding`               | Yes           |
| 2  | Quick Start      | `/quick-start`              | No            |
| 3  | Skill Library    | `/skills`                   | Yes           |
| 4  | Dashboard        | `/home`                     | Yes           |
| 5  | Upload Materials | `/upload`                   | Yes           |
| 6  | AI Summary       | `/summaries/{id}`           | Yes           |
| 7  | Flashcards       | `/flashcards/{deckId}`      | Yes           |
| 8  | Quiz             | `/quizzes/{quizId}`         | Yes           |
| 9  | Quiz Results     | `/quizzes/{quizId}/results` | Yes           |
| 10 | Analytics        | `/analytics`                | Yes           |
| 11 | Achievements     | `/achievements`             | Yes           |
| 12 | Settings         | `/settings`                 | Yes           |

---

# 5. Database Schema

## Core Tables

### users

| Column                  | Description                     |
| ----------------------- | ------------------------------- |
| id                      | Primary key                     |
| name                    | User full name                  |
| email                   | User email                      |
| password                | Hashed password                 |
| avatar                  | Profile image                   |
| bio                     | User biography                  |
| xp_total                | Total XP                        |
| level                   | Current level                   |
| daily_goal_minutes      | Daily learning target           |
| streak_current          | Current streak                  |
| streak_best             | Best streak                     |
| onboarding_completed_at | Onboarding completion timestamp |
| last_active_at          | Last activity timestamp         |
| timestamps              | Laravel timestamps              |

---

### user_preferences

| Category      | Fields                                                              |
| ------------- | ------------------------------------------------------------------- |
| Accessibility | dyslexia_font, font_size, letter_spacing, line_height, word_spacing |
| Visuals       | high_contrast, contrast_theme, bold_text, focus_indicators          |
| Audio         | tts_enabled, tts_speed, auto_read_cards, auto_read_questions        |
| Motion        | reduce_motion, slow_flip_speed                                      |
| Learning      | learning_goals, learning_pace, difficulty_default                   |
| Localization  | output_language, simplify_language                                  |
| Extras        | visual_diagrams, notifications, offline_mode                        |

---

### skills

| Column            | Description                        |
| ----------------- | ---------------------------------- |
| title             | Skill title                        |
| slug              | URL slug                           |
| description       | Short overview                     |
| category          | Technology / Science / etc.        |
| level             | Beginner / Intermediate / Advanced |
| icon              | Skill icon                         |
| estimated_minutes | Estimated study duration           |
| learner_count     | Number of learners                 |
| tags              | JSON tags                          |
| is_featured       | Featured flag                      |
| is_popular        | Popular flag                       |

---

### uploads

| Column            | Description                    |
| ----------------- | ------------------------------ |
| original_filename | Uploaded filename              |
| type              | text/pdf/image/url             |
| raw_content       | Extracted text                 |
| file_path         | Storage path                   |
| word_count        | Word count                     |
| status            | pending/processing/done/failed |
| processed_at      | Completion timestamp           |

---

### summaries

| Column            | Description               |
| ----------------- | ------------------------- |
| title             | Generated title           |
| difficulty        | AI-assigned difficulty    |
| estimated_minutes | Estimated completion time |
| content_sections  | JSON lesson sections      |
| timeline_steps    | JSON timeline             |
| key_terms         | JSON terminology          |
| source_filename   | Original source file      |

---

### quizzes

| Column         | Description       |
| -------------- | ----------------- |
| mode           | adaptive/standard |
| question_count | Total questions   |
| difficulty     | Quiz difficulty   |

---

### quiz_questions

| Column         | Description        |
| -------------- | ------------------ |
| body           | Question body      |
| correct_option | Correct answer     |
| explanation    | Answer explanation |
| type           | Concept type       |
| difficulty     | easy/medium/hard   |
| sort_order     | Question order     |

---

### badges

| Column      | Description       |
| ----------- | ----------------- |
| slug        | Badge slug        |
| title       | Badge title       |
| description | Badge description |
| icon        | Badge icon        |
| xp_reward   | XP reward         |
| criteria    | JSON criteria     |

---

# 6. Implementation Roadmap

---

## Phase 0 — Foundation Setup & Shared Layout

### Goal

Create a shared authenticated application shell.

### Tasks

* Create authenticated layout
* Create guest layout
* Configure Vue 3
* Build reusable shared components
* Configure dark theme variables
* Run default migrations

### Deliverable

A logged-in user can access `/home` with the shared sidebar layout.

---

## Phase 1 — Auth & Onboarding

### Goal

Registration → onboarding → dashboard flow.

### Key Features

* Multi-step onboarding wizard
* Accessibility preferences
* Learning goals
* Progress tracking

### Deliverable

User registers and completes onboarding before accessing dashboard.

---

## Phase 2 — Skill Library

### Goal

Allow users to browse and enroll in skills.

### Features

* Search
* Filters
* Categories
* Skill cards
* Enrollment system

### Deliverable

Users can browse and enroll in learning paths.

---

## Phase 3 — Upload & AI Processing

### Goal

Allow AI-powered lesson generation.

### Input Types

* Paste text
* Upload PDF/DOCX
* Upload images (OCR)
* Submit URLs

### Processing Flow

1. Upload content
2. Queue processing job
3. Generate summary
4. Generate flashcards
5. Generate quiz
6. Redirect to lesson

### Deliverable

User uploads material and receives AI-generated study content.

---

## Phase 4 — AI Summary View

### Features

* Structured lesson cards
* Learning timeline
* Key terminology
* Difficulty indicators
* Accessibility controls
* Audio narration

### Deliverable

AI-generated lessons render correctly with interactive study tools.

---

## Phase 5 — Flashcards

### Features

* 3D flip cards
* Progress tracking
* Keyboard shortcuts
* XP rewards
* Accessibility support

### Deliverable

Complete flashcard study mode with persistence.

---

## Phase 6 — Adaptive Quiz Engine

### Features

* Adaptive difficulty
* Live XP tracking
* Timed questions
* Quiz review
* Achievement rewards

### Deliverable

Dynamic quiz experience with analytics and progression.

---

## Phase 7 — Dashboard

### Features

* XP tracking
* Current streak
* Continue learning
* Recommendations
* Daily challenges
* Progress summaries

### Deliverable

Personalized learning dashboard.

---

## Phase 8 — Analytics

### Features

* Weekly study charts
* Skill growth tracking
* Quiz accuracy
* Time investment charts
* AI-powered insights

### Deliverable

Comprehensive analytics dashboard.

---

## Phase 9 — Achievements

### Features

* Badge collection
* XP history
* Leaderboards
* Progress tracking

### Deliverable

Gamified achievement system.

---

## Phase 10 — Settings & Accessibility

### Features

* Dyslexia mode
* High contrast themes
* TTS controls
* Font adjustments
* Keyboard navigation settings

### Deliverable

Persistent accessibility customization.

---

## Phase 11 — QA & Submission

### QA Checklist

* Cross-browser testing
* Mobile responsiveness
* WCAG 2.2 AA audit
* Keyboard navigation
* Screen reader testing
* Error handling validation

### Submission Requirements

* Working deployment
* Demo seed data
* README documentation
* Optional demo walkthrough video

---

# 7. AI Integration Strategy

## API Configuration

```txt
Endpoint:
https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent

Environment Variable:
GEMINI_API_KEY
```

## AI Responsibilities

### Summary Generation

Produces:

* Structured lessons
* Timelines
* Key terms
* Difficulty ratings

### Quiz Generation

Produces:

* Multiple-choice questions
* Adaptive difficulty
* Explanations
* Distractors

---

## Queue Flow

```txt
POST /upload
    ↓
UploadController::store()
    ↓
ProcessUploadJob
    ↓
Generate Summary
    ↓
Generate Flashcards
    ↓
Generate Quiz
    ↓
Save Database Records
    ↓
Update Upload Status
```

---

# 8. Accessibility Commitments

SkillSprint follows **WCAG 2.2 Level AA** standards.

| Requirement           | Implementation                         |
| --------------------- | -------------------------------------- |
| Keyboard navigation   | Full keyboard support                  |
| Focus indicators      | 2px visible focus ring                 |
| Color contrast        | WCAG-compliant contrast ratios         |
| Dyslexia font         | OpenDyslexic integration               |
| Text-to-Speech        | Web Speech API                         |
| Reduced motion        | Respects prefers-reduced-motion        |
| Screen reader support | ARIA labels and live regions           |
| Skip links            | Included globally                      |
| Semantic HTML         | Proper landmarks and heading hierarchy |

---

# 9. File & Folder Conventions

## Backend Structure

```txt
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
├── Services/
├── Jobs/
├── Models/
```

## Database Structure

```txt
database/
├── migrations/
├── seeders/
```

## Frontend Structure

```txt
resources/
├── css/
├── js/
│   ├── components/
├── views/
```

## Routes

```txt
routes/
├── web.php
├── api.php
```

---

# 10. Milestone Checklist

```txt
[ ] Phase 0  — Foundation & shared layout
[ ] Phase 1  — Auth flow + onboarding wizard
[ ] Phase 2  — Skill library
[ ] Phase 3  — Upload hub + AI processing
[ ] Phase 4  — AI summary view
[ ] Phase 5  — Flashcard study mode
[ ] Phase 6  — Quiz engine + results
[ ] Phase 7  — Dashboard
[ ] Phase 8  — Analytics page
[ ] Phase 9  — Achievements & badges
[ ] Phase 10 — Settings & accessibility
[ ] Phase 11 — QA, polish, demo seed
```

---
