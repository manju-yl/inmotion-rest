(function($){"use strict";var input=$('.validate-input .input100');$('.validate-form').on('submit',function(){var check=true;for(var i=0;i<input.length;i++){if(validate(input[i])==false){showValidate(input[i]);check=false;}}
return check;});$('.validate-form .input100').each(function(){$(this).focus(function(){hideValidate(this);});});function validate(input){if($(input).attr('type')=='email'||$(input).attr('name')=='email'){if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/)==null){return false;}}
else{if($(input).val().trim()==''){return false;}}}
function showValidate(input){var thisAlert=$(input).parent();$(thisAlert).addClass('alert-validate');}
function hideValidate(input){var thisAlert=$(input).parent();$(thisAlert).removeClass('alert-validate');}


$( document ).ready(function() {
  $('.infoAppointmentMessage').show();
  $('.infoBoothMessage').hide();
  $.ajax({
  url: 'api/download.php',
  success: function(data) {
    if(data=="false"){
      $("#appointmentDivDisp").hide();
    }else{
      $("#appointmentDivDisp").show();
      $("#dispEventLists").html(data);
    }
  },
  error: function(data) {
  }
  });


});

$("#login_form").on('submit',function(e){
	e.preventDefault();
    var email      = $('#email').val();
    var password   = $('#password').val(); 

	$.ajax({
	type: "post",
	dataType: "json",
	headers: {
	    'Accept': 'application/json',
	    'Content-Type': 'application/json'
	},
	data: JSON.stringify({ 
	    "email" : email,
	    "password" : password
	}),
	url: 'api/generateToken.php',
	success: function(data, result) {
	    if(data.status ==200){
	    	$.cookie('token',data.jwt);
	    	$.cookie('firstname',data.firstname);
	    	$.cookie('userId',data.userId);
	    	window.location = 'importData.php';
	    }else{
	    	$(".error").html(data.error)
	    }
	},
	error: function(data, result) {
		console.log(data.status);
	}
	});


});


$("#import_form").on('submit',function(e){
  $('.infoAppointmentMessage').show();
  $('.infoBoothMessage').hide();
	e.preventDefault();
	$.ajax({
          url:'api/import.php',
          method:'POST',
          data:new FormData(this),
          contentType:false,
          cache:false,
          processData:false,
          beforeSend:function(){
          	$('#importappintmentSubmit').attr('disabled', 'disabled');
            $('#resetbtn').attr('disabled', 'disabled');
    		    $("#floormanager").prop("disabled", true);
          	$('#importappintmentSubmit').val('Importing...');
          },
          success:function(data){
            $('#message').html(data.message);
            if(data.totalRecords > 0){
              if(data.totalRecords == data.missedRowCount){
                $("#message").append( '<div class="errorMessage errormsgWrapperDi"> None of the record(s) were inserted due to missed Mandatory Records on the uploaded file.</div>' );
              }else if(data.missedRowCount > 0){
                $("#message").append( '<div class="alert alert-success"> ' + data.missedRowCount + ' record(s) were not inserted due to missed Mandatory Records on the uploaded file.</div>' );

              }
            }
            
            $.ajax({
              url:'api/download.php',
              cache:false,
              success:function(result){
                if(result=="false"){
                  $("#appointmentDivDisp").hide();
                }else{
                  $("#appointmentDivDisp").show();
                  $("#dispEventLists").html(result);
                  if(data.emptyUniqueAppointment == 1){
                      var insertedId = $( "#eventselection option:selected" ).text(); 
                      $("#message").append( '<div class="alert alert-success"> '+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please find the missing data in the section below for event ID: '+insertedId+'</div>' );
                    }else if(data.emptyUniqueAppointment > 1){
                      $("#message").append( '<div class="alert alert-success">'+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please check the missing record section below for more details.</div>' );
                    }
                }
              }
            });
            $('#importappintmentSubmit').attr('disabled', false);
            $('#importappintmentSubmit').val('Import');
    		    $('#floormanager').prop("checked", false);
    		    $("#floormanager").prop("disabled", false);
            $("#import_form")[0].reset();
            $('#resignappintment').prop("checked", true);
          }
        });


});

$("#import_floor_form").on('submit',function(e){
  $('.infoBoothMessage').show();
  $('.infoAppointmentMessage').hide();
	e.preventDefault();
	$.ajax({
          url:'api/import.php',
          method:'POST',
          data:new FormData(this),
          contentType:false,
          cache:false,
          processData:false,
          beforeSend:function(){
          	$('#importFloormanagerSubmit').attr('disabled', 'disabled');
            $('#boothresetbtn').attr('disabled', 'disabled');
            $("#resignappintment").prop("disabled", true);
          	$('#importFloormanagerSubmit').val('Importing...');
          },
          success:function(data){
            $('#message').html(data.message);

            if(data.totalRecords > 0){
              if(data.totalRecords == data.missedRowCount){
                $("#message").append( '<div class="errorMessage errormsgWrapperDi"> None of the record(s) were inserted due to missed Mandatory Records on the uploaded file.</div>' );
              }else if(data.missedRowCount > 0){
                $("#message").append( '<div class="alert alert-success"> ' + data.missedRowCount + ' record(s) were not inserted due to missed Mandatory Records on the uploaded file.</div>' );

              }
            }
 
            $.ajax({
              url: 'api/downloadFloorManager.php',
              success: function(result) {
                if(result=="false"){
                  $("#floorManagerDivDisp").hide();
                }else{
                  $("#floorManagerDivDisp").show();
                  $("#dispFloorEventLists").html(result);
                  if(data.emptyUniqueAppointment == 1){
                    var insertedId = $( "#flooreventselection option:selected" ).text(); 
                    $("#message").append( '<div class="alert alert-success"> '+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please find the missing data in the section below for event ID: '+insertedId+'</div>' );
                  }else if(data.emptyUniqueAppointment > 1){
                    $("#message").append( '<div class="alert alert-success">'+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please check the missing record section below for more details.</div>' );
                  }
                }
              }
              });


            
            $('#importFloormanagerSubmit').attr('disabled', false);
            $('#importFloormanagerSubmit').val('Import');
			      $('#resignappintment').prop("checked", false);
			      $("#resignappintment").prop("disabled", false);
            $("#import_floor_form")[0].reset();
            $('#floormanager').prop("checked", true);
          }
        });


});

$('#resetbtn').on('click', function(e) {
    var $el = $('#file');
    $el.wrap('<form>').closest(
      'form').get(0).reset();
    $el.unwrap();
});

$('#boothresetbtn').on('click', function(e) {
    var $el = $('#myfile');
    $el.wrap('<form>').closest(
      'form').get(0).reset();
    $el.unwrap();
});


})(jQuery);
