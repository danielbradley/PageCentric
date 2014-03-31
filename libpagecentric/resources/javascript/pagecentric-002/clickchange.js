
function clickchange()
{
	var html = this.getAttribute( "data-change" );
	
	this.innerHTML = html;
}

function setup_clickchange()
{
	var inputs = document.getElementsByTagName( 'a' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'clickchange' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onclick = clickchange;
		}
	}
}

//setup_clickchange();
