<?php
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

<title>InMotion APP - Delete Appointment and Floor Form</title>
<style>
.container-login100 {
    background: #11a7d9 !important;
}
.wrap-login100 {
    align-items: center;
    padding: 20px 50px !important;
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
select {
    width: 100%;
    padding: .5em 1em;
}

.deleteLoader {
    width: 23%;
    height: auto;
    display: inline-block;
    justify-content: center;
    align-items: center;
    -webkit-transition: all .4s;
    -o-transition: all .4s;
    -moz-transition: all .4s;
    transition: all .4s;
    text-align: center;
    margin: 0px auto;
    padding: 30px;
}

</style>

<link rel="stylesheet" href="public/css/ipc_fbf.css">
<link rel="stylesheet" href="public/css/bootstrap.min.css">
<script src="public/scripts/bootstrap.min.js"></script>

<div class="container">
  <span><a href="importData.php">Back</a></span>
  <span class="login100-form-title">Delete Appointment and Floor Manager Data</span>
  <div id="message"></div>
    <div class="wrapper clearfix">
    <div class="row eventDeletionDiv">
    <div class="col-md-4"></div>
    <div class="col-md-4 text-center form-container">
    <div class="form-group">
    <div id="dispAllOptions">
    <select id='appointflooroption' name='appointflooroption'>";
    <option id='appointment'>Re-Sign Appointment</option>";
    <option id='floorManager'>Floor Manager</option>";
    </select>
    </div>
    </div>
    <div class="form-group">
    <div id="dispAllEventIds">
      <span class="deleteLoader" style="display:none"><span class="loader" ></span></span>
    </div>
    </div>
    <div class="form-group">
    <button class="delete button button4" title="Delete">Delete</button>
    <button class="cancel button button4" onclick="window.location.href = 'importData.php'" title="Cancel">Cancel</button>
    </div>
    </div>
   <div class="col-md-4"></div>
    </div>
    </div>  
</div>
<script type="text/javascript">
$.ajax({
  url: 'api/getEventSelectOption.php',
  cache:false,
  dataType:"html",
  beforeSend:function(){
    $('.deleteLoader').show();
    $('.delete').hide();
    $('.cancel').hide();
  },
  success: function(data) {
    $('.deleteLoader').hide();
    if(data=="false"){
        $(".eventDeletionDiv").show();
        $("#dispAllOptions").show();
    }else{
        $(".eventDeletionDiv").show();
        $('.delete').show();
        $('.cancel').show();
        $("#dispAllEventIds").html(data);
        $("#dispAllOptions").show();
    }
  },
  error: function(data) {
  }
});
function Confirm(title, msg, $true, $false, selectedEventId, getSelectedOption) { 
  var $content =  "<div class='dialog-ovelay'>" +
                  "<div class='dialog'><header>" +
                   "<i class='fa fa-close'></i>" +
                   " <h3> " + title + " </h3> " +
               "</header>" +
               "<div class='dialog-msg'>" +
                   " <p> " + msg + getSelectedOption + " having EventId: "+ selectedEventId + "?</p> " +
                   " <p> Once deleted all data will be permanenetly removed. </p> " +
               "</div>" +
               "<footer>" +
                   "<div class='controls'>" +
                       " <button class='button button-danger doAction'>" + $true + "</button> " +
                       " <button class='button button-default cancelAction'>" + $false + "</button> " +
                   "</div>" +
               "</footer>" +
            "</div>" +
          "</div>";
  $('body').prepend($content);
  $('.doAction').click(function () {
      var selectedEventId = $( "#eventdeletion option:selected" ).text();
      var getSelectedOption = $("#appointflooroption option:selected").attr("id"); 
      $.ajax({
         type: "post",
         dataType: "json",
         headers: {
           'Accept': 'application/json',
           'Content-Type': 'application/json',
           'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
         },
          url: 'api/deleteEventData.php',
          data: JSON.stringify({ "event_id":selectedEventId,"deleteFlag":getSelectedOption }),
          success: function(response){
            $.ajax({
              url: 'api/getEventSelectOption.php',
              cache:false,
              dataType:"html",
              beforeSend:function(){
                $('.deleteLoader').show();
                $('.delete').hide();
                $('.cancel').hide();
              },
              success: function(data) {
                $('.deleteLoader').hide();
                if(data=="false"){
                    $(".eventDeletionDiv").show();
                  }else{
                    $(".eventDeletionDiv").show();
                    $('.delete').show();
                    $('.cancel').show();
                    $("#dispAllEventIds").html(data);
                    $("#dispAllOptions").show();
                  }
                  
              },
              error: function(data) {
              }
            });

            $('#message').html('<div class="alert alert-success">'+response.message+'</div>');
          },
          error: function(data, result) {
            $('#message').html('<div class="errorMessage errormsgWrapperDi">No Data found.</div>');
          }
      });
      $('.dialog-ovelay').remove();
  });
  $('.cancelAction, .fa-close').click(function () {
    $(this).parents('.dialog-ovelay').fadeOut(500, function () {
      $(this).remove();
    });
  });
}

$('.delete').click(function () {
    var selectedEventId = $( "#eventdeletion option:selected" ).text();
    var getSelectedOption = $("#appointflooroption option:selected").text(); 
    Confirm('Confirm', 'Are you sure you want to delete the ', 'Yes', 'Cancel', selectedEventId, getSelectedOption);
});

$(document).on('change','#appointflooroption',function(e){
    // Preventing form to submit
    e.preventDefault();
    $('#message').html('');
    var selectedEventOption = $("#appointflooroption option:selected").attr("id"); 
    var data = JSON.stringify({
      options: selectedEventOption
    });
    var settings = {
        "url": 'api/getEventsDisplay.php',
        "method": "POST",
        "timeout": 0,
        "headers": {
            "Content-Type": "application/json",
            'Accept': 'application/json',
            'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
        },
        "data":data
    };

    var html = '';
    $.ajax(settings).done(function (response) {
        $("#dispAllEventIds").show();
        html += '<select id="eventdeletion" name="eventdeletion">';
        $.each(response, function (index, value) {console.log(value)
            html += '<option>' + value.option_value + '</option>';
        });
        
        $('#dispAllEventIds').html(html);
        $('.delete').show();
        $('.cancel').show();
    }).fail(function(response){//alert("ss");
    $('#message').html('<div class="errorMessage errormsgWrapperDi">There are no events to display.</div>');
        $("#dispAllEventIds").hide();
        $('.delete').hide();
        $('.cancel').hide();           
    });
});

</script>

<?php
require './api/common/footer.php';
?>
