<?php
session_start();
if(!isset($_COOKIE['token'])) {
    header("Location: index.php"); 
    exit();
}

require './api/common/header.php';
header("Strict-Transport-Security: max-age=15768000");
header('Access-Control-Allow-Origin', "lookuptools-dev.informamarkets.com, lookuptools.informamarkets.com");
header("X-XSS-Protection: 0");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Referrer-Policy: same-origin");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header("Expires: 0"); 
header("X-Frame-Options: SAMEORIGIN");
header("X-Frame-Options: DENY");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Request-Headers: X-Custom-Header');
header('Access-Control-Allow-Headers: x-requested-with, Content-Type, origin, authorization, accept, client-security-token');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
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
<link rel="stylesheet" href="public/css/bootstrap.min.css">
<script src="public/scripts/bootstrap.min.js"></script>

<span class="login100-form-title">Upload Appointment and Floor Manager Data</span>
<div class="container">
        <div class="wrapper clearfix">
           <div id="message"></div>
           <p><a href="deleteAppointmentFloorEvents.php" class="deleteBtn" title="Clear Event Data" style="float:right;color:red"><i class="glyphicon glyphicon-trash"></i> Clear Event Data</a></p>
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
                <?php 
                $_SESSION['pageupload'] = base64_encode(bin2hex(openssl_random_pseudo_bytes(32)));  
                ?>
                <div id="pagerefresh">
                
                <input type="hidden" name="pageupload" value="<?=$_SESSION['pageupload']?>"/>
                </div>
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
                  <div id="pagedownloadrefresh">
                  <input type="hidden" name="pageupload" value="<?=$_SESSION['pageupload']?>"/>
                  </div>
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
                <div id="pagefloorrefresh">
                <input type="hidden" name="pageupload" value="<?=$_SESSION['pageupload']?>"/>
                </div>
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
                  <div id="pagefloordownloadrefresh">
                  <input type="hidden" name="pageupload" value="<?=$_SESSION['pageupload']?>"/>
                  </div>
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
