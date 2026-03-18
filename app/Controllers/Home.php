<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return $this->renderPage('pages/home', ['title' => 'Home']);
    }

    public function about()
    {
        return $this->renderPage('pages/about', ['title' => 'About Us']);
    }
    
    public function contact() {
        return $this->renderPage('pages/contact', ['title' => 'Contact Us']);
    }

    // ==========================================
    // SPECIFIC HANDLERS (Easier to Debug)
    // ==========================================

    public function viewNep($page)
    {
        // Force the folder to be 'nep'
        return $this->loadView('nep', $page);
    }
    
    public function viewPolicy($page)
    {
        // Force the folder to be 'nep'
        return $this->loadView('policy', $page);
    }

    public function viewSchool($page)
    {
        // Force the folder to be 'school'
        return $this->loadView('school', $page);
    }

    public function viewService($page)
    {
        // Force the folder to be 'schools' (plural)
        return $this->loadView('service', $page);
    }
    
    public function viewSoltions($page)
    {
        // Force the folder to be 'schools' (plural)
        return $this->loadView('solutions', $page);
    }

    // ==========================================
    // THE LOADER LOGIC
    // ==========================================
    private function loadView($folder, $page)
    {
        // 1. Sanitize: Remove .php if the user typed it in the URL
        $page = str_replace('.php', '', $page);

        // 2. Construct path: pages/nep/nep2020
        $viewName = "pages/{$folder}/{$page}";
        

        // 4. Render if found
        $data = ['title' => ucfirst($folder) . ' | ' . ucfirst($page)];
        return $this->renderPage($viewName, $data);
    }
}