
function clicksubmit()
{
	alert( document.domain );

	this.submit();
}

function setup_clicksubmit()
{
	var elements = document.getElementsByTagName( 'form' );
	var n        = elements.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'clicksubmit' == elements[i].getAttribute( 'data-action' ) )
		{
			elements[i].onsubmit = clicksubmit;
		}
	}
}

//setup_clickchange();
