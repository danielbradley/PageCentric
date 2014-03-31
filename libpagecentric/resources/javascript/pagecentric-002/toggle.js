
function toggle_class()
{
	var target = this.getAttribute( 'data-target' );
	
	var divs = document.getElementsByTagName( 'div' );
	var n = divs.length;
	
	for ( var i=0; i < n; i++ )
	{
		var cls = divs[i].getAttribute( 'data-class' );
		if ( target == cls )
		{
			if ( "block" == divs[i].style.display )
			{
				divs[i].style.display = "none";
			}
			else
			if ( "none" == divs[i].style.display )
			{
				divs[i].style.display = "block";
			}
		}
	}
	return false;
}

function setupToggleEventHandlerForEach( elements )
{
	if ( elements )
	{
		var n = elements.length;

		for ( var i=0; i < n; i++ )
		{
			var action = elements[i].getAttribute( 'data-action' );

			if ( 'toggle' == action )
			{
				elements[i].onclick = toggle_class;
			}
		}
	}
}

function setup_toggle()
{
	setupToggleEventHandlerForEach( document.getElementsByTagName( 'input' ) );
	setupToggleEventHandlerForEach( document.getElementsByTagName( 'a'     ) );
}

//	setup_toggle();