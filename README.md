# Online Examination System

A web-based examination system that allows students to take MCQ tests and administrators to manage questions and view results.

## Features

- Student Login with name and roll number
- Admin Login with username and password
- Student Dashboard with test access
- MCQ Test with auto-submit functionality
- Admin Dashboard with student results
- Question Management (Add, Edit, Delete)
- Score Export to Excel

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone the repository to your web server directory
2. Create a MySQL database and import the schema:
   ```bash
   mysql -u root -p < database.sql
   ```
3. Configure the database connection in `includes/db.php`:
   ```php
   $host = 'localhost';
   $dbname = 'online_exam';
   $username = 'your_username';
   $password = 'your_password';
   ```

## Default Admin Account

- Username: admin
- Password: admin123

## Usage

### For Students

1. Access the system through your web browser
2. Login with your name and roll number
3. Click "Start Test" to begin the examination
4. Answer all questions (MCQ format)
5. Test will auto-submit when all questions are answered or time runs out
6. View your score after submission

### For Administrators

1. Login with admin credentials
2. View all students and their test results
3. Manage questions (add, edit, delete)
4. Export student scores to Excel
5. Monitor test progress and results

## Security Features

- Session-based authentication
- Password hashing for admin accounts
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars
- CSRF protection through session tokens

## File Structure

```
/project-root
│
├── index.html           # Login selection
├── student_login.php    # Student login handler
├── admin_login.php      # Admin login handler
├── student_dashboard.php # Student dashboard
├── test.php            # Test interface
├── submit_test.php     # Test submission handler
├── admin_dashboard.php  # Admin dashboard
├── edit_questions.php  # Question management
├── export_scores.php   # Score export
├── logout.php          # Logout handler
│
├── /css
│   └── styles.css      # Stylesheet
│
├── /js
│   └── scripts.js      # JavaScript functions
│
└── /includes
    ├── db.php          # Database connection
    └── auth.php        # Authentication functions
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 