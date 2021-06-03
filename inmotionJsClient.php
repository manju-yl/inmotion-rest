<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Connect to API</title>
        
        <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
    </head>
    <body>
        <style>
            .table {
                font-family: Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            .table td, .table th {
                border: 1px solid #ddd;
                padding: 8px;
            }

            .table tr:nth-child(even){background-color: #f2f2f2;}

            .table tr:hover {background-color: #ddd;}

            .table th {
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: left;
                background-color: #4CAF50;
                color: white;
            }
            h4 {
                background-color: #4CAF50;
                padding-top: 12px;
                padding-bottom: 12px;
                color: white;
                text-align: left;
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
            }
            .loader {
                border: 5px solid #eaeaea;
                border-radius: 50%;
                border-top: 5px solid #57b846;
                width: 20px;
                height: 20px;
                -webkit-animation: spin 2s linear infinite;
                animation: spin 2s linear infinite;
                margin: 0px auto;
                display: inline-block;
                vertical-align: middle;
                justify-content: center;
        }
        </style>
        <div id="message"></div>
        <div id="dispAllEventIds">
          <span class="deleteLoader" style="display:none"><span class="loader" ></span></span>
        </div><br/>
        <div id="dispAllCompanyIds">
          <span class="deleteLoader" style="display:none"><span class="loader" ></span></span>
        </div><br/>
        <?php require "start.php"; ?>
        <script src="https://inmotion-app.iplatformsolutions.com/public/scripts/jquery.cookie.js"></script>
        <script>
        var url = 'https://inmotion-app.iplatformsolutions.com/api/validateToken.php'; 
        var proxy = 'https://cors-anywhere.herokuapp.com/';
        var tokenfinalurl = proxy+url;

        var datasetting = {
                "url": tokenfinalurl,
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                    'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?>'
                }
            };
            $.ajax(datasetting).done(function (response) {
                return false;
            }).fail(function(response){
                var url = 'https://inmotion-app.iplatformsolutions.com/api/generateToken.php'; 
                var proxy = 'https://cors-anywhere.herokuapp.com/';
                var finalurl = proxy+url;
                $.ajax({
                       type: "post",
                       dataType: "json",
                       headers: {
                         'Accept': 'application/json',
                         'Content-Type': 'application/json'
                       },
                       data: JSON.stringify({ 
                         "email" : '<?php echo $_ENV['USER_NAME'] ?>',
                         "password" : '<?php echo $_ENV['PASSWORD'] ?>'
                       }),
                       url: url,
                       success: function(data, result) {
                         if(data.status ==200){
                          $.cookie('token',data.jwt);
                          $.cookie('firstname',data.firstname);
                          $.cookie('userId',data.userId);
                          $.cookie('expireAt',data.expireAt);
                        }else{
                          $(".error").html(data.error)
                        }
                      },
                      error: function(data, result) {
                        console.log(data.status);
                      }
                });
            });
        
        var url = 'https://inmotion-app.iplatformsolutions.com/api/getEventSelectOption.php'; 
        var proxy = 'https://cors-anywhere.herokuapp.com/';
        var eventurl = proxy+url;
        $.ajax({
              url: eventurl,
              cache:false,
              beforeSend:function(){
                $('.deleteLoader').show();
              },
              success: function(data) {
                $('.deleteLoader').hide();
                if(data=="false"){
                    $('#message').html('<div class="errorMessage errormsgWrapperDi">There are no events to display.</div>');
                }else{
                    $("#dispAllEventIds").html(data);
                    var url = 'https://inmotion-app.iplatformsolutions.com/api/getCompanySelectOption.php'; 
                    var proxy = 'https://cors-anywhere.herokuapp.com/';
                    var companyurl = proxy+url;
                    $.ajax({
                          url: companyurl,
                          cache:false,
                          beforeSend:function(){
                            $('.deleteLoader').show();
                          },
                          success: function(data) {
                            $('.deleteLoader').hide();
                            if(data=="false"){
                                $('#message').html('<div class="errorMessage errormsgWrapperDi">There are no events to display.</div>');
                            }else{
                                $("#dispAllCompanyIds").html(data);
                            }
                          },
                          error: function(data) {
                          }
                    });
                }
              },
              error: function(data) {
              }
        });

        

        $(document).on('change','#eventdeletion',function(e){
            // Preventing form to submit
            e.preventDefault();
            $('#appointments tbody').html('');
            $('#floormanager tbody').html('');
            var selectedEventId = $( "#eventdeletion option:selected" ).text();
            var data = JSON.stringify({
              event_id: selectedEventId
            });
            var url = 'https://inmotion-app.iplatformsolutions.com/api/getEventCompanys.php'; 
            var proxy = 'https://cors-anywhere.herokuapp.com/';
            var eventcompanyurl = proxy+url;
            var settings = {
                "url": eventcompanyurl,
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
            var optionHtml = '';
            var appointmentHtml = '';
            var floorHtml = '';
            $.ajax(settings).done(function (response) {
                optionHtml += '<select id="companyselection" name="companyselection">';
                $.each(response, function (index, value) {
                    optionHtml += '<option>' + value.option_value + '</option>'; 
                });
            $('#dispAllCompanyIds').html(optionHtml); 
            var selectedCompanyId = $( "#companyselection option:selected" ).text();
            var selecteddata = JSON.stringify({
              event_id: selectedEventId,
              company_id: selectedCompanyId
            });
            var url = 'https://inmotion-app.iplatformsolutions.com/api/getAppointments.php'; 
            var proxy = 'https://cors-anywhere.herokuapp.com/';
            var finalurl = proxy+url;
            var datasetting = {
                "url": finalurl,
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                    'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
                },
                "data":selecteddata
            };
            $.ajax(datasetting).done(function (response) {
                $.each(response, function (index, value) {
                if(value.day != null && value.day != ""){
                    appointmentHtml += 'Your Re-Sign Appointment is on <b>' + value.day + ' </b>at <b>' + value.time+ ' </b><br/>';
                }else{
                    appointmentHtml = '<span>No Appointments found.</span>'; 
                }
                });
                $("#appointments").show();
                $('#appointments').html(appointmentHtml);
            }).fail(function(response){
                appointmentHtml = '<span>No Appointments found.</span>'; 
                $('#appointments').html(appointmentHtml);
            });
            var url = 'https://inmotion-app.iplatformsolutions.com/api/getFloorManagerDetails.php'; 
            var proxy = 'https://cors-anywhere.herokuapp.com/';
            var finalurl = proxy+url;
            var datasettings = {
                "url": finalurl,
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                    'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
                },
                "data":selecteddata
            };
            $.ajax(datasettings).done(function (response) {
                $.each(response, function (index, value) {
                floorHtml += 'The contact person details for your booth no: <b>' + value.booth + '</b> at <b>' + value.hall + '</b> hall is Floor Manager: <b> ' + value.fm_name + ' </b>Phone: <b>' + value.fm_phone + ' </b>Text Number: <b> ' + value.fm_text_number + ' </b>GES ESE: <b>' + value.ges_ese+ '</b><br/>';
            });
            $("#floormanager").show();
            $('#floormanager').html(floorHtml);
            }).fail(function(response){
                floorHtml = '<span>No Booth details and Floor Manager details found.</span>'; 
                $('#floormanager').html(floorHtml);
            });
            });
        });

        $(document).on('change','#companyselection',function(e){
            // Preventing form to submit
            e.preventDefault();
            $('#appointments tbody').html('');
            $('#floormanager tbody').html('');
            var selectedEventId = $( "#eventdeletion option:selected" ).text();
            var html = '';
            var appointmentHtml = '';
            var floorHtml = '';
            var selectedCompanyId = $( "#companyselection option:selected" ).text();
            var selecteddata = JSON.stringify({
                  event_id: selectedEventId,
                  company_id: selectedCompanyId
                });
            var url = 'https://inmotion-app.iplatformsolutions.com/api/getAppointments.php'; 
            var proxy = 'https://cors-anywhere.herokuapp.com/';
            var finalurl = proxy+url;
            var datasetting = {
                "url": finalurl,
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                    'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
                },
                "data":selecteddata
            };
            $.ajax(datasetting).done(function (response) {
                $.each(response, function (index, value) {
                if(value.day != null && value.day != ""){
                    appointmentHtml += 'Your Re-Sign Appointment is on <b>' + value.day + ' </b>at <b>' + value.time+ ' </b><br/>';
                }else{
                    appointmentHtml = '<span>No Appointments found.</span>'; 
                }
                });
                $("#appointments").show();
                $('#appointments').html(appointmentHtml);
            }).fail(function(response){
                appointmentHtml = '<span>No Appointments found.</span>'; 
                $('#appointments').html(appointmentHtml);
            });
            var url = 'https://inmotion-app.iplatformsolutions.com/api/getFloorManagerDetails.php'; 
            var proxy = 'https://cors-anywhere.herokuapp.com/';
            var finalurl = proxy+url;
            var datasettings = {
                "url": finalurl,
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                    'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
                },
                "data":selecteddata
            };
            $.ajax(datasettings).done(function (response) {
                $.each(response, function (index, value) {
                floorHtml += 'The contact person details for your booth no: <b>' + value.booth + '</b> at <b>' + value.hall + '</b> hall is Floor Manager: <b> ' + value.fm_name + ' </b>Phone: <b>' + value.fm_phone + ' </b>Text Number: <b> ' + value.fm_text_number + ' </b>GES ESE: <b>' + value.ges_ese+ '</b><br/>';
            });
            $("#floormanager").show();
            $('#floormanager').html(floorHtml);
            }).fail(function(response){
                floorHtml = '<span>No Booth details and Floor Manager details found.</span>'; 
                $('#floormanager').html(floorHtml);
            });
        });
        </script>
        <div>
            <div><h4>Re-Sign Appointment</h4><div id="appointments"></div></div>
            <div><h4>Floor Manager Details</h4><div id="floormanager"></div></div>
        </div>
    </body>
</html>
