# TourStack - Home-Stay Finder

## Setup Instructions

### Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `tourstack`
3. Import the `database.sql` file
4. Update `config.php` with your database credentials

### Default Logins
- **Host**: host@test.com / password123
- **Guest**: guest@test.com / password123
- **Admin**: admin@test.com / password123

### File Structure
All PHP files should be placed in your web server root (htdocs for XAMPP)

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- MySQLi extension enabled

### Features Implemented
-  Role-based authentication (Host/Guest/Admin)
-  Browse and filter home-stays
-  Add/edit listings (Hosts only)
-  Booking system with live price calculator
-  Guest booking history
-  Host dashboard with statistics
-  Admin verification system
-  Responsive design
-  Prepared statements for all DB queries