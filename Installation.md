# Installation Guide

## 1. Install packages on Raspberry Pi

```bash
sudo apt update
sudo apt install apache2 mariadb-server php php-mysql git unzip -y
```

## 2. Start services

```bash
sudo systemctl enable apache2
sudo systemctl enable mariadb
sudo systemctl start apache2
sudo systemctl start mariadb
```

## 3. Clone the repository

```bash
cd /var/www/html
sudo rm -rf lzw-car-rental-system
sudo git clone https://github.com/YOUR_ACCOUNT/lzw-car-rental-system.git
```

Replace `YOUR_ACCOUNT` with your GitHub username.

## 4. Create/reset the database

From the project folder:

```bash
cd /var/www/html/lzw-car-rental-system
sudo mysql < database/schema.sql
sudo mysql < database/seed.sql
```

This will create the database, tables, sample data, and default login accounts.

## 5. Set permissions

```bash
sudo chown -R www-data:www-data /var/www/html/lzw-car-rental-system
sudo chmod -R 755 /var/www/html/lzw-car-rental-system
```

## 6. Open the web application

Using normal LAN IP:

```text
http://RASPBERRY_PI_IP/lzw-car-rental-system/public/
```

Using link-local demo IP, for example:

```text
http://169.254.1.1/lzw-car-rental-system/public/
```

## 7. Default accounts

Admin:

```text
Email: admin@lzw.local
Password: admin123
```

Normal user:

```text
Email: user@lzw.local
Password: user123
```

## 8. Link-local demo note

For final demo, use link-local networking only and turn WiFi off if required by the instructor. Confirm the Raspberry Pi uses a `169.254.x.x` IP address before presenting.
