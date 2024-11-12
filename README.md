<h2 align="center">Sandrine Coupart CMS</h2>

<div align="center">

![Status](https://img.shields.io/badge/status-not%20active-red.svg)

[![Static Badge PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](https://www.php.net/)

[![Static Badge](https://img.shields.io/badge/MySQL-Community-4479A1?logo=mysql)](https://www.mysql.com/products/community/)

![Static Badge](https://img.shields.io/badge/HTML-5-E34F26?logo=HTML5)

[![Static Badge](https://img.shields.io/badge/bootstrap-v5.3-7952B3?logo=bootstrap)](https://getbootstrap.com/)

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](/LICENSE.txt)

</div>

---

<p align="center"> This project is a content management system (CMS) designed for managing allergens,
recipes, user accounts, reviews, and testimonials for the website of Sandrine Coupart, a fictional entity.
It is implemented primarily in PHP with a database layer for data persistence.
The system is designed to be used by administrators for maintaining various aspects of the website,
including adding, editing, and deleting data as required.
    <br> 
</p>

## 📋 Features

- Session management for user authentication.
- CRUD operations for allergens, diet types, recipes, user accounts, and reviews.
- File handling for recipe images with potential integration with AWS S3 service.
- Error handling and form validations.
- Responsive HTML forms with Bootstrap CSS framework for the frontend.

## 🔧 Project Structure

```
sandrine-coupart-cms
├── allergenes.php
├── app_configs
│   └── db_config.php
├── composer.json
├── composer.lock
├── dashboard.php
├── index.php
├── logout.php
├── manage_diet_types.php
├── manage_employees.php
├── manage_users.php
├── messages.php
├── recipes.php
├── reviews_manage.php
├── testim_manage.php
└── user.ini
```

### 📄 allergenes.php

- Handles allergen management by providing functionalities to add and delete allergens.
- Interacts with the database to perform these actions and handles form submissions.

### 📁 app_configs

#### db_config.php

- Contains database connection configurations.
- It utilizes environment variables for sensitive data like the database connection string.
- Connects to the database using PHP Data Objects (PDO) for secure database operations.

### 📄 composer.json

- Describes the project's dependencies and metadata.
- Uses Composer as the dependency manager to include packages like AWS SDK for PHP and Dotenv for managing environment variables.

### 🔒 composer.lock

- Automatically generated file that locks the project dependencies to specific versions that are tested and known to work for the application.

### 🖥️ dashboard.php

- Serves as the main administrative dashboard to provide navigation to different sections like user management, recipe management, message management, etc.
- Displays different functionalities based on the `user` session variable to enforce authorization.

### 🔑 index.php

- The main login page for the administration panel.
- Handles user authentication and redirects authenticated users to `dashboard.php`.

### 🚪 logout.php

- Destroys the user session to safely log out users from the admin panel.
- Redirects back to `index.php` after logging out.

### 🍏 manage_diet_types.php

- Manages diet types which can be associated with recipes.
- Allows administrators to add and delete diet types from the database.

### 👥 manage_employees.php

- Used to manage administrative users in the system.
- Provides functionalities to add new admins and remove existing ones from the system.

### 🧍 manage_users.php

- Manages user accounts for the site.
- Allows add, delete and edit operations on user records along with detailed information such as dietary restrictions and allergens.

### ✉️ messages.php

- Handles the messages received from users.
- Provides functionalities to view and delete messages.

### 🥘 recipes.php

- Offers a recipe management panel to add, update, and delete recipes.
- Handles file uploads for recipe images, which implies potential AWS S3 integration for storing images.

### 🌟 reviews_manage.php

- Provides an interface to manage user-submitted reviews for the recipes.
- Enables the administrator to approve or disapprove reviews for public visibility on the website.

### 🗣️ testim_manage.php

- Admin panel for managing testimonials provided by users.
- Enables functionality to filter displayed testimonials based on their approved status.

### ⚙️ user.ini

- A configuration file for PHP settings regarding file uploads.
- Defines parameters such as `upload_max_filesize` and `post_max_size`, which are crucial for handling recipe image uploads.

## 🖱️ Usage

Administrators can use this CMS to keep the Sandrine Coupart website up-to-date with current content. They can manage recipes, users, reviews, testimonials, and messages through a web interface by logging into the system with their credentials. For developers, the project's source files may be modified to add or change functionality as per the business requirements.

## 🛑 Prerequisites

The system requires a web server with PHP installed and access to a MySQL database. Composer must be installed to handle PHP dependencies. Environment variables should be set appropriately for secure operations, especially for database connection and any third-party service integration like AWS S3.

## 🚀 Getting Started

1. Clone the repository to your local machine or server.
2. Run `composer install` to install the required dependencies.
3. Set your environment variables in the `.env` file or as server environment variables.
4. Access the `index.php` through your web server to start using the CMS.

## 🛠️ Installation

No additional installation is required beyond the initial setup of PHP, Composer, and web server configuration. Ensure the database is set up correctly by executing provided SQL schema files if included.

## 📐 Configuration

Configure your database credentials and any other environment settings in `app_configs\db_config.php` or through environment variables as per the deployment environment. Ensure file upload settings in `user.ini` satisfy your requirements for size and time limits.

## 💡 Tips

- Always keep your environment variables secure and do not commit sensitive data like passwords or API keys to version control systems.
- Use secure hashing methods to store passwords and other sensitive information.
- Regularly update your PHP and Composer dependencies to maintain security and functionality.

## 📜 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## ⛏️ Built Using <a name = "built_using"></a>

- [MySQL Community](https://www.mysql.com/products/community/) - Database
- [PHP](https://www.php.net/) - Server & Dashboard logic
- [HTML](https://en.wikipedia.org/wiki/HTML5) - Pages markup
- [Bootstrap 5.3](https://getbootstrap.com/) - CSS framework

## ✍️ Authors <a name = "authors"></a>

- [@Dima-McArrow](https://github.com/Dima-McArrow) - Idea & Initial work

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [Studi](https://www.studi.com/fr)

---
