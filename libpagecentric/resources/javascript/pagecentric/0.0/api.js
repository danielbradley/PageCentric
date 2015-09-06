
function DefaultHandler()
{
	alert( "No default handler" );
}

function XCall( location, parameters, handler )
{
	var command = EncodeToString( parameters );

	Ajax_Post( location, command, handler, DefaultHandler );
}

function Ajax_Post( location, command, handler, default_handler, failover )
{
	failover = failover ? failover : false;

	var url              = Resolve_API_Server( failover ) + location;
	var response_handler = (handler) ? handler : default_handler;
	
	var httpRequest = new XMLHttpRequest();
		httpRequest.open( "POST", url, true );
		httpRequest.withCredentials = true;
		httpRequest.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
		httpRequest.onreadystatechange
		=
		function()
		{
			switch ( httpRequest.readyState )
			{
			case 0:
			case 1:
			case 2:
			case 3:
				break;
				
			case 4:
				if ( 200 == httpRequest.status )
				{
					console.log( "Posted to: " + url );
				
					response_handler( httpRequest.responseText );
				}
				else
				if ( failover )
				{
					alert( "Could not connect to API server." );
				}
				else
				{
					Ajax_Post( location, command, handler, default_handler, true );
				}
				break;
				
			default:
				console.log( "Unexpected httpRequest ready state" );
			}
		}
        httpRequest.send( command );
}

function Ajax_Call( location, command )
{
	var url = Resolve_API_Server() + location;
	
	var httpRequest = new XMLHttpRequest();
		httpRequest.open( "POST", url, false );
		httpRequest.withCredentials = true;
		httpRequest.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
        httpRequest.send( command );

	return httpRequest.responseText;
}

function Sync_Call( location, command )
{
	var response = "";
	var url = location;
	
	var httpRequest = new XMLHttpRequest();
		httpRequest.open( "POST", url, false );
		httpRequest.withCredentials = true;
		httpRequest.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );

	try
	{
		httpRequest.send( command );

		response = httpRequest.responseText;
	}
	catch ( exception )
	{
		console.log( "Could not get service list from: " + location );
	}

	return response;
}

function EncodeToString( parameters )
{
	var string = "";
	var sep    = "";

	for ( member in parameters )
	{
		if ( "" != member )
		{
			string += sep;
			string += member;
			string += "=";
			string += parameters[member];

			sep = "&";
		}
	}
	return string;
}

function GetDocumentValue( id )
{
	var ret     = "";
	var element = document.getElementById( id );

	if ( element )
	{
		ret = element.value;
	}
	return ret;
}

function GetFormValue( self, name )
{
	var value = "";
	{
		var element  = null;
		var index    = 0;
		var elements = document.getElementsByName( name );
		var n        = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( ! elements[i].disabled )
			{
				if ( IsAncestorOf( self, elements[i] ) )
				{
					element = elements[i];
				
					switch ( element.type )
					{
					case "select-one":
						index = element.options.selectedIndex;
						if ( index )
						{
							var object = element.options.item(index);
							if ( object )
							{
								value = encodeURIComponent( object.value );
							}
						}
						break;
						
					case "radio":
						if ( element.checked ) value = encodeURIComponent( element.value );
						break;
					
					default:
						value = element.value ? encodeURIComponent( element.value ) : "";
					}
					i = n; // Terminate loop.
				}
			}
		}
	}
	return value;
}

function GetSearchValue( name )
{
    var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		match = match && decodeURIComponent(match[1].replace(/\+/g, ' '));
		return match ? match : "";
}

function GetSearchValues()
{
	var object = new Object;
	
	var bits = window.location.search.substring( 1 ).split( "&" );
	var n    = bits.length;
	
	for ( var i=0; i < n; i++ )
	{
		var keyvalue = bits[i].split( "=" );
		var key      = keyvalue[0];
		var value    = keyvalue[1];
	
		object[key] = value;
	}
	return object;
}

//function GetFormValues( form )
//{
//	var object = new Object;
//	var n      = form.elements.length;
//
//	for ( var i=0; i < n; i++ )
//	{
//		var key   = form.elements[i].name;
//		var value = form.elements[i].value;
//		
//		if ( key && value )
//		{
//			object[key] = value;
//		}
//	}
//	return object;
//}

function IsAncestorOf( self, element )
{
	while ( element.parentNode && (self != element.parentNode) )
	{
		element = element.parentNode;
	}

	return (self == element.parentNode);
}

function ReplaceContents( tagname, action, value )
{
	var elements = document.getElementsByTagName( tagname );
	var n        = elements.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( action == elements[i].getAttribute( "data-action" ) )
		{
			elements[i].innerText = value;
		}
	}
}

function ReplaceValue( tagname, name, value )
{
	var elements = document.getElementsByTagName( tagname );
	var n        = elements.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( name == elements[i].name )
		{
			elements[i].value = value;
		}
	}
}

function CallHandler( preferred, def )
{
	if ( preferred )
	{
		preferred( "{}" );
	}
	else
	{
		def( "{}" );
	}
}

/****************************************************************************
 *	Page Centric
 ****************************************************************************/

pagecentric                  = (typeof(pagecentric) !== 'undefined') ? pagecentric : {};
pagecentric.api              = {};
pagecentric.api.handlers     = {};
pagecentric.session          = {};
pagecentric.session.handlers = {};

/****************************************************************************
 *	Page Centric Default Handlers
 ****************************************************************************/

pagecentric.api.handlers.reload
=
function ( responseText )
{
	location.reload();
}

pagecentric.api.handlers.referrer
=
function ( responseText )
{
	location.assign( document.referrer );
}

/****************************************************************************
 *	Signup
 ****************************************************************************/

pagecentric.api.signup
=
function ( email, password, name, type, handler )
{
	var action   = "users_create";
	var command  = "action="           + action;
		command += "&user_type="       + type;
		command += "&email="           + email;
		command += "&password="        + password;
		command += "&name="            + name;

	Ajax_Post( command, handler, pagecentric.api.handlers.signup );

	return false;
}

pagecentric.api.handlers.signup
=
function ( responseText )
{
	if ( "AUTHENTICATED" == responseText )
	{
		window.location = "/auth_redirect/";
	}
}

/****************************************************************************
 *	Login
 ****************************************************************************/

pagecentric.api.login
=
function ( username, password, handler )
{
	var action   = "users_login";

	var command  = "username="  + username;
		command += "&password=" + password;

	Ajax_Post( "/auth/login/", command, handler, pagecentric.api.handlers.login );
	
	return false;
}

pagecentric.api.handlers.login
=
function ( responseText )
{
	if ( "" != responseText )
	{
		var obj = JSON.parse( responseText );
		if ( obj && obj.idtype )
		{
			var redirect_to = "/" + obj.idtype.toLowerCase() + "/";
		
			window.location = redirect_to;
		}
		else
		{
			
		}
	}
}

/****************************************************************************
 *	Session
 ****************************************************************************/

pagecentric.session.init
=
function ( handler )
{
	var command      = "";
	
	Ajax_Post( "/auth/session/", command, handler, pagecentric.session.handlers.init );
}

pagecentric.session.handlers.init
=
function ( responseText )
{
	var idtype = "";

	if ( -1 != responseText.indexOf( "UNAUTHENTICATED" ) )
	{
		pagecentric.session.status = "UNAUTHENTICATED";
	}
	else
	if ( -1 != responseText.indexOf( "INVALID_SESSION" ) )
	{
		pagecentric.session.status = "INVALID_SESSION";
	}
	else
	if ( "" != responseText )
	{
		var obj = JSON.parse( responseText );
		if ( obj && obj.sessionid )
		{
			pagecentric.session.USER        = obj.USER;
			pagecentric.session.email       = obj.email;
			pagecentric.session.sessionid   = obj.sessionid;
			pagecentric.session.idtype      = obj.idtype;
			pagecentric.session.given_name  = obj.given_name;
			pagecentric.session.family_name = obj.family_name;
			pagecentric.session.user_hash   = obj.user_hash;
			pagecentric.session.read_only   = obj.read_only;
			pagecentric.session.status      = "AUTHENTICATED";

			idtype = pagecentric.session.idtype;
		}
		else
		if ( obj && obj.error )
		{
			pagecentric.session.status      = obj["error"];
		}
	}

	Redirect( idtype );
}

pagecentric.session.is_valid
=
function ()
{
	return ( "AUTHENTICATED" == pagecentric.session.status );
}

/****************************************************************************
 *	USER
 ****************************************************************************/

pagecentric.api.user
=
function ( handler )
{
	var command = "";

	Ajax_Post( "/auth/user/", command, handler, pagecentric.api.handlers.user );
}

pagecentric.api.handlers.user
=
function ( responseText )
{
	if ( "" != responseText )
	{
		ReplaceContents(  "span", "USER", responseText );
		ReplaceValue   ( "input", "USER", responseText );
	}
}

/****************************************************************************
 *	USERS
 ****************************************************************************/

pagecentric.api.users
=
function ( handler )
{
	var command = "";

	Ajax_Post( "/auth/users/", command, handler, pagecentric.api.handlers.users );
}

pagecentric.api.handlers.user
=
function ( responseText )
{
	alert( "No default handler for pagecentric.api.users" );
}

/****************************************************************************
 *	Is Authenticated
 ****************************************************************************/

pagecentric.api.is_authenticated
=
function ( handler )
{
	var command = "";

	Ajax_Post( "/auth/is_authenticated/", command, handler, pagecentric.api.handlers.is_authenticated );
}

pagecentric.api.handlers.is_authenticated
=
function ( responseText )
{
	switch ( responseText )
	{
	case "YES":
		if ( "/" == location.pathname ) location.pathname = "/feed/";
		break;
		
	case "NO":
		if ( "/feed/" == location.pathname ) location.pathname = "/";
		break;
	}
}

/****************************************************************************
 *	logout
 ****************************************************************************/

pagecentric.api.logout
=
function ( handler )
{
	var command = "";

	Ajax_Post( "/auth/logout/", command, handler, pagecentric.api.handlers.logout );
}

pagecentric.api.handlers.logout
=
function ( responseText )
{
	location.pathname = "/";
}
