(function($){"use strict";var input=$('.validate-input .input100');$('.validate-form').on('submit',function(){var check=true;for(var i=0;i<input.length;i++){if(validate(input[i])==false){showValidate(input[i]);check=false;}}
return check;});$('.validate-form .input100').each(function(){$(this).focus(function(){hideValidate(this);});});function validate(input){if($(input).attr('type')=='email'||$(input).attr('name')=='email'){if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/)==null){return false;}}
else{if($(input).val().trim()==''){return false;}}}
function showValidate(input){var thisAlert=$(input).parent();$(thisAlert).addClass('alert-validate');}
function hideValidate(input){var thisAlert=$(input).parent();$(thisAlert).removeClass('alert-validate');}
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
	    	window.location = 'importData.php';
	    }else{
	    	$(".error").html(data.error)
	    }
	},
	error: function(data, result) {
		console.log(data.status);
	}
	});


})

})(jQuery);
