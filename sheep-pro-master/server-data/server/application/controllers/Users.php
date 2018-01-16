<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *         http://example.com/index.php/welcome
     *    - or -
     *         http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        parent::__construct();
    }

    public function add()
    {
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            $postdata             = $_POST;
            $postdata['password'] = randomPassword();
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('first_name', 'User first name', 'required');
            $this->form_validation->set_rules('last_name', 'User last name', 'required');
            $this->form_validation->set_rules('company', 'Company', 'required');
            $this->form_validation->set_rules('email', 'email', 'valid_email|required|is_unique[users.email]');
            $this->form_validation->set_rules('secondary_email', 'Secondary Email', 'valid_email');
            $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'required');
            $this->form_validation->set_rules('suburb', 'Suburb', 'required');
            $this->form_validation->set_rules('post_code', 'Postcode', 'required');
            $this->form_validation->set_rules('state', 'State', 'required');
            $this->form_validation->set_rules('user_type', 'User type', 'required');

            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            } else {
                $InsertData = array(
                    'user_type'       => ucfirst($postdata['user_type']),
                    'first_name'      => $postdata['first_name'],
                    'last_name'       => $postdata['last_name'],
                    'company'         => $postdata['company'],
                    'email'           => $postdata['email'],
                    'password'        => md5($postdata['password']),
                    'secondary_email' => @$postdata['secondary_email'],
                    'mobile_phone'    => $postdata['mobile_phone'],
                    'landline_phone'  => @$postdata['landline_phone'],
                    'address_1'       => @$postdata['address_1'],
                    'address_2'       => @$postdata['address_2'],
                    'suburb'          => $postdata['suburb'],
                    'post_code'       => $postdata['post_code'],
                    'state'           => $postdata['state'],
                    'notes'           => @$postdata['notes'],
                    'status'          => '0',
                    'created'         => date("Y-m-d H:i:s"),
                    'modified'        => date("Y-m-d H:i:s"),
                );

                if ($this->common_model->insert_data('users', $InsertData)) {
                    //Create Calendar and share calendar code start here
                    if (strtolower($postdata['user_type']) == 'scanner') {
                        //create start
                        $this->load->library('google');
                        $array       = array('title' => $postdata['first_name'] . ' ' . $postdata['last_name'] . '(' . $postdata['email'] . ')', 'timeZone' => 'Australia/Sydney');
                        $res         = $this->google->addCalendar($array);
                        $update_data = array();

                        if ($res['status']) {
                            $update_data['calendar_id'] = $res['data'];
                            $res2                       = $this->google->setHook($res['data']);
                            if ($res2['status']) {
                                $update_data['channel_id'] = $res2['data'];
                            }
                            $this->common_model->update_data('users', $update_data, array('email' => $postdata['email']));
                            //Share with Client
                            $args = array(
                                'share_with'  => $postdata['email'],
                                'permission'  => 'writer',
                                'calendar_id' => $res['data'],
                            );
                            $this->google->shareCal($args);
                            //Share withA Super Admin
                            $args = array(
                                'share_with'  => CAL_ADMIN_EMAIL,
                                'permission'  => 'owner',
                                'calendar_id' => $res['data'],
                            );
                            $this->google->shareCal($args);
                        }
                    }
                    //End
                    $message         = $this->lang->line('Registration_mail_body');
                    $replaceto       = array("__FULL_NAME", "__TYPE", "__EMAIL", "__PASSWORD", "__ADMIN_EMAIL");
                    $replacewith     = array($postdata['first_name'] . " " . $postdata['last_name'], $postdata['user_type'], $postdata['email'], $postdata['password'], ADMIN_EMAIL);
                    $data['content'] = str_replace($replaceto, $replacewith, $message);
                    $subject         = $this->lang->line('Registration_mail_subject');
                    $body            = $this->load->view('email/simple_content', $data, true);
                     if (strtolower($postdata['user_type']) != 'client') {
                        send_email($postdata['email'], $subject, $body);
                     }
                    $response['status'] = true;
                    $response['msg']    = $postdata['user_type'] . ' added successfully.';
                } else {
                    $response['status'] = false;
                    $response['msg']    = 'data could not be inserted.';
                }
            }
        } else {
            $response['status'] = false;
            $response['msg']    = 'Please send data first';
        }
        exit(json_encode($response));
    }

    //Edit User Function
    public function edit()
    {
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            $postdata             = $_POST;
            $postdata['password'] = randomPassword();
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('first_name', 'User first name', 'required');
            $this->form_validation->set_rules('last_name', 'User last name', 'required');
            $this->form_validation->set_rules('company', 'Company', 'required');
            $this->form_validation->set_rules('email', 'email', 'valid_email|required');
            $this->form_validation->set_rules('secondary_email', 'Secondary Email', 'valid_email');
            $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'required');
            $this->form_validation->set_rules('suburb', 'Suburb', 'required');
            $this->form_validation->set_rules('post_code', 'Postcode', 'required');
            $this->form_validation->set_rules('state', 'State', 'required');
            $this->form_validation->set_rules('user_type', 'User type', 'required');
            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            } else {
                if ($this->common_model->check_data('users', array('user_id!=' => $postdata['user_id'], 'email' => $postdata['email']))) {
                    $userInfo = $this->common_model->get_data('users',array('user_id' => $postdata['user_id'], 'email' => $postdata['email']),'single');
                    $InsertData = array(
                        'user_type'       => ucfirst($postdata['user_type']),
                        'first_name'      => $postdata['first_name'],
                        'last_name'       => $postdata['last_name'],
                        'company'         => $postdata['company'],
                        'email'           => $postdata['email'],
                        'secondary_email' => @$postdata['secondary_email'],
                        'mobile_phone'    => $postdata['mobile_phone'],
                        'landline_phone'  => @$postdata['landline_phone'],
                        'address_1'       => @$postdata['address_1'],
                        'address_2'       => @$postdata['address_2'],
                        'suburb'          => $postdata['suburb'],
                        'post_code'       => $postdata['post_code'],
                        'state'           => $postdata['state'],
                        'notes'           => @$postdata['notes'],
                        'status'          => '0',
                        'created'         => date("Y-m-d H:i:s"),
                        'modified'        => date("Y-m-d H:i:s"),
                    );

                    if (strtolower($postdata['user_type']) == 'scanner') {
                        $this->load->library('google');
                            $res2                       = $this->google->setHook($userInfo['calendar_id']);
                            if ($res2['status']) {
                                $InsertData['channel_id'] = $res2['data'];
                            }
                    }
                    if ($this->common_model->update_data('users', $InsertData, array('user_id' => $postdata['user_id']))) {
                        $response['status'] = true;
                        $response['msg']    = $postdata['user_type'] . ' updated successfully.';
                    } else {
                        $response['status'] = false;
                        $response['msg']    = 'data could not be updated.';
                    }
                } else {
                    $response['status'] = false;
                    $response['msg']    = "Email should be unique";
                }
            }
        } else {
            $response['status'] = false;
            $response['msg']    = 'Please send data first';
        }
        exit(json_encode($response));
    }

    //Edit Profile Function
    public function edit_profile()
    {
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            $postdata = $_POST;
            // $postdata['password'] = randomPassword();
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('first_name', 'User first name', 'required');
            $this->form_validation->set_rules('last_name', 'User last name', 'required');
            $this->form_validation->set_rules('company', 'Company', 'required');
            $this->form_validation->set_rules('email', 'email', 'valid_email|required');
            $this->form_validation->set_rules('secondary_email', 'Secondary Email', 'valid_email');
            $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'required');
            $this->form_validation->set_rules('suburb', 'Suburb', 'required');
            $this->form_validation->set_rules('post_code', 'Postcode', 'required');
            $this->form_validation->set_rules('state', 'State', 'required');
            $this->form_validation->set_rules('user_type', 'User type', 'required');
            if (!empty($postdata['npassword'])) {

                $this->form_validation->set_rules('npassword', 'Password', 'required|matches[rpassword]');
                $this->form_validation->set_rules('cpassword', 'Current Password', 'required');
                $this->form_validation->set_rules('rpassword', 'Repeat password', 'required|matches[npassword]');
            }
            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            } else {
                if (!empty($postdata['npassword'])) {
                    if ($this->common_model->check_data('users', array('user_id' => $postdata['user_id'], 'password' => md5($postdata['cpassword'])))) {
                        $response['status'] = false;
                        $response['msg']    = "Current password not matched";
                        exit(json_encode($response));
                    }
                }
                // if($this->common_model->check_data('users',array('user_id'=>$postdata['user_id'],'password'=>md5($postdata['cpassword'])))==false)
                // {
                if ($this->common_model->check_data('users', array('user_id!=' => $postdata['user_id'], 'email' => $postdata['email']))) {
                    $InsertData = array(
                        'first_name'      => $postdata['first_name'],
                        'last_name'       => $postdata['last_name'],
                        'company'         => $postdata['company'],
                        'email'           => $postdata['email'],
                        'secondary_email' => @$postdata['secondary_email'],
                        'mobile_phone'    => $postdata['mobile_phone'],
                        'landline_phone'  => @$postdata['landline_phone'],
                        'address_1'       => @$postdata['address_1'],
                        'address_2'       => @$postdata['address_2'],
                        'suburb'          => $postdata['suburb'],
                        'post_code'       => $postdata['post_code'],
                        'state'           => $postdata['state'],
                        'notes'           => @$postdata['notes'],
                        'status'          => '0',
                        'created'         => date("Y-m-d H:i:s"),
                        'modified'        => date("Y-m-d H:i:s"),
                    );
                    if (!empty($postdata['npassword'])) {
                        $InsertData['password'] = md5($postdata['npassword']);
                    }
                    if ($this->common_model->update_data('users', $InsertData, array('user_id' => $postdata['user_id']))) {
                        $response['status'] = true;
                        $response['msg']    = 'Profile updated successfully.';
                        $response['data']  = $this->common_model->get_data('users',array('user_id' => $postdata['user_id']),'single');
                    } else {
                        $response['status'] = false;
                        $response['msg']    = 'data could not be updated.';
                    }
                } else {
                    $response['status'] = true;
                    $response['msg']    = "Email should be unique";
                }
                // }else{
                //         $response['status'] = false;
                //         $response['msg']    = "Current password not matched";
                // }
            }
        } else {
            $response['status'] = false;
            $response['msg']    = 'Please send data first';
        }
        exit(json_encode($response));
    }

    public function get_all_users($type = null)
    {
        $notification['status'] = false;
        $notification['msg']    = 'Something went wrong.';
        if (isset($type) && !empty($type)) {

            $notification['data']   = $this->common_model->get_data('users', array('user_type' => ucfirst($type)),'','user_id','','','desc');
            $notification['status'] = true;
        }
        exit(json_encode($notification));
    }

    public function user_action()
    {

        $notification['status'] = false;
        $notification['msg']    = $this->lang->line('common_error');

        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);

        if (isset($_POST) && !empty($_POST)) {

            $input  = $this->input->post();
            $status = $input['status'];

            if (!empty($input['data'])) {

                $input = $input['data'];

                $update_data = array(
                    'status' => $status,
                );
                if ($this->common_model->update_data('housekeeping_user', $update_data, array('id' => $input['id']))) {
                    $notification['status'] = true;

                    if ($status == '1') {
                        $data['message'] = $this->lang->line('account_activate_mail_body');
                        $replaceto       = array("__FULL_NAME", "__USERNAME", "__ADMIN_EMAIL");
                        $replacewith     = array($input['first_name'] . " " . $input['last_name'], $input['email'], FROM_EMAIL);
                        $data['content'] = str_replace($replaceto, $replacewith, $data['message']);
                        $data['subject'] = $this->lang->line('account_activate_mail_subject');
                        $view_content    = $this->load->view('email/simple_content', $data, true);
                        send_email($input['email'], $data['subject'], $view_content);
                    } else {
                        $data['message'] = $this->lang->line('account_deactivate_mail_body');
                        $replaceto       = array("__FULL_NAME", "__USERNAME", "__ADMIN_EMAIL");
                        $replacewith     = array($input['first_name'] . " " . $input['last_name'], $input['email'], FROM_EMAIL);
                        $data['content'] = str_replace($replaceto, $replacewith, $data['message']);
                        $data['subject'] = $this->lang->line('account_deactivate_mail_subject');
                        $view_content    = $this->load->view('email/simple_content', $data, true);
                        send_email($input['email'], $data['subject'], $view_content);
                    }

                    if ($status == '1') {
                        $notification['msg'] = $this->lang->line('account_activate');
                    } else {
                        $notification['msg'] = $this->lang->line('account_suspend');
                    }

                }
            }

        }
        exit(json_encode($notification));
    }

    //Get All Users By Role
    public function getusers($type = '')
    {
        if (!empty($type)) {
            $notification['status'] = true;
            $notification['data']   = $this->common_model->get_data('users', array('user_type' => ucfirst($type), 'status' => '0'));
        } else {
            $notification['status'] = false;
            $notification['msg']    = "No " . $type . " yet";
        }
        exit(json_encode($notification));
    }

    //Get All Users By Role
    public function getuser($ID = '')
    {
        if (!empty($ID)) {
            $notification['status'] = true;
            $notification['data']   = $this->common_model->get_data('users', array('user_id' => $ID), 'single');
        } else {
            $notification['status'] = false;
            $notification['msg']    = "No " . $type . " yet";
        }
        exit(json_encode($notification));
    }

    //Get All Users By Role
    public function deactivate()
    {
        $notification['status'] = false;
        $notification['msg']    = $this->lang->line('common_error');
        $res                    = file_get_contents("php://input");
        $_POST                  = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            if ($this->common_model->update_data('users', array('status' => '1'), array('user_id' => $_POST['user_id']))) {
                // echo $this->db->last_query();
                $notification['status'] = true;
                $notification['msg']    = $_POST['user_type'] . " Deactivated successfully";
            } else {
                $notification['status'] = false;
                $notification['msg']    = "Unable to deactivate user try again";
            }
            // $notification['data'] =
        } else {
            $notification['status'] = false;
            $notification['msg']    = "No " . $type . " yet";
        }
        exit(json_encode($notification));
    }

    //Get All Users By Role
    public function activate()
    {
        $notification['status'] = false;
        $notification['msg']    = $this->lang->line('common_error');
        $res                    = file_get_contents("php://input");
        $_POST                  = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            if ($this->common_model->update_data('users', array('status' => '0'), array('user_id' => $_POST['user_id']))) {
                // echo $this->db->last_query();
                $notification['status'] = true;
                $notification['msg']    = $_POST['user_type'] . " activated successfully";
            } else {
                $notification['status'] = false;
                $notification['msg']    = "Unable to activate user try again";
            }
            // $notification['data'] =
        } else {
            $notification['status'] = false;
            $notification['msg']    = "No " . $type . " yet";
        }
        exit(json_encode($notification));
    }

    //Mutilple USers BY Roles
    public function multiple_users()
    {
        $notification['status'] = false;
        $notification['msg']    = $this->lang->line('common_error');
        $res                    = file_get_contents("php://input");
        $_POST                  = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            foreach ($_POST['users'] as $user_type) {
                $notification['status']   = true;
                $notification[$user_type] = $this->common_model->get_data('users', array('user_type' => $user_type));
            }
        } else {
            $notification['status'] = false;
            $notification['msg']    = "No " . $type . " yet";
        }
        exit(json_encode($notification));
    }

    //Mail html
    public function html()
    {
        // $this->load->view('email/header');
        $this->load->view('email/simple_content');
        // $this->load->view('email/footer');
    }

    public function all_users_count()
    {
        $data['status'] = true;
        $data['admins'] = $this->db->where('user_type','Admin')->get('users')->num_rows();
        $data['clients'] = $this->db->where('user_type','Client')->get('users')->num_rows();
        $data['scanners'] = $this->db->where('user_type','Scanner')->get('users')->num_rows();
        $data['bookings'] = $this->db->get('joinings')->num_rows();
        exit(json_encode($data));
    }
}
