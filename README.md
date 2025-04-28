# Smart Attendance Management System

A web-based attendance management system with role-based access control, real-time attendance tracking, and multi-language support.

## Features

- Role-based access (Admin, Lecturer, Student)
- Real-time attendance tracking
- Multi-language support (English, Swahili, French, Arabic)
- Dark/Light mode themes
- Responsive design
- Secure authentication system
- Attendance reports and analytics

## Technology Stack

- Backend: PHP
- Database: MySQL
- Frontend: HTML, CSS, JavaScript
- UI Framework: Bootstrap
- Icons: FontAwesome

## Installation

1. Clone the repository
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Set up your web server (Apache/Nginx) to point to the project directory
5. Access the application through your web browser

## Project Structure

```
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   ├── database.php
│   └── config.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   └── header.php
├── lang/
│   ├── en.php
│   ├── sw.php
│   ├── fr.php
│   └── ar.php
├── modules/
│   ├── admin/
│   ├── lecturer/
│   └── student/
└── index.php
```

## License

MIT License 