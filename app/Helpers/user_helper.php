<?php

function pre()
{
    echo (php_sapi_name() !== 'cli') ? '<pre>' : '';
    foreach (func_get_args() as $arg) {
        echo preg_replace('#\n{2,}#', "\n", print_r($arg, true));
    }
    echo (php_sapi_name() !== 'cli') ? '</pre>' : '';
    exit();
}

function getDirectValue($table, $columnRequired, $columnNameToCompare, $columnValueToCompare)
{
    $db      = \Config\Database::connect();
    $builder = $db->table($table);
    $value   = $builder->select($columnRequired)->getWhere(array($columnNameToCompare=>$columnValueToCompare))->getResultArray();
    if ($value) {
        return $value[0][$columnRequired];
    } else {
        return 0;
    }
}

function get_DirectValueConcat($table_name,$column_need,$column_have,$column_value,$concatValue = null){
    
    $db      = \Config\Database::connect();
    $builder = $db->table($table_name);

    if($concatValue != '')
    $builder->select($concatValue);
    else
    $builder->select($column_need);

    $builder->where($column_have,$column_value);
    $result = $builder->get($table_name)->getResultArray();
    // echo $CI->db->last_query();
    if(count($result) != 0)
        return $result[0][$column_need];
    else
        return null;
}


function get_direct_value_custom_where($table, $columnRequired,$where = array())
{
    $db      = \Config\Database::connect();
    $builder = $db->table($table);
    $value   = $builder->select($columnRequired)->getWhere($where)->getResultArray();
    if ($value) {
        return $value[0][$columnRequired];
    } else {
        return 0;
    }
}

function updateDirectValue($table,$data,$where = null){
    $db      = \Config\Database::connect();
    $builder = $db->table($table);
    $value  = $builder->update($table, $data, $where);
    if($value)
        return 1;
    else
        return 0;
}

function get_numrows($table_name, $where){
    $db      = \Config\Database::connect();
    $builder = $db->table($table_name);
    if (count($where) > 0) {
        $builder->where($where);
    }
    $sql = $builder->select('*')->get()->getFieldCount();
    return $sql;
}

function findKey($array, $keySearch)
{
    $key = array_search($keySearch, array_column($array, 'id'));
    if(false !== $key)
       return true;
    else
       return false;
}

function isImage($filename)
{
    $file_extension = explode('.', $filename);
    $file_extension = strtolower(end($file_extension));
    $accepted_formate = array('jpeg', 'jpg', 'png', 'gif', 'svg');
    if (in_array($file_extension, $accepted_formate)) {
        return 1;
    } else {
        return 0;
    }
}


function getSunday($months,$years)
{
    $monthName = date("F", mktime(0, 0, 0, $months));
    $fromdt=date('Y-m-01 ',strtotime("First Day Of  $monthName $years")) ;
    $todt=date('Y-m-d ',strtotime("Last Day of $monthName $years"));
    
    $num_sundays='';                
    for ($i = 0; $i < ((strtotime($todt) - strtotime($fromdt)) / 86400); $i++)
    {
        if(date('l',strtotime($fromdt) + ($i * 86400)) == 'Sunday')
        {
                $num_sundays++;
        }    
    }
    return $num_sundays;
}

function app_name()
{
    return getDirectValue('general_settings', 'value', 'name', 'app_name');
}

function get_office_ip()
{
    $ip = getDirectValue('general_settings', 'value', 'name', 'office_ip');
    return explode(',',$ip);
}

function RandomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

function authenticate($para='hr_logged_in')
{
    if ($para == 'hr_logged_in') {
        if (empty(session('hr_logged_in'))) {
            return false;
        } else {
            return true;
        }
    }
    if ($para == 'hr_id') {
        if (empty(session('hr_logged_in'))) {
            return false;
        } else {
            return session('hr_id');
        }
    }
}


function isAuthorized($action)
{
    if (session('hr_logged_in')) {
        $role_id = getDirectValue('hr_login', 'role', 'id', session('hr_id'));
        $perms = unserialize(getDirectValue('admins_role_perms', 'permission', 'id', $role_id));
        if (!in_array($action, $perms)) {
            session()->setFlashdata('denied', 'Access Denied.');
            return 0;
        } else {
            return 1;
        }
    } else {
        return 0;
    }
}

function Permission($action)
{
    if (session('hr_logged_in')) {
        $role_id = getDirectValue('hr_login', 'role', 'id', session('hr_id'));
        $perms = unserialize(getDirectValue('admins_role_perms', 'permission', 'id', $role_id));
        if (!in_array($action, $perms)) {
            return 0;
        } else {
            return 1;
        }
    } else {
        return 0;
    }
}

function countRow($table, $where)
{
    $db      = \Config\Database::connect();
    $builder = $db->table($table);
    $builder->selectCount('id');
    $builder->where($where);
    $value = $builder->get()->getResultArray();

    if ($value) {
        return $value[0]['id'];
    } else {
        return 0;
    }
}

function SumColumn($table, $where, $column)
{
    $db      = \Config\Database::connect();
    $builder = $db->table($table);
    $builder->selectSum($column);
    $builder->where($where);
    $value = $builder->get()->getResultArray();
    if ($value[0][$column] !== '') {
        return $value[0][$column];
    } else {
        return 0;
    }
}

function BgColor()
{
    $color = ['bg-red','bg-pink','bg-purple','bg-deep-purple','bg-indigo','bg-blue','bg-light-blue','bg-blue','bg-teal','bg-green','bg-light-green','bg-lime','bg-yellow','bg-amber','bg-orange','bg-deep-orange','bg-brown','bg-grey','bg-blue-grey'];
    shuffle($color);
    return $color[0];
}

