
function scrolldownto()
{
	var d            = 20;
	var final_offset = scrolldownto_offset;
	var delta        = scrolldownto_delta;

	var next_offset = window.pageYOffset + d;

	if ( scrolldownto_delta < 0 )
	{
		clearInterval( scrolldownto_id );

		scrolldownto_delta  = 0;
		scrolldownto_offset = 0;
		scrolldownto_id     = 0;
	}
	else
	{
		delta -= d;

		var target = Math.min( next_offset, final_offset );

		scrollTo( pageXOffset, target );
	}
	
	scrolldownto_delta = delta;
}

function scrolldown( px )
{
	var page_offset    = window.pageYOffset;
	var height_content = document.height ? document.height : document.body.scrollHeight;
	var height_screen  = innerHeight;
	var limit          = (height_content - height_screen) - 1;
	
	var target         = Math.min( px, limit );

	scrolldownto_delta  = target;
	scrolldownto_offset = target;
	scrolldownto_id     = setInterval( scrolldownto, 1 );
}
