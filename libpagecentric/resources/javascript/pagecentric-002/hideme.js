
pagecentric.hidemeSetup
=
function()
{
	var divs = document.getElementsByTagName( 'DIV' );
	if ( divs )
	{
		var len = divs.length;
		for ( var i=0; i < len; i++ )
		{
			if ( "hideme" == divs[i].getAttribute( "data-action" ) )
			{
				divs[i].style.display = "none";
			}
		}
	}
}