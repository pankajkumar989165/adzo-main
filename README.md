# Aadzo Digital Blog Management System

## Deployment Guide for GoDaddy Shared Hosting

### 1. Database Setup
1. Log in to your GoDaddy cPanel
2. Go to **MySQL Databases**
3. Create a new database named `adzodigital_blog` (Already done based on your info)
4. Create a user `adzo_admin` with password `Adzonoida@1208`
5. Add the user to the database with **ALL PRIVILEGES**
6. Go to **phpMyAdmin** and select your database
7. Click **Import** and upload `database/schema.sql` first
8. Then import `database/seed.sql` to add the admin user and sample data

### 2. File Upload
1. Use File Manager in cPanel or an FTP client (FileZilla)
2. Upload all files from this project to your `public_html` folder
3. Ensure the `.htaccess` file is uploaded (it might be hidden on some systems)

### 3. File Permissions
1. Ensure the `uploads/blog-images/` directory exists
2. Set permissions for `uploads/` folder to `755` (or `777` if you have upload issues, but 755 is safer)

### 4. Configuration
1. The `config/database.php` is already configured with your provided credentials:
   - User: `adzo_admin`
   - Pass: `Adzonoida@1208`
   - DB: `adzodigital_blog`
   
### 5. Accessing the Site
- **Website**: `https://adzodigital.com`
- **Admin Panel**: `https://adzodigital.com/admin/index.php`
- **Login Credentials**:
  - Email: `admin@adzodigital.com`
  - Password: `Admin@123`
  - **IMPORTANT**: Change your password immediately after logging in!

### 6. SEO Features
- **Sitemap**: `https://adzodigital.com/sitemap.php` (Submit this to Google Search Console)
- **Robots**: `https://adzodigital.com/robots.txt`
- **Clean URLs**: Blog posts will look like `adzodigital.com/blog/my-post-title`

### Troubleshooting
- If you see a "500 Internal Server Error", check the `.htaccess` file syntax or try renaming it to debug
- If images don't upload, check the `uploads` folder permissions
- If database connection fails, double check the user password in `config/database.php`
