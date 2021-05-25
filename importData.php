<?php
if(!isset($_COOKIE['token'])) {
    header("Location: index.php"); 
    exit();
}

require './api/common/header.php';
 
?>

<title>InMotion APP - Upload Form</title>
<style>
.container-login100 {
    background: #11a7d9 !important;
}
.wrap-login100 {
    width: 80% !important;
    align-items: center;
    padding: 8px 50px !important;
}
.login100-form {
    width: 290px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-flow: column;
    min-height: 75vh;
    padding: 4em 0px;
}
.login100-form-title {
  padding-bottom: 30px;
}
.btnexcel{
  padding:50px 12px !important;
}
</style>
<script type="text/javascript">

if( $('input:radio[name=resignappintments]:checked').val()=='resignappintment'){
    $('#resignappintment_div').show();
    $('#floormanager_div').hide();

}else if($('input:radio[name=resignappintments]:checked').val()=='floormanager'){
    $('#resignappintment_div').hide();
    $('#floormanager_div').show();

}
</script>

<link rel="stylesheet" href="public/css/ipc_fbf.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<span class="login100-form-title">Upload Appointment and Floor Manager Data</span>
<div class="container">
        <div class="wrapper clearfix">
           <div id="message"></div>
           <p><a href="deleteAppointmentFloorEvents.php" class="deleteBtn" title="Clear Event Data" style="display:none;float:right;color:red"><i class="glyphicon glyphicon-trash"></i> Clear Event Data</a></p>
           <p></p>
           <div class="infoAppointmentMessage" style="display:none">
                  <?php $appointmentSampleExcel = 'downloadfile/appointment.xlsx'; ?>
                  <ol>
                    <li>Please upload a Re-sign appointment excel file (<a href="<?php echo $appointmentSampleExcel; ?>">Sample</a>). </li>
                    <li>EventId and COID are Mandatory. </li>
                  </ol>
            </div>
            
            <div class="infoBoothMessage" style="display:none">
                  <?php $floorSampleExcel = 'downloadfile/floormanager.xlsx'; ?>
                  <ol>
                    <li>Please upload a Floor Manager excel file (<a href="<?php echo $floorSampleExcel; ?>">Sample</a>). </li>
                    <li>EventId and  CoID and Booth Number are Mandatory. </li>
                  </ol>
            </div>
            <section>
              <form  class="form" action="" method="post" enctype="multipart/form-data" id="import_form">
                <input type="radio" name="resignappintments" value="resignappintment"  id="resignappintment" checked />
               <label>Re-sign Appointment</label>
               <div id='resignappintment_div'>
               <p><label>Choose Appointment Excel File</label> <input type="file"
                    name="file" id="file" class="txtbx" accept=".xls,.xlsx"></p>
                <p><span class="loaderContainer" style="display:none"><span class="loader" ></span></span><input type="submit" name="importappintmentSubmit" id="importappintmentSubmit" alt="Upload" title="Upload" class="button button4" value="Import"/>
                  <button id="resetbtn" class="button button4" type="button" alt="Reset" title="Reset File">Reset File</button></p>
                </div>
                </form>
                <div class="excelLoader btn btnexcel" style="display:none"><span class="loader" ></span></div>
                <div class="btn" id="appointmentDivDisp" style="display:none">
                <label>Fetch missing records for Event Id:</label>
                <form action="api/downloadFile.php" method="post">
                    <div id="dispEventLists"> 
                    </div>
                      <button type="submit" id="btnExport" name='export'
                          value="Export to Excel" class="button button4" title="Export to Excel">Export
                                to Excel</button>
                </form>
                </div>
            </section>
            <section>
              <form  class="form" action="" method="post" enctype="multipart/form-data" id="import_floor_form">
                <input type="radio" name="floormanager"  value="floormanager" id="floormanager"/>
                <label>Floor Manager Lookup </label>
                <div id='floormanager_div'>
                  <p><label class="formlabel">Choose Floor Manager Excel File</label> <input type="file"
                      name="myfile" id="myfile" class="txtbx"  accept=".xls,.xlsx"></p>
                  <p><span class="loaderContainer" style="display:none"><span class="loader" ></span></span><input type="submit" name="importFloormanagerSubmit" id="importFloormanagerSubmit" alt="Upload" title="Upload" class="button button4" value="Import"/>
                  <button id="boothresetbtn" class="button button4" type="button" alt="Reset" title="Reset File">Reset File</button></p></div>
              </form>
              <div class="excelfloorLoader btn btnexcel" style="display:none"><span class="loader" ></span></div>
              <div class="btn" id="floorManagerDivDisp" style="display:none">
              <label>Fetch missing records for Event Id:</label>
              <form action="api/downloadFile.php" method="post">
                  <div id="dispFloorEventLists"></div>
                    <button type="submit" id="btnExport" name='exportEmptyFloorDetails'
                        value="Export to Excel" class="button button4" title="Export to Excel">Export
                              to Excel</button>
                </form>
              </div>
            </section>
    </div>
</div>
<?php
require './api/common/footer.php';
?>
