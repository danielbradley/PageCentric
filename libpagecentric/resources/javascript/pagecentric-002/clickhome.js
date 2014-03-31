
function clickhome()
{
	document.location.href = "/";
}

function setup_clickhome()
{
	var inputs = document.getElementsByTagName( 'a' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'clickhome' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onclick = clickhome;
		}
	}
}

//setup_clickhome();
