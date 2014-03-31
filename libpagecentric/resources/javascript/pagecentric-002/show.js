
function show()
{
	var bodies = document.getElementsByTagName( 'body' );
	var id     = bodies[0].getAttribute( 'data-show' );
	
	if ( id )
	{
		//var tag = "#" + id;
		var modal = document.getElementById( id );
		if ( modal )
		{
			closeAllModals();
			
			document.body.className = document.body.className + " show-modal";
			modal.style.display     = "block";
		}
	}
	pagecentric.scrollByOffset();
}

//show();