<?php

namespace App\Controllers;

use App\Models\User_Model;
use CodeIgniter\API\ResponseTrait;

class User extends BaseController
{
    //Common Variables
    use ResponseTrait;


    protected $adminFolder;
    protected $statusOk;
    protected $unAuthorized;
    protected $methodNotAllowed;
    protected $internalServerError;
    protected $session;
    protected $remember_me;
    protected $PC_token;
    protected $admin_id;

    public function __construct()
    {
        helper(['form', 'common', 'cookie']);

        // librarys & all other imports
        $this->common_model = new User_Model();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();

        // set variable values
        $this->adminFolder = 'user/';
        $this->statusOk = 200;
        $this->unAuthorized = 401;
        $this->methodNotAllowed = 405;
        $this->internalServerError = 500;


        $this->remember_me = get_cookie('remember_me');
        $this->PC_token = get_cookie('PC_token');
        $this->admin_id = $this->session->get('admin_id');
    }



    // Views
    public function index()
    {
        return view('welcome_message');
    }

    public function homepage(){
        return $this->loadViews('homepage');
    }

    // Usefull Fucntions
    public function hash_pass($pass)
    {
        echo password_hash($pass, PASSWORD_DEFAULT);
    }

    public function loadViews($url, $data = [])
    {
        $uri = service('uri');
        $data['projectName'] = getDirectValue('general_settings', 'value', 'name', 'siteName'); // get Project Name
        $underMaintenance = getDirectValue('general_settings', 'value', 'name', 'underMaintenance'); // get underMaintenance status

        if ($underMaintenance == 0) {

            // $data['left_sidebar'] = $this->loadSidebarFields();
            $segments = $uri->getSegments();
            $data['url'] = $segments[1];
            $data['username'] = 'Shree vyas';
            $data['notice_count'] = 2;
            $data['title'] = $data['projectName'] . ' - ' . ucfirst($segments[1] == '' ? 'login' : $segments[1]);
            $url = $this->adminFolder . $url;

            // admin_logs('pageAccess', '1', $data['url']); // Admin Activities Logs.
            return View($url, $data);
        } else {
            $data['title'] = $data['projectName'] . ' - Site Under Maintenance';
            return View($this->adminFolder . 'commonPage/underMaintenance', $data);
        }
    }

    public function SignOut()
    {
        $this->session->destroy();
        return $this->homepage();
    }
}
