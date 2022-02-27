<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher_model extends CI_Model {

	//Datatable start
	var $table = 'tbl_gift_without_sale as a';
	var $column_order = array( 'a.voucher_code','a.notes','a.customer_id','a.id','a.created_at','a.reference_no','b.customer_name','a.voucher_date','a.created_by'); //set column field database for datatable orderable
	var $column_search = array('a.voucher_code','a.notes','a.customer_id','a.id','a.created_at','a.reference_no','b.customer_name','a.voucher_date','a.created_by'); //set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order  

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
	}

	public function getItemByvoucher($id){
			$json_array=array();
        $query1="select db_items.id,db_items.item_name,tbl_gift_without_sale_items.quantity from db_items,tbl_gift_without_sale_items where db_items.id = tbl_gift_without_sale_items.item_id and tbl_gift_without_sale_items.voucher_id='$id'";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array[]=['id'=>(int)$value->id, 'text'=>$value->item_name,'quantity'=>$value->quantity];
            }
        }
        return ($json_array);
	}
	private function _get_datatables_query()
	{
		
		$this->db->select($this->column_order);
		$this->db->select("COALESCE(SUM(c.quantity),0)  as total");
		$this->db->from($this->table);
		$this->db->join('db_customers b', 'b.id=a.customer_id', 'left');
		$this->db->join('tbl_gift_without_sale_items c', 'a.id=c.voucher_id', 'left');
		$this->db->group_by('c.voucher_id');
		// $this->db->from('tbl_gift_without_sale_items as c');
		// $this->db->select("COALESCE(SUM(c.quantity),0)  as total");
		//$this->db->from('db_warehouse as c');
		// $this->db->where('b.id=a.customer_id');
		// $this->db->where('a.id=c.voucher_id');
		//$this->db->where('c.id=a.warehouse_id');


         // ->join('user_bookings', 'user_bookings.book_listing = user_listings.list_reference', 'left')
         // ->join('(SELECT * FROM user_images WHERE user_images.img_key IN(SELECT MIN(user_images.img_key) FROM user_images GROUP BY user_images.img_listing)) AS image',
         //                                    'image.img_listing = user_listings.list_reference')
         // ->join('user_states', 'user_states.state_id = user_listings.list_state')
         // ->group_by('user_listings.list_reference')
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				

				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.

					$this->db->like($item, $_POST['search']['value']);

				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				


				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}
	//Datatable end

	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}

	//Save Sales
	public function verify_save_and_update(){
		//Filtering XSS and html escape from user inputs 
		extract($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));
		// echo "<pre>";print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		
		$this->db->trans_begin();
		$voucher_date=date('Y-m-d',strtotime($sales_date));

	    if($command=='save'){//Create voucher code unique if first time entry

			$this->db->query("ALTER TABLE tbl_gift_without_sale AUTO_INCREMENT = 1");
			$q4=$this->db->query("select coalesce(max(id),0)+1 as maxid from tbl_gift_without_sale");
			$maxid=$q4->row()->maxid;
			$voucher_code="GFT".str_pad($maxid, 4, '0', STR_PAD_LEFT);

		    $voucher_entry = array(
		    				'voucher_code' 				=> $voucher_code, 
		    				'reference_no' 				=> $reference_no, 
		    				'voucher_date' 				=> $voucher_date,
		    				'customer_id' 				=> $customer_id,
		    				'notes'						=>$notes,
		    				/*System Info*/
		    				'created_at' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME
		    			);

			$q1 = $this->db->insert('tbl_gift_without_sale', $voucher_entry);
			$voucher_id = $this->db->insert_id();
		}
		else if($command=='update'){	
			$voucher_entry = array(
		    				'reference_no' 				=> $reference_no, 
		    				'voucher_date' 				=> $voucher_date,
		    				'customer_id' 				=> $customer_id,
		    				'notes'						=>$notes,
		    				/*System Info*/
		    				'updated_time' 				=> $CUR_DATE." ".$CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME
		    			);
					
			$q1 = $this->db->where('id',$voucher_id)->update('tbl_gift_without_sale', $voucher_entry);

			$q11=$this->db->query("delete from tbl_gift_without_sale_items where voucher_id='$voucher_id'");
			if(!$q11){
				return "failed";
			}
		}
		//end

		// insertion -----------for gift-----------items //
		// insertion -----------for gift-----------items //
		// insertion -----------for gift-----------items //
		for($i=1;$i<=$rowcount2;$i++){
		
			if(isset($_REQUEST['tr2_item_id_'.$i]) && !empty($_REQUEST['tr2_item_id_'.$i])){

				$item_id 			=$this->xss_html_filter(trim($_REQUEST['tr2_item_id_'.$i]));
				$quantity			=$this->xss_html_filter(trim($_REQUEST['td2_data_'.$i.'_3']));
				
				$salesgift_entry = array(
		    				'voucher_id' 			=> $voucher_id, 
		    				'item_id' 			=> $item_id, 
		    				'quantity' 		=> $quantity
		    			);

				// print_r($salesgift_entry );
				// die('hello');
				 $this->db->insert('tbl_gift_without_sale_items', $salesgift_entry);
				//UPDATE itemS QUANTITY IN itemS TABLE
				$this->load->model('pos_model');				
				$q6=$this->pos_model->update_items_quantity($item_id);
				if(!$q6){
					return "failed";
				}
			}
		
		}//for end
		//  ----------------------- //
		//  ----------------------- //
		//  ----------------------- //


		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success!! Record Saved Successfully! '.$sms_info);
		return "success<<<###>>>$sales_id";
		
	}//verify_save_and_update() function end



	//Get sales_details
	public function get_details($id,$data){
		//Validate This sales already exist or not
		$query=$this->db->query("select * from db_sales where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['item_code']=$query->item_code;
			$data['item_name']=$query->item_name;
			$data['category_name']=$query->category_name;
			$data['hsn']=$query->hsn;
			$data['unit_name']=$query->unit_name;
			$data['available_qty']=$query->available_qty;
			$data['alert_qty']=$query->alert_qty;
			$data['sales_price']=$query->sales_price;
			$data['gst_percentage']=$query->gst_percentage;
			
			return $data;
		}
	}


	
	public function search_item($q){
		$json_array=array();
        $query1="select id,item_name from db_items where (upper(item_name) like upper('%$q%') or upper(item_code) like upper('%$q%'))";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array[]=['id'=>(int)$value->id, 'text'=>$value->item_name];
            }
        }
        return json_encode($json_array);
	}
	
	public function find_item_details($id){
		$json_array=array();
        $query1="select id,hsn,alert_qty,unit_name,sales_price,sales_price,gst_percentage,available_qty from db_items where id=$id";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array=['id'=>$value->id, 
        			 'hsn'=>$value->hsn,
        			 'alert_qty'=>$value->alert_qty,
        			 'unit_name'=>$value->unit_name,
        			 'sales_price'=>$value->sales_price,
        			 'sales_price'=>$value->sales_price,
        			 'gst_percentage'=>$value->gst_percentage,
        			 'available_qty'=>$value->available_qty,
        			];
            }
        }
        return json_encode($json_array);
	}

	


	/*v1.1*/
	/*public function inclusive($price='',$tax_per){
		return ($tax_per!=0) ? $price/(($tax_per/100)+1)/10 : $tax_per;
	}*/
	public function get_items_info($rowcount,$item_id){
		$q1=$this->db->select('*')->from('db_items')->where("id=$item_id")->get();
		$q3=$this->db->query("select * from db_tax where id=".$q1->row()->tax_id)->row();

		$stock	=	$q1->row()->stock;

		$qty = ($stock>1) ? 1 : $stock;
	      
		$info['item_id'] = $q1->row()->id;
		$info['item_name'] = $q1->row()->item_name;
		$info['description'] = '';//$q1->row()->description;
		$info['item_sales_qty'] = $qty;
		$info['item_available_qty'] = $stock;
		$info['item_sales_price'] = $q1->row()->sales_price;
		//$info['item_tax_id'] = $q1->row()->tax_id;
		$info['item_tax_name'] = $q3->tax_name;
		$info['item_price'] = $q1->row()->price;
		$info['item_tax_id'] = $q3->id;
		$info['item_tax'] = $q3->tax;
		$info['item_tax_type'] = $q1->row()->tax_type;
		$info['item_discount'] = 0;
		$info['item_discount_type'] = 'Percentage';
		$info['item_discount_input'] = 0;

		$info['item_tax_amt'] = ($q1->row()->tax_type=='Inclusive') ? calculate_inclusive($q1->row()->sales_price,$q3->tax) :calculate_exclusive($q1->row()->sales_price,$q3->tax);

		$this->return_row_with_data($rowcount,$info);
	}
	public function get_items_info2($rowcount,$item_id){
		$q1=$this->db->select('*')->from('db_items')->where("id=$item_id")->get();
		$q3=$this->db->query("select * from db_tax where id=".$q1->row()->tax_id)->row();

		$stock	=	$q1->row()->stock;

		$qty = ($stock>1) ? 1 : $stock;
	      
		$info['item_id'] = $q1->row()->id;
		$info['item_name'] = $q1->row()->item_name;
		$info['description'] = '';//$q1->row()->description;
		$info['item_sales_qty'] = $qty;
		$info['item_available_qty'] = $stock;
		$info['item_sales_price'] = $q1->row()->sales_price;
		//$info['item_tax_id'] = $q1->row()->tax_id;
		$info['item_tax_name'] = $q3->tax_name;
		$info['item_price'] = $q1->row()->price;
		$info['item_tax_id'] = $q3->id;
		$info['item_tax'] = $q3->tax;
		$info['item_tax_type'] = $q1->row()->tax_type;
		$info['item_discount'] = 0;
		$info['item_discount_type'] = 'Percentage';
		$info['item_discount_input'] = 0;

		$info['item_tax_amt'] = ($q1->row()->tax_type=='Inclusive') ? calculate_inclusive($q1->row()->sales_price,$q3->tax) :calculate_exclusive($q1->row()->sales_price,$q3->tax);

		$this->return_row_with_data2($rowcount,$info);
	}

	public function return_row_with_data2($rowcount,$info){
		extract($info);
		?>
            <tr id="row2_<?=$rowcount;?>" data-row='<?=$rowcount;?>'>
               <td id="td2_<?=$rowcount;?>_1">
                  <label class='form-control' style='height:auto;' data-toggle="tooltip" title='Item ?' >
                  <a id="td2_data_<?=$rowcount;?>_1" href="javascript:void(0)" title=""><?=$item_name;?></a> 
                  	</label>
               </td>

               <!-- description  -->
              <!--  <td id="td_<?=$rowcount;?>_17">
                  
                  <textarea rows="1" type="text" style="font-weight: bold; height=34px;" id="td_data_<?=$rowcount;?>_17" name="td_data_<?=$rowcount;?>_17" class="form-control no-padding"><?=$description;?></textarea>
               </td> -->

               <!-- Qty -->
               <td id="td2_<?=$rowcount;?>_3">
                  <div class="input-group ">
                     <span class="input-group-btn">
                     <button onclick="decrement_qty2(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
                     <input typ="text" value="<?=$item_sales_qty;?>" class="form-control no-padding text-center" onchange="item_qty_input2(<?=$rowcount;?>)" id="td2_data_<?=$rowcount;?>_3" name="td2_data_<?=$rowcount;?>_3">
                     <span class="input-group-btn">
                     <button onclick="increment_qty2(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
                  </div>
               </td>
               
               <!-- Unit Cost Without Tax-->
               <!-- <td id="td_<?=$rowcount;?>_10"><input type="text" name="td_data_<?=$rowcount;?>_10" id="td_data_<?=$rowcount;?>_10" class="form-control text-right no-padding only_currency text-center" onkeyup="calculate_tax(<?=$rowcount;?>)" value="<?=$item_sales_price;?>"></td> -->

               <!-- Discount -->
             <!--   <td id="td_<?=$rowcount;?>_8">
                  <input type="text" data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal(<?=$rowcount;?>)" name="td_data_<?=$rowcount;?>_8" id="td_data_<?=$rowcount;?>_8" class="pointer form-control text-right no-padding only_currency text-center item_discount" value="<?=$item_discount;?>" onkeyup="calculate_tax(<?=$rowcount;?>)" readonly>
               </td> -->

               <!-- Tax Amount -->
               <!-- <td id="td_<?=$rowcount;?>_11">
                  <input type="text" name="td_data_<?=$rowcount;?>_11" id="td_data_<?=$rowcount;?>_11" class="form-control text-right no-padding only_currency text-center" value="<?=$item_tax_amt;?>" readonly>
               </td> -->

               <!-- Tax Details -->
               <!-- <td id="td_<?=$rowcount;?>_12">
                  <label class='form-control ' style='width:100%;padding-left:0px;padding-right:0px;'>
                  <a id="td_data_<?=$rowcount;?>_12" href="javascript:void(0)" data-toggle="tooltip" title='Click to Change' onclick="show_sales_item_modal(<?=$rowcount;?>)" title=""><?=$item_tax_name ;?></a>
                  	</label>
               </td> -->

               <!-- Amount -->
               <!-- <td id="td_<?=$rowcount;?>_9"><input type="text" name="td_data_<?=$rowcount;?>_9" id="td_data_<?=$rowcount;?>_9" class="form-control text-right no-padding only_currency text-center" style="border-color: #f39c12;" readonly value="<?=$item_amount;?>"></td> -->
               
               <!-- ADD button -->
               <td id="td2_<?=$rowcount;?>_16" style="text-align: center;">
                  <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow2(<?=$rowcount;?>)" title="Delete ?" name="td2_data_<?=$rowcount;?>_16" id="td2_data_<?=$rowcount;?>_16"></a>
               </td>
               <!-- <input type="hidden" id="td_data_<?=$rowcount;?>_4" name="td_data_<?=$rowcount;?>_4" value="<?=$item_sales_price;?>"> -->
               <!-- <input type="hidden" id="td_data_<?=$rowcount;?>_15" name="td_data_<?=$rowcount;?>_15" value="<?=$item_tax_id;?>"> -->
               <!-- <input type="hidden" id="td_data_<?=$rowcount;?>_5" name="td_data_<?=$rowcount;?>_5" value="<?=$item_tax_amt;?>"> -->
               <input type="hidden" id="tr2_available_qty_<?=$rowcount;?>_13" value="<?=$item_available_qty;?>">
               <input type="hidden" id="tr2_item_id_<?=$rowcount;?>" name="tr2_item_id_<?=$rowcount;?>" value="<?=$item_id;?>">
               <!-- <input type="hidden" id="tr_tax_type_<?=$rowcount;?>" name="tr_tax_type_<?=$rowcount;?>" value="<?=$item_tax_type;?>"> -->
               <!-- <input type="hidden" id="tr_tax_id_<?=$rowcount;?>" name="tr_tax_id_<?=$rowcount;?>" value="<?=$item_tax_id;?>"> -->
               <!-- <input type="hidden" id="tr_tax_value_<?=$rowcount;?>" name="tr_tax_value_<?=$rowcount;?>" value="<?=$item_tax;?>"> -->
               <!-- <input type="hidden" id="description_<?=$rowcount;?>" name="description_<?=$rowcount;?>" value="<?=$description;?>"> -->

               <!-- <input type="hidden" id="item_discount_type_<?=$rowcount;?>" name="item_discount_type_<?=$rowcount;?>" value="<?=$item_discount_type;?>"> -->
               <!-- <input type="hidden" id="item_discount_input_<?=$rowcount;?>" name="item_discount_input_<?=$rowcount;?>" value="<?=$item_discount_input;?>"> -->
            </tr>
		<?php

	}
	public function return_sales_list2($sales_id){
		$q1=$this->db->select('*')->from('tbl_gift_without_sale_items')->where("voucher_id=$sales_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$q2=$this->db->query("select * from db_items where id=".$res1->item_id);
			
			$info['item_id'] = $res1->item_id;
			$info['description'] = $res1->description;
			$info['item_name'] = $q2->row()->item_name;
			//$info['description'] = $res1->description;
			$info['item_sales_qty'] = $res1->quantity;
			$info['item_available_qty'] = $q2->row()->stock+$info['item_sales_qty'];
			$info['item_price'] = $q2->row()->price;
			//$info['item_sales_price'] = $q2->row()->sales_price;
			//$info['item_tax_id'] = $res1->tax_id;
		
			
			$result = $this->return_row_with_data2($rowcount++,$info);
		}
		return $result;
	}
	
	/* For Purchase Items List Retrieve*/
	public function return_sales_list($sales_id){
		$q1=$this->db->select('*')->from('db_salesitems')->where("sales_id=$sales_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$q2=$this->db->query("select * from db_items where id=".$res1->item_id);
			$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();
			
			$info['item_id'] = $res1->item_id;
			$info['description'] = $res1->description;
			$info['item_name'] = $q2->row()->item_name;
			//$info['description'] = $res1->description;
			$info['item_sales_qty'] = $res1->sales_qty;
			$info['item_available_qty'] = $q2->row()->stock+$info['item_sales_qty'];
			$info['item_price'] = $q2->row()->price;
			//$info['item_sales_price'] = $q2->row()->sales_price;
			$info['item_sales_price'] = $res1->price_per_unit;
			//$info['item_tax_id'] = $res1->tax_id;
			$info['item_tax_name'] = $q3->tax_name;
			$info['item_tax_id'] = $q3->id;
			$info['item_tax'] = $q3->tax;
			$info['item_tax_type'] = $res1->tax_type;
			$info['item_tax_amt'] = $res1->tax_amt;
			$info['item_discount'] = $res1->discount_input;

			$info['item_discount_type'] = $res1->discount_type;
			$info['item_discount_input'] = $res1->discount_input;
			
			$result = $this->return_row_with_data($rowcount++,$info);
		}
		return $result;
	}

	public function return_row_with_data($rowcount,$info){
		extract($info);
		$item_amount = ($item_sales_price * $item_sales_qty) + $item_tax_amt;
		?>
            <tr id="row_<?=$rowcount;?>" data-row='<?=$rowcount;?>'>
               <td id="td_<?=$rowcount;?>_1">
                  <label class='form-control' style='height:auto;' data-toggle="tooltip" title='Edit ?' >
                  <a id="td_data_<?=$rowcount;?>_1" href="javascript:void(0)" onclick="show_sales_item_modal(<?=$rowcount;?>)" title=""><?=$item_name;?></a> 
                  		<i onclick="show_sales_item_modal(<?=$rowcount;?>)" class="fa fa-edit pointer"></i>
                  	</label>
               </td>

               <!-- description  -->
               <!-- <td id="td_<?=$rowcount;?>_17">
                  
                  <textarea rows="1" type="text" style="font-weight: bold; height=34px;" id="td_data_<?=$rowcount;?>_17" name="td_data_<?=$rowcount;?>_17" class="form-control no-padding"><?=$description;?></textarea>
               </td> -->

               <!-- Qty -->
               <td id="td_<?=$rowcount;?>_3">
                  <div class="input-group ">
                     <span class="input-group-btn">
                     <button onclick="decrement_qty(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
                     <input typ="text" value="<?=$item_sales_qty;?>" class="form-control no-padding text-center" onchange="item_qty_input(<?=$rowcount;?>)" id="td_data_<?=$rowcount;?>_3" name="td_data_<?=$rowcount;?>_3">
                     <span class="input-group-btn">
                     <button onclick="increment_qty(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
                  </div>
               </td>
               
               <!-- Unit Cost Without Tax-->
               <td id="td_<?=$rowcount;?>_10"><input type="text" name="td_data_<?=$rowcount;?>_10" id="td_data_<?=$rowcount;?>_10" class="form-control text-right no-padding only_currency text-center" onkeyup="calculate_tax(<?=$rowcount;?>)" value="<?=$item_sales_price;?>"></td>

               <!-- Discount -->
               <td id="td_<?=$rowcount;?>_8">
                  <input type="text" data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal(<?=$rowcount;?>)" name="td_data_<?=$rowcount;?>_8" id="td_data_<?=$rowcount;?>_8" class="pointer form-control text-right no-padding only_currency text-center item_discount" value="<?=$item_discount;?>" onkeyup="calculate_tax(<?=$rowcount;?>)" readonly>
               </td>

               <!-- Tax Amount -->
               <td id="td_<?=$rowcount;?>_11">
                  <input type="text" name="td_data_<?=$rowcount;?>_11" id="td_data_<?=$rowcount;?>_11" class="form-control text-right no-padding only_currency text-center" value="<?=$item_tax_amt;?>" readonly>
               </td>

               <!-- Tax Details -->
               <td id="td_<?=$rowcount;?>_12">
                  <label class='form-control ' style='width:100%;padding-left:0px;padding-right:0px;'>
                  <a id="td_data_<?=$rowcount;?>_12" href="javascript:void(0)" data-toggle="tooltip" title='Click to Change' onclick="show_sales_item_modal(<?=$rowcount;?>)" title=""><?=$item_tax_name ;?></a>
                  	</label>
               </td>

               <!-- Amount -->
               <td id="td_<?=$rowcount;?>_9"><input type="text" name="td_data_<?=$rowcount;?>_9" id="td_data_<?=$rowcount;?>_9" class="form-control text-right no-padding only_currency text-center" style="border-color: #f39c12;" readonly value="<?=$item_amount;?>"></td>
               
               <!-- ADD button -->
               <td id="td_<?=$rowcount;?>_16" style="text-align: center;">
                  <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow(<?=$rowcount;?>)" title="Delete ?" name="td_data_<?=$rowcount;?>_16" id="td_data_<?=$rowcount;?>_16"></a>
               </td>
               <input type="hidden" id="td_data_<?=$rowcount;?>_4" name="td_data_<?=$rowcount;?>_4" value="<?=$item_sales_price;?>">
               <input type="hidden" id="td_data_<?=$rowcount;?>_15" name="td_data_<?=$rowcount;?>_15" value="<?=$item_tax_id;?>">
               <input type="hidden" id="td_data_<?=$rowcount;?>_5" name="td_data_<?=$rowcount;?>_5" value="<?=$item_tax_amt;?>">
               <input type="hidden" id="tr_available_qty_<?=$rowcount;?>_13" value="<?=$item_available_qty;?>">
               <input type="hidden" id="tr_item_id_<?=$rowcount;?>" name="tr_item_id_<?=$rowcount;?>" value="<?=$item_id;?>">
               <input type="hidden" id="tr_tax_type_<?=$rowcount;?>" name="tr_tax_type_<?=$rowcount;?>" value="<?=$item_tax_type;?>">
               <input type="hidden" id="tr_tax_id_<?=$rowcount;?>" name="tr_tax_id_<?=$rowcount;?>" value="<?=$item_tax_id;?>">
               <input type="hidden" id="tr_tax_value_<?=$rowcount;?>" name="tr_tax_value_<?=$rowcount;?>" value="<?=$item_tax;?>">
               <input type="hidden" id="description_<?=$rowcount;?>" name="description_<?=$rowcount;?>" value="<?=$description;?>">

               <input type="hidden" id="item_discount_type_<?=$rowcount;?>" name="item_discount_type_<?=$rowcount;?>" value="<?=$item_discount_type;?>">
               <input type="hidden" id="item_discount_input_<?=$rowcount;?>" name="item_discount_input_<?=$rowcount;?>" value="<?=$item_discount_input;?>">
            </tr>
		<?php

	}



}
