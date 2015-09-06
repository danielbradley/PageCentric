Session
=
function()
{
	Call( Resolve_API_Server() + "/api/users/sessions/current/", {}, Session.Handler );
}

Session.Handler
=
function ( responseText )
{
	var idtype = "";

	if ( -1 != responseText.indexOf( "UNAUTHENTICATED" ) )
	{
		Session.status = "UNAUTHENTICATED";
	}
	else
	if ( -1 != responseText.indexOf( "INVALID_SESSION" ) )
	{
		Session.status = "INVALID_SESSION";
	}
	else
	if ( "" != responseText )
	{
		var obj = JSON.parse( responseText );

		if ( obj && obj.results && (1 == obj.results.length) )
		{
			var tuple = obj.results[0];
		
			Session.USER        = tuple.USER;
			Session.email       = tuple.email;
			Session.sessionid   = tuple.sessionid;
			Session.idtype      = tuple.idtype;
			Session.given_name  = tuple.given_name;
			Session.family_name = tuple.family_name;
			Session.user_hash   = tuple.user_hash;
			Session.read_only   = tuple.read_only;
			Session.status      = "AUTHENTICATED";

			idtype = Session.idtype;
		}
		else
		if ( obj && obj.error )
		{
			Session.status      = obj["error"];
		}
	}

	Redirect( idtype );
}

