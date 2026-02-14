# Career Analysis SPA - Industry Standard Psychometric Assessment System

## 🎯 Overview

A comprehensive Single Page Application (SPA) for career guidance and psychometric assessment, combining 6 industry-standard tests (RIASEC, VARK, MBTI, Gardner, EQ, Aptitude) to provide in-depth career analysis, personality assessment, and career roadmaps.

**Compliant with:**
- ✅ US Standards (APA - American Psychological Association)
- ✅ UK Standards (BPS - British Psychological Society)
- ✅ EU Standards (EFPA - European Federation of Psychologists' Associations)

## 🌟 Key Features

### Psychometric Assessments
- **RIASEC (Holland Code)** - Career interest inventory
- **VARK** - Learning style assessment  
- **MBTI** - Personality type indicator
- **Gardner's Multiple Intelligences** - Intelligence profile
- **EQ (Emotional Intelligence)** - Emotional competencies
- **Aptitude Tests** - Cognitive abilities assessment

### Comprehensive Analysis
- ✅ Personality analysis with validated instruments
- ✅ Career matching with 500+ career profiles
- ✅ IQ estimation based on aptitude scores
- ✅ Learning style preferences
- ✅ Motivational drivers
- ✅ Strengths and development areas
- ✅ Emotional competencies breakdown

### Career Mapping
- 📊 Top 10-15 career matches with percentages
- 📈 Detailed "Why this career?" explanations
- 🗺️ Complete career roadmaps (1, 3, 5, 10 years)
- 📚 Educational pathway recommendations
- 🎓 Skill development plans
- 🌍 Region-specific guidance (US, UK, EU, India)

### Age-Specific Content
- **Class 8-10** - Age-appropriate questions and guidance
- **Class 11-12** - Advanced assessments with career planning

## 🏗️ Technology Stack

### Backend
- **Framework**: CodeIgniter 4.4+
- **Language**: PHP 8.0+
- **Database**: MySQL 8.0+ / MariaDB 10.3+
- **Libraries**: 
  - TCPDF (PDF generation)
  - PHPMailer (Email notifications)
  - JWT (API authentication)

### Frontend
- **Framework**: Bootstrap 5.3
- **JavaScript**: jQuery 3.7+
- **Charts**: Chart.js 4.0
- **Icons**: Font Awesome 6.0
- **Design**: Modern, responsive SPA

### Additional Tools
- **PDF Reports**: TCPDF with custom templates
- **Email**: SMTP integration for notifications
- **Caching**: Redis/Memcached (optional)
- **Session**: Database-backed sessions

## 📦 Installation

### Prerequisites

```bash
# Required software
- PHP >= 8.0
- MySQL >= 8.0 or MariaDB >= 10.3
- Composer 2.0+
- Web server (Apache/Nginx)
- SSL certificate (for production)
```

### Step 1: Clone and Setup

```bash
# Clone the repository
git clone <repository-url>
cd career_spa

# Install CodeIgniter 4
composer create-project codeigniter4/appstarter .

# Install dependencies
composer require tecnickcom/tcpdf
composer require phpmailer/phpmailer
composer require firebase/php-jwt
```

### Step 2: Database Setup

```bash
# Create database
mysql -u root -p

CREATE DATABASE career_analysis_spa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Import schema
mysql -u root -p career_analysis_spa < database_schema.sql
mysql -u root -p career_analysis_spa < questions_part1.sql
mysql -u root -p career_analysis_spa < questions_part2.sql
mysql -u root -p career_analysis_spa < careers_data.sql
mysql -u root -p career_analysis_spa < psychometric_norms.sql
```

### Step 3: Configuration

Edit `app/Config/Database.php`:

```php
public array $default = [
    'DSN'      => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => 'your_password',
    'database' => 'career_analysis_spa',
    'DBDriver' => 'MySQLi',
    'DBPrefix' => '',
    'pConnect' => false,
    'DBDebug'  => (ENVIRONMENT !== 'production'),
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_unicode_ci',
    // ... other settings
];
```

Edit `app/Config/App.php`:

```php
public string $baseURL = 'https://yourdomain.com/';
public string $indexPage = '';
public string $appTimezone = 'UTC'; // or your timezone
```

Edit `.env`:

```env
CI_ENVIRONMENT = production

database.default.hostname = localhost
database.default.database = career_analysis_spa
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi

app.sessionDriver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
app.sessionSavePath = 'ci_sessions'

# Email configuration
email.fromEmail = noreply@yourdomain.com
email.fromName = Career Analysis System
email.SMTPHost = smtp.gmail.com
email.SMTPUser = your@email.com
email.SMTPPass = your_password
email.SMTPPort = 587
```

### Step 4: Set Permissions

```bash
# Make writable directories
chmod -R 777 writable/
chmod -R 755 public/

# Set proper ownership (adjust as needed)
chown -R www-data:www-data writable/
```

### Step 5: Configure Web Server

**Apache** (.htaccess in public folder):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/career_spa/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    # Security
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

## 🗂️ Project Structure

```
career_spa/
├── app/
│   ├── Config/
│   │   ├── Routes.php           # URL routing
│   │   ├── Database.php         # Database config
│   │   └── Email.php            # Email config
│   ├── Controllers/
│   │   ├── BaseController.php   # Base controller with auth
│   │   ├── AuthController.php   # Authentication
│   │   ├── AssessmentController.php  # Test management
│   │   ├── DashboardController.php   # User dashboard
│   │   ├── ReportController.php      # Report generation
│   │   └── CareerController.php      # Career exploration
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── AssessmentSessionModel.php
│   │   ├── TestAttemptModel.php
│   │   ├── QuestionModel.php
│   │   ├── UserResponseModel.php
│   │   ├── TestResultModel.php
│   │   ├── ComprehensiveReportModel.php
│   │   ├── CareerModel.php
│   │   └── PsychometricNormModel.php
│   ├── Libraries/
│   │   ├── PsychometricEngine.php   # Core assessment engine
│   │   ├── ReportGenerator.php      # PDF report generation
│   │   ├── CareerMatcher.php        # Career matching algorithm
│   │   └── ValidationEngine.php     # Data validation
│   └── Views/
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── dashboard/
│       │   └── index.php
│       ├── assessment/
│       │   ├── test_selection.php
│       │   ├── test_interface.php
│       │   └── report.php
│       └── layouts/
│           ├── header.php
│           └── footer.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css
│   │   │   └── assessment.css
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── assessment.js
│   │   │   └── chart-configs.js
│   │   └── images/
│   ├── uploads/           # User uploads
│   └── index.php          # Entry point
├── writable/
│   ├── cache/
│   ├── logs/
│   ├── session/
│   └── uploads/
├── database_schema.sql
├── questions_part1.sql
├── questions_part2.sql
├── careers_data.sql
├── psychometric_norms.sql
├── composer.json
└── README.md
```

## 🔐 Security Features

- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ SQL injection prevention (prepared statements)
- ✅ Session hijacking protection
- ✅ Rate limiting on API endpoints
- ✅ Input validation and sanitization
- ✅ Secure session management
- ✅ HTTPS enforcement (production)
- ✅ Audit logging

## 📊 Psychometric Standards Compliance

### Reliability Standards
- **Cronbach's Alpha**: α ≥ 0.70 (acceptable), α ≥ 0.80 (good)
- **Test-Retest Reliability**: Measured for longitudinal studies
- **Inter-rater Reliability**: Applied where subjective scoring exists

### Validity Standards
- **Content Validity**: Questions reviewed by domain experts
- **Construct Validity**: Factor analysis performed
- **Criterion Validity**: Correlated with external outcomes
- **Face Validity**: Clear, understandable questions

### Normative Data
- Age-stratified norms (13-18, 19-25)
- Region-specific norms (US, UK, EU, India, Global)
- Sample sizes: n ≥ 500 per demographic group
- Regular updates (annual review)

### Ethical Standards
- Informed consent required
- Data privacy (GDPR, COPPA compliant)
- No discrimination based on protected characteristics
- Results presented with appropriate caveats
- Professional interpretation recommended for clinical use

## 🧪 Testing

```bash
# Unit tests
./vendor/bin/phpunit tests/

# Integration tests
./vendor/bin/phpunit tests/integration/

# Run specific test
./vendor/bin/phpunit tests/PsychometricEngineTest.php
```

## 📖 API Documentation

### Authentication Endpoints

```
POST   /api/auth/login          # User login
POST   /api/auth/register       # User registration
POST   /api/auth/logout         # User logout
GET    /api/auth/check          # Check auth status
```

### Assessment Endpoints

```
GET    /api/assessment          # Get user's assessments
POST   /api/assessment/start    # Start new assessment session
GET    /api/assessment/questions/{session}/{category}  # Get questions
POST   /api/assessment/response # Save response
POST   /api/assessment/complete # Complete test
GET    /api/assessment/report/{code}  # Get comprehensive report
GET    /api/assessment/download/{code} # Download PDF report
```

### Career Endpoints

```
GET    /api/careers             # Browse careers
GET    /api/careers/{id}        # Get career details
GET    /api/careers/match       # Get career matches for user
GET    /api/careers/roadmap/{career}/{age_group}  # Get career roadmap
```

## 🎨 User Interface Features

### Dashboard
- Welcome screen with progress overview
- Previous assessment history
- Quick start buttons
- Performance metrics

### Test Interface
- Progress bar
- Question navigation
- Save and continue later
- Time tracking (optional)
- Mobile-responsive design

### Report Viewing
- Interactive charts (Chart.js)
- Tabbed sections
- Print-friendly format
- PDF download option
- Social sharing (optional)

## 🌍 Internationalization

Currently supports:
- English (US)
- English (UK)

Planned:
- Spanish
- French
- German
- Hindi

## 📈 Analytics & Insights

The system provides:

1. **Personal Insights**
   - Personality profile
   - Learning style
   - Emotional intelligence breakdown
   - Cognitive strengths

2. **Career Guidance**
   - Top career matches with percentages
   - Detailed fit explanations
   - Potential challenges
   - Success factors

3. **Action Plans**
   - Immediate next steps (1 year)
   - Short-term goals (1-3 years)
   - Medium-term goals (3-5 years)
   - Long-term vision (5-10 years)

4. **Educational Pathways**
   - Recommended subjects
   - Exam preparation strategies
   - Course suggestions
   - Certification roadmap

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is proprietary. All rights reserved.

## 📞 Support

For support and queries:
- Email: support@careerguidance.com
- Documentation: https://docs.careerguidance.com
- Community Forum: https://community.careerguidance.com

## 🙏 Acknowledgments

- American Psychological Association (APA) for test standards
- British Psychological Society (BPS) for ethical guidelines
- European Federation of Psychologists' Associations (EFPA) for review model
- John Holland for RIASEC theory
- Neil Fleming for VARK model
- Isabel Briggs Myers for MBTI framework
- Howard Gardner for Multiple Intelligences theory
- Daniel Goleman for Emotional Intelligence framework

## 📝 Version History

- v1.0.0 (2024-02-13) - Initial release
  - All 6 psychometric tests
  - Comprehensive reporting
  - Career matching engine
  - PDF report generation

## 🔮 Roadmap

- [ ] Mobile app (iOS/Android)
- [ ] AI-powered career counseling chatbot
- [ ] Video interview practice
- [ ] Mentor matching
- [ ] Job board integration
- [ ] Skills gap analysis
- [ ] Learning resources library
- [ ] Parent/Guardian portal

---

**Made with ❤️ for empowering career decisions**
