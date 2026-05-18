# 🛒 Smart E-Commerce Platform (Online Shopping System)

A full-stack dynamic e-commerce web application built using **PHP, MySQL, HTML, CSS, Bootstrap, and JavaScript**.  
This project simulates a real-world online shopping system with user authentication, product management, cart functionality, and order processing.

---

## 🚀 Features

### 👤 User Module
- User registration and login system
- Secure authentication with password hashing
- Browse products with images and pricing
- Add products to cart
- Update/remove cart items
- Place orders
- View order history

---

### 🛠️ Admin Module
- Admin dashboard
- Add / Update / Delete products (CRUD)
- Manage product categories
- View all orders
- Update order status (Pending → Shipped → Delivered)
- Manage users

---

### 🛍️ Shopping Features
- Product search functionality
- Category-based filtering
- Dynamic shopping cart (session-based)
- Order checkout system
- Image upload for products

---

## 🧰 Tech Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5
- **Client-side:** JavaScript, jQuery
- **Backend:** PHP (Core PHP / Laravel optional)
- **Database:** MySQL
- **Server:** XAMPP / Apache

---

## 🗂️ Project Structure
ecommerce/
│
├── config/ # Database configuration
├── includes/ # Header, footer, auth helpers
├── auth/ # Login, Register, Logout
├── admin/ # Admin dashboard & product management
├── cart/ # Cart & checkout system
├── images/ # Product images
├── index.php # Homepage (Product listing)
├── product.php # Product details page
└── README.md


---

## 🗄️ Database Schema

### Users Table
- id
- name
- email
- password
- role (admin/user)

### Products Table
- id
- name
- price
- image

### Orders Table
- id
- user_id
- total
- created_at

### Order Items Table
- id
- order_id
- product_id
- quantity

---

## ⚙️ Installation & Setup

1. Clone or download the project
2. Move folder to `htdocs` (XAMPP)
3. Start Apache & MySQL in XAMPP
4. Import database from phpMyAdmin
5. Run project: http://localhost/ecommerce


---

## 🔐 Security Features
- Password hashing using `password_hash()`
- Session-based authentication
- Role-based access control (Admin/User)
- Basic SQL injection protection (recommended to use prepared statements)

---

## 🎯 Project Objectives
- Build a complete full-stack web application
- Implement CRUD operations in real-world scenario
- Understand authentication and session handling
- Practice database design and relationships
- Develop dynamic UI using JavaScript

---


## 📈 Future Improvements
- Payment gateway integration
- AJAX-based real-time updates
- Wishlist feature
- Email notifications
- Laravel migration for scalability

---


## 🏁 Conclusion
This project demonstrates a complete implementation of an e-commerce system with frontend, backend, and database integration. It is designed for academic submission as well as portfolio enhancement for web development roles.

---

## ⭐ Author
Developed by Bilal Hassan BSCS Student as a semester project.
