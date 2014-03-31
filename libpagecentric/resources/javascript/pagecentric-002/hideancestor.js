
pagecentric.hideancestor
=
function ()
{
	var group = this.getAttribute( "data-group" );
	if ( group )
	{
		var parent = this.parentNode;

		while ( parent )
		{
			if ( parent.getAttribute( "data-group" ) == group )
			{
				parent.style.display = "none";
				break;
			}
			else
			{
				parent = parent.parentNode;
			}
		}
	}
	return false;
}

pagecentric.hideancestorSetup
=
function ()
{
	var inputs = document.getElementsByTagName( 'a' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'hideancestor' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onclick = pagecentric.hideancestor;
		}
	}
}

//setup_hideparent();
