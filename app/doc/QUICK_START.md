# QUICK START GUIDE - Career Analysis SPA
## Industry-Standard Psychometric Assessment System

### ⚡ 10-Minute Setup

## Prerequisites Checklist
- [ ] PHP 8.0+ installed
- [ ] MySQL 8.0+ installed
- [ ] Composer installed
- [ ] Web server (Apache/Nginx)
- [ ] Domain with SSL (recommended for production)

---

## STEP 1: Install CodeIgniter 4 (5 minutes)

```bash
# Create project directory
mkdir career-analysis-spa
cd career-analysis-spa

# Install CodeIgniter 4
composer create-project codeigniter4/appstarter .

# Install additional dependencies
composer require tecnickcom/tcpdf
composer require phpmailer/phpmailer
```

---

## STEP 2: Database Setup (2 minutes)

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE career_analysis_spa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Import schema (from downloaded files)
mysql -u root -p career_analysis_spa < database_schema.sql
```

---

## STEP 3: Configure Application (2 minutes)

### Edit `.env` file:

```env
# CHANGE THESE VALUES
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = career_analysis_spa
database.default.username = root
database.default.password = YOUR_PASSWORD_HERE
database.default.DBDriver = MySQLi

app.baseURL = 'http://localhost:8080/'
app.sessionDriver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
app.sessionSavePath = 'ci_sessions'
```

### Edit `app/Config/App.php`:

```php
public string $baseURL = 'http://localhost:8080/';
public string $indexPage = '';
```

---

## STEP 4: File Structure Setup (1 minute)

```bash
# Copy downloaded files to correct locations

