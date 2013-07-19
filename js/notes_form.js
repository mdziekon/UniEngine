$(document).ready(function()
{
	var noteLength = $('#noteLength');
	$('#goBack').click(function()
	{
		document.location.href = '?';
	});
	
	$('textarea[name="text"]')
	.keyup(function()
	{
		Length = $(this).val().length;
		if(Length > MaxNoteLength)
		{
			$(this).val($(this).val().substr(0, MaxNoteLength));
			Length = MaxNoteLength;
		}		
		noteLength.html(Length);
	})
	.keydown(function(){ $(this).keyup(); })
	.change(function(){ $(this).keyup(); });
	
	$('textarea[name="text"]').keyup();
});