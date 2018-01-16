<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {
	
	public function __construct()
    {	
    	header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: X-Requested-With');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        parent::__construct();
    }

	public function index() 
	{
		$res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST))
        {
			$postdata = $_POST;
			$this->form_validation->set_error_delimiters('','');
			$this->form_validation->set_rules('email','email','required|callback_check_email');
            $this->form_validation->set_rules('password', 'password', 'required|callback_check_password');
            if ($this->form_validation->run() == false)
            {
				$response['status'] = false;
                $response['msg']    = validation_errors();
            }
			else
			{
            	$where = array('email'=>$postdata['email'],'password'=>md5($postdata['password']));
                $response['data'] = $this->common_model->get_data('users', $where, 'single');
                if($response['data']['user_type']=='Client'){
                	$response['msg'] = "Email or password not match.";
	                $response['status'] = false;
                }
                else {
	                $response['msg'] = $this->lang->line('login_success');
	                $response['status'] = true;
                }
            }
        }
		else {
			$response['status'] = false;
			$response['msg']    = 'Please send all required fields.';
		}
        exit(json_encode($response));
	}
		
	public function forget()
    {
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST))
        {
            $input = $this->input->post();
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('email', 'Email', 'required|callback_check_email');
            if ($this->form_validation->run() == false)
            {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            }
            else
            {
                $where = array('email'=>$input['email']);
                $response['data'] = $this->common_model->get_data('users', $where, 'single');
                if(!empty($response['data'])){
	                $password                         = $Password                         = randomPassword();
	                $update_password['password'] = md5($password);
	                // $update_password['Modified_Date'] = date('Y-m-d H:i:s');
	                $this->common_model->update_data('users', $update_password, $where);

	                $data['subject'] = $this->lang->line('forgot_password_mail_subject');
	                $data['message'] = $this->lang->line('forgot_password_mail_body');
	                $replaceto       = array("email__", "password__");
	                $replacewith     = array($response['data']['email'], $password);
	                $data['content'] = str_replace($replaceto, $replacewith, $data['message']);
	                $view_content             = $this->load->view('email/simple_content', $data, true);
	                send_email($response['data']['email'], $data['subject'], $view_content);
	                // $response['data']   = $$response['data'];
	                $response['status'] = true;
	                $response['msg']    = "Your new password has been sent on registered mail '".$response['data']['email']."'";
                } else {
                	$response['msg'] = "Email not match.";
	                $response['status'] = false;
                }
            }
        }
        else
        {
            $response['status'] = false;
            $response['msg']    = $this->lang->line('common_error');
        }
        exit(json_encode($response));
    }


	public function check_email($email)
	{
		$where = array('email' => $email);
		$UserInfo = $this->common_model->exist_data('users',$where);
		if(isset($UserInfo) && !empty($UserInfo)) {
			if($UserInfo['status']==='1') {
				$this->form_validation->set_message('check_email','Your account has been deactivated.');
				return false;
			}
			else {
				return true;
			}
		}
        else
        {
			$this->form_validation->set_message('check_email','Email does not exist.');
            return false;
        }
	}
	
	public function check_password($password)
    {
        $email    = $_POST['email'];
        $password = md5($password);
        $where = array('email'=>$email,'password'=>$password);
		$UserInfo = $this->common_model->exist_data('users',$where);
        if(isset($UserInfo) && !empty($UserInfo))
		{
			return true;
        }
        else
        {
            $this->form_validation->set_message('check_password','Password does not match.');
            return false;
        }
    }
}