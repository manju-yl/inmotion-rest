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
button.delete {
font-family: Montserrat-Bold;
    font-size: 15px;
    line-height: 1.5;
    color: #fff;
    text-transform: uppercase;
    width: 50%;
    height: 38px;
    border-radius: 25px;
    background: #57b846;
    display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: inline-block;
    justify-content: center;
    align-items: center;
    padding: 0 25px;
    -webkit-transition: all .4s;
    -o-transition: all .4s;
    -moz-transition: all .4s;
    transition: all .4s;
}

</style>

<link rel="stylesheet" href="public/css/ipc_fbf.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<span class="login100-form-title">Delete Appointment and Floor Manager Data</span>
<div class="container">
  <div id="message"></div>
    <div class="wrapper clearfix">
    <div class="row eventDeletionDiv">
    <div class="col-md-4"></div>
    <div class="col-md-4 text-center form-container">
    <div class="form-group">
    <div id="dispAllEventIds"></div>
    </div>
    <div class="form-group">
    <select id='appointflooroption' name='appointflooroption'>
                <option id="appointment">Re-Sign Appointment</option>
                <option id="floorManager">Floor Manager</option>
            </select>
    </div>
    <div class="form-group">
    <button class="delete button button4">Delete</button>
    </div>
    </div>
   <div class="col-md-4"></div>
    </div>
    </div>  
</div>
<script type="text/javascript">
$.ajax({
    url: 'api/getEventSelectOption.php',
    success: function(data) {
      if(data=="false"){
          $('#message').html('<div class="errorMessage errormsgWrapperDi">There are no events to delete.</div>');
          $(".eventDeletionDiv").hide();
        }else{
          $("#dispAllEventIds").html(data);
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
                         " <p> " + msg + getSelectedOption + " having EventId: "+ selectedEventId + "</p> " +
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
</script>

<?php
require './api/common/footer.php';
?>