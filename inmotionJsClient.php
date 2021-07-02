<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Connect to API</title>
        
    <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
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
        <?php require "./start.php"; ?>
        <script src="<?php echo $_ENV['SERVER_URL'] ?>/public/scripts/jquery.cookie.js"></script>
        <script>
        var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/validateToken.php'; 
        var proxy = 'https://cors-anywhere.herokuapp.com/';
        var tokenfinalurl = url;

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
            var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/generateToken.php';
            var finalurl = url;
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
                   url: finalurl,
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

        var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/dispEventOptions.php'; 
        var eventurl = url;
        var eventHtml = "";
        var eventsettings = {
            "url": eventurl,
            "method": "GET",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json",
                'Accept': 'application/json',
                'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
            },
            beforeSend:function(){
                $('.deleteLoader').show();
              }
        };

        $.ajax(eventsettings).done(function (response) {
            $('.deleteLoader').hide();
            eventHtml += '<select id="eventselection" name="eventselection">';
            eventHtml += '<option>Select Event</option>';
            $.each(response, function (index, value) {
                eventHtml += '<option>' + value.option_value + '</option>'; 
            });
            $('#dispAllEventIds').html(eventHtml);
        }).fail(function(response){
            $('.deleteLoader').hide();
            eventHtml = '<span>There are no events to display.</span>'; 
            $('#message').html(eventHtml);
        });
        var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/getCompanySelectOption.php';
        var companyurl = url;
        var companyHtml = "";
        var companysettings = {
            "url": companyurl,
            "method": "GET",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json",
                'Accept': 'application/json',
                'Authorization': 'Bearer <?php echo $_COOKIE['token'] ?> '
            },
            beforeSend:function(){
                $('.deleteLoader').show();
              }
        };

        $.ajax(companysettings).done(function (response) {
            $('.deleteLoader').hide();
            companyHtml += '<select id="companyselection" name="companyselection">';
            companyHtml += '<option>Select Company</option>';
            $.each(response, function (index, value) {
                companyHtml += '<option>' + value.option_value + '</option>'; 
            });
            $('#dispAllCompanyIds').html(companyHtml);
        }).fail(function(response){
            $('.deleteLoader').hide();
            companyHtml = '<span>There are no events to display.</span>'; 
            $('#message').html(companyHtml);
        });

        

        $(document).on('change','#eventselection',function(e){
            // Preventing form to submit
            e.preventDefault();
            $('#appointments tbody').html('');
            $('#floormanager tbody').html('');
            var selectedEventId = $( "#eventselection option:selected" ).text();
            var data = JSON.stringify({
              event_id: selectedEventId
            });
            var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/getEventCompanys.php'; 
            var eventcompanyurl = url;
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
            var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/getAppointments.php'; 
            var finalurl = url;
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
                if((value.day != null && value.day != "") && (value.time != null && value.time != "")){
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
            var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/getFloorManagerDetails.php'; 
            var finalurl = url;
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
            var selectedEventId = $( "#eventselection option:selected" ).text();
            var html = '';
            var appointmentHtml = '';
            var floorHtml = '';
            var selectedCompanyId = $( "#companyselection option:selected" ).text();
            var selecteddata = JSON.stringify({
                  event_id: selectedEventId,
                  company_id: selectedCompanyId
                });
            var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/getAppointments.php'; 
            var finalurl = url;
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
                if((value.day != null && value.day != "") && (value.time != null && value.time != "")){
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
            var url = '<?php echo $_ENV['SERVER_URL'] ?>/api/getFloorManagerDetails.php'; 
            var finalurl = url;
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
