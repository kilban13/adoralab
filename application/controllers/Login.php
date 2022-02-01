<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_info();
		 if(get_domain()!=get_dbdomain()){echo appinfo_domain_msg();exit();}
         if( appinfo()!=get_dbmid() || appinfo()!="74be16979710d4c4e7c6647856088456"){echo appinfo_mid_msg();exit();}
		is_sql_full_group_by_enabled();
	}
	public function index()
	{
		// new code for check first time license table set up or not
		$this->load->model('login_model');//Model
		// die($this->login_model->check_license()) ;
		if($this->login_model->check_license() == 'license_table_not_set_up'){
			redirect(base_url().'login/license_page','refresh');
		}
		if($this->login_model->check_license() == 'license_expired'){
			redirect(base_url().'login/license_page_update','refresh');

		}

		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		$data = $this->data;
		$this->load->view('login',$data);
	}
	
	// new code for check first time license table set up or not
	public function license_page(){

		$this->load->model('login_model');//Model
		if($this->login_model->check_license() == 'license_table_not_set_up'){

			if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
			$data = $this->data;
			$this->load->view('license_page',$data);
		}

		if($this->login_model->check_license() == 'license_expired'){

			if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
			$data = $this->data;
			$this->load->view('license_page_update',$data);
		}
	
	}
	public function license_page_update(){

		$this->load->model('login_model');//Model
		if($this->login_model->check_license() == 'license_table_not_set_up'){

			if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
			$data = $this->data;

			$this->load->view('license_page',$data);
		}

		if($this->login_model->check_license() == 'license_expired'){

			if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
			$data = $this->data;
			$p = $this->db->query('select store_name from db_lincense limit 1');
			print_r($p);
			die();
			$this->load->view('license_page_update',$data);
		}
	
	}
	public function license_submit(){


		$this->load->model('login_model');//Model
		$this->form_validation->set_rules('store_name', 'Store Name', 'required|max_length[100]');
		$this->form_validation->set_rules('license_duration', 'Duration', 'required|is_natural_no_zero');

		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Please Check the input!');
			redirect(base_url().'login/license_page');
		}else{

			$store_name=$this->input->post('store_name');
			$license_duration="+".$this->input->post('license_duration')." months";
			$effectiveDate = date('Y-m-d h:m:s');
			$expired_at = date('Y-m-d h:m:s', strtotime($license_duration, strtotime($effectiveDate))); // striing value like "+3 months"
			$description ="License start form ".date('Y-m-d')." to ".$expired_at;

			if($this->login_model->create_license($store_name,$license_duration,$description,$expired_at)){
				$expired_done= "<h3 class='text-success'>Success! Your license will be expired at <strong>".$expired_at."<strong><h3>";
				$this->session->set_flashdata('license_done', $expired_done);
				redirect(base_url().'login','refresh');
			}else{
				die('license error occured');
			}
		}

	}
		// //////////

	public function verify()
	{
		$this->form_validation->set_rules('username','Username','required');
		$this->form_validation->set_rules('pass','Password','required');
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Please enter username & password!');
			redirect('login');
		}
		else{

			$username=$this->input->post('username');
			$password=$this->input->post('pass');

			$this->load->model('login_model');//Model
			if($this->login_model->verify_credentials($username,$password)){//Model->Method
				redirect(base_url().'dashboard');
			}
			else{
				$this->session->set_flashdata('failed', 'Invalid username & password.');
				redirect('login');
			}			
		}
	}
	public function forgot_password(){
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		$data = $this->data;
		$this->load->view('forgot-password',$data);
	}
	/*public function send_otp(){		
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Invalid Email!');
			redirect(base_url().'login/forgot_password');
		}
		else{
			$email=$this->input->post('email');
			$this->load->model('login_model');//Model
			if($this->login_model->verify_email_send_otp($email)){//Model->Method
				redirect(base_url().'login/otp');
			}
			else{
				$this->session->set_flashdata('failed', 'Invalid Email!!');
				redirect(base_url().'login/forgot_password');
			}			
		}
	}*/
	public function send_otp(){		
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Invalid Email!');
			redirect(base_url().'login/forgot_password');
		}
		else{
			$email=$this->input->post('email');
			$this->load->model('login_model');//Model
			$response = $this->login_model->verify_email_send_otp($email);

			if($response==true){//Model->Method
				redirect(base_url().'login/otp');
			}
			else{
				//$this->session->set_flashdata('failed', 'Invalid Email!!');
				redirect(base_url().'login/forgot_password');
			}			
		}
	}
	public function otp(){
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		$data = $this->data;
		$this->load->view('otp',$data);
	}
	public function verify_otp(){
		$this->form_validation->set_rules('otp', 'OTP', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required');
		
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Invalid OTP!');
			redirect(base_url().'login/otp');
		}
		else{
			$otp=$this->input->post('otp');
			$email=$this->input->post('email');
			
			if($this->session->userdata('email')==$email && $this->session->userdata('otp')==$otp){
				$data=$this->data;
				$data['email']=$email;
				$data['otp']=$otp;
				
				$this->load->view("change-login-password",$data);
			}
			else{
				$this->session->set_flashdata('failed', 'Invalid OTP!!');
				redirect(base_url().'login/otp');
			}			
		}
	}
	public function change_password(){

		$this->form_validation->set_rules('otp', 'OTP', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('cpassword', 'Confirm Password', 'required');
		
		//print_r($_POST);exit;
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Please Enter Correct Passwords!');
			redirect(base_url().'login/verify_otp');
		}
		else{
			$otp=$this->input->post('otp');
			$email=$this->input->post('email');
			$password=$this->input->post('password');
			$cpassword=$this->input->post('cpassword');
			$this->load->model('login_model');//Model
			if($password==$cpassword && $this->session->userdata('email')==$email && $this->session->userdata('otp')==$otp){
				if($this->login_model->change_password($password,$email)){//Model->Method
					$data = $this->data;
					$array_items = array('email','otp');
					$this->session->unset_userdata($array_items);
					$this->session->set_flashdata('success', 'Password Changed Successfully!');
					redirect(base_url().'login','refresh');
				}
				else{
					$this->session->set_flashdata('failed', 'Please Enter Correct Passwords!');
					redirect(base_url().'login/verify_otp');
				}			
			}
		}

	}
	

}
