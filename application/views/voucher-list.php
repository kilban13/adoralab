<!DOCTYPE html>
<html>
<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css_datatable.php"; ?>
<!-- bootstrap datepicker -->
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/datepicker/datepicker3.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Left side column. contains the logo and sidebar -->
  
  <?php include"sidebar.php"; ?>

  <?php 
      /*Total Invoices*/
      $total_invoice=$this->db->query("SELECT COUNT(*) as total FROM tbl_gift_without_sale")->row()->total;
      /*Total Invoices Total*/
      $sal_total=$this->db->query("SELECT COALESCE(sum(quantity),0) AS tot_sal_grand_total FROM tbl_gift_without_sale_items")->row()->tot_sal_grand_total;

      /*PAID AMOUNT*/
      $tot_received_amt=$this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount")->from("db_sales")->get()->row()->paid_amount;

      $sales_due_total=$this->db->query("SELECT COALESCE(SUM(sales_due),0) AS sales_due FROM db_customers")->row()->sales_due;
      //$sales_due_total = $sal_total - $sal_return_total;
     
  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>

    <div class="pay_now_modal">
    </div>
    <div class="view_payments_modal">
    </div>

    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?= $total_invoice;?></h3>

              <p>Total Gift Invoices</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?=$sal_total,$with_comma?></h3>
              <p><?= "Number of items gifted" ?></p>
            </div>
            <div class="icon">
              <i class="fa fa-gift"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <!-- ./col -->
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
        <?php include"comman/code_flashdata.php"; ?>
        <!-- ********** ALERT MESSAGE END******* -->
 
      </div>
      <!-- /.row -->

      <!-- /.row -->

      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
        <!-- ********** ALERT MESSAGE END******* -->
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Gift/Sample</h3>
              <?php if($CI->permissions('gv_add')) { ?>
              <div class="box-tools">
                <a class="btn btn-block btn-info" href="<?php echo $base_url; ?>voucher/add">
                <i class="fa fa-plus"></i> Add Gift/Sample</a>
              </div>
              <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example2" class="table table-bordered table-striped" width="100%">
                <thead class="bg-primary ">
                <tr>
                  <th>Voucher Code</th>
                  <th>Gift Reason</th>
                  <th>Customer Name</th>
                  <!-- <th>Warehouse</th> -->
                  <th>Items</th>
                  <th>Voucher Date</th>
                  <th>Created By</th>
                  <th><?= $this->lang->line('action'); ?></th>
                </tr>
                </thead>
                <tbody>
				
                </tbody>

              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
    <?= form_close();?>
  </div>
  <!-- /.content-wrapper -->
  <?php include"footer.php"; ?>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js_datatable.php"; ?>
<!-- bootstrap datepicker -->
<script src="<?php echo $theme_link; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
  //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });
</script>

<script type="text/javascript">
$(document).ready(function() {
    //datatables
   var table = $('#example2').DataTable({ 

      /* FOR EXPORT BUTTONS START*/
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      /* dom:'<"row"<"col-sm-12"<"pull-left"B><"pull-right">>> <"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>tip',*/
      // buttons: {
      //   buttons: [
      //       {
      //           className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
      //           text: 'Delete',
      //           action: function ( e, dt, node, config ) {
      //               multi_delete();
      //           }
      //       },
      //       { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
      //       { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
      //       { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
      //       { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
      //       { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
      //       { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',text:'Columns' },  

      //       ]
      //   },
        /* FOR EXPORT BUTTONS END */

        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.
        "responsive": true,
        language: {
            processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
        },
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "<?php echo site_url('voucher/ajax_list')?>",
            "type": "POST",
            
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                /*uncheckedClass: 'bg-white',*/
                radioClass: 'iradio_square-orange',
                increaseArea: '10%' // optional
              });
             call_code();
              //$(".delete_btn").hide();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0,5], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },
        
        ],
        /*Start Footer Total*/
        "footerCallback": function ( row, data, start, end, display ) {
            // var api = this.api(), data;
            // // Remove the formatting to get integer data for summation
            // var intVal = function ( i ) {
            //     return typeof i === 'string' ?
            //         i.replace(/[\$,]/g, '')*1 :
            //         typeof i === 'number' ?
            //             i : 0;
            // };
            // var total = api
            //     .column( 6, { page: 'none'} )
            //     .data()
            //     .reduce( function (a, b) {
            //         return intVal(a) + intVal(b);
            //     }, 0 );
            // var paid = api
            //     .column( 7, { page: 'none'} )
            //     .data()
            //     .reduce( function (a, b) {
            //         return intVal(a) + intVal(b);
            //     }, 0 );
            // var due = api
            //     .column( 8, { page: 'none'} )
            //     .data()
            //     .reduce( function (a, b) {
            //         return intVal(a) + intVal(b);
            //     }, 0 );
           
            // //$( api.column( 0 ).footer() ).html('Total');
            // $( api.column( 6 ).footer() ).html(app_number_format(total));
            // $( api.column( 7 ).footer() ).html(app_number_format(paid));
            // $( api.column( 8 ).footer() ).html(app_number_format(due));
           
        },
        /*End Footer Total*/
    });
    new $.fn.dataTable.FixedHeader( table );

});
</script>
<script src="<?php echo $theme_link; ?>js/voucher.js"></script>
<script type="text/javascript">
  function print_invoice(id){
  window.open("<?= base_url();?>pos/print_invoice_pos/"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
}

</script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
		
</body>
</html>
