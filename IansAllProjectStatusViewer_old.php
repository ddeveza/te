<?php require('../dmsg/Ianhome.php'); ?>
 <head>
  <title>Export jQuery Datatables Data to Excel, CSV, PDF using PHP Ajax</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>

  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.css"/>

  <script type="text/javascript" src="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.js">
  </script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

 </head>
<body>
	<div id='dennis'>
		<div class="modal-header" style="position:relative"><button type="button" class="btn btn-warning " id="Saveremarks">Save Remarks</</button></div>
	<table id="example" class="table table-bordered hover" style="width:100%" >
		<thead><tr>
			   
                <th>Project ID</th>
                <th>Engineer</th>
                <th>Projec Name</th>
                <!-- <th>OSPI PE</th> -->
                <th>Device ID</th>
                 <th>Project Status</th>
                <th>YearWeek</th>
                <th>Weekly Status Update</th>
                <th >Remarks</th>
                 
                
            </tr>
		
	</thead>
		<tbody>
					<?php 
					   include 'D:\xampp\htdocs\dmsg\config\config.php';
					   
					   	
					$sql = "create TEMPORARY TABLE dennis  \n"

				    . "SELECT concatyrww.*\n"

				    . "from concatyrww\n"

				    . "inner JOIN getmaxdate\n"

				    . "on concatyrww.Project_ID = getmaxdate.Project_ID and concatyrww.yearweek = getmaxdate.maxyear";

				    mysqli_query($con,$sql);

				    //Added Device Description or Monicker
					$sqlDeviceMonicker = "CREATE TEMPORARY TABLE DeviceMonicker AS
											SELECT projects.* ,products.Description
											FROM projects 
											LEFT JOIN products
											on projects.Device_ID = products.Device_ID";	

					mysqli_query($con,$sqlDeviceMonicker);

				    $projsql = "SELECT * \n"

					    . "FROM DeviceMonicker f\n"

					    . "LEFT JOIN dennis w\n"

					    . "on f.Project_ID = w.project_id";



					$result = mysqli_query($con,$projsql);
					
					while($row=mysqli_fetch_array($result)){
						$Engineer = $row[4];
						$ProjName = $row[1];
						$deviceID = $row['Device_ID'];
						//Fetch Device Monicker to add on the device id during project review
						$monicker = $row['Description'];	
								

						$Workweek = $row['yearweek'];
						$StatusUpdate = $row['Detailed_Status_Update'];
						$Remarks = $row['Weekly_Remarks'];
						$ProjectID = $row[0];
						$ProjStatus = $row['Project_Status'];
						$OSPIPE = $row['OSPI_PE'];
						$BusinessCase = strip_tags($row['Project_Description']);

					?>
					<tr>
						<td witdth=""><?php echo $ProjectID;  ?></td>

						<td witdth=""><?php 
									if ($OSPIPE !=''){
										echo $Engineer. ','.$OSPIPE;  
									}else echo $Engineer;
						?></td>
						<td witdth=""><span class="d-inline-block" tabindex="0" data-placement="bottom"  data-toggle="tooltip" title="<?php echo $BusinessCase  ;?>" ><strong style="cursor: pointer;"><?php echo $ProjName ; ?></strong></span></td>
						
						<!--Remove PE  <td witdth=""><?php //echo $OSPIPE  ;?></td> -->
						<!-- Append monicker to device ID -->
						<td witdth="50%"><?php 
									if($monicker==""){
										echo $deviceID ;	
									}else{echo $deviceID ." (" .$monicker.")"; }?>
									
						</td>
						<td witdth=""><?php echo $ProjStatus  ;?></td>
						<td witdth=""><?php echo $Workweek  ;?></td>
					
					
						<td witdth=""><?php echo $StatusUpdate;  ?></td>
							<td witdth="" id="data"><textarea rows="3" cols="20"  id="<?php echo $ProjectID ?>"><?php echo $Remarks;  ?></textarea></td>

					</tr>

				<?php } ?>

        </tbody>
        <tfoot>
            <tr>
                <th>Project ID</th>
                <th>Engineer</th>
                <th>Projec Name</th>
                <!-- <th>OSPI PE</th> -->
                <th>Device ID</th>
                <th>Project Status</th>
                <th>YearWeek</th>
                <th>Weekly Status Update</th>
                <th>Remarks</th>
                 
              
               
            </tr>
        </tfoot>
	</table>
	
	</div>	
	
<?php
	include'..\dmsg/inc/footer.php'; 
?>

<script type="text/javascript" language="javascript">
	

$(document).ready(function() {
    $('#example').DataTable( {
    	   
          "processing" : true,
		   dom: 'lBfrtip',
		   buttons: [ 
                     {
                        extend: 'excelHtml5',
                        title: 'Data export'
                    }, 'csv', 'copy','print'
		   ],
		   
		   "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ] ,
		    initComplete: function () {
            this.api().columns([1,4]).every( function () {
                var column = this;
				
                var select = $('<select name="wgtmsr" id="wgtmsr" style="width: 100px !important; min-width: 100px; max-width: 50px;><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
						
						column
                            .search( val, true, false )
                            .draw();
                    } );
				//FIlter engineername
				const FetchEngineer = column.data().unique().sort().map(function(a){
										var dennis = a.indexOf(',');
											if (dennis>0){
												return a.substr(0,dennis);
											}else{ return a;}
									}).unique();

				//console.log(FetchEngineer);
				select.append('<option value="">All</option>');
                FetchEngineer.each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        } 
		  
   		 });

		
  
});
</script>

<script type="text/javascript">
	
			$('textarea').focusout(function(){
				var UniqueId =  $(this).attr('id');
				var Remarks = $(this).val();


				$.ajax({
					type:'post',
					url:'/dmsg/MyPHP/SaveWeeklyRemarks.php',
					data:{
						'UniqueId':UniqueId,
						'Remarks':Remarks
					},
					success:function(data){
						
					}
				});

			});
	
</script>

<script type="text/javascript">
	
	$(document).ready(function() {
		$('#Saveremarks').click(function(){
			location.reload();
			alert('SaveRemarks');
		})
	});
</script>