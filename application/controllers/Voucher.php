<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('voucher_model','voucher');
	}

	public function index()
	{
		$this->permission_check('gv_view');
		$data=$this->data;
		$data['page_title']="Gift/Sample Lists";
		$this->load->view('voucher-list',$data);
	}
	public function add()
	{	
		$this->permission_check('gv_add');
		$data=$this->data;
		$data['page_title']="Gift/Sample add";
		$this->load->view('voucher',$data);
		
	}
	

	public function save_and_update(){
		$this->form_validation->set_rules('customer_id', 'Customer Name', 'trim|required');
		$this->form_validation->set_rules('sales_date', 'Date ', 'trim|required');
		if ($this->form_validation->run() == TRUE) {
			
			$result = $this->voucher->verify_save_and_update();
	    	echo $result;				

		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	
	
	public function update($id){
		$this->permission_check('gv_edit');
		$data=$this->data;
		$data=array_merge($data,array('voucher_id'=>$id));
		$data['page_title']="Gift/Voucher Lists";
		$this->load->view('voucher', $data);
	}

	
	public function ajax_list(){

		$list = $this->voucher->get_datatables();
		
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $voucher) {
			$no++;
			$row = array();

			$row[] = $voucher->voucher_code;
			$row[] = $voucher->notes;
			$row[] = $voucher->customer_name;
			//$voucher->total;
			$are='';
			$arr=$this->voucher->getItemByvoucher($voucher->id);
			if(!empty($arr)){
				foreach($arr as $val){
					$are.='<a>'.$val['text'].'</a> = '.' '.$val['quantity'].'<br>';
				}
			}
			$row[]="Total=".$voucher->total."<br> ".$are. "";
			$row[] = show_date($voucher->voucher_date);
			$row[] = ucfirst($voucher->created_by);

					

					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';
											// if($this->permissions('sales_view'))
											// $str2.='<li>
											// 	<a title="View Invoice" href="voucher/invoice/'.$sales->id.'" >
											// 		<i class="fa fa-fw fa-eye text-blue"></i>View sales
											// 	</a>
											// </li>';

											// if($this->permissions('sales_edit'))
											$str2.='<li>
												<a title="Update Record ?" href="voucher/update/'.$voucher->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

									// 		$str2.='<li>
									// 			<a style="cursor:pointer" title="Delete Record ?" onclick="delete_sales(\''.$voucher->id.'\')">
									// 				<i class="fa fa-fw fa-trash text-red"></i>Delete
									// 			</a>
									// 		</li>
											
									// 	</ul>
									// </div>';	
											// if($this->permissions('sales_delete'))
											$str2.='
											
										</ul>
									</div>';			

			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->voucher->count_all(),
						"recordsFiltered" => $this->voucher->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	//Table ajax code
	public function search_item(){
		$q=$this->input->get('q');
		$result=$this->voucher->search_item($q);
		echo $result;
	}
	public function find_item_details(){
		$id=$this->input->post('id');
		
		$result=$this->voucher->find_item_details($id);
		echo $result;
	}
	public function return_sales_list2($sales_id){
		echo $this->voucher->return_sales_list2($sales_id);
	}

}
