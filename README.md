# Cloud Storage Project

## Overview
This project is a web-based cloud storage application that allows users to upload, manage, and preview their files securely. Each user has a unique folder for storing their files, and the application includes features for file upload, delete, rename, and preview functionalities.

## Features
- User registration and login
- Unique folders for each user based on username
- File upload with validation (extension and size)
- File management (delete and rename)
- File preview for PNG, JPG, PDF, and audio files
- Admin access to manage user accounts and files
- Responsive design using Bootstrap
- Visual feedback for all actions
- Security measures against XSS and SQL injection

## Project Structure
```
cloud-storage-project
├── htdocs
│   └── cloud-storage
│       ├── assets
│       │   ├── css
│       │   │   ├── bootstrap.min.css
│       │   │   └── styles.css
│       │   ├── js
│       │   │   ├── bootstrap.bundle.min.js
│       │   │   ├── main.js
│       │   │   └── ajax.js
│       │   └── images
│       ├── includes
│       │   ├── db.php
│       │   ├── header.php
│       │   └── footer.php
│       ├── uploads
│       │   └── [user-folders]
│       ├── admin
│       │   ├── index.php
│       │   └── manage-users.php
│       ├── user
│       │   ├── dashboard.php
│       │   ├── upload.php
│       │   ├── delete.php
│       │   ├── rename.php
│       │   └── preview.php
│       ├── index.php
│       ├── login.php
│       ├── register.php
│       ├── logout.php
│       └── README.md
└── README.md
```

## Setup Instructions
1. **Install XAMPP**: Download and install XAMPP from the official website.
2. **Start Apache and MySQL**: Open the XAMPP control panel and start the Apache and MySQL services.
3. **Create Database**: Access phpMyAdmin (http://localhost/phpmyadmin) and create a new database for the project.
4. **Configure Database Connection**: Update the `db.php` file in the `includes` directory with your database credentials.
5. **Upload Files**: Place the `cloud-storage` folder in the `htdocs` directory of your XAMPP installation.
6. **Access the Application**: Open your web browser and navigate to `http://localhost/cloud-storage`.

## Security Measures
- Input validation and sanitization to prevent XSS attacks.
- Prepared statements in SQL queries to protect against SQL injection.
- Proper session management for user authentication.

## Technologies Used
- HTML, CSS, JavaScript
- Bootstrap for responsive design
- PHP for server-side scripting
- MySQL for database management

## Future Enhancements
- Implement additional file types for preview.
- Enhance user interface with more interactive elements.
- Add user notifications for file actions.