# Copy Controllers
cp app/Controllers/*.php your-project/app/Controllers/

# Copy Libraries
cp app/Libraries/*.php your-project/app/Libraries/

# Copy Models (create them from templates below)
# See MODELS.md for complete model code

# Copy Views
cp -r public/* your-project/public/

# Set permissions
chmod -R 777 writable/
```

---

## STEP 5: Run Development Server

```bash
# Start PHP development server
php spark serve

# Or specify port
php spark serve --port=8080
```

**Access at: http://localhost:8080**

---

## 📁 FILE LOCATIONS REFERENCE

```
career-analysis-spa/
├── app/
│   ├── Config/
│   │   ├── Routes.php           ← Configure URLs here
│   │   ├── Database.php         ← Database settings
│   │   └── Email.php            ← Email configuration
│   ├── Controllers/
│   │   ├── BaseController.php   ← FROM DOWNLOADS
│   │   ├── AuthController.php   ← FROM DOWNLOADS
│   │   └── AssessmentController.php ← FROM DOWNLOADS
│   ├── Models/
│   │   └── (Create from templates in MODELS.md)
│   ├── Libraries/
│   │   └── PsychometricEngine.php ← FROM DOWNLOADS
│   └── Views/
│       └── (Will be created)
├── public/
│   ├── index.php               ← Entry point
│   ├── index.html              ← FROM DOWNLOADS (SPA frontend)
│   └── assets/
├── writable/                    ← Must be writable
└── .env                         ← Configure this!
```

---

## 🔐 DEFAULT CREDENTIALS

**No default credentials** - You must register first user through the registration page.

---

## ⚙️ ROUTES CONFIGURATION

Add to `app/Config/Routes.php`:

```php
<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('/', 'Home::index');

// Authentication Routes
$routes->group('api/auth', function($routes) {
    $routes->post('login', 'AuthController::processLogin');
    $routes->post('register', 'AuthController::processRegister');
    $routes->post('logout', 'AuthController::logout');
    $routes->get('check', 'AuthController::checkAuth');
});

// Assessment Routes (Requires Authentication)
$routes->group('api/assessment', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'AssessmentController::index');
    $routes->post('start', 'AssessmentController::startSession');
    $routes->get('questions/(:num)/(:any)', 'AssessmentController::getQuestions/$1/$2');
    $routes->post('response', 'AssessmentController::saveResponse');
    $routes->post('complete', 'AssessmentController::completeTest');
    $routes->get('report/(:any)', 'AssessmentController::viewReport/$1');
    $routes->get('download/(:any)', 'AssessmentController::downloadReport/$1');
});
```

---

## 🧪 TEST THE INSTALLATION

### 1. Check Homepage
Visit: `http://localhost:8080`
- Should see landing page with test cards

### 2. Test Registration
Click "Get Started" → Fill form → Submit
- Should create user account

### 3. Test Login
Login with created credentials
- Should redirect to dashboard

### 4. Start Assessment
Select "Class 8-10" or "Class 11-12" → Start assessment
- Should load first test

---

## 🔧 TROUBLESHOOTING

### Database Connection Error
```bash
# Check MySQL is running
sudo service mysql status

# Verify credentials in .env file
# Make sure database exists
mysql -u root -p career_analysis_spa
```

### Permission Denied
```bash
# Fix writable directory permissions
chmod -R 777 writable/
chown -R www-data:www-data writable/
```

### 404 Not Found
```bash
# Enable mod_rewrite (Apache)
sudo a2enmod rewrite
sudo service apache2 restart

# Or use .htaccess in public folder
```

### Blank Page
```bash
# Check error logs
tail -f writable/logs/log-*.log

# Enable error display (development only)
# In .env file:
CI_ENVIRONMENT = development
```

---

## 📊 SAMPLE DATA

### Insert Sample Careers (Optional)

```sql
-- Will auto-load from database_schema.sql
-- Contains 50+ pre-configured career profiles
```

### Insert Psychometric Norms (Required for percentiles)

```sql
-- Auto-loaded from psychometric_norms.sql
-- Industry-standard normative data
```

---

## 🚀 PRODUCTION DEPLOYMENT

### 1. Update Environment
```env
CI_ENVIRONMENT = production
```

### 2. Configure HTTPS
```bash
# Obtain SSL certificate (Let's Encrypt)
sudo certbot --apache -d yourdomain.com
```

### 3. Update Base URL
```php
// app/Config/App.php
public string $baseURL = 'https://yourdomain.com/';
```

### 4. Security Checklist
- [ ] Change all default passwords
- [ ] Enable CSRF protection
- [ ] Configure email for password resets
- [ ] Set up database backups
- [ ] Enable rate limiting
- [ ] Configure firewall rules

---

## 📱 FEATURES ENABLED

✅ User Registration & Login
✅ 6 Psychometric Tests (RIASEC, VARK, MBTI, Gardner, EQ, Aptitude)
✅ Real-time Progress Tracking
✅ Comprehensive Career Analysis
✅ PDF Report Generation
✅ Career Matching Algorithm
✅ Personalized Roadmaps
✅ Industry-Standard Scoring

---

## 🎓 TEST DATA FOR DEVELOPMENT

```sql
-- Insert test user (password: Test@123)
INSERT INTO users (username, email, password_hash, full_name, date_of_birth, gender, educational_level, is_active, email_verified) VALUES
('testuser', 'test@example.com', '$2y$10$YourHashedPasswordHere', 'Test User', '2008-01-01', 'other', 'class_10', 1, 1);
```

---

## 📞 SUPPORT

### Documentation
- Full README: See README.md
- API Docs: See API_DOCUMENTATION.md
- Models Reference: See MODELS.md

### Common Issues
1. **Can't login** → Check user is active and email verified
2. **No questions showing** → Run questions_part1.sql import
3. **Report not generating** → Check TCPDF installation
4. **Slow performance** → Enable database query caching

---

## ✨ NEXT STEPS

1. **Customize Branding** → Edit colors in index.html
2. **Add More Careers** → Insert into careers table
3. **Configure Email** → Set SMTP settings in Email.php
4. **Add Logo** → Place in public/assets/images/
5. **Review Questions** → Customize in database
6. **Test Thoroughly** → Try all 6 assessments
7. **Deploy to Production** → Follow production deployment steps

---

## 🎯 SUCCESS CRITERIA

Your installation is successful when:
- ✅ You can register a new user
- ✅ You can login
- ✅ You can start an assessment session
- ✅ You can complete all 6 tests
- ✅ You receive a comprehensive report
- ✅ Report shows career matches
- ✅ PDF download works
- ✅ All charts display correctly

---

## 📈 MONITORING

```bash
# Check application logs
tail -f writable/logs/log-$(date +%Y-%m-%d).log

# Check MySQL slow queries
mysql -u root -p -e "SHOW FULL PROCESSLIST;"

# Monitor disk space
df -h
```

---

**🎉 You're all set! Start helping students discover their perfect career path.**

For detailed documentation, see README.md
For technical support, check writable/logs/ for error messages
