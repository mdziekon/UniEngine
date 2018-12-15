var $Elements = {MsgSpace: null, MsgBox: null};

function showMsgBox(Text, Color)
{
	if($Elements.MsgSpace.css('opacity') == 0)
	{
		$Elements.MsgSpace.animate({opacity: 1}, 250);
	}
	$Elements.MsgBox.html(Text);
	if(typeof Color == 'undefined')
	{
		Color = '';
	}
	$Elements.MsgBox.css('color', Color);
}
function hideMsgBox()
{
	if($Elements.MsgSpace.css('opacity') == 1)
	{
		$Elements.MsgSpace.animate({opacity: 0}, 250);
	}
	$Elements.MsgBox.html('&nbsp;');
}

$.support.cors = true;

$(document).ready(function()
{	
	$Elements.MsgSpace = $('#MsgSpace');
	$Elements.MsgBox = $('#MsgBox');
	
	var $UniInfo_Holder = $('#UniInfo_Holder');
	
	$('#uniSel')
	.change(function()
	{
		NewPos = -$('#'+$(this).children('option:selected').attr('id').replace('UniSelector_', 'UniInfo_')).position().left;
		OldPos = parseInt($UniInfo_Holder.css('left'), 10);
		if(NewPos != OldPos)
		{
			$UniInfo_Holder.animate({left: NewPos}, 500);
		}
	})
	.keyup(function()
	{
		$(this).change();
	});
	
	$('#username, #password, #email, #galaxy')
	.change(function()
	{
		if(!$(this).hasClass('BadInput'))
		{
			return;
		}
		$(this).removeClass('BadInput');
	})
	.keyup(function()
	{
		$(this).change();
	})
	.keydown(function()
	{
		$(this).change();
	})
	.focus(function()
	{
		$(this).change();
	});

	$('#form').submit(function()
	{		
		$('#submitForm').prop('disabled', true);
		$('#username, #password, #email, #galaxy').removeClass('BadInput');
		
		var ErrorFound = false;
		
		if($('#username').val().length < 4)
		{
			showMsgBox(JSLang['Alert_UsernameShort'], 'red');
			$('#username').addClass('BadInput');
			ErrorFound = true;
		}
		else if($('#username').val().length > 64)
		{
			showMsgBox(JSLang['Alert_UsernameLong'], 'red');
			$('#username').addClass('BadInput');
			ErrorFound = true;
		}
		else if($('#password').val().length < 4)
		{
			showMsgBox(JSLang['Alert_PasswordShort'], 'red');
			$('#password').addClass('BadInput');
			ErrorFound = true;
		}
		else if($('#email').val().length <= 0)
		{
			showMsgBox(JSLang['Alert_NoEmail'], 'red');
			$('#email').addClass('BadInput');
			ErrorFound = true;
		}
		else if($('#galaxy').val().replace(/[^0-9]{1,}/gi, '').length <= 0)
		{
			showMsgBox(JSLang['Alert_NoGalaxy'], 'red');
			$('#galaxy').addClass('BadInput');
			ErrorFound = true;
		}
		else if($('#rules').is(':checked') == false)
		{
			showMsgBox(JSLang['Alert_RulesNotAccepted'], 'red');
			ErrorFound = true;
		}
		else if($('#recaptcha_response_field').length > 0 && $('#recaptcha_response_field').val().length <= 0)
		{
			showMsgBox(JSLang['Alert_CaptchaNotWriten'], 'red');
			ErrorFound = true;
		}
		
		if(ErrorFound === true)
		{
			$('#submitForm').prop('disabled', false);
			return false;
		}
		
		$('body').css('cursor', 'wait');
		showMsgBox(JSLang['Info_RequestSent']);
		
		$.ajax(
		{
			url: $('#uniSel').val(),
			data: $(this).serialize(),
			dataType: 'jsonp',
			jsonp: 'callback',
			jsonpCallback: 'regCallback'
		})
		.complete(function(ThisResponse, ThisType)
		{			
			if(ThisType != 'success')
			{
				$('#submitForm').prop('disabled', false);
				$('body').css('cursor', '');
				if(ThisType == 'timeout')
				{
					showMsgBox(JSLang['Alert_RequestTimeout'], 'red');
				}
				else
				{
					showMsgBox(JSLang['Alert_RequestError'], 'red');
				}
			}
		});
		
		return false;
	});

	$('#prevUni').click(function()
	{
		el = $('#uniSel');
		if(el.children().length > 1)
		{
			if(!el.children(':first-child').is(':selected'))
			{
				el.children(':selected').prev().attr('selected', true);
			}
			$('#uniSel').change();
		}
	});
	$('#nextUni').click(function()
	{
		el = $('#uniSel');
		if(el.children().length > 1)
		{
			if(!el.children(':last-child').is(':selected'))
			{
				el.children(':selected').next().attr('selected', true);
			}
			$('#uniSel').change();
		}
	});
	
	$UniInfo_Holder.animate({left: -$('#'+$('#uniSel > option:selected').attr('id').replace('UniSelector_', 'UniInfo_')).position().left}, 0);
});

function regCallback(ResponseObject)
{
	// Handle Errors
	if(typeof ResponseObject.Errors != 'undefined')
	{
		$('#submitForm').prop('disabled', false);
		for(var ErrorID in ResponseObject.Errors)
		{
			ResponseObject.Errors[ErrorID] = JSLang['Alert_RequestCode_'+ResponseObject.Errors[ErrorID]];
		}
		showMsgBox(ResponseObject.Errors.join('<br/>'), 'red');
		if($('#recaptcha_response_field').length > 0)
		{
		Recaptcha.reload();
		}
	}
	if(typeof ResponseObject.BadFields != 'undefined')
	{
		for(var FieldID in ResponseObject.BadFields)
		{
			$('#'+ResponseObject.BadFields[FieldID]).addClass('BadInput');
		}
	}
		
	if(typeof ResponseObject.Cookie != 'undefined')
	{
		for(var CookieID in ResponseObject.Cookie)
		{
			$.cookie(ResponseObject.Cookie[CookieID].Name, ResponseObject.Cookie[CookieID].Value, { domain: '.172.19.0.3'});
		}
	}
		
	if(typeof ResponseObject.Code != 'undefined')
	{
		showMsgBox(JSLang['Info_RequestCode_'+ResponseObject.Code], 'lime');
		if(typeof ResponseObject.Redirect != 'undefined')
		{
			window.location.href = ResponseObject.Redirect;
		}
	}
				
	$('body').css('cursor', '');
}