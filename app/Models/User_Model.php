<?php
namespace App\Models;

use CodeIgniter\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class User_Model extends Model
{
    protected $table = null;

    
    public function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }

    //***************************************Common Functions*****************************************************

    public function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
    }

    // public function isAlredy_exists($table,$column,$value,$id)
    // {
    //     $this->db->select('id');
    // 	$where = array('id !=' => $id, $column => $value);
    //     $query = $this->db->get_where($table,$where);
    //     $result = $query->result_array();
    //     $count = count($result);
    //     if (empty($count)) {
    //         return 1;
    //     } else {
    //         return 0;
    //     }
    // }

    // *************************************************************************Employee Functions*********************************************************************
    public function update_last_login_emp($username,$remember_me,$login_token)
    {
        $request = \Config\Services::request();
        $builder = $this->db->table('admin');
        $builder->where('username', $username);
        $builder->update(array('last_login' => date('Y-m-d H:i:s'),'last_login_ip' => $request->getIPAddress(),'login_token'=>$login_token,'remember_me'=>$remember_me));
        return true;
    }

    public function validate_credentials($username, $password)
    {
        $builder = $this->db->table('admin');
        $result = $builder->getWhere(array('username' => $username))->getResultArray();
        if (count($result) == 1) {
            if (!check_HashPass($password, $result[0]['password'])) {
                return 0;
            }
            if ($result[0]['status'] == 1) {
                $result[0]['remember_me'] = password_hash($username, PASSWORD_DEFAULT);
                $result[0]['login_token_for_session'] = uniqid(time());
                $this->update_last_login_emp($username ,$result[0]['remember_me'], $result[0]['login_token_for_session']);
                return $result[0];
            } else {
                $result['status'] = 2;
                return $result;
            }
        }else{
            $result['status'] = 0;
            return $result;
        }
    }

    function send_mail($mail_data)
    {
        $email_config = Array(
            'charset' => 'utf-8',
            'mailType' => 'html',
        );
        $email = \Config\Services::email();
        $email->initialize($email_config);
        $email->setNewline("\r\n");
        $email->setCRLF("\r\n");
        $sender_email       = getDirectValue('general_settings','value','name','system_email');
        $sender_name        = getDirectValue('general_settings','value','name','sender_name');

        $receiver_email     = $mail_data['receiver_email'];
        $receiver_cc_email  = isset($mail_data['receiver_cc_email'])?$mail_data['receiver_cc_email']:array();
        $receiver_bcc_email = isset($mail_data['receiver_bcc_email'])?$mail_data['receiver_bcc_email']:array();
        $mail_subject       = $mail_data['mail_subject'];
        $mail_body          = $mail_data['mail_body'];
        $attachments        = isset($mail_data['attachment'])?$mail_data['attachment']:array();

        $email->setFrom($sender_email, $sender_name);
        $email->setTo($receiver_email);

        if(count($receiver_cc_email) > 0){
            $email->setCC($receiver_cc_email);
        }
        if(count($receiver_bcc_email) > 0){
            $email->setBCC($receiver_bcc_email);
        }

        if(!empty($attachments)){
            $email->attach($mail_data['attachment'],'application/pdf',$mail_data['file_name'], false);
        }

/*        if(!empty($attachments) && is_array($attachments)){
            foreach ($attachments as $key => $value) {
                $email->attach($value);
            }
        }*/

        $email->setSubject($mail_subject);
        $email->setMessage($mail_body);

        if ($email->send()){
            return 1;
        }
        else{
            return 0;
            // pre($email->printDebugger(['headers']));
        }
    }

    public function create_excel($table, $where, $fields){

        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FF4F81BD')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'ffffff'),
                'size'  => 10,
                'name'  => 'Verdana'
            )
        );

        $rows = 2;
        $builder = $this->db->table($table);
        $checkIfRowExist = $builder->where($where)->get()->getResultArray();
        $fileName = $table.'.xlsx';  
        $spreadsheet = new Spreadsheet();
    
        $sheet = $spreadsheet->getActiveSheet();
        $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $newAlp = []; // 25
        
        for ($i=0; $i < count($alphabet); $i++) { 
            if(count($newAlp) == 25 && count($fields) > 25){
                for ($j=0; $j < 2; $j++) {
                    for ($k=0; $k < count($alphabet); $k++) { 
                        $newAlp[] = $alphabet[$j].$alphabet[$k];
                    }
                }
            }
            else
                $newAlp[] = $alphabet[$i];
        }

        $cell = 'A1:'.$newAlp[count($fields)-1].'1';
        $sheet->getStyle($cell)->applyFromArray($styleArray);
        foreach ($fields as $key=>$field) {
            $sheet->setCellValue($alphabet[$key].'1', $field);
        }

        foreach ($checkIfRowExist as $key=>$val){
            foreach ($fields as $key=>$field) {
                $sheet->setCellValue($alphabet[$key] . $rows, $val[$fields[$key]]);
            }
            $rows++;
        } 
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename='.$fileName);
        $writer->save("php://output");
    }

    public function generalSettings()
    {
        return $this->db->table('general_settings')->orderBy('general_settings.id','DESC')->get()->getResultArray();
    }

    public function addsetting($data)
    {
        return $this->db->table('general_settings')->insert($data);
    }

    public function editSetting($id,$data)
    {
        return $this->db->table('general_settings')->where('id',$id)->update($data);
    }

    public function adminUsers()
    {
        return $this->db->table('admin')->orderBy('admin.id','DESC')->get()->getResultArray();
    }

    public function Logs($type)
    {
        return $this->db->table('system_logs')->where('type',$type)->orderBy('system_logs.id','DESC')->get()->getResultArray();
    }

    public function vendorList()
    {
        return $this->db->table('vendor')->orderBy('id','DESC')->get()->getResultArray();
    }
    
    public function singleVendor($id)
    {
        return $this->db->table('vendor')->where('id',$id)->get()->getRowArray();
    }


       //************************************************Coupons Functions*********************************************

       public function get_coupon($id)
       {
           $coupon = $this->db->Where('coupons', array('id' => $id))->result_array();
           return $coupon[0];
       }
   
       public function couponList($where_array = 'FALSE')
       {
           if (is_array($where_array) && isset($where_array)) {
               $this->db->where($where_array);
           }
           return $this->db->table('coupons')->select('id','title','coupon_code','status','end_date')->get()->getResultArray();
       }
   
       public function delete_coupon($coupon_id)
       {
           $this->db->where('id', $coupon_id);
           if ($this->db->delete('coupons'))
               return 1;
           else
               return 0;
       }
   
   
       public function generate_unique_coupon_code()
       {
           $response = array(
               'response_code'  => 0,
               'response_error' => '',
               'response_data'  => array()
           );
   
           $referral_code = '';
           $loop_end_flag = 0;
           $loop_count    = 0;
   
           do {
               $referral_code     = $this->generate_coupon_code();
               $unique_code_flag  = $this->check_unique_code($referral_code);
   
               if ($unique_code_flag) {
                   $loop_end_flag = 1;
               }
   
               $loop_count++;
           } while ($loop_end_flag == 0 && $loop_count < 10);

           if ($loop_end_flag == 1) {
               $response['response_code']                = 1;
               $response['response_data']['coupon_code'] = $referral_code;
           } else {
               $response['response_code']  = -1;
               $response['response_error'] = "Oops! some error occurred while processing. Please try again.";
           }
   
           return $response;
       }
   
       public function generate_coupon_code($code_len = '8', $container = '')
       {
           if ($container == '' || $code_len > strlen($container)) {
               $_alphaCaps     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
               $_numerics       = '1234567890';
               $_container     = $_numerics . $_alphaCaps . $_numerics;
           } else {
               $_container     = $container;
           }

           $_coupon_code         = '';

           for ($i = 0; $i < $code_len; $i++) {
               $_rand           = mt_rand(0, strlen($_container) - 1);
               $_coupon_code .= substr($_container, $_rand, 1);
           }
   
           return $_coupon_code;
       }

       public function check_unique_code($coupon_code)
        {
            $coupon_query = $this->db->select("id")->get_where("coupons", array('UPPER(coupon_code)' => strtoupper($coupon_code)));
            if ($coupon_query->num_rows() < 1) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
   

}