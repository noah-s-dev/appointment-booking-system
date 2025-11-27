# 🏥 Appointment Booking System

A modern, responsive web application for managing appointment bookings with an intuitive user interface and comprehensive admin panel.

## 🚀 Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.1.3
- **Icons**: Bootstrap Icons 1.7.2
- **Server**: Apache/Nginx (XAMPP/WAMP/LAMP)
- **Security**: bcrypt password hashing, SQL injection prevention, XSS protection

## 📋 Project Overview

The Appointment Booking System is a comprehensive web application designed to streamline the appointment booking process for businesses and service providers. It features a modern, responsive design with separate interfaces for users and administrators.

### Key Features

- **User Management**: Secure registration and login system with role-based access
- **Appointment Booking**: Easy-to-use interface for scheduling appointments
- **Time Slot Management**: Dynamic time slot generation and availability tracking
- **Admin Dashboard**: Comprehensive admin panel for managing appointments and users
- **Responsive Design**: Mobile-friendly interface that works on all devices
- **Real-time Updates**: Live availability checking and instant confirmations
- **Email Notifications**: Automated email confirmations and reminders
- **Search & Filter**: Advanced filtering and search capabilities
- **Export Functionality**: Data export in various formats
- **Security Features**: Password hashing, SQL injection prevention, session management

## 👥 User Roles

### **Regular Users**
- Register and login to personal accounts
- Book appointments with available time slots
- View and manage personal appointments
- Cancel appointments (with restrictions)
- Update profile information

### **Administrators**
- Access comprehensive admin dashboard
- Manage all appointments across the system
- View and manage user accounts
- Generate time slots and manage availability
- Export data and generate reports
- System configuration and settings

## 📁 Project Structure

```
appointment-booking-system/
├── admin/                     # Admin panel files
│   ├── dashboard.php         # Admin dashboard
│   ├── appointments.php      # Manage all appointments
│   ├── login.php            # Admin login
│   └── logout.php           # Admin logout
├── ajax/                     # AJAX handlers
│   └── get-time-slots.php   # Dynamic time slot loading
├── config/                   # Configuration files
│   └── database.php         # Database connection
├── css/                      # Stylesheets
│   └── style.css            # Main CSS file
├── includes/                 # Shared components
│   ├── header.php           # Common header
│   ├── footer.php           # Common footer
│   ├── functions.php        # Utility functions
│   └── security.php         # Security functions
├── sql/                      # Database files
│   ├── setup.sql            # Database schema
│   └── demo_data.sql        # Sample data
├── index.php                # Landing page
├── login.php                # User login
├── register.php             # User registration
├── dashboard.php            # User dashboard
├── book-appointment.php     # Appointment booking
├── my-appointments.php      # User appointments
├── view-appointment.php     # Appointment details
├── cancel-appointment.php   # Cancel appointment
└── logout.php               # User logout
```

## ⚙️ Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP stack (recommended)

### Installation Steps

1. **Clone or Download the Project**
   ```bash
   # If using git
   git clone https://github.com/noah-s-dev/appointment-booking-system.git
   # Or download and extract the ZIP file
   ```

2. **Set Up Web Server**
   - Place the project in your web server's document root
   - For XAMPP: `C:\xampp\htdocs\appointment-booking-system\`
   - For WAMP: `C:\wamp\www\appointment-booking-system\`

3. **Configure Database**
   - Create a new MySQL database named `appointment_booking`
   - Import the database schema:
     ```sql
     mysql -u username -p appointment_booking < sql/setup.sql
     ```
   - Import demo data (optional):
     ```sql
     mysql -u username -p appointment_booking < sql/demo_data.sql
     ```

4. **Configure Database Connection**
   - Edit `config/database.php`
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'appointment_booking');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

5. **Set Up Application Constants**
   - Edit `config/database.php`
   - Update application settings:
     ```php
     define('APP_NAME', 'Your Business Name');
     define('APP_URL', 'http://localhost/appointment-booking-system');
     ```

6. **Configure Email Settings** (Optional)
   - Update email configuration in `includes/functions.php`
   - Set up SMTP settings for email notifications

7. **Set Permissions**
   - Ensure web server has read/write permissions
   - For file uploads (if implemented): `chmod 755 uploads/`

8. **Test the Installation**
   - Visit: `http://localhost/appointment-booking-system`
   - Test user registration and login
   - Test admin login (default: admin/admin123)

## 🎯 Usage

### For Users
1. **Registration**: Create a new account
2. **Login**: Access your personal dashboard
3. **Book Appointment**: Select date, time, and service
4. **Manage Appointments**: View, cancel, or reschedule appointments
5. **Profile Management**: Update personal information

### For Administrators
1. **Admin Login**: Access admin panel at `/admin/login.php`
2. **Dashboard**: View system overview and statistics
3. **Appointment Management**: Handle all appointments
4. **User Management**: Manage user accounts
5. **System Settings**: Configure application settings

### Demo Accounts
- **Admin**: admin / admin123
- **Demo User**: demo@example.com / demo123

## 🎯 Intended Use

This appointment booking system is designed for:

- **Medical Clinics**: Patient appointment scheduling
- **Beauty Salons**: Service booking and management
- **Consulting Services**: Client meeting scheduling
- **Educational Institutions**: Student consultation booking
- **Professional Services**: Client appointment management
- **Small Businesses**: Customer service scheduling

### Use Cases
- **Healthcare**: Doctor appointments, consultations
- **Beauty & Wellness**: Salon appointments, spa services
- **Professional Services**: Legal consultations, financial planning
- **Education**: Tutoring sessions, academic advising
- **Automotive**: Service appointments, test drives
- **Real Estate**: Property viewings, consultations

## 🔧 Customization

### Styling
- Modify `css/style.css` for custom styling
- Update color scheme in CSS variables
- Customize Bootstrap components

### Functionality
- Add new appointment types in the database
- Implement additional user roles
- Extend admin features
- Add payment integration
- Implement SMS notifications

### Database
- Modify `sql/setup.sql` for schema changes
- Add new tables for additional features
- Update relationships and constraints

## 🛡️ Security Features

- **Password Security**: bcrypt hashing with cost 12
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling
- **CSRF Protection**: Form token validation
- **Input Validation**: Server-side validation
- **Access Control**: Role-based permissions

## 📱 Responsive Design

- **Mobile-First**: Optimized for mobile devices
- **Tablet Support**: Responsive layout for tablets
- **Desktop Optimization**: Enhanced desktop experience
- **Cross-Browser**: Compatible with all modern browsers

## 🚀 Performance Optimization

- **Minified CSS/JS**: Optimized file sizes
- **Efficient Queries**: Optimized database queries
- **Caching**: Session and query caching
- **CDN Integration**: Bootstrap and icons from CDN

---

# 📄 License

**License for RiverTheme**

[RiverTheme.com](https://RiverTheme.com) makes this project available for demo, instructional, and personal use. You can ask for or buy a license from Rivertheme if you want a pro website, sophisticated features, or expert setup and assistance. A Pro license is needed for production deployments, customizations, and commercial use.

**Disclaimer**

The free version is offered "as is" with no warranty and might not function on all devices or browsers. It might also have some coding or security flaws. For additional information or to get a Pro license, please get in touch with [RiverTheme.com](https://RiverTheme.com).
