<?php
namespace App\Controllers;

class Login extends BaseController {
    public function index() {
        if(!isset($this->session->user)){
            helper(['form', 'url']);
            return view('login');
        }else{
            return redirect()->to('/');
        }
    }

    public function login() {
        if(!isset($this->session->user)){
            $validation = \Config\Services::validation();
            helper(['form', 'url']);

            if(!$this->validate(
                    $validation->getRuleGroup('login'),
                    $validation->getRuleGroup('login_errors'))){
                return view('login', [
                    'validation' => $this->validator
                ]);
            }else{
                $email = $this->request->getVar('email', FILTER_SANITIZE_STRING);
                $password = $this->request->getVar('password', FILTER_SANITIZE_STRING);
                //$rememberMe = $this->request->getVar('rememberMe');
                $userModel = new \App\Models\User();
                $user = $userModel->where('email', $email)->get()->getRow();
                if($user && password_verify($password, $user->password)){
                    unset($user->password);             // Unset password for safety sake.
                    $this->session->set('user', $user); // Store user info in session.
                    //if(isset($rememberMe)) set_cookie('user', $user);
                    return redirect()->to('/');
                }else{
                    return redirect()->to('/login');
                }
            }
        }
    }
}
?>