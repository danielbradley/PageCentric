
pagecentric.clearElements
=
function( elements )
{
	if ( elements )
	{
		var n = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( "clickclear" == elements[i].getAttribute( "data-group" ) )
			{
				elements[i].style.display = "none";
			}
		}
	}
}

pagecentric.clickclear
=
function ()
{
	pagecentric.clearElements( document.getElementsByTagName( "UL"  ) );
	pagecentric.clearElements( document.getElementsByTagName( "DIV" ) );
	
	closeAllModals();

	return true;
}

pagecentric.clickclearSetup
=
function()
{
	var htmls = document.getElementsByTagName( "HTML" );

//	if ( htmls && htmls[0] )
//	{
//		htmls[0].onclick = pagecentric.clickclear;
//	}
}