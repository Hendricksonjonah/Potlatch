<?php

namespace App\Controllers;

class Account extends BaseController
{
	public function index() {
        // Check if user is signed in,
        if(isset($this->session->user)){
            helper(['form', 'url', 'html']);

            $data['title'] = 'Account';
            $data['user'] = $this->session->user;
            echo view('components/header', $data);
            echo view('account');
            echo view('components/footer');
        }else{
            return redirect()->to('/login');
        }
	}

    // Edit account information. (Not finished.)
    public function edit() {
        if(!isset($this->session->user)){ // If signed in.
            $validation = \Config\Services::validation();
            helper(['form', 'url']);

            // Validate email if provided.
            if($this->validate(['email' => 'valid_email|is_unique[users.email]'])){
                $fName = $this->request->getVar('first_name', FILTER_SANITIZE_STRING);
                $lName = $this->request->getVar('last_name', FILTER_SANITIZE_STRING);
                $email = $this->request->getVar('email', FILTER_SANITIZE_STRING);
                $userModel = new \App\Models\UserModel();
                $user = $userModel->where('email', $email)->get()->getRow();
                if(password_verify($password, $user->password)){
                    unset($user->password);
                    $this->session->set('user', $user);
                    //if(isset($rememberMe)) set_cookie('user', $user);
                    return redirect()->to('/');
                }else{
                    return redirect()->to('/login');
                }
            }else{
                return redirect()->to('/login');
            }
        }
    }
}

?>