
function openpage_validate()
{
	var tmin = this.getAttribute( "data-min" );
	var tmax = this.getAttribute( "data-max" );
	
	var min  = (tmin) ? parseInt( tmin ) : 0;
	var max  = (tmax) ? parseInt( tmax ) : 1000000;
	
	if ( this.value )
	{
		var val  = parseInt( this.value );
		if ( val )
		{
			if ( (min <= val) && (val <= max) )
			{
				return true;
			}
			else
			{
				alert( "Sorry, that is an invalid value" );
				this.value = "";
				return false;
			}
		}
	}
	return true;
}

function setup_openpage_validate_foreach( elements )
{
	if ( elements )
	{
		var n = elements.length;

		for ( var i=0; i < n; i++ )
		{
			var value = elements[i].getAttribute( 'data-action' );

			if ( 'validate' == value )
			{
				elements[i].onchange = openpage_validate;
			}
		}
	}
}

function setup_validate()
{
	setup_openpage_validate_foreach( document.getElementsByTagName( 'input' ) );
}

//setup_validate();