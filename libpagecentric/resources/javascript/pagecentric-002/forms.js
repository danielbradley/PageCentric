
pagecentric.forms = {};

pagecentric.forms.alert
=
function( element )
{
	alert( "Alert me" );
}

pagecentric.forms.isAncestorOf
=
function( self, element )
{
	while ( element.parentNode && (self != element.parentNode) )
	{
		element = element.parentNode;
	}

	return (self == element.parentNode);
}

pagecentric.forms.getValue
=
function( self, name )
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
				if ( pagecentric.forms.isAncestorOf( self, elements[i] ) )
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
						value = encodeURIComponent( element.value );
					}
					i = n; // Terminate loop.
				}
			}
		}
	}
	return value;
}

pagecentric.forms.getNamesWithValue
=
function( self, value )
{
	var names = new Array();
	{
		var next = 0;
	
		var elements = document.getElementsByTagName( input );
		var n        = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			var element = elements[i];
		
			if ( ! element.disabled )
			{
				if ( value == element.value )
				{
					if ( pagecentric.forms.isAncestorOf( self, element ) )
					{
						names[next++] = element.name;
					}
				}
			}
		}
	}
	return names;
}

pagecentric.forms.isComplete_inputs
=
function( elements )
{
	var complete = true;

	if ( elements )
	{
		var n = elements.length;
	
		for ( var i=0; i < n; i++ )
		{
			if ( "radio" != elements[i].type )
			{
				var required = elements[i].getAttribute( 'data-required' );
				if ( required && (0 < required.length) )
				{
					var value = elements[i].value;
					if ( ! value )
					{
						complete = false;
						pagecentric.removeClass( elements[i], "ticked" );
					}
					else
					{
						pagecentric.addClass( elements[i], "ticked" );
					}
				}
			}
		}
	}
	
	return complete;
}

pagecentric.forms.isComplete_selects
=
function( elements )
{
	var complete = true;

	if ( elements )
	{
		var n = elements.length;
	
		for ( var i=0; i < n; i++ )
		{
			var required = elements[i].getAttribute( 'data-required' );
			if ( required && (0 < required.length) )
			{
				if ( ! elements[i].disabled )
				{
					var value = elements[i].selectedIndex;
					if ( 0 == value )
					{
						complete = false;
						break;
					}
				}
			}
		}
	}
	
	return complete;
}

pagecentric.forms.isComplete
=
function( form, target_id )
{
	var complete  = pagecentric.forms.isComplete_inputs ( form.getElementsByTagName( 'input'    ) );
	    complete &= pagecentric.forms.isComplete_inputs ( form.getElementsByTagName( 'textarea' ) );
		complete &= pagecentric.forms.isComplete_selects( form.getElementsByTagName( 'select'   ) );
	
	var target = document.getElementById( target_id );
	if ( target )
	{
		if ( complete )
		{
			target.style.display = "block";
		}
		else
		{
			target.style.display = "none";
		}
	}
	
	return complete;
}

pagecentric.forms.callIfComplete
=
function( form, command )
{
	var target = form.getAttribute( 'data-target' );
	if ( target )
	{
		pagecentric.forms.isComplete( form, target );
	}
	sm_call( command );
}

pagecentric.forms.findAndCheckCheckboxes
=
function( input )
{
	var parent = input.parentNode;
	while ( parent && ('FORM' != parent.nodeName ))
	{
		parent = parent.parentNode;
	}
	
	var target = '';
	if ( parent && (target = parent.getAttribute( "data-target" )) )
	{
		var inputs = parent.getElementsByTagName( 'input' );
		var n      = inputs.length;
		var tick   = false;
		
		for ( var i=0; i < n; i++ )
		{
			if ( ('checkbox' == inputs[i].type) && inputs[i].checked )
			{
				tick = true;
				break;
			}
		}

		var div = document.getElementById( target );
		if ( div )
		{
			div.style.display = tick ? "block" : "none";
		}
	}
}

pagecentric.forms.countCheckedCheckboxes
=
function( input )
{
	var count = 0;

	var parent = input.parentNode;
	while ( parent && ('FORM' != parent.nodeName ))
	{
		parent = parent.parentNode;
	}
	
	if ( parent )
	{
		var inputs = parent.getElementsByTagName( 'input' );
		var n      = inputs.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( ('checkbox' == inputs[i].type) && inputs[i].checked ) count++;
		}
	}

	return count;
}

pagecentric.forms.consumetab
=
function( event )
{
	/*
	 *	Adapted from: http://stackoverflow.com/questions/6140632/how-to-handle-tab-in-textarea
	 */

	if ( 9 === event.keyCode )
	{
		var start     = this.selectionStart;
		var end       = this.selectionEnd;
		var value     = this.value;
		var new_value = value.substring( 0, start ) + "\t" + value.substring( end );
		
		this.value = new_value;
		
		this.selectionStart = start + 1;
		this.selectionEnd   = start + 1;

		pagecentric.preventDefault( event );
	}
}



pagecentric.forms.consumetabSetup
=
function( event )
{
	var textareas = document.getElementsByTagName( "textarea" );
	var n         = textareas.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( "consumetab" == textareas[i].getAttribute( "data-action" ) )
		{
			textareas[i].onkeydown = pagecentric.forms.consumetab;
		}
	}
}

pagecentric.forms.ancestorForm
=
function( self )
{
	while ( ("form" != self.tagName.toLowerCase()) && (null != self.parentNode) )
	{
		self = self.parentNode;
	}
	return self;
}


