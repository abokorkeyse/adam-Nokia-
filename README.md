# Adam Nokia Mobile Management System

A comprehensive web-based management system for Nokia mobile devices, designed to streamline device management, customer information, and technician assignments.

## Features

- **User Authentication**
  - Secure login system
  - Role-based access control
  - Session management

- **Device Management**
  - Add, edit, and delete devices
  - View device details
  - Track device status

- **Customer Management**
  - Add, edit, and delete customer records
  - View customer information
  - Track customer history

- **User Management**
  - Add, edit, and delete users
  - Assign roles and permissions
  - Manage technician assignments

## Technical Requirements

- PHP 7.4 or higher
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/abokorkeyse/adam-Nokia-.git
   ```

2. Set up your web server to point to the project directory

3. Import the database schema:
   - Run the `setup_database.php` script
   - Or import the SQL file from the `config` directory

4. Configure the database connection:
   - Edit `config/database.php`
   - Update database credentials

5. Access the system through your web browser

## Directory Structure

```
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── data_flow.php
├── index.php
├── dashboard.php
├── profile.php
├── setup.php
└── [other PHP files]
```

## Security Features

- Password hashing
- SQL injection prevention
- XSS protection
- Session management
- Input validation

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository or contact the development team. 