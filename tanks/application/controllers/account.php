<?php 

class Account extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    }
        
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	

    		$protected = array('updatePasswordForm','updatePassword','index','logout');
    		
    		if (in_array($method,$protected) && !isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
 	    	
	    	return call_user_func_array(array($this, $method), $params);
    }
          
    
    function loginForm() {
    		$this->load->view('account/loginForm');
    }
    
    function login() {
    		$this->load->library('form_validation');
    		$this->form_validation->set_rules('username', 'Username', 'required');
    		$this->form_validation->set_rules('password', 'Password', 'required');

    		if ($this->form_validation->run() == FALSE)
    		{
    			$this->load->view('account/loginForm');
    		}
    		else
    		{
    			$login = $this->input->post('username');
    			$clearPassword = $this->input->post('password');
    			 
    			$this->load->model('user_model');
    		
    			$user = $this->user_model->get($login);
    		if(isset($_SESSION['loginDate']) && $this->getSecDelay($_SESSION['loginDate']) > 0) {
    	        $data['errorMsg'] = "You must wait " . $this->getSecDelay($_SESSION['loginDate']) . " seconds before attempting to log in again";
            	//$data['errorMsg'] = 'some msg';
            	$this->load->view('account/loginForm', $data);
            
          	}else if (isset($user) && $user->comparePassword($clearPassword)) {     			
          		$_SESSION['user'] = $user;
    				$data['user']=$user;
    				
    				$this->user_model->updateStatus($user->id, User::AVAILABLE);
    				
    				redirect('arcade/index', 'refresh'); //redirect to the main application page
    				
    		
            } else {
            
            if(!isset($_SESSION['attemptedLogins'])){
              $_SESSION['attemptedLogins'] = 1;
            }  else {
              $_SESSION['attemptedLogins'] = $_SESSION['attemptedLogins'] + 1;
            }

            $_SESSION['loginDate'] = getdate();
          	$data['errorMsg']='Incorrect username or password! ';
            $this->load->view('account/loginForm',$data);
         } 
    		}
    }

    function logout() {
		$user = $_SESSION['user'];
    		$this->load->model('user_model');
	    	$this->user_model->updateStatus($user->id, User::OFFLINE);
    		session_destroy();
    		redirect('account/index', 'refresh'); //Then we redirect to the index page again
    }

    function newForm() {
	    	$this->load->view('account/newForm');
    }
    
    function createNew() {
    		
    		$this->load->library('form_validation');
    	    $this->form_validation->set_rules('username', 'Username', 'required|is_unique[user.login]');
	    	$this->form_validation->set_rules('password', 'Password', 'required');
	    	$this->form_validation->set_rules('first', 'First', "required");
	    	$this->form_validation->set_rules('last', 'last', "required");
	    	$this->form_validation->set_rules('email', 'Email', "required|is_unique[user.email]");
	    	$this->form_validation->set_rules('captcha_code', 'Captcha', 'required|callback_checkCaptcha');
	    	
	    
	    	if ($this->form_validation->run() == FALSE)
	    	{
	    		$this->load->view('account/newForm');
	    	}
	    	else  
	    	{
	    		$user = new User();
	    		 
	    		$user->login = $this->input->post('username');
	    		$user->first = $this->input->post('first');
	    		$user->last = $this->input->post('last');
	    		$clearPassword = $this->input->post('password');
	    		$user->encryptPassword($clearPassword);
	    		$user->email = $this->input->post('email');
	    		
	    		$this->load->model('user_model');
	    		 
	    		
	    		$this->user_model->insert($user);
	    		
	    		$this->load->view('account/loginForm');
	    	}
    }

    
    function updatePasswordForm() {
	    	$this->load->view('account/updatePasswordForm');
    }
    
    function updatePassword() {
	    	$this->load->library('form_validation');
	    	$this->form_validation->set_rules('oldPassword', 'Old Password', 'required');
	    	$this->form_validation->set_rules('newPassword', 'New Password', 'required');
	    	 
	    	 
	    	if ($this->form_validation->run() == FALSE)
	    	{
	    		$this->load->view('account/updatePasswordForm');
	    	}
	    	else
	    	{
	    		$user = $_SESSION['user'];
	    		
	    		$oldPassword = $this->input->post('oldPassword');
	    		$newPassword = $this->input->post('newPassword');
	    		 
	    		if ($user->comparePassword($oldPassword)) {
	    			$user->encryptPassword($newPassword);
	    			$this->load->model('user_model');
	    			$this->user_model->updatePassword($user);
	    			$data['user']=$user;
	    			$this->load->view('mainPage',$data);
	    		}
	    		else {
	    			$data['errorMsg']="Incorrect password!";
	    			$this->load->view('account/updatePasswordForm',$data);
	    		}
	    	}
    }
    
    function recoverPasswordForm() {
    		$this->load->view('account/recoverPasswordForm');
    }
    
    function recoverPassword() {
	    	$this->load->library('form_validation');
	    	$this->form_validation->set_rules('email', 'email', 'required');
	    	
	    	if ($this->form_validation->run() == FALSE)
	    	{
	    		$this->load->view('account/recoverPasswordForm');
	    	}
	    	else
	    	{ 
	    		$email = $this->input->post('email');
	    		$this->load->model('user_model');
	    		$user = $this->user_model->getFromEmail($email);

	    		if (isset($user)) {
	    			$newPassword = $user->initPassword();
	    			$this->user_model->updatePassword($user);
	    			
	    			$this->load->library('email');
	    		
	    			$config['protocol']    = 'smtp';
	    			$config['smtp_host']    = 'ssl://smtp.gmail.com';
	    			$config['smtp_port']    = '465';
	    			$config['smtp_timeout'] = '7';
	    			$config['smtp_user']    = 'google_username@gmail.com';
	    			$config['smtp_pass']    = 'google_password';
	    			$config['charset']    = 'utf-8';
	    			$config['newline']    = "\r\n";
	    			$config['mailtype'] = 'text'; // or html
	    			$config['validation'] = TRUE; // bool whether to validate email or not
	    			
		    	  	$this->email->initialize($config);
	    			
	    			$this->email->from('csc309Login@cs.toronto.edu', 'Login App');
	    			$this->email->to($user->email);
	    			
	    			$this->email->subject('Password recovery');
	    			$this->email->message("Your new password is $newPassword");
	    			
	    			$result = $this->email->send();
	    			
	    			//$data['errorMsg'] = $this->email->print_debugger();	
	    			
	    			//$this->load->view('emailPage',$data);
	    			$this->load->view('account/emailPage');
	    			
	    		}
	    		else {
	    			$data['errorMsg']="No record exists for this email!";
	    			$this->load->view('account/recoverPasswordForm',$data);
	    		}
	    	}
    }

    function checkCaptcha($str){
    	$this->load->library('securimage');
    	$securimage = new Securimage();
    	
    	if ($securimage->check($str) == false) {
    		$this->form_validation->set_message('checkCaptcha', 'Captchas do not match');
    		return FALSE;
    	}
    	return TRUE;
    }
    
        /**************************HELPER FUNCTIONS*************************************/
        function getSecDelay($loginDate){
    	      $currentDate = getdate();
    	      if($loginDate['mday'] < $currentDate['mday']){
    		        return 0;
    		      } else{
    			        $loginDateSec = $loginDate['seconds'] + $loginDate['minutes']*60 + $loginDate['hours']*60*60;
    			        $currentDateSec = $currentDate['seconds'] + $currentDate['minutes']*60 + $currentDate['hours']*60*60;
    			        return ($loginDateSec + pow(2, $_SESSION['attemptedLogins'])) - $currentDateSec;
    			      }
    	}
 }

