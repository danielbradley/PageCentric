
//-----------------------------------------------------------------------------
//	Popup
//-----------------------------------------------------------------------------

pagecentric.setup.popup
=
function ()
{
//	pagecentric.setup.popup.foreach( document.getElementsByTagName( 'input'    ) );
//	pagecentric.setup.popup.foreach( document.getElementsByTagName( 'select'   ) );
//	pagecentric.setup.popup.foreach( document.getElementsByTagName( 'textarea' ) );
	pagecentric.setup.popup.foreach( document.getElementsByTagName( 'div'      ) );
}

pagecentric.setup.popup.foreach
=
function ( elements )
{
	if ( elements )
	{
		var n = elements.length;

		for ( var i=0; i < n; i++ )
		{
			var popup = elements[i].getAttribute( 'data-popup' );
		
			if ( popup )
			{
				elements[i].onmouseover = pagecentric.popup.show;
				elements[i].onmousemove = pagecentric.popup.move;
				elements[i].onmouseout  = pagecentric.popup.hide;
				elements[i].onmousedown = pagecentric.popup.hide;
			}
		}
	}
}

pagecentric.popup = {}

pagecentric.popup.show
=
function ( event )
{
	var self    = pagecentric.self( this );
	var content = self.getAttribute( "data-popup" );

	if ( content )
	{
		var div = document.getElementById( "popup" );
		{
			div.style.display = "none";
			div.innerHTML = content;
			div.style.display = "block";

			document.body.style.cursor = 'help';
		}
	}
}

pagecentric.popup.move
=
function ( event )
{
	var event = event ? event : window.event;

	var offset = 20;

	var sx     = pagecentric.scrollOffsetX();
	var sy     = pagecentric.scrollOffsetY();

	var x      = sx + event.clientX;
	var y      = sy + event.clientY;

	var div    = document.getElementById( "popup" );

	if ( div )
	{
		//	Flip to left side of arrow if beyond 700 pixels.

		if ( x > 700 )
		{
			x -= (offset + div.clientWidth);
			div.style.left = x + "px";
		}
		else
		{
			x += offset;
			div.style.left = x + "px";
		}
		div.style.top  = y + "px";
	}
}

pagecentric.popup.hide
=
function ( event )
{
	var div = document.getElementById( "popup" );
	{
		div.style.display = "none";
	}

	document.body.style.cursor = 'default';
}
