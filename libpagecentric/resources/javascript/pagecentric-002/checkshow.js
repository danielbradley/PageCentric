
function checkshow()
{
	var max    = 3;
	var target = this.getAttribute( 'data-target' );
	var group  = this.getAttribute( 'data-group'  );

	var div    = document.getElementById( target );

	var inputs = document.getElementsByTagName( "input" );
	var n      = inputs.length;
	var count  = 0;

	for ( var i=0; i < n; i++ )
	{
		if ( group == inputs[i].getAttribute( "data-group" ) )
		{
			if ( inputs[i].checked ) count++;
		}
	}

	if ( count > max )
	{
		alert( "You may only select " + max + " " + group );
		this.checked = false;
	}
	else
	{
		if ( div )
		{
			if ( this.checked )
			{
				div.style.display = 'block';
			}
			else
			{
				div.style.display = 'none';
			}
		}
	}
}

function setup_checkshow()
{
	var inputs = document.getElementsByTagName( 'input' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'checkshow' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onchange = checkshow;
		}
	}
}

//setup_checkshow();
