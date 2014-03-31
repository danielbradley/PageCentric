
pagecentric.submenu
=
function ( event )
{
	pagecentric.stopPropagation( event );

	var href    = this.getAttribute( "href" );
	var element = document.getElementById( href.substring( 1 ) );
	if ( element )
	{
		var li = element.parentNode;
		if ( li )
		{
			var rect = li.getBoundingClientRect();

			element.style.right   = rect.right;
			element.style.display = ( "block" == element.style.display ) ? "none" : "block";
		}
	}

	return false;
}

pagecentric.submenuSetup
=
function()
{
	var anchors = document.getElementsByTagName( 'a' );
	if ( anchors )
	{
		var len = anchors.length;
		for ( var i=0; i < len; i++ )
		{
			if ( "submenu" == anchors[i].getAttribute( "data-action" ) )
			{
				anchors[i].onclick = pagecentric.submenu;
			}
		}
	}
}
