<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');
$routes->get('contact', 'Home::contact');
$routes->get('home/search', 'Home::search'); // AJAX Search Endpoint


// Auth Routes
$routes->get('login', 'Auth::login');
$routes->post('auth/authenticate', 'Auth::authenticate');
$routes->get('register', 'Auth::register');
$routes->post('auth/store', 'Auth::store');
$routes->get('logout', 'Auth::logout');

// Protected Routes
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
// Add this under your Home routes
$routes->get('career-audit', 'Home::audit');



$routes->group('tests', function($routes) {
    $routes->get('/', 'TestController::index');
    $routes->get('results/(:segment)', 'TestController::results/$1');
    $routes->post('submit', 'TestController::submit');
    $routes->get('(:segment)', 'TestController::index/$1'); 
});

$routes->get('report/consolidated', 'ReportController::index');
$routes->get('report/view', 'ReportController::viewReport');     // HTML View
$routes->get('report/download', 'ReportController::downloadPdf');
// Add this line
$routes->get('report/edumile', 'ReportController::viewOfficialReport');

// Optional: Redirect /test to a dashboard or list of tests
$routes->add('test', function() {
    return redirect()->to('/dashboard'); 
});