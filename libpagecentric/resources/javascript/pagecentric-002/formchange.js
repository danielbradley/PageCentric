
//-----------------------------------------------------------------------------
//	Form change
//-----------------------------------------------------------------------------

pagecentric.setup.formchange
=
function ()
{
	pagecentric.setup.formchange.foreach( document.getElementsByTagName( 'form' ) );
}

pagecentric.setup.formchange.foreach
=
function ( forms )
{
	if ( forms )
	{
		var n = forms.length;

		for ( var i=0; i < n; i++ )
		{
			var action = forms[i].getAttribute( 'data-action' );

			//if ( 'formchange' == action )
			{
				//if ( IE8() )
				{
					forms[i].formchange = pagecentric.formchange;

					pagecentric.setup.formchange.foreach.input( forms[i].getElementsByTagName( "input"    ) );
					pagecentric.setup.formchange.foreach.input( forms[i].getElementsByTagName( "select"   ) );
					pagecentric.setup.formchange.foreach.input( forms[i].getElementsByTagName( "textarea" ) );
				}
//				else
//				{
//					pagecentric.addEventListener( forms[i], "change", pagecentric.formchange );
//				}
			}
		}

	}
}

pagecentric.setup.formchange.foreach.input
=
function ( inputs )
{
	if ( inputs )
	{
		var n = inputs.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( "select" == inputs[i].tagName.toLowerCase() )
			{
				pagecentric.addEventListener( inputs[i], "change", pagecentric.formchange.inputchange );
			}
			else
			if ( "file" == inputs[i].type )
			{
				pagecentric.addEventListener( inputs[i], "change", pagecentric.formchange.inputchange );
			}
			else
			{
				pagecentric.addEventListener( inputs[i], "keyup", pagecentric.formchange.inputchange );
			}
		}
	}
}

pagecentric.formchange
=
function ()
{
	var elements = this.getElementsByTagName( 'input' );
	var n        = elements.length;

	for ( var i=0; i < n; i++ )
	{
		if ( "submit" == elements[i].name )
		{
			pagecentric.removeClass( elements[i], "disabled" );
			elements[i].disabled = false;

			if ( "saved" == elements[i].value )
			{
				elements[i].value = "save";
			}
		}
	}
}

pagecentric.formchange.inputchange
=
function ( event )
{
	var self = pagecentric.self( this );
	
	var form = pagecentric.forms.ancestorForm( self );
	    form.formchange();
}

//function formchange_blur()
//{
//
//	alert( "Form has changed" );
//
//	var elements = this.getElementsByTagName( 'input' );
//	var n        = elements.length;
//
//	for ( var i=0; i < n; i++ )
//	{
//		if ( "submit" == elements[i].name )
//		{
//			if ( ! elements[i].name.disabled )
//			{
//				confirm( "The form has changed are you sure you want to leave" );
//			}
//		}
//	}
//}


