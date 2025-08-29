# RISE CRM v3.9.4

A comprehensive Customer Relationship Management (CRM) system built with CodeIgniter 4.

## Features

- **Customer Management**: Complete customer database with contact information, history, and interactions
- **Lead Management**: Track and manage sales leads through the pipeline
- **Project Management**: Manage client projects with tasks, timelines, and file sharing
- **Invoice & Billing**: Generate invoices, track payments, and manage financial records
- **Email Integration**: Connect with Gmail, Outlook, and other email providers
- **Calendar Integration**: Google Calendar sync for appointments and meetings
- **Reporting & Analytics**: Comprehensive reporting dashboard with customizable metrics
- **Multi-language Support**: Available in multiple languages including English, Spanish, French, German, and more
- **Role-based Access Control**: Secure user management with customizable permissions
- **Mobile Responsive**: Works seamlessly on desktop and mobile devices

## Requirements

- **PHP**: 8.1 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache/Nginx/IIS
- **Extensions**: curl, gd, mbstring, mysqli, openssl, pdo_mysql, intl, zip, exif, fileinfo

## Installation

### Quick Start (Development)

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd RISE-v3.9.4-Nulled
   ```

2. **Install PHP dependencies** (if using Composer)
   ```bash
   composer install
   ```

3. **Start local development server**
   ```bash
   cd CRM
   php -S 127.0.0.1:8000
   ```

4. **Open installer**
   Navigate to `http://127.0.0.1:8000/install/` in your browser

5. **Complete installation**
   - Enter database credentials
   - Create admin account
   - Configure application settings

### Production Installation

1. **Upload files** to your web server
2. **Set permissions** for writable directories
3. **Configure web server** to point to the `CRM` directory
4. **Run installer** via web interface
5. **Remove install directory** after successful installation

## Configuration

### Database Setup

The installer will automatically configure your database connection. Manual configuration can be done in `app/Config/Database.php`:

```php
public $default = [
    'hostname' => 'your_db_host',
    'username' => 'your_db_user',
    'password' => 'your_db_password',
    'database' => 'your_db_name',
    'DBDriver' => 'MySQLi',
    'DBPrefix' => 'your_prefix_',
    // ... other settings
];
```

### Application Settings

Configure application settings in `app/Config/App.php`:

```php
public $baseURL = 'https://yourdomain.com/';
public $appTimezone = 'UTC';
public $encryption_key = 'your_encryption_key';
```

## Directory Structure

```
CRM/
├── app/                    # Application code
│   ├── Config/            # Configuration files
│   ├── Controllers/       # Controller classes
│   ├── Models/            # Model classes
│   ├── Views/             # View templates
│   ├── Libraries/         # Custom libraries
│   └── ThirdParty/        # Third-party libraries
├── assets/                 # Static assets (CSS, JS, images)
├── files/                  # User uploads and system files
├── system/                 # CodeIgniter core files
├── writable/               # Writable directories (logs, cache, sessions)
└── index.php              # Entry point
```

## Security Features

- **CSRF Protection**: Built-in CSRF token validation
- **SQL Injection Prevention**: Prepared statements and query builder
- **XSS Protection**: Output encoding and validation
- **Session Security**: Secure session handling
- **File Upload Security**: Restricted file types and validation

## API Endpoints

The system provides RESTful API endpoints for integration:

- **Authentication**: `/api/auth/login`, `/api/auth/logout`
- **Customers**: `/api/customers`, `/api/customers/{id}`
- **Projects**: `/api/projects`, `/api/projects/{id}`
- **Invoices**: `/api/invoices`, `/api/invoices/{id}`

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and documentation:
- **Documentation**: Check the `documentation/` folder
- **Issues**: Report bugs and feature requests via GitHub Issues
- **Community**: Join our community forums

## Changelog

### v3.9.4
- Enhanced security features
- Improved performance and caching
- Bug fixes and stability improvements
- Updated third-party libraries
- Enhanced mobile responsiveness

## Acknowledgments

- Built with [CodeIgniter 4](https://codeigniter.com/)
- Icons by [Font Awesome](https://fontawesome.com/)
- Charts by [Chart.js](https://www.chartjs.org/)
- UI components by [Bootstrap](https://getbootstrap.com/)

---

**Note**: This is a development version. For production use, ensure all security measures are properly configured and tested.
