
/****************************************************************************
 *	User Functions
 ****************************************************************************/

function login( event )
{
	if ( event.target )
	{
		var username = GetFormValue( event.target, "email"    );
		var password = GetFormValue( event.target, "password" );
		
		pagecentric.api.login( username, password, null );
	}
	else
	{
		alert( "Could not retrieve credentials from form!" );
	}
}

function session( event )
{
	if ( event.target )
	{
		var username = GetFormValue( event.target, "email"    );
		var password = GetFormValue( event.target, "password" );
		
		pagecentric.api.login( username, password, null );
	}
	else
	{
		alert( "Could not retrieve credentials from form!" );
	}
}

function logout( event )
{
	pagecentric.api.logout( null );
}
