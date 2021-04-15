<?php
if(!isset($_COOKIE['token'])) {
    header("Location: index.php"); 
    exit();
}

require './api/common/header.php';

// Get status message
if(!empty($_GET['status'])){
    switch($_GET['status']){
        case 'succ':
            $statusType = 'alert-success';
            $statusMsg = 'Members data has been imported successfully.';
            break;
        case 'err':
            $statusType = 'alert-danger';
            $statusMsg = 'Some problem occurred, please try again.';
            break;
        case 'invalid_file':
            $statusType = 'alert-danger';
            $statusMsg = 'Please upload a valid CSV file.';
            break;
        default:
            $statusType = '';
            $statusMsg = '';
    }
}

 
?>

<!-- Display status message -->
<?php if(!empty($statusMsg)){ ?>
<div class="col-xs-12">
    <div class="alert <?php echo $statusType; ?>"><?php echo $statusMsg; ?></div>
</div>
<?php } ?>
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
<link rel="stylesheet" href="css/ipc_fbf.css">
<link rel="stylesheet" href="css/impromptu.css">
<!-- Validator Javascript -->
<script type='text/javascript' language='JavaScript' src='scripts/language/en/messages_lang.js'></script>
<script type='text/javascript' language='JavaScript' src='scripts/impromptu.js'></script>
<script type='text/javascript' language='JavaScript' src='scripts/import_users.js'></script>
<script type="text/javascript" src="scripts/ui.datepicker.js"></script>
<script language='JavaScript' src='scripts/jquery.selectbox.js' type='text/javascript'></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<span class="login100-form-title">Upload Form</span>
<div>
 
         <form  class="form" action="" method="post" enctype="multipart/form-data">
            <table width="100%" border="0" cellpadding="4" cellspacing="4">
                        <div id="errormsg" class="errorMessage errormsgWrapperDiv" style="display: none;"></div>
                      <tr>
                        <td><input type="radio" name="resignappintments" value="resignappintment"  id="resignappintment" checked />
                          <label>Re-sign Appointment</label>
                        </td>
                        <td><input type="radio" name="resignappintments"  value="floormanager" id="floormanager"/>
                          <label>Floor Manager Lookup </label>
                        </td>
                      </tr>

                      <tr>
                        <td class="formlabel"  id='resignappintment_div'>
                        <input type="file" name="file" size='30' class='txtbx' id="file"></td>
                        <td class="formlabel" id='floormanager_div'>
                        <input type="file" name="file" size='30' class='txtbx' id="file"></td>
                      </tr>
                      <tr>
                          <td>
                              <input type="submit" name="importSubmit" alt="Upload" title="Upload" class="button button4" onclick="return validate_import_users(this.form);" value="Upload" />
                          </td>
                      </tr>
            </table>
        </form>
      
</div>


<script type="text/javascript">

if( $('input:radio[name=resignappintments]:checked').val()=='resignappintment'){
    $('#resignappintment_div').show();
    $('#floormanager_div').hide();
}else if($('input:radio[name=resignappintments]:checked').val()=='floormanager'){
    $('#resignappintment_div').hide();
    $('#floormanager_div').show();

}
$('#resignappintment').click(function(){
      $('#resignappintment_div').show();
      $('#floormanager_div').hide();
    });

$('#floormanager').click(function(){
      $('#resignappintment_div').hide();
      $('#floormanager_div').show();
   });
</script>   
<?php

require './api/common/footer.php';

?>