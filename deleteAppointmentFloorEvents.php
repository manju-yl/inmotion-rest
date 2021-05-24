<?php
if(!isset($_COOKIE['token'])) {
    header("Location: index.php"); 
    exit();
}

require './api/common/header.php';
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

</style>

<link rel="stylesheet" href="public/css/ipc_fbf.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<div class="container">
  <span><a href="importData.php">Back</a></span>
  <span class="login100-form-title">Delete Appointment and Floor Manager Data</span>
  <div id="message"></div>
    <div class="wrapper clearfix">
    <div class="row eventDeletionDiv">
    <div class="col-md-4"></div>
    <div class="col-md-4 text-center form-container">
    <div class="form-group">
    <div id="dispAllEventIds"></div>
    </div>
    <div class="form-group">
    <div id="dispAllOptions"></div>
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
  beforeSend:function(){
    $(".eventDeletionDiv").hide();
  },
  success: function(data) {
    if(data=="false"){
        $('#message').html('<div class="errorMessage errormsgWrapperDi">There are no events to display.</div>');
        $(".eventDeletionDiv").hide();
    }else{
        $(".eventDeletionDiv").show();
        $("#dispAllEventIds").html(data);
        $("#dispAllOptions").hide();
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
    $(this).parents('.dialog-ovelay').fadeOut(500, function () {
      var selectedEventId = $( "#eventdeletion option:selected" ).text();
      var getSelectedOption = $("#appointflooroption option:selected").attr("id"); 
      $.ajax({
         type: "post",
         dataType: "json",
         headers: {
           'Accept': 'application/json',
           'Content-Type': 'application/json'
         },
          url: 'api/deleteEventData.php',
          data: JSON.stringify({ "event_id":selectedEventId,"deleteFlag":getSelectedOption }),
          success: function(response){
            $.ajax({
              url: 'api/getEventSelectOption.php',
              cache:false,
              beforeSend:function(){
                $(".eventDeletionDiv").hide();
              },
              success: function(data) {
                if(data=="false"){
                    $('#message').append('<div class="errorMessage errormsgWrapperDi">There are no events to display.</div>');
                    $(".eventDeletionDiv").hide();
                  }else{
                    $(".eventDeletionDiv").show();
                    $("#dispAllEventIds").html(data);
                    $("#dispAllOptions").hide();
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
      $(this).remove();
            });

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

$(document).on('change','#eventdeletion',function(e){
    // Preventing form to submit
    e.preventDefault();
    var selectedEventId = $( "#eventdeletion option:selected" ).text();
    var data = JSON.stringify({
      event_id: selectedEventId
    });
    var settings = {
        "url": 'api/getEventFlagList.php',
        "method": "POST",
        "timeout": 0,
        "headers": {
            "Content-Type": "application/json",
            'Accept': 'application/json'
        },
        "data":data
    };

    var html = '';
    $.ajax(settings).done(function (response) {
        $("#dispAllOptions").show();
        html += '<select id="appointflooroption" name="appointflooroption">';
        $.each(response, function (index, value) {
            html += '<option id=' + value.option_key + '>' + value.option_value + '</option>';
        });
        $('#dispAllOptions').html(html);
    })
});

</script>

<?php
require './api/common/footer.php';
?>
