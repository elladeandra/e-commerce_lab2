# Deployment Guide for Remote Server

## Step 1: Pull Latest Code to Remote Server

SSH into your remote server:
```bash
ssh -p 422 emmanuella.oteng@169.239.251.102
```

Navigate to your public_html directory:
```bash
cd ~/public_html
```

Pull the latest code from GitHub:
```bash
git pull origin main
```

## Step 2: Set Up Database

### Option A: Using phpMyAdmin (Recommended)
1. Go to: http://169.239.251.102:442/phpmyadmin
2. Select database: `ecommerce_2025A_emmanuella_oteng`
3. Click on "SQL" tab
4. Copy and paste the contents of `database_setup.sql`
5. Click "Go" to execute

### Option B: Using MySQL Command Line
```bash
cd ~/public_html
mysql -u emmanuella.oteng -p ecommerce_2025A_emmanuella_oteng < database_setup.sql
# Enter password: NeverForget20
```

## Step 3: Verify Database Configuration

The file `settings/db_cred.php` should already be configured correctly:
```php
define("SERVER", "localhost");
define("USERNAME", "emmanuella.oteng");
define("PASSWD", "NeverForget20");
define("DATABASE", "ecommerce_2025A_emmanuella_oteng");
```

## Step 4: Set File Permissions

Make sure the uploads directory is writable:
```bash
chmod 755 uploads/
```

## Step 5: Test Your Application

Visit your application:
- Main page: http://169.239.251.102:442/
- Admin login: Use `admin@example.com` / `admin123`

## Troubleshooting

If you encounter issues:

1. **Check PHP errors**: Look at error logs
2. **Verify database connection**: Check `settings/db_cred.php`
3. **Check file permissions**: Ensure uploads directory is writable
4. **Clear browser cache**: Ctrl+F5 to reload page

## Admin Credentials

- Email: admin@example.com
- Password: admin123
