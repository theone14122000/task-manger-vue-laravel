# Task Manager Application

A modern full-stack task management application built with Laravel and Vue 3.

This project was created to practice real-world full-stack development concepts including authentication, REST APIs, drag-and-drop task management, file uploads, pagination, admin dashboards, and responsive UI design.

---

# Table of Contents

1. Project Overview
2. Features
3. Tech Stack
4. Application Modules
5. Screenshots Section
6. Installation Guide
7. Backend Setup
8. Frontend Setup
9. API Features
10. Database Structure
11. Drag & Drop Functionality
12. File Upload System
13. Admin Dashboard
14. Search & Filtering
15. Pagination
16. Future Improvements
17. Deployment
18. Author
19. License

---

# Project Overview

This application allows users to create and manage tasks using a modern Kanban-style interface.

Users can:

- Create tasks
- Update tasks
- Delete tasks
- Move tasks between columns
- Upload attachments
- Set priorities and due dates
- Search and filter tasks

Admins can manage all user tasks through a dedicated admin dashboard.

---

# Features

## Authentication

- User Login
- User Registration
- Protected Routes
- API Authentication using Laravel Sanctum

## Task Management

- Create Tasks
- Edit Tasks
- Delete Tasks
- Update Task Status
- Set Due Dates
- Task Priorities

## Kanban Board

- Todo Column
- In Progress Column
- Completed Column
- Drag & Drop Support
- Auto Save Task Order

## File Uploads

- Upload Attachments
- View Uploaded Files
- File Storage using Laravel Storage

## Dashboard Features

- Total Tasks Counter
- Completed Tasks Counter
- Pending Tasks Counter
- High Priority Tasks Counter

## UI Features

- Dark Mode
- Responsive Layout
- Toast Notifications
- Modern Dashboard Design

## Additional Features

- Pagination
- Search Tasks
- Filter Tasks
- User Avatars
- API-based Architecture

---

# Tech Stack

## Frontend

- Vue 3
- TypeScript
- Tailwind CSS
- Axios
- Vue Toastification
- VueDraggable
- Vite

## Backend

- Laravel
- PHP
- MySQL
- Laravel Sanctum
- REST APIs

---

# Application Modules

## User Module

Users can:

- Register account
- Login securely
- Manage personal tasks
- Upload task attachments
- Track task progress

## Admin Module

Admins can:

- View all tasks
- Manage user tasks
- Monitor task statistics
- Reorder tasks using drag and drop
- Delete any task

---

# Screenshots Section

You can add screenshots here later.

Example:

```md
![Dashboard Screenshot](screenshots/dashboard.png)
```

---

# Installation Guide

Clone the repository:

```bash
git clone <your-repository-url>
```

---

# Backend Setup (Laravel)

Move into backend directory:

```bash
cd backend
```

Install dependencies:

```bash
composer install
```

Copy environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

---

# Database Configuration

Update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Create storage link:

```bash
php artisan storage:link
```

Start backend server:

```bash
php artisan serve
```

---

# Frontend Setup (Vue)

Move into frontend directory:

```bash
cd frontend
```

Install dependencies:

```bash
npm install
```

Start development server:

```bash
npm run dev
```

---

# API Features

The backend provides RESTful APIs for:

- Authentication
- Task CRUD Operations
- Task Reordering
- Pagination
- File Uploads
- Admin Task Management

---

# Database Structure

## Users Table

Stores:

- User name
- Email
- Password
- Avatar

## Tasks Table

Stores:

- Task title
- Status
- Priority
- Due date
- Attachment
- Position
- Completion status
- User relationship

---

# Drag & Drop Functionality

Tasks can be moved between:

- Todo
- In Progress
- Completed

Task order and status are automatically updated in the database.

This feature is implemented using:

- VueDraggable
- Laravel reorder API

---

# File Upload System

Users can upload:

- JPG
- PNG
- WEBP
- PDF
- DOC
- DOCX

Uploaded files are stored using Laravel public storage.

---

# Admin Dashboard

The admin dashboard includes:

- Task statistics
- User task management
- Kanban board
- Drag & drop ordering
- Task filtering
- Pagination
- Attachment viewing

---

# Search & Filtering

Tasks can be:

- Searched by title
- Filtered by completion status
- Sorted by task position

---

# Pagination

Pagination is implemented on both frontend and backend.

Features include:

- Next/Previous navigation
- Current page tracking
- Total pages handling

---

# Future Improvements

Planned improvements:

- Role-based permissions
- Team collaboration
- Real-time notifications
- Email reminders
- Activity logs
- Docker support
- CI/CD pipelines
- Unit testing
- Redis caching

---

# Deployment

## Frontend Deployment

Recommended:

- Vercel

## Backend Deployment

Recommended:

- Render

## Database

- MySQL
- PostgreSQL

---

# Author

Shubham Sharma.
