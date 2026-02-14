<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Home::index');

// =============================================
// AUTHENTICATION ROUTES
// =============================================
$routes->group('', function($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::processLogin');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::processRegister');
    $routes->get('logout', 'AuthController::logout');
    $routes->get('forgot-password', 'AuthController::forgotPassword');
    $routes->post('forgot-password', 'AuthController::processForgotPassword');
    $routes->get('reset-password/(:any)', 'AuthController::resetPassword/$1');
    $routes->post('reset-password/(:any)', 'AuthController::processResetPassword/$1');
    $routes->get('verify-email/(:any)', 'AuthController::verifyEmail/$1');
});

// =============================================
// API ROUTES (AJAX endpoints)
// =============================================
$routes->group('api', function($routes) {
    $routes->group('auth', function($routes) {
        $routes->post('login', 'AuthController::processLogin');
        $routes->post('register', 'AuthController::processRegister');
        $routes->post('logout', 'AuthController::logout');
        $routes->get('check', 'AuthController::checkAuth');
        $routes->post('forgot-password', 'AuthController::processForgotPassword');
    });
    
    // Assessment API
    $routes->group('assessment', ['filter' => 'auth'], function($routes) {
        $routes->get('/', 'AssessmentController::index');
        $routes->post('start', 'AssessmentController::startSession');
        $routes->get('questions/(:num)/(:any)', 'AssessmentController::getQuestions/$1/$2');
        $routes->post('response', 'AssessmentController::saveResponse');
        $routes->post('complete', 'AssessmentController::completeTest');
        $routes->get('session/(:num)', 'AssessmentController::getSession/$1');
        $routes->get('progress/(:num)', 'AssessmentController::getProgress/$1');
    });
    
    // Career API
    $routes->group('careers', function($routes) {
        $routes->get('/', 'CareerController::index');
        $routes->get('search', 'CareerController::search');
        $routes->get('(:num)', 'CareerController::show/$1');
        $routes->get('(:num)/roadmap', 'CareerController::roadmap/$1');
        $routes->get('categories', 'CareerController::categories');
        $routes->get('trending', 'CareerController::trending');
        $routes->get('by-riasec/(:any)', 'CareerController::byRiasec/$1');
        $routes->post('compare', 'CareerController::compare');
        $routes->get('matches', 'CareerController::matches', ['filter' => 'auth']);
        $routes->get('recommendations', 'CareerController::recommendations', ['filter' => 'auth']);
        $routes->get('(:num)/statistics', 'CareerController::statistics/$1');
    });
    
    // Report API
    $routes->group('reports', ['filter' => 'auth'], function($routes) {
        $routes->get('/', 'ReportController::index');
        $routes->get('(:any)', 'ReportController::get/$1');
    });
});

// =============================================
// DASHBOARD ROUTES
// =============================================
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('profile', 'ProfileController::index');
    $routes->get('profile/edit', 'ProfileController::edit');
    $routes->post('profile/update', 'ProfileController::update');
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings/update', 'SettingsController::update');
});

// =============================================
// ASSESSMENT ROUTES (Requires authentication)
// =============================================
$routes->group('assessment', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'AssessmentController::index');
    $routes->get('start', 'AssessmentController::index'); 
    $routes->get('test/(:num)/(:any)', 'AssessmentController::test/$1/$2'); 
    $routes->get('resume/(:num)', 'AssessmentController::resume/$1');
    
    // ADDED THIS LINE TO FIX 404 ERROR:
    $routes->get('report/(:any)', 'ReportController::view/$1');
});

// =============================================
// REPORT ROUTES
// =============================================
$routes->group('report', ['filter' => 'auth'], function($routes) {
    $routes->get('(:any)', 'ReportController::view/$1');
    $routes->get('download/(:any)', 'ReportController::download/$1');
});

// =============================================
// CAREER EXPLORATION ROUTES
// =============================================
$routes->group('careers', function($routes) {
    $routes->get('/', 'CareerController::browse');
    $routes->get('(:num)', 'CareerController::details/$1');
    $routes->get('category/(:any)', 'CareerController::category/$1');
});

// =============================================
// STATIC PAGES
// =============================================
$routes->get('about', 'PagesController::about');
$routes->get('faq', 'PagesController::faq');
$routes->get('contact', 'PagesController::contact');
$routes->post('contact', 'PagesController::sendContact');
$routes->get('privacy', 'PagesController::privacy');
$routes->get('terms', 'PagesController::terms');
$routes->get('help', 'PagesController::help');

// =============================================
// ADMIN ROUTES
// =============================================
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/(:num)', 'Admin\UserController::show/$1');
    $routes->post('users/(:num)/activate', 'Admin\UserController::activate/$1');
    $routes->post('users/(:num)/deactivate', 'Admin\UserController::deactivate/$1');
    $routes->get('questions', 'Admin\QuestionController::index');
    $routes->get('questions/create', 'Admin\QuestionController::create');
    $routes->post('questions', 'Admin\QuestionController::store');
    $routes->get('questions/(:num)/edit', 'Admin\QuestionController::edit/$1');
    $routes->post('questions/(:num)', 'Admin\QuestionController::update/$1');
    $routes->delete('questions/(:num)', 'Admin\QuestionController::delete/$1');
    $routes->get('careers', 'Admin\CareerController::index');
    $routes->get('careers/create', 'Admin\CareerController::create');
    $routes->post('careers', 'Admin\CareerController::store');
    $routes->get('careers/(:num)/edit', 'Admin\CareerController::edit/$1');
    $routes->post('careers/(:num)', 'Admin\CareerController::update/$1');
    $routes->get('analytics', 'Admin\AnalyticsController::index');
    $routes->get('reports', 'Admin\ReportController::index');
    $routes->get('settings', 'Admin\SettingsController::index');
    $routes->post('settings', 'Admin\SettingsController::update');
});

if (is_cli()) {
    $routes->cli('migrate', 'Migrate::index');
    $routes->cli('seed', 'Seed::index');
    $routes->cli('cache/clear', 'Cache::clear');
}

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}