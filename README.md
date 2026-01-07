# 👕 MyCling — Custom Clothing E-commerce Website

## 📌 Overview
**MyCling** is a web-based e-commerce platform designed for a clothing brand.  
It allows customers to:
- Purchase **original brand designs**.
- Submit **fully custom designs**, with an option to book a tailor visit for exact measurements and material selection.

---

## ✨ Features

### 🛍 Product Categories
1. **Brand Originals** — Standard sizes, limited colors.
2. **Fully Custom Designs** — Book a tailor visit for measurements & final design confirmation.

### 📅 Tailor Visit
- Customers can select **date, time, and venue** for a tailor visit.
- Tailor will take measurements, confirm materials, and provide a **cost estimate**.

### 🛒 E-Commerce Features
- Add to cart
- Checkout with shipping details
- Order confirmation
- Transaction ID for payment tracking

### 🔒 Admin Dashboard
- Manage products, users, and orders
- Update database tables for products, orders, and users
- Add new shipping columns & transaction IDs

---

## 🗂 Project Structure
```
My-cling-WebDevelopment-project-main/
│
├── css/                     # Stylesheets for UI pages
│   ├── checkout.css
│   ├── checkout_styles.css
│   ├── form.css
│   └── styles.css
│
├── fonts/                   # Custom typography
│   ├── AhganiryaPersonalUse-Yznaj.ttf
│   ├── BellagiaDisplayThin-x32Mj.ttf
│   ├── California.ttf
│   └── DestinePersonalUseRegular-6YAev.otf
│
├── html/                    # HTML/PHP Pages
│   ├── appointment.html / .php
│   ├── cart.html / .php
│   ├── checkout.php
│   ├── index.html
│   ├── login.html / .php
│   ├── product_details.php
│   ├── shop.html / .php
│   └── signup.html / .php
│
├── add_shipping_columns.php
├── add_transaction_id_to_orders.php
├── check_orders_table.php
├── check_products_table.php
├── check_users_table.php
├── database.sql             # MySQL database schema
└── README.md                # Project documentation
```

---

## 🛠 Tech Stack
- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP
- **Database:** MySQL (database.sql)
- **Fonts:** Custom typography for brand identity

---

## ⚙ Installation & Setup

### 1️⃣ Prerequisites
- PHP ≥ 7.x
- MySQL ≥ 5.x
- Apache / XAMPP / WAMP

### 2️⃣ Steps
```bash
# Clone or unzip the project
git clone <repo-link>
cd My-cling-WebDevelopment-project-main

# Import database
- Open phpMyAdmin
- Create a new database (e.g., mycling_db)
- Import `database.sql`

# Configure database connection
- Open PHP files handling DB (e.g., config.php)
- Update host, username, password, and DB name

# Run the project
- Place the project folder in `htdocs` (XAMPP)
- Start Apache & MySQL
- Access via http://localhost/My-cling-WebDevelopment-project-main/html/index.html
```

---

## 📸 UI Highlights
- Dark-themed **artistic** design for modern appeal
- Clean checkout process
- Custom font branding

---

## 📌 Future Enhancements
- Payment gateway integration
- User profile dashboard
- Order tracking system
- Admin analytics dashboard

---

## 👤 Author
Developed by **Prit Vadodaria**  & **Shubham Mistry**
📧 *pritvadodaria16@gmail.com*
github - *@Prit-Vadodaria*

