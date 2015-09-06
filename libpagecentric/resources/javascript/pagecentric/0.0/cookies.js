pagecentric.cookies = {}

pagecentric.cookies.SetCookie
=
function( name, value, days )
{
	if ( 0 < days )
	{
		var d = new Date(); d.setTime( d.getTime() + (days * 24 * 60 * 60 * 1000) );
		var expires = "expires=" + d.toUTCString();

		document.cookie = name + "=" + value + "; path=/ " + expires;
	}
	else
	{
		document.cookie = name + "=" + value + "; path=/";
	}
}

pagecentric.cookies.GetCookie
=
function GetCookie( cname )
{
	var value = "";
    var name = cname + "=";
    var bits = document.cookie.split(';');

    for ( var i=0; i < bits.length; i++ )
	{
		var cookie = bits[i];
		
        if ( -1 != cookie.indexOf( name ) )
		{
			while ( ' ' == cookie.charAt( 0 ) ) cookie = cookie.substring( 1 );

			value = cookie.substring( name.length, cookie.length );
			break;
		}
    }
    return value;
}

pagecentric.cookies.HasCookie
=
function( name )
{
	return "" != pagecentric.cookies.GetCookie( name );
}
