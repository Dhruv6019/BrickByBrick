# BrickByBrick 🧱

**BrickByBrick** is a PHP-based web application for managing real estate listings. It allows users to register, log in, list properties, manage their accounts, search for listings, and mark properties as favorites. An admin interface is also included to manage overall content.

## 📸 Screenshots

![Web capture_23-6-2025_14511_localhost](https://github.com/user-attachments/assets/673e2bc0-1764-4610-aa3d-06c806525394)


## 🌟 Features

- 🏠 Add, edit, delete, and view property listings
- ❤️ Mark properties as favorites
- 🔎 Search and filter properties
- 👤 User registration, login, and profile management
- 🛠 Admin panel for property and user management
- 📧 Contact form with email configuration
- 📂 Image uploads for property listings
- 🔐 Password reset functionality

## 🧱 Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Email:** PHPMailer
- **Dependencies:** Managed via Composer

## 📁 Folder Structure

```
BrickByBrick/
├── admin/                 # Admin panel files
├── css/                   # Stylesheets
├── images/                # Uploaded property images
├── includes/              # Header, footer, config files
├── js/                    # JavaScript files
├── uploads/               # Dynamic uploads
├── vendor/                # Composer dependencies
├── about.php              # About page
├── add-property.php       # Add new listing
├── config.php             # Database and settings
├── contact.php            # Contact form
├── database.sql           # MySQL schema
├── index.php              # Home page
├── login.php              # Login page
├── logout.php             # Logout script
├── mail_config.php        # Mail settings
├── my-properties.php      # User's property listings
├── register.php           # Signup page
└── ...                    # Other functionality pages
```

## 🚀 Getting Started

### Prerequisites

- PHP 7.x or above
- MySQL
- Apache/Nginx server (XAMPP, WAMP, LAMP recommended)
- Composer

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/BrickByBrick.git
   ```

2. Navigate into the project folder:
   ```bash
   cd BrickByBrick
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Import the database:
   - Create a MySQL database.
   - Import `database.sql` using phpMyAdmin or CLI.

5. Configure your settings:
   - Edit `config.php` for database credentials.
   - Update `mail_config.php` for SMTP settings.

6. Host the project on your local server (XAMPP/WAMP) and visit:
   ```
   http://localhost/BrickByBrick/
   ```

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## ✍️ Author

- Dhruv Teli — [@Dhruv6019](https://github.com/Dhruv6019)
- Email: dhruvteli6019@gmail.com
