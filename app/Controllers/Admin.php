<?php

namespace App\Controllers;
use App\Models\AdminModel;

class Admin extends BaseController
{
    public function __construct()
    {
    helper(['form', 'common', 'cookie']);
    $this->adminModel = new AdminModel();
    $this->session = \Config\Services::session();
    $this->db = \Config\Database::connect();
    $validation =  \Config\Services::validation();
    }

    public function index()
    {   
        return view('admin/auth/login');
    }

    public function auth(){
        if($this->request->isAJAX()){
            $rules = [
                'username' => ['label' => 'username', 'rules' => 'required'],
                'password' => ['label' => 'password', 'rules' => 'required|min_length[8]']
            ];
            if($this->validate($rules) == false){
                return $this->$validation->getErrors();
            }
            $username   = $this->request->getPost('username');
            $password   = $this->request->getPost('password');
    
            $result = $this->adminModel->checkauth($username, $password);
            if($result){
                echo $result;
            }else{
                echo 'failed';
            }
        }
    }
    
}
