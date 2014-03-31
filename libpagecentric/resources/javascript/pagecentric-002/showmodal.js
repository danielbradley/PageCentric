
//-----------------------------------------------------------------------------
//	showmodal
//-----------------------------------------------------------------------------

pagecentric.setup.showmodal
=
function ()
{
	pagecentric.setup.showmodal.foreach( document.getElementsByTagName( 'a' ) );
}

pagecentric.setup.showmodal.foreach
=
function( elements )
{
	if ( elements )
	{
		var n = elements.length;

		for ( var i=0; i < n; i++ )
		{
			var value = elements[i].getAttribute( 'data-toggle' );

			if ( 'modal' == value )
			{
				//pagecentric.addEventListener( elements[i], "click", showmodal );
				elements[i].onclick = pagecentric.showmodal;
			}
		}
	}
}

pagecentric.showmodal
=
function ( event )
{
	pagecentric.stopPropagation( event );

	var href = this.href;
	var i    = href.indexOf( '#' );

	if ( -1 != i )
	{
		var tag  = href.substr( i + 1 );

		pagecentric.showmodal.byId( tag );

		pagecentric.preventDefault( event );
		return false;
	}
	else
	{
		return true;
	}
}

pagecentric.showmodal.byId
=
function ( id )
{
	pagecentric.showmodal.closeAllModals();

	var modal = document.getElementById( id );
	if ( modal )
	{
		pagecentric.addClass( document.body, "show-modal" );
	
		modal.style.display = "block";
	}
}

pagecentric.showmodal.closeAllModals
=
function()
{
	var divs = document.getElementsByTagName( 'div' );
	var n    = divs.length;
	
	for ( var i=0; i < n; i++ )
	{
		var cls = divs[i].getAttribute( 'data-class' );
		
		if ( 'modal' == cls )
		{
			divs[i].style.display = "none";
		}
	}
	pagecentric.removeClass( document.body, "show-modal" );
}

pagecentric.showmodal.show
=
function ()
{
	var bodies = document.getElementsByTagName( 'body' );
	var id     = bodies[0].getAttribute( 'data-show' );
	
	if ( id )
	{
		var modal = document.getElementById( id );
		if ( modal )
		{
			pagecentric.showmodal.byId( id );
		
//			pagecentric.showmodal.closeAllModals();
//			document.body.className = document.body.className + " show-modal";
//			modal.style.display     = "block";
		}
	}
	pagecentric.scrollByOffset();
}

//function setExistingBodyClass()
//{
//	var browser = document.body.getAttribute( "data-browser" );
//	if ( browser ) document.body.className = browser;
//}




