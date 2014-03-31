
function countWords( text )
{
	var w = 0;
	var n = text.length;
	var t = false;
	for ( var i=0; i < n; i++ )
	{
		switch ( text.charAt( i ) )
		{
		case ' ':
			t = false;
			break;
		case '\n':
			t = false;
			break;
		default:
			if ( !t ) { w++; t = true; }
		}
	}
	return w;
}

function limitwords()
{
    var name    = this.name;
    var max     = parseInt( this.getAttribute( "data-max" ) );
	var target  = this.getAttribute( "data-target" );
	var content = "";
    var w       = countWords( this.value );

	if ( w > max )
	{
		alert( "Sorry, there is a limit of " + max + " words" );

		var truncated = this.value;

		while ( countWords( truncated ) > max )
		{
			truncated = truncated.slice( 0, -1 );
		}
		this.value = truncated;
		w = countWords( this.value );
	}

    //	Update max words span
    {
        var span = document.getElementById( target );
        span.innerText = "Max " + max + " words (" + w + " words used)";
    }
}

function setup_limitwords()
{
	var elements = document.getElementsByTagName( 'textarea' );
	var n        = elements.length;

	for ( var i=0; i < n; i++ )
	{
		if ( "limitwords" == elements[i].getAttribute( "data-action" ) )
		{
			elements[i].onkeyup = limitwords;
		}
	}

//	var text_area1 = document.getElementById( 'field_objective' );
//	if ( text_area1 ) text_area1.onkeyup = limitWords;
//
//	var text_area2 = document.getElementById( 'field_past_and_present' );
//	if ( text_area2 ) text_area2.onkeyup = limitWords;
}

//setup_limitwords();