function generateKey($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_g_setting_val($column)
{
    $db      = \Config\Database::connect();
    $builder = $db->table('general_settings');
    return $builder->getWhere(array('name'=>$column))->getRowArray()['value'];
}


function sendNotification($from,$to,$title,$msg,$url){
    $url = $url == '' ? 'notification' : $url;
    $to_fcm_token = getDirectValue('emp_login', 'fcm_token', 'emp_id', $to);
    if($to_fcm_token != ''){
        $data['to']    = $to_fcm_token;
        $data['title'] = $title;
        $data['body']  = $msg;
        $data['sound'] = "default";
        $data['priority'] = "high";
        $data['data']['link'] = $url;
        $ApiData = json_encode($data);
        
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://exp.host/--/api/v2/push/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$ApiData,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $insertData['sender_emp_id'] = $from;
        $insertData['message'] = json_encode($data);
        $insertData['receiver_emp_id'] = $to;
        $insertData['is_read'] = 1;
        $insertData['created_at'] = date('Y-m-d H:i:s');
        
        $db = \Config\Database::connect();
        $builder = $db->table('notification');
        return $builder->insert($insertData);
    }
}

function UploadFile($FILE)
{
    $url        = get_g_setting_val('dcloud_api');
    $X_Key      = get_g_setting_val('x-key');
    $X_Secret   = get_g_setting_val('x-secret');
    $ch = curl_init();
    $RealTitle = $FILE['name'];

    $postfields['file'] = new CurlFile($FILE['tmp_name'], $FILE['type'], $RealTitle);
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfields,
        CURLOPT_HTTPHEADER => array(
            'X-Key: ' . $X_Key,
            'X-Secret: ' . $X_Secret
        ),
    ));

    $response = curl_exec($ch);
    if (!curl_errno($ch)) {
        curl_close($ch);
        return json_decode($response, true);
    } else {
        curl_close($ch);
        $errmsg = curl_error($ch);
        return $errmsg;
    }
}

function DeleteDcloudFile($FILES)
{
    $url        = get_g_setting_val('dcloud_api');
    $X_Key      = get_g_setting_val('x-key');
    $X_Secret   = get_g_setting_val('x-secret');
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_POSTFIELDS => json_encode($FILES),
        CURLOPT_HTTPHEADER => array(
            'X-Key: ' . $X_Key,
            'X-Secret: ' . $X_Secret
        ),
    ));

    $response = curl_exec($ch);
    if (!curl_errno($ch)) {
        curl_close($ch);
        return json_decode($response, true);
    } else {
        curl_close($ch);
        // $errmsg = curl_error($ch);
        // return $errmsg;
        return array('status'=>false,'message'=>'Failed To upload');
    }
}


function check_HashPass($pass, $hash){
    if(password_verify($pass,$hash))
        return true;
    else
        return false;
} 

function arrayToList(array $array): string
{
    $html = '';
    if (count($array)) {
        $html .= '<ul>';
        foreach ($array as $value) {
            $html .= '<li>' . $value . '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}

function navCategory()
{
    $db               = \Config\Database::connect();
    $builder          = $db->table('category');
    $parentCategory   = $builder->where('parent_id',0)->get()->getResultArray();
    $data['category'] = [];
    foreach ($parentCategory as $key => $parentCat) {
        $childCategory = $builder->where('parent_id',$parentCat['code'])->get()->getResultArray();
        foreach ($childCategory as $k => $childCat) {
            $data['category'][$parentCat['name']][] = $childCat['name'].','.$childCat['code'];
        }
    }
    return $data;
}

function navWishList($user_id)
{
    $db = \Config\Database::connect();
    return $db->table('my_wish_list')->where('user_id', $user_id)->get()->getResultArray();
}



//**************************************************************************************SMS Gateway************************************************************************************************
function send_gateway_message($contact, $msg, $template_id=null)
{
    return send_bulksmsgateway($contact, $template_id, $msg);
    // if (get_g_setting_val('message_gateway') == 'gupshup') {
    //     return send_smsgupshup($contact, $msg);
    // } else {
    //     return send_bulksmsgateway($contact, $template_id, $msg);
    // }
}

function send_smsgupshup($phone, $msg)
{
    $curl = curl_init();
    $new = str_replace('&', '%26', $msg);
    $new = str_replace(' ', '%20', $new);
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=sendMessage&msg=" . $new . "&send_to=" . $phone . "&msg_type=Text&userid=2000190745&auth_scheme=Plain&password=jdHq2QoSg&v=1.1&format=TEXT",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => "",
    CURLOPT_HTTPHEADER => array(
        "Content-Type: text/plain"
    ) ,
));
    $response = curl_exec($curl);
    // print_arrays($response);exit;
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        // echo "cURL Error #:" . $err;
        return 0;
    } else {
        $str1 = explode('|', $response);
        $str = str_replace(' ', '', $str1[0]);
        if ($str == 'success') {
            return 1;
        } else {
            return 0;
        }
    }
}

function send_bulksmsgateway($number, $template_id, $message)
{
    $username="Zaeem23";
    $password ="7208992803";
    $sender="DARPRL";
    $url="http://api.bulksmsgateway.in/sendmessage.php?user=".urlencode($username)."&password=".urlencode($password)."&mobile=".urlencode($number)."&sender=".urlencode($sender)."&message=".urlencode($message)."&type=".urlencode('3')."&template_id=".urlencode($template_id);
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => "",
    CURLOPT_HTTPHEADER => array(
        "Content-Type: text/plain"
    ) ,
));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        // echo "cURL Error #:" . $err;
        return 0;
    } else {
        // echo $response;exit;
        $res = json_decode($response, true);
        if ($res['status'] == 'success') {
            return 1;
        } else {
            return 0;
        }
    }
}