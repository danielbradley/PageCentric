
function hideparent()
{
	if ( this.parentNode ) this.parentNode.style.display = "none";
}

function setup_hideparent()
{
	var inputs = document.getElementsByTagName( 'button' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'hideparent' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onclick = hideparent;
		}
	}
}

//setup_hideparent();
