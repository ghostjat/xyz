<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// 1. Static Pages
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact','Home::contact');
$routes->post('contact/submit', 'Home::submitContact');

$routes->get('nep/(:any)', 'Home::viewNep/$1');
$routes->get('policy/(:any)', 'Home::viewPolicy/$1');
$routes->get('school/(:any)', 'Home::viewSchool/$1');
$routes->get('service/(:any)', 'Home::viewService/$1');
$routes->get('solutions/(:any)', 'Home::viewSoltions/$1');



$routes->setAutoRoute(false);


$routes->get('login', 'Auth::login');
$routes->post('auth/authenticate', 'Auth::authenticate');
$routes->post('auth/store', 'Auth::store');
$routes->get('register', 'Auth::register');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/review_user/(:num)/(:any)', 'Auth::review_user/$1/$2');
$routes->post('auth/process_review', 'Auth::process_review');

$routes->group('payment', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'PaymentController::index');
    $routes->post('verify', 'PaymentController::verify');
});

// Group protected routes
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('dashboard/bookAppointment', 'Dashboard::bookAppointment');

    // Test integration routing
    $routes->get('tests/(:segment)', 'TestController::index/$1');
    $routes->post('tests/submit', 'TestController::submit');
    $routes->get('tests/results/(:segment)', 'TestController::results/$1');

    // Report logic
    $routes->get('report/viewReport', 'ReportController::viewReport');
    $routes->get('report/downloadPdf', 'ReportController::downloadPdf');
});

$routes->get('report/consolidated', 'ReportController::index');
$routes->get('report/view', 'ReportController::viewReport');     // HTML View
$routes->get('report/download', 'ReportController::downloadPdf');
// Add this line
$routes->get('report/edumile', 'ReportController::viewOfficialReport');

$routes->group('cca', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'CcaController::index');
    $routes->get('dashboard', 'CcaController::index');
    
    $routes->post('getCareerClusters', 'CcaController::getCareerClusters');
    $routes->post('getCareersByCluster', 'CcaController::getCareersByCluster');
    $routes->post('getCareerDetails', 'CcaController::getCareerDetails');
    
    $routes->post('loadCandidateList', 'CcaController::loadCandidateList');
    $routes->post('getStudentRawScores', 'CcaController::getStudentRawScores');
    
    $routes->post('loadCompletedReports', 'CcaController::loadCompletedReports');
    $routes->get('viewStudentReport/(:num)', 'CcaController::viewStudentReport/$1');
    $routes->get('downloadPdf/(:num)', 'CcaController::downloadPdf/$1');
    
    $routes->post('saveReportRemarks', 'CcaController::saveReportRemarks');
    $routes->post('getReportMeta', 'CcaController::getReportMeta');
    $routes->post('emailStudentReport', 'CcaController::emailStudentReport');
    
    $routes->post('getSchoolAnalytics', 'CcaController::getSchoolAnalytics');
    $routes->post('getCrmData', 'CcaController::getCrmData');
    $routes->post('saveCrmItem', 'CcaController::saveCrmItem');
    // =========================================================
    // AI CAREER SIMULATOR ROUTES
    // =========================================================
    $routes->get('simulator', 'CcaController::aiSimulator');
    $routes->post('searchCandidate', 'CcaController::searchCandidate');
    $routes->post('fetchCandidateData', 'CcaController::fetchCandidateData');
    $routes->post('runSimulation', 'CcaController::runSimulation');
    $routes->post('updateAppointmentStatus', 'CcaController::updateAppointmentStatus');
    $routes->post('loadAppointments', 'CcaController::loadAppointments');
});

$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('dashboard', 'AdminController::index');
    
    // Create/Update Endpoints
    $routes->post('saveuser', 'AdminController::saveUser');
    $routes->post('savequestion', 'AdminController::saveQuestion');
    $routes->post('saveReportComment', 'AdminController::saveReportComment');
    
    // Delete Endpoints
    $routes->post('deleteuser/(:num)', 'AdminController::deleteUser/$1');
    $routes->post('deletequestion/(:num)', 'AdminController::deleteQuestion/$1');
    $routes->post('deletereport/(:num)', 'AdminController::deleteReport/$1');
    
    $routes->post('bulkImportUsers', 'AdminController::bulkImportUsers');
    $routes->post('saveschool', 'AdminController::saveSchool');
    $routes->post('updateAppointmentStatus', 'AdminController::updateAppointmentStatus');
    
    $routes->post('getCategoriesByModule', 'AdminController::getCategoriesByModule');
    
    // =========================================================
    // AI CAREER SIMULATOR ROUTES
    // =========================================================
    $routes->get('simulator', 'AdminController::aiSimulator');
    $routes->post('api/searchCandidate', 'AdminController::searchCandidate');
    $routes->post('api/fetchCandidateData', 'AdminController::fetchCandidateData');
    $routes->post('api/runSimulation', 'AdminController::runSimulation');
});
