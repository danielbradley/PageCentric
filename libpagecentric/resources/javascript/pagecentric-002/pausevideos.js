
function pausevideos()
{
	var elements = document.getElementsByTagName( 'video' );
	var n        = elements.length;

	for ( var i=0; i < n; i++ )
	{
		elements[i].pause();
	}
}

function setup_pausevideos()
{
	var elements = document.getElementsByTagName( 'a' );
	var n        = elements.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'pausevideos' == elements[i].getAttribute( 'data-action' ) )
		{
			elements[i].onmouseup = pausevideos;
		}
	}
}

//setup_closevideo();
