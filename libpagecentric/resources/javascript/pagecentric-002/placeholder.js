
//-----------------------------------------------------------------------------
//	Placeholder
//-----------------------------------------------------------------------------

pagecentric.setup.placeholder
=
function ()
{
	if ( IE8() )
	{
		pagecentric.setup.placeholder.foreach( document.getElementsByTagName( 'input'    ) );
		pagecentric.setup.placeholder.foreach( document.getElementsByTagName( 'textarea' ) );
	}
}

pagecentric.setup.placeholder.foreach
=
function ( elements )
{
	var n = elements.length;
	
	for ( var i=0; i < n; i++ )
	{
		var placeholder = elements[i].getAttribute('placeholder');
	
		if ( placeholder && (0 < placeholder.length) )
		{
			pagecentric.addEventListener( elements[i],   "focus", pagecentric.placeholder.focus   );
			pagecentric.addEventListener( elements[i], "keydown", pagecentric.placeholder.keydown );
			pagecentric.addEventListener( elements[i],    "blur", pagecentric.placeholder.blur    );

			pagecentric.placeholder.setValue( elements[i], placeholder );
		}
	}
}

pagecentric.placeholder = {}

pagecentric.placeholder.focus
=
function ( event )
{
	var self        = pagecentric.self( this );
	var placeholder = self.getAttribute( "placeholder" );

	if ( "password" == self.type.toLowerCase() )
	{
		pagecentric.removeClass( self, "password" );
	}
	else
	if ( placeholder == self.value )
	{
		self.value = "";
	}
}

pagecentric.placeholder.keydown
=
function ( event )
{
	var self        = pagecentric.self( this );
	var placeholder = self.getAttribute( "placeholder" );

	if ( placeholder == self.value )
	{
		self.value = "";
	}
}

pagecentric.placeholder.blur
=
function ( event )
{
	var self        = pagecentric.self( this );
	var placeholder = self.getAttribute( "placeholder" );

	pagecentric.placeholder.setValue( self, placeholder );
}

pagecentric.placeholder.setValue
=
function ( element, placeholder )
{
	var value = element.value;

	if ( (0 == value.length) || ("" == value) )
	{
		if ( "password" == element.type.toLowerCase() )
		{
			pagecentric.addClass( element, "password" );
		}
		else
		{
			element.value = placeholder;
		}
	}
}




