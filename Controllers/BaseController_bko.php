<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['url', 'form', 'text', 'html']; // Added 'text' and 'html' as they are often needed

    /**
     * Instance of the Session object.
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // --------------------------------------------------------------------
        // 1. ENABLE SESSION (Critical for the Test Module)
        // --------------------------------------------------------------------
        $this->session = \Config\Services::session();
    }
    
    /**
     * Custom View Handler for SPA/Layouts
     * * @param string $viewName The view file to load
     * @param array $data Data to pass to the view
     * @param bool $useLayout If TRUE, wraps content in 'layout'. If FALSE, renders standalone.
     */
    protected function spa_view($viewName, array $data = [], $useLayout = true) 
    {
        // If it's an AJAX request, we usually only want the partial view fragment,
        // regardless of the layout setting (unless specific logic dictates otherwise).
        if ($this->request->isAJAX()) {
            return view($viewName, $data);
        }

        // If $useLayout is true, wrap the view in the main application layout.
        // If false, render the view directly (useful for the standalone test pages).
        if ($useLayout) {
            return view('layout', ['content' => view($viewName, $data)]);
        } else {
            return view($viewName, $data);
        }
    }
}