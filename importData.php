<?php
if(!isset($_COOKIE['token'])) {
    header("Location: index.php"); 
    exit();
}

require './api/common/header.php';
 
?>

<title>InMotion APP - Upload Form</title>

<script type="text/javascript">

if( $('input:radio[name=resignappintments]:checked').val()=='resignappintment'){
    $('#resignappintment_div').show();
    $('#floormanager_div').hide();

}else if($('input:radio[name=resignappintments]:checked').val()=='floormanager'){
    $('#resignappintment_div').hide();
    $('#floormanager_div').show();

}
</script>

<script type="text/javascript" language="JavaScript" src="scripts/jquery.js" ></script>
<link rel="stylesheet" href="public/css/ipc_fbf.css">
<link rel="stylesheet" href="css/impromptu.css">
<script type='text/javascript' language='JavaScript' src='public/scripts/messages_lang.js'></script>
<script type='text/javascript' language='JavaScript' src='public/scripts/impromptu.js'></script>
<script type='text/javascript' language='JavaScript' src='public/scripts/import_users.js'></script>
<script type="text/javascript" src="scripts/ui.datepicker.js"></script>
<script language='JavaScript' src='scripts/jquery.selectbox.js' type='text/javascript'></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<span class="login100-form-title">Upload Form</span>
<div class="container">
        <div class="wrapper clearfix">
           <div id="message"></div>
           <div class="infoMessage">
                  <?php $csvFilename = 'sample.xlsx'; ?>
                  <ol>
                      <!--<li>Please upload a excel file(<a href="api/sample.php?filename=<?php echo $csvFilename; ?>">Sample</a>). </li>-->
                      <li>EVENTID and COID are Mandatory. </li>
                  </ol>
              </div>
            <section>
              <form  class="form" action="" method="post" enctype="multipart/form-data" id="import_form">
                <input type="radio" name="resignappintments" value="resignappintment"  id="resignappintment" checked />
                          <label>Re-sign Appointment</label>
               <div id='resignappintment_div'>
               <p><label>Choose Excel File</label> <input type="file"
                    name="file" id="file" class="txtbx" accept=".xls,.xlsx"></p>
                <p><input type="submit" name="importappintmentSubmit" id="importappintmentSubmit" alt="Upload" title="Upload" class="button button4" value="Import"/>
                  <button id="resetbtn" class="button button4" type="button">Reset</button></p>
                </div>
                </form>
                <div class="btn" id="appintmentDivDisp">
              <form action="api/downloadFile.php" method="post">
                    <div id="recentEventId"></div>
                    <div id="dispEventLists"></div>
                    <div class="btn" id="dispDownloadBtn" style="display:none;">
                            <button type="submit" id="btnExport" name='export'
                                value="Export to Excel" class="button button4">Export
                                to Excel</button>
                    </div>
                  </form>
                </div>
            </section>
            <section>
              <form  class="form" action="" method="post" enctype="multipart/form-data" id="import_floor_form">
                <input type="radio" name="floormanager"  value="floormanager" id="floormanager"/>
                <label>Floor Manager Lookup </label>
                <div id='floormanager_div'>
                  <p><label class="formlabel">Choose Excel File</label> <input type="file"
                      name="myfile" id="myfile" class="txtbx"  accept=".xls,.xlsx"></p>
                  <p><input type="submit" name="importFloormanagerSubmit" id="importFloormanagerSubmit" alt="Upload" title="Upload" class="button button4" value="Import"/>
                  <button id="boothresetbtn" class="button button4" type="button">Reset</button></p></div>
              </form>
              <div class="btn" id="floorManagerDivDisp">
              <form action="api/downloadFile.php" method="post">
                  <div id="recentFloorEventId"></div>
                  <div id="dispFloorEventLists"></div>
                  <div class="btn" id="dispFloorDownloadBtn" style="display:none;">
                          <button type="submit" id="btnExport" name='exportEmptyFloorDetails'
                              value="Export to Excel" class="button button4">Export
                              to Excel</button>
                  </div>
                </form>
              </div>
            </section>
    </div>  
</div>


<script type="text/javascript">

if( $('input:radio[name=resignappintments]:checked').val()=='resignappintment'){
    $('#resignappintment_div').show();
    $('#floormanager_div').hide();

}else if($('input:radio[name=floormanager]:checked').val()=='floormanager'){
    $('#resignappintment_div').hide();
    $('#floormanager_div').show();

}
$('#resignappintment').click(function(){
    $('#message').html('');
    $('#floormanager').prop("checked", false);
    $('#resignappintment_div').show();
    $('#floormanager_div').hide();
    $('#appintmentDivDisp').show();
    $('#floorManagerDivDisp').hide();
    });

$('#floormanager').click(function(){
    $('#message').html('');
    $('#resignappintment').prop("checked", false);
    $('#resignappintment_div').hide();
    $('#floormanager_div').show();
    $('#appintmentDivDisp').hide();
    $('#floorManagerDivDisp').show();

    $.ajax({
    url: 'api/downloadFloorManager.php',
    success: function(data) {
        if(data=="false"){
        $("#dispFloorEventLists").html('');
        $("#dispFloorDownloadBtn").hide();
      }else{
        $("#dispFloorEventLists").html(data);
        $("#dispFloorDownloadBtn").show();
      }
    },
    error: function(data) {
    }
    }); 
   });
</script>   
<?php

require './api/common/footer.php';

?>
