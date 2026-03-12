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
    protected $request;
    protected $helpers = ['url', 'form', 'text', 'html'];
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load the URL helper automatically
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
    }

    /**
     * CLEAN RENDERER: Does not check file paths.
     * It just loads the view you tell it to.
     */
    protected function renderPage(string $viewName, array $data = [],$userLayout=true)
    {
        // 1. Get the content
        $contentFragment = view($viewName, $data);

        // 2. SPA Mode (AJAX)
        if ($this->request->isAJAX()) {
            return $this->response->setBody($contentFragment);
        }

        if($userLayout) {
            return view('layout_main', [
            'page_content' => $contentFragment,
            'page_title'   => $data['title'] ?? 'Pharos Education'
        ]);
        }else {
             return view($viewName, $data);
        }
        
    }
    
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