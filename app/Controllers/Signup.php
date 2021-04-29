<?php
namespace App\Controllers;

class Signup extends BaseController {
    public function index() {
        helper(['form', 'url']);
        return view('signup');
    }

    public function create() {
        $validation = \Config\Services::validation();
        helper(['form', 'url']);

        if(!$this->validate(
                $validation->getRuleGroup('signup'),
                $validation->getRuleGroup('signup_errors'))){
            return view('signup', [
                'validation' => $this->validator
            ]);
        }else{
            $password = trim($this->request->getVar('password', FILTER_SANITIZE_STRING));
            $userModel = new \App\Models\User();
            // Validate data
            $data = [
                'first_name' => $this->request->getVar('first_name', FILTER_SANITIZE_STRING),
                'last_name' => $this->request->getVar('last_name', FILTER_SANITIZE_STRING),
                'email' => $this->request->getVar('email', FILTER_SANITIZE_STRING),
                'password' => password_hash(
                    $password,
                    PASSWORD_DEFAULT)
            ];
            if($userModel->insert($data)){
                return redirect('/');
            }else{
                return redirect()->to('/signup');
            }
        }
    }
}
?>