
pagecentric.fileupdate
=
function ()
{
	var grandparent = this.parentNode.parentNode;
	var target      = this.getAttribute( "data-target" );
	var inputs      = grandparent.getElementsByTagName( "INPUT" );
	var n           = inputs.length;
	
	for ( var i=0; i < n; i++ )
	{
		var id = inputs[i].getAttribute( "data-id" );
	
		if ( target == id )
		{
			inputs[i].value = this.value;
		}
	}
}

pagecentric.fileupdateSetup
=
function ()
{
	var elements = document.getElementsByTagName( 'INPUT' );
	if ( elements )
	{
		var n = elements.length;

		for ( var i=0; i < n; i++ )
		{
			var value = elements[i].getAttribute( 'data-action' );

			if ( 'fileupdate' == value )
			{
				elements[i].fileupdate = pagecentric.fileupdate;
			}
		}
	}
}
