(function($){"use strict";var input=$('.validate-input .input100');$('.validate-form').on('submit',function(){var check=true;for(var i=0;i<input.length;i++){if(validate(input[i])==false){showValidate(input[i]);check=false;}}
  return check;});$('.validate-form .input100').each(function(){$(this).focus(function(){hideValidate(this);});});function validate(input){if($(input).attr('type')=='email'||$(input).attr('name')=='email'){if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/)==null){return false;}}
else{if($(input).val().trim()==''){return false;}}}
function showValidate(input){var thisAlert=$(input).parent();$(thisAlert).addClass('alert-validate');}
function hideValidate(input){var thisAlert=$(input).parent();$(thisAlert).removeClass('alert-validate');}

//on page load
$( document ).ready(function() {
  if($('input:radio[name=resignappintments]:checked').val()=='resignappintment'){
    $("#floorManagerDivDisp").hide();
    $(".excelfloorLoader").hide();
    $.ajax({
      url: 'api/download.php',
      cache:false,
      beforeSend:function(){
        $('.excelLoader').show();
        $("#appointmentDivDisp").hide();
      },
      success: function(data) {
        $('.excelLoader').hide();
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
    $.ajax({
      url: 'api/getEventSelectOption.php',
      cache:false,
      beforeSend:function(){
        $('.deleteLoaderContainer').show();
        $(".deleteBtn").hide();
      },
      success: function(data) {
        $('.deleteLoaderContainer').hide();
        if(data=="false"){
            $(".deleteBtn").hide();
          }else{
            $(".deleteBtn").show();
          }
          
      },
      error: function(data) {
      }
    });
    $('.infoAppointmentMessage').show();
    $('.infoBoothMessage').hide();
    $('#message').html('');
    $('#floormanager').prop("checked", false);
    $('#resignappintment_div').show();
    $('#floormanager_div').hide();

  }else if($('input:radio[name=floormanager]:checked').val()=='floormanager'){
    $("#appointmentDivDisp").hide();
    $('.excelLoader').hide();
    $.ajax({
      url: 'api/downloadFloorManager.php',
      cache:false,
      beforeSend:function(){
        $('.excelfloorLoader').show();
        $("#floorManagerDivDisp").hide();
      },
      success: function(data) {
        $('.excelfloorLoader').hide();
        if(data=="false"){
          $("#floorManagerDivDisp").hide();
        }else{
          $("#floorManagerDivDisp").show();
          $("#dispFloorEventLists").html(data);
        }
      },
      error: function(data) {
      }
    });
    $.ajax({
      url: 'api/getEventSelectOption.php',
      cache:false,
      beforeSend:function(){
        $('.deleteLoaderContainer').show();
        $(".deleteBtn").hide();
      },
      success: function(data) {
        $('.deleteLoaderContainer').hide();
        if(data=="false"){
            $(".deleteBtn").hide();
          }else{
            $(".deleteBtn").show();
          }
          
      },
      error: function(data) {
      }
    }); 
    $('.infoBoothMessage').show();
    $('.infoAppointmentMessage').hide();
    $('#message').html('');
    $('#resignappintment').prop("checked", false);
    $('#resignappintment_div').hide();
    $('#floormanager_div').show();
  }
//when appointment radio button is clicked
$('#resignappintment').click(function(){
  $("#floorManagerDivDisp").hide();
  $(".excelfloorLoader").hide();
  $.ajax({
    url: 'api/download.php',
    cache:false,
    beforeSend:function(){
      $('.excelLoader').show();
      $("#appointmentDivDisp").hide();
    },
    success: function(data) {
      $('.excelLoader').hide();
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
  $.ajax({
    url: 'api/getEventSelectOption.php',
    cache:false,
    beforeSend:function(){
      $('.deleteLoaderContainer').show();
      $(".deleteBtn").hide();
    },
    success: function(data) {
      $('.deleteLoaderContainer').hide();
      if(data=="false"){
          $(".deleteBtn").hide();
        }else{
          $(".deleteBtn").show();
        }
        
    },
    error: function(data) {
    }
  });
  $('.infoAppointmentMessage').show();
  $('.infoBoothMessage').hide();
  $('#message').html('');
  $('#floormanager').prop("checked", false);
  $('#resignappintment_div').show();
  $('#floormanager_div').hide();
  
});

//when floor manager radio button is clicked
$('#floormanager').click(function(){
  $("#appointmentDivDisp").hide();
  $('.excelLoader').hide();
  $.ajax({
    url: 'api/downloadFloorManager.php',
    cache:false,
    beforeSend:function(){
      $('.excelfloorLoader').show();
      $("#floorManagerDivDisp").hide();
    },
    success: function(data) {
      $('.excelfloorLoader').hide();
      if(data=="false"){
        $("#floorManagerDivDisp").hide();
      }else{
        $("#floorManagerDivDisp").show();
        $("#dispFloorEventLists").html(data);
      }
    },
    error: function(data) {
    }
  }); 
  $.ajax({
    url: 'api/getEventSelectOption.php',
    cache:false,
    beforeSend:function(){
      $('.deleteLoaderContainer').show();
      $(".deleteBtn").hide();
    },
    success: function(data) {
      $('.deleteLoaderContainer').hide();
      if(data=="false"){
          $(".deleteBtn").hide();
        }else{
          $(".deleteBtn").show();
        }
        
    },
    error: function(data) {
    }
  });
  $('.infoBoothMessage').show();
  $('.infoAppointmentMessage').hide();
  $('#message').html('');
  $('#resignappintment').prop("checked", false);
  $('#resignappintment_div').hide();
  $('#floormanager_div').show();
  
});
});

//On click of login button
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
      $.cookie('expireAt',data.expireAt);
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

//Appointment import button submit event
$("#import_form").on('submit',function(e){
  $('.infoAppointmentMessage').show();
  $('.infoBoothMessage').hide();
  $("#floorManagerDivDisp").hide();
  $(".excelfloorLoader").hide();
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
     $('#file').attr('disabled', 'disabled');
     $("#floormanager").prop("disabled", true);
     $('#importappintmentSubmit').val('Importing...');
     $('.loaderContainer').show();

   },
   success:function(data){
    $('#message').html(data.message);
    if(data.totalRecords > 0){
      if(data.totalRecords == data.missedRowCount){
        $("#message").append( '<div class="errorMessage errormsgWrapperDi"> None of the record(s) were inserted due to missed mandatory records on the uploaded file.</div>' );
      }else if(data.missedRowCount > 0){
        $("#message").append( '<div class="alert alert-success"> ' + data.missedRowCount + ' record(s) were not inserted due to missed mandatory records on the uploaded file.</div>' );

      }
    }
    
    $.ajax({
      url:'api/download.php',
      cache:false,
      beforeSend:function(){
        $('.excelLoader').show();
        $("#appointmentDivDisp").hide();
      },
      success:function(result){
        $('.excelLoader').hide();
        if(result=="false"){
          $("#appointmentDivDisp").hide();
        }else{
          $("#appointmentDivDisp").show();
          $("#dispEventLists").html(result);
          if(data.emptyUniqueAppointment == 1){
            var insertedId = $( "#eventselection option:selected" ).text(); 
            $("#message").append( '<div class="alert alert-success"> '+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please find the missing data in the section below for event ID: '+insertedId+'</div>' );
          }else if(data.emptyUniqueAppointment > 1){
            if(data.eventCount == 1){
              var insertedId = $( "#eventselection option:selected" ).text(); 
              $("#message").append( '<div class="alert alert-success"> '+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please find the missing data in the section below for event ID: '+insertedId+'</div>' );
            }else{
              $("#message").append( '<div class="alert alert-success">'+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please check the missing record section below for more details.</div>' );
            }
          }
        }
      }
    });

    $.ajax({
      url: 'api/getEventSelectOption.php',
      cache:false,
      beforeSend:function(){
        $('.deleteLoaderContainer').show();
        $(".deleteBtn").hide();
      },
      success: function(data) {
        $('.deleteLoaderContainer').hide();
        if(data=="false"){
            $(".deleteBtn").hide();
          }else{
            $(".deleteBtn").show();
          }
          
      },
      error: function(data) {
      }
    });
    $('#importappintmentSubmit').attr('disabled', false);
    $('.loaderContainer').hide();
    $('#importappintmentSubmit').val('Import');
    $('#floormanager').prop("checked", false);
    $("#floormanager").prop("disabled", false);
    $("#import_form")[0].reset();
    $('#resignappintment').prop("checked", true);
    $('#resetbtn').attr('disabled', false);
    $('#file').attr('disabled', false);
  }
});


});

//Floor Manager import button submit event
$("#import_floor_form").on('submit',function(e){
  $('.infoBoothMessage').show();
  $('.infoAppointmentMessage').hide();
  $("#appointmentDivDisp").hide();
  $('.excelLoader').hide();
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
     $('#myfile').attr('disabled', 'disabled');
     $("#resignappintment").prop("disabled", true);
     $('#importFloormanagerSubmit').val('Importing...');
     $('.loaderContainer').show();
   },
   success:function(data){
    $('#message').html(data.message);

    if(data.totalRecords > 0){
      if(data.totalRecords == data.missedRowCount){
        $("#message").append( '<div class="errorMessage errormsgWrapperDi"> None of the record(s) were inserted due to missed mandatory records on the uploaded file.</div>' );
      }else if(data.missedRowCount > 0){
        $("#message").append( '<div class="alert alert-success"> ' + data.missedRowCount + ' record(s) were not inserted due to missed mandatory records on the uploaded file.</div>' );

      }
    }
    
    $.ajax({
      url: 'api/downloadFloorManager.php',
      cache:false,
      beforeSend:function(){
        $('.excelfloorLoader').show();
        $("#floorManagerDivDisp").hide();
      },
      success: function(result) {
        $('.excelfloorLoader').hide();
        if(result=="false"){
          $("#floorManagerDivDisp").hide();
        }else{
          $("#floorManagerDivDisp").show();
          $("#dispFloorEventLists").html(result);
          if(data.emptyUniqueFloor == 1){
            var insertedId = $( "#flooreventselection option:selected" ).text(); 
            $("#message").append( '<div class="alert alert-success"> '+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please find the missing data in the section below for event ID: '+insertedId+'</div>' );
            
          }else if(data.emptyUniqueFloor > 1){
            if(data.eventCount == 1){
              var insertedId = $( "#flooreventselection option:selected" ).text(); 
              $("#message").append( '<div class="alert alert-success"> '+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please find the missing data in the section below for event ID: '+insertedId+'</div>' );
            }else{
              $("#message").append( '<div class="alert alert-success">'+data.emptyRowsCount+ ' record(s) were inserted with missing data. Please check the missing record section below for more details.</div>' );
            }
        }
      }
      }
    });

    $.ajax({
      url: 'api/getEventSelectOption.php',
      cache:false,
      beforeSend:function(){
        $('.deleteLoaderContainer').show();
        $(".deleteBtn").hide();
      },
      success: function(data) {
        $('.deleteLoaderContainer').hide();
        if(data=="false"){
            $(".deleteBtn").hide();
          }else{
            $(".deleteBtn").show();
          }
          
      },
      error: function(data) {
      }
    });
    
    $('#importFloormanagerSubmit').attr('disabled', false);
    $('.loaderContainer').hide();
    $('#importFloormanagerSubmit').val('Import');
    $('#resignappintment').prop("checked", false);
    $("#resignappintment").prop("disabled", false);
    $("#import_floor_form")[0].reset();
    $('#floormanager').prop("checked", true);
    $('#boothresetbtn').attr('disabled', false);
    $('#myfile').attr('disabled', false);
   }
  });
});

//on click of appointment reset file button
$('#resetbtn').on('click', function(e) {
  var $el = $('#file');
  $el.wrap('<form>').closest(
    'form').get(0).reset();
  $el.unwrap();
});

//on click of floor manager reset file button
$('#boothresetbtn').on('click', function(e) {
  var $el = $('#myfile');
  $el.wrap('<form>').closest(
    'form').get(0).reset();
  $el.unwrap();
});

})(jQuery);
