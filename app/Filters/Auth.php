<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if(!session()->get('isLoggedIn')){
            // If AJAX, return JSON error to trigger frontend redirect
            if($request->isAJAX()){
                return response()->setJSON(['status'=>'error', 'redirect'=> base_url('login')]);
            }
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}