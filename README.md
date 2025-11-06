Ghanaian Personal Lawyer Website

A complete, responsive website for a Ghana-based personal lawyer built with modern web technologies. Features include a floating AI chatbot, secure AJAX forms, PHP backend, MySQL database, and automated email notifications.

Table of Contents
- [Ghanaian Personal Lawyer Website](#ghanaian-personal-lawyer-website)
  - [Table of Contents](#-table-of-contents)
  - [Features](#-features)
  - [Quick start](#quick-start)
  - [API Endpoints](#api-endpoints)
  - [Security Features](#security-features)
  - [Ghanaian Legal Context](#ghanaian-legal-context)
  - [Features \& Technology Stack](#features--technology-stack)
    - [Frontend](#frontend)
    - [Backend](#backend)
    - [AI Integration](#ai-integration)
  - [Admin Panel](#admin-panel)
    - [Access \& Authentication](#access--authentication)
    - [Default Credentials](#default-credentials)
    - [Admin Features](#admin-features)
    - [Admin Account Management](#admin-account-management)
  - [Configuration](#-configuration)
    - [Database Setup](#database-setup)
    - [Email Configuration](#email-configuration)
    - [AI Chatbot Setup](#ai-chatbot-setup)
  - [Deployment](#-deployment)
    - [Requirements](#requirements)
    - [Production Checklist](#production-checklist)
  - [Contributing](#-contributing)
  - [License](#-license)
  - [Author](#-author)
  - [Acknowledgments](#-acknowledgments)

## Features
- **Responsive Design**: Mobile-first approach with Ghanaian cultural elements
- **AI-Powered Chatbot**: Intelligent legal assistance with Ghana-specific knowledge
- **Secure Forms**: AJAX-powered contact, appointment, and complaint forms
- **Admin Dashboard**: Complete management system for clients and appointments
- **Email Notifications**: Automated SMTP email system
- **Multi-Admin Support**: Create and manage multiple administrator accounts
- **Ghanaian Legal Context**: Localized content for Ghanaian law practices

## Quick start
1. Copy the folder to your PHP server root (e.g., XAMPP `htdocs`).
2. Create a MySQL database called `ghana_lawyer` and run `server/sql/schema.sql`.
3. Edit `server/config.php` with your DB credentials and email settings.
4. If you want SMTP, `composer require phpmailer/phpmailer` and ensure autoloading in your project, then set `smtp.enabled=true` in config.
5. Visit `index.html` in your browser via your local server URL.
6. Access admin panel at `admin/login.php` (default: admin/password123).
7. Create additional admin accounts via `admin/register.php` or manage them in the admin panel.

## API Endpoints
- `server/handlers/submit_contact.php` - Handle contact form submissions
- `server/handlers/submit_appointment.php` - Process appointment bookings
- `server/handlers/submit_complaint.php` - Manage complaint submissions
- `server/handlers/chatbot.php` - AI-powered chatbot responses

All endpoints accept JSON POST and return `{ ok: boolean, message?: string, error?: string }`.

## Security Features
- **Database Security**: Prepared statements to prevent SQL injection
- **Rate Limiting**: Basic IP-based rate limiting on form submissions
- **Input Validation**: Server-side validation for all user inputs
- **Password Security**: Bcrypt hashing for admin passwords
- **Session Management**: Secure session handling for admin authentication
- **CORS Protection**: Configured CORS headers for API endpoints
- **Production Recommendations**: Add CSRF tokens and Google reCAPTCHA

## Ghanaian Legal Context
Text uses Ghana-appropriate terms: 1992 Constitution, Lands Commission, Land Act, GIS procedures, Registrar of Companies, professional conduct rules. Adapt to your exact practice.

## Features & Technology Stack

### Frontend
- **Responsive Design**: Mobile-first approach with CSS Grid and Flexbox
- **Ghanaian Branding**: Kente-inspired patterns, Adinkra symbols, and Ghana colors
- **Animations**: Smooth intersection reveals and micro-interactions
- **JavaScript**: AJAX form submissions, chatbot functionality, and interactive elements

### Backend
- **PHP**: Server-side processing with PDO for database interactions
- **MySQL**: Relational database with proper schema design
- **Email Integration**: PHPMailer for SMTP and HTML email notifications
- **API Design**: RESTful endpoints with JSON responses

### AI Integration
- **Chatbot**: OpenAI GPT integration for intelligent legal assistance
- **Fallback Mode**: Mock responses when API key is not configured
- **Legal Expertise**: Specialized prompts for Ghanaian law context



## Admin Panel

### Access & Authentication
- **Login**: `admin/login.php`
- **Registration**: `admin/register.php` (for creating new admin accounts)
- **Management**: `admin/admins.php` (for managing existing admin accounts)

### Default Credentials
- Username: `admin`
- Password: `password123`
- Email: `admin@akotochambers.com`
- **Important**: Change the default password immediately after first login.

### Admin Features
- **Dashboard**: Overview of clients, appointments, and complaints
- **Client Management**: View and manage client inquiries
- **Appointment Management**: Schedule and update appointment statuses
- **Admin Management**: Create, edit, and delete admin accounts
- **Email Notifications**: Automatic notifications for form submissions

### Admin Account Management
- Create new admin accounts via `admin/register.php` or through the admin panel
- Manage existing accounts via `admin/admins.php`
- Secure password hashing and validation
- Role-based access control (all admins have full access)

## Configuration

### Database Setup
1. Create MySQL database: `ghana_lawyer`
2. Run `server/sql/schema.sql` to create tables
3. Update `server/config.php` with your database credentials

### Email Configuration
- **SMTP Setup**: Configure SMTP settings in `server/config.php`
- **PHPMailer**: Install via Composer: `composer install`
- **Fallback**: Uses PHP mail() if SMTP not configured

### AI Chatbot Setup
- Add OpenAI API key to `server/config.php`
- Fallback to mock responses if API key not provided

## Deployment

### Requirements
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+
- Composer (for PHPMailer)
- Web server (Apache/Nginx)

### Production Checklist
- [ ] Change default admin password
- [ ] Configure SMTP email settings
- [ ] Set up SSL/HTTPS certificate
- [ ] Configure proper file permissions
- [ ] Set up database backups
- [ ] Configure rate limiting
- [ ] Add CSRF protection
- [ ] Set up monitoring/logging

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

**Paul Teye Lamptey**
- GitHub: https://github.com/Paullamptey
- LinkedIn: https://www.linkedin.com/in/paul-teye-lamptey-65a271275
- Email: paullamptey05@gmail.com
- Website: paulteyelampteye.com

## Acknowledgments

- Ghana Bar Association for legal guidance
- OpenAI for GPT API integration
- PHPMailer for email functionality
- Unsplash for background images
