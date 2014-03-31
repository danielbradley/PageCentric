
pagecentric.scrollboard
=
function ()
{
	var target      = this.getAttribute( "data-target"    );
	var direction   = this.getAttribute( "data-direction" );

	var scrollboard = document.getElementById( target );
	if ( scrollboard )
	{
		var n              = pagecentric.countChildDivs( scrollboard );
		var current_offset = parseInt( scrollboard.className.substr( 13, 1 ) );

		if ( NaN != current_offset )
		{
			switch ( direction )
			{
			case "left":
				var next = (current_offset + n - 1) % n;
				break;
				
			case "right":
				var next = (current_offset + n + 1) % n;
				break;
			}
			
			var next_offset = "scroll_offset" + next;
			scrollboard.className  = next_offset;
		}
	}
	
	return false;
}

pagecentric.scrollboardSetup
=
function()
{
	var anchors = document.getElementsByTagName( 'a' );
	if ( anchors )
	{
		var len = anchors.length;
		for ( var i=0; i < len; i++ )
		{
			if ( "scrollboard" == anchors[i].getAttribute( "data-action" ) )
			{
				anchors[i].onclick = pagecentric.scrollboard;
			}
		}
	}
}