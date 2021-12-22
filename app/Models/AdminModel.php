<?php
namespace App\Models;
use CodeIgniter\Model;

class AdminModel extends Model
{
    public function checkauth($username, $password){
        $result = $this->db->table('users')->where('username', $username)->get()->getRowArray();
        if(count($result) == 1){
            $verify_pass = password_verify($password, $result['password']);
            if($verify_pass){
                $data = [
                    'status'    => 1,
                    'user_id'   => $result['id'],
                    'active'    => $result['status'],
                    'username'  => $result['username'],
                    'email'     => $result['email'],
                ];
                return $data;
            }else{
                $result['status'] = 2;
                return $result;
            }
        }else{
            $result['status'] = 0;
            return $result;
        }
    }

}