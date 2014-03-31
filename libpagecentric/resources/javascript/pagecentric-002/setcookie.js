
function setcookie()
{
	var self = pagecentric.self( this );

	var name  = self.name;
	var value = self.options[self.selectedIndex].value;

	document.cookie = name + "=" + escape(value);
	
	window.location = ".";
}

function setup_setcookie_foreach( elements )
{
	if ( elements )
	{
		var n = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( 'setcookie' == elements[i].getAttribute( 'data-action' ) )
			{
				elements[i].onchange = setcookie;
			}
		}
	}
}

function setup_setcookie()
{
	setup_setcookie_foreach( document.getElementsByTagName( 'select' ) );
}

//setup_setcookie();