
function checklimit()
{
	var self = pagecentric.self( this );

	if ( self.checked )
	{
		var max    = 3;
		var count  = 0;
		var name   = self.name;
		var group  = self.getAttribute( 'data-group' );
		var inputs = document.getElementsByTagName( 'input' );
		var n      = inputs.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( group == inputs[i].getAttribute( 'data-group' ) )
			{
				if ( inputs[i].checked ) count++;
			}
		
			if ( count > max )
			{
				alert( "You may only select " + max + " items per group." );
				self.checked = false;
				break;
			}
		}
	}
}

function setup_checklimit()
{
	var inputs = document.getElementsByTagName( 'input' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'checklimit' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onchange = checklimit;
		}
	}
}

//setup_checklimit();
