# 🌱 FarmFresh Organic

FarmFresh Organic is a PHP and MySQL–based web application designed to connect local farmers directly with customers for fresh, organic produce.  
The platform enables farmers to list products, customers to browse and purchase them through a cart system, and administrators to manage users, products, and orders efficiently.

This project is built using core PHP with a clean, structured architecture and serves as both a functional marketplace and an educational full-stack web development project.

---

## 📌 Project Overview

FarmFresh Organic is a web-based marketplace connecting farmers directly with customers for fresh, organic produce.  
The system supports **role-based access** (Admin, Farmer, Customer) and implements essential e-commerce features such as product management, shopping cart, and order processing.

---

## 🚀 Features

### 👤 User Roles
- **Admin**
  - Manage users, products, and orders
  - Monitor system activity and data

- **Farmer**
  - Add, view, and delete products
  - Manage inventory
  - View customer orders and sales

- **Customer**
  - Browse and search products
  - Add items to cart
  - Checkout and place orders
  - Track order history
  - Rate and review products

---

## 🛒 Core Functionalities
- User authentication (Login & Registration)
- Role-based access control
- Product listing and product details
- Shopping cart system
- Order and order-items management
- Secure database interaction using MySQL
- Image upload support for products

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Icons**: Font Awesome 6
- **Server**: Apache (XAMPP / WAMP)
- **Version Control**: Git & GitHub
- **Architecture**: MVC-inspired structured PHP project

---

## 📋 Prerequisites

- **XAMPP** or **WAMP** (includes Apache, PHP, MySQL)
- Web browser (Chrome, Firefox, etc.)
- Text editor (VS Code recommended)

---

## ⚙️ Installation Guide

## Prerequisites
- **XAMPP** or **WAMP** (includes Apache, PHP 7.4+, MySQL)
- Web browser (Chrome, Firefox, etc.)
- Text editor (optional, for code modifications)

## Installation Steps

### 1. Install XAMPP/WAMP
- Download XAMPP from https://www.apachefriends.org/
- Install and start Apache and MySQL services

### 2. Setup Project Files
1. Copy the entire `farmfresh` folder to:
   - **XAMPP**: `C:\xampp\htdocs\`
   - **WAMP**: `C:\wamp64\www\`

Your folder structure should look like:
```
htdocs/farmfresh/
├── config/
├── assets/
├── includes/
├── admin/
├── farmer/
├── customer/
├── index.php
└── (other files)
```

### 3. Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create a new database
3. Name it: `farmfresh`
4. Click on the `farmfresh` database
5. Go to "Import" tab
6. Choose the `farmfresh.sql` file
7. Click "Go" to import

**OR** Run the SQL manually:
- Copy the contents of `farmfresh.sql`
- Paste in phpMyAdmin SQL tab
- Click "Go"

### 4. Configure Database Connection
The database configuration is already set in `config/database.php`:
```php
DB_HOST: 'localhost'
DB_USER: 'root'
DB_PASS: '' (empty for default XAMPP)
DB_NAME: 'farmfresh'
```

If your MySQL has a password, update it in `config/database.php`.

### 5. Create Image Upload Directory
1. Navigate to `farmfresh/assets/`
2. Create a folder named `images` if it doesn't exist
3. Set permissions (Windows: Right-click > Properties > Security > Edit > Allow write access)

### 6. Access the Application
Open your browser and go to:
```
http://localhost/farmfresh/
```

## Default Login Credentials

### Admin Account
- **Email**: admin@farmfresh.com
- **Password**: admin123

### Create Test Accounts
Register new accounts through the registration page:
- Choose "Farmer" to sell products
- Choose "Customer" to buy products

## Features Overview

### For Farmers:
- Register and manage account
- Add products with images, prices, descriptions
- Manage product inventory
- View orders
- Track sales

### For Customers:
- Browse organic products
- Search and filter by category
- Add items to cart
- Place orders
- Track order status
- Rate and review products

### For Admin:
- Manage all users
- Monitor products
- View all orders
- System statistics
- Revenue tracking

## Technology Stack
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Icons**: Font Awesome 6
- **Architecture**: MVC-inspired structure

## Project Structure
```
farmfresh/
├── config/              # Database configuration
│   └── database.php
├── assets/              # Static files
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Product images (uploads)
├── includes/            # Reusable components
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── admin/              # Admin panel
├── farmer/             # Farmer dashboard & features
├── customer/           # Customer dashboard & features
└── *.php               # Main pages
```

## Common Issues & Solutions

### Issue: Page not loading
**Solution**: Ensure Apache is running in XAMPP/WAMP control panel

### Issue: Database connection failed
**Solution**: 
- Check if MySQL is running
- Verify database name is `farmfresh`
- Check credentials in `config/database.php`

### Issue: Images not uploading
**Solution**:
- Ensure `assets/images/` folder exists
- Check folder permissions
- Maximum file size in php.ini (default 2MB)

### Issue: Session errors
**Solution**: Clear browser cache and cookies

### Issue: Blank page
**Solution**: 
- Enable error reporting in php.ini
- Check Apache error logs
- Verify all files are uploaded correctly

## Customization

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #2ecc71;
    --secondary-color: #27ae60;
    /* Modify these values */
}
```

### Add Categories
Edit `farmer/add_product.php` category dropdown

### Modify Email Settings
Update SMTP settings in respective files for email notifications

## Security Notes
- Change default admin password immediately
- Use strong passwords for production
- Enable HTTPS in production
- Keep PHP and MySQL updated
- Sanitize all user inputs (already implemented)
- Use prepared statements (already implemented)

## Support & Documentation
- **Project Synopsis**: See `final project symposis.pdf`
- **Bootstrap Docs**: https://getbootstrap.com/
- **PHP Manual**: https://www.php.net/manual/
- **MySQL Docs**: https://dev.mysql.com/doc/

## Future Enhancements
As mentioned in the project synopsis:
- Mobile app development
- AI-based recommendations
- Blockchain integration
- Advanced analytics
- Multi-language support
- Payment gateway integration

## Credits
**Developed by Group 07**
- Lakhan Uddhav Ashtekar (Roll No. 16)
- Ajit Dadasaheb Jarande (Roll No. 19)
- Rushikesh Pandurang Kende (Roll No. 24)
- Shubham Satish Vanave (Roll No. 41)

**Guide**: Mrs. P.A. Ternikar

**Institute**: Pimpri Chinchwad Polytechnic
Department of Information Technology
Academic Year 2025-26 (ODD SEM)

## License
Educational project for academic purposes.

---

**Enjoy using FarmFresh Organic! 🌱**
