
//-----------------------------------------------------------------------------
//	Validation Setup
//-----------------------------------------------------------------------------

pagecentric.validation = {}

pagecentric.setup.validation
=
function ()
{
	pagecentric.setup.validation.foreach( document.getElementsByTagName( "input"    ) );
	pagecentric.setup.validation.foreach( document.getElementsByTagName( "select"   ) );
	pagecentric.setup.validation.foreach( document.getElementsByTagName( "textarea" ) );

	var forms = document.getElementsByTagName( "form" );
	var n     = forms.length;

	for ( var i=0; i < n; i++ )
    {
        if ( "validation" == forms[i].getAttribute( "data-action" ) )
        {
			forms[i].validate = pagecentric.validateForm;

			pagecentric.addEventListener( forms[i], "change", pagecentric.validateForm );
        }
    }
}

pagecentric.setup.validation.foreach
=
function ( elements )
{
	if ( elements )
	{
		var n = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			elements[i].mytick   = pagecentric.tick;
			elements[i].myuntick = pagecentric.untick;
		
			if ( "select" == elements[i].tagName.toLowerCase() )
			{
				var required    = (      "true" == elements[i].getAttribute( "data-required"   ));
				var validation1 = ("validation" == elements[i].getAttribute( "data-action"     ));
				var validation2 = (      "true" == elements[i].getAttribute( "data-validation" ));

				if ( required || validation1 || validation2 )
				{
					pagecentric.addEventListener( elements[i], "change", elements[i].mytick );
				}
			}
			else
			if ( "validation" == elements[i].getAttribute( "data-action" ) )
			{
				var kind = elements[i].getAttribute( "data-kind" );

				switch ( kind )
				{
				case "axn":
					elements[i].mykeyup = pagecentric.validateAxN;
					break;

				case "email":
					elements[i].mykeyup = pagecentric.validateEmail;
					break;

				case "phone":
					elements[i].mykeyup = pagecentric.validatePhone;
					break;

				case "website":
					elements[i].mykeyup = pagecentric.validateWebsite;
					break;

				case "year":
					elements[i].mykeyup = pagecentric.validateYear;
					break;

				case "required":
				case "password":
				default:
					elements[i].mykeyup = pagecentric.validateRequired;
				}

				pagecentric.addEventListener( elements[i], "focus", elements[i].myuntick );
				pagecentric.addEventListener( elements[i],  "blur", elements[i].mytick   );
				pagecentric.addEventListener( elements[i], "keyup", elements[i].mykeyup  );

				elements[i].mykeyup( null );
				elements[i].mytick();
			}
		}
	}
}

pagecentric.alertMe
=
function ()
{
	alert( "NOW" );
}

//-----------------------------------------------------------------------------
//	Tick Management
//-----------------------------------------------------------------------------

pagecentric.untick
=
function ( event )
{
	var self = pagecentric.self( this );

	if ( ! self.readOnly )
	{
		if ( pagecentric.hasClass( self, "ticked" ) )
		{
			pagecentric.removeClass( self, "ticked" );
			//pagecentric.addClass   ( self, "valid"  );
		}
	}
}

pagecentric.tick
=
function ( event )
{
	var self = pagecentric.self( this );

	if ( ("select" == self.tagName.toLowerCase()) && self.selectedIndex )
	{
		pagecentric.hideWarning( self );
	}
	else
	if ( pagecentric.hasClass( self, "valid" ) )
	{
		pagecentric.removeClass( self, "valid"  );
		pagecentric.addClass   ( self, "ticked" );
		pagecentric.hideWarning( self );
	}
}

//-----------------------------------------------------------------------------
//	Validation Checks
//-----------------------------------------------------------------------------

pagecentric.validateAxN
=
function ( event )
{
	var self = pagecentric.self( this );

	var trimmed = self.value.replace( / /g, "" );
	var len     = trimmed.length;
	var num     = parseInt( trimmed );

	if ( 0 == self.value.length )
	{
		pagecentric.removeClass( self,   "valid" );
		pagecentric.removeClass( self, "invalid" );
		pagecentric.removeClass( self,    "warn" );
	}
	else
	if ( (NaN != num) && pagecentric.isNumeric( trimmed ) )
	{
		if ( 9 <= len )
		{
			if ( (9 <= len) && (len <= 11) )
			{
				pagecentric.removeClass( self,    "warn" );
				pagecentric.removeClass( self, "invalid" );
				pagecentric.addClass   ( self,   "valid" );
			}
			else
			{
				pagecentric.removeClass( self,    "warn" );
				pagecentric.removeClass( self,   "valid" );
				pagecentric.addClass   ( self, "invalid" );
			}
		}
		else
		{
			pagecentric.removeClass( self,   "valid" );
			pagecentric.removeClass( self, "invalid" );
			pagecentric.addClass   ( self,    "warn" );
		}
	}
	else
	{
		pagecentric.removeClass( self,    "warn" );
		pagecentric.removeClass( self,   "valid" );
		pagecentric.addClass   ( self, "invalid" );
	}
	if ( event ) pagecentric.validateMyForm( self );
}

pagecentric.validateEmail
=
function ( event )
{
	var self = pagecentric.self( this );

	var trimmed = self.value.replace( / /g, "" );
	var len     = trimmed.length;
	var num     = parseInt( trimmed );

	if ( 0 == self.value.length )
	{
		pagecentric.removeClass( self,   "valid" );
		pagecentric.removeClass( self, "invalid" );
		pagecentric.removeClass( self,    "warn" );
	}
	else
    if ( -1 == trimmed.indexOf( '@' ) )
    {
        pagecentric.removeClass( self,   "valid" );
        pagecentric.removeClass( self, "invalid" );
        pagecentric.addClass   ( self,    "warn" );
    }
    else
    {
        pagecentric.removeClass( self,    "warn" );
        pagecentric.removeClass( self, "invalid" );
        pagecentric.addClass   ( self,   "valid" );
    }
	if ( event ) pagecentric.validateMyForm( self );
}

pagecentric.validatePhone
=
function ( event )
{
	var self = pagecentric.self( this );

	var trimmed = self.value.replace( / /g, "" );
	var len     = trimmed.length;
	var num     = parseInt( trimmed );

	if ( 0 == self.value.length )
	{
		pagecentric.removeClass( self,   "valid" );
		pagecentric.removeClass( self, "invalid" );
		pagecentric.removeClass( self,    "warn" );
	}
	else
	if ( (NaN != num) && pagecentric.isNumeric( trimmed ) )
	{
		if ( 10 <= len )
		{
			if ( 10 == len )
			{
				pagecentric.removeClass( self,    "warn" );
				pagecentric.removeClass( self, "invalid" );
				pagecentric.addClass   ( self,   "valid" );
			}
			else
			{
				pagecentric.removeClass( self,    "warn" );
				pagecentric.removeClass( self,   "valid" );
				pagecentric.addClass   ( self, "invalid" );
			}
		}
		else
		{
			pagecentric.removeClass( self,   "valid" );
			pagecentric.removeClass( self, "invalid" );
			pagecentric.addClass   ( self,    "warn" );
		}
	}
	else
	{
		pagecentric.removeClass( self,    "warn" );
		pagecentric.removeClass( self,   "valid" );
		pagecentric.addClass   ( self, "invalid" );
	}
	if ( event ) pagecentric.validateMyForm( self );
}

pagecentric.validateRequired
=
function ( event )
{
	var self = pagecentric.self( this );

	var trimmed     = self.value.replace( / /g, "" );
	var len         = trimmed.length;
	var num         = parseInt( trimmed );
	var placeholder = self.getAttribute( "placeholder" );

	if ( (0 == self.value.length) || (placeholder == self.value) )
	{
		pagecentric.removeClass( self,   "valid" );
		pagecentric.removeClass( self, "invalid" );
		pagecentric.removeClass( self,    "warn" );
	}
	else
    {
        pagecentric.removeClass( self,    "warn" );
        pagecentric.removeClass( self, "invalid" );
        pagecentric.addClass   ( self,   "valid" );
    }
	if ( event ) pagecentric.validateMyForm( self );
}

pagecentric.validateYear
=
function ( event )
{
	var self = pagecentric.self( this );

	var trimmed = self.value.replace( / /g, "" );
	var year = parseInt( self.value );

	if ( 0 == self.value.length )
	{
		pagecentric.removeClass( self,    "warn" );
		pagecentric.removeClass( self,   "valid" );
		pagecentric.removeClass( self, "invalid" );
	}
	else
	if ( (NaN != year) && pagecentric.isNumeric( trimmed ) )
	{
		if ( 4 <= self.value.length  )
		{
			var now = new Date();
		
			if ( (1900 <= year) && (year <= now.getFullYear()) )
			{
				pagecentric.removeClass( self,    "warn" );
				pagecentric.removeClass( self, "invalid" );
				pagecentric.addClass   ( self,   "valid" );
			}
			else
			{
				pagecentric.removeClass( self,    "warn" );
				pagecentric.removeClass( self,   "valid" );
				pagecentric.addClass   ( self, "invalid" );
			}
		}
		else
		{
			pagecentric.removeClass( self,   "valid" );
			pagecentric.removeClass( self, "invalid" );
			pagecentric.addClass   ( self,    "warn" );
		}
	}
	else
	{
		pagecentric.removeClass( self,    "warn" );
		pagecentric.removeClass( self,   "valid" );
		pagecentric.addClass   ( self, "invalid" );
	}
	if ( event ) pagecentric.validateMyForm( self );
}

pagecentric.validateWebsite
=
function ( event )
{
	var self = pagecentric.self( this );

	var trimmed = self.value.replace( / /g, "" );
	var len     = trimmed.length;
	var num     = parseInt( trimmed );

	if ( 0 == self.value.length )
	{
		pagecentric.removeClass( self,   "valid" );
		pagecentric.removeClass( self, "invalid" );
		pagecentric.removeClass( self,    "warn" );
	}
	else if ( -1 == trimmed.indexOf( "." ) )
	{
        pagecentric.addClass   ( self,    "warn" );
        pagecentric.removeClass( self, "invalid" );
        pagecentric.removeClass( self,   "valid" );
	}
	else
    {
        pagecentric.removeClass( self,    "warn" );
        pagecentric.removeClass( self, "invalid" );
        pagecentric.addClass   ( self,   "valid" );
    }
	if ( event ) pagecentric.validateMyForm( self );
}

//-----------------------------------------------------------------------------
//	Validate Forms
//-----------------------------------------------------------------------------

pagecentric.validateMyForm
=
function ( anElement )
{
	var self = pagecentric.self( this );

	var form = pagecentric.forms.ancestorForm( anElement );
	if ( form && form.validate )
	{
		form.validate();
	}
	else
	{
		form.validate = pagecentric.validateForm;
		form.validate();
	}
}

pagecentric.validateForm
=
function ( event )
{
	var self = pagecentric.self( this );

    var validated = true;
    {
		validated &= pagecentric.validateElements( self.getElementsByTagName( "input"    ) );
		validated &= pagecentric.validateElements( self.getElementsByTagName( "select"   ) );
		validated &= pagecentric.validateElements( self.getElementsByTagName( "textarea" ) );
    }

	var inputs = self.getElementsByTagName( "input" );
	var n      = inputs.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( ("submit" == inputs[i].type) )
		{
			if ( "validation-submit" == inputs[i].getAttribute( "data-action" ) )
			{
				if ( validated )
				{
					inputs[i].disabled = false;
					pagecentric.removeClass( inputs[i], "disabled" );
					
					if ( "saved" == inputs[i].value )
					{
						inputs[i].value = "save";
					}
				}
				else
				{
					inputs[i].disabled = true;
					pagecentric.addClass( inputs[i], "disabled" );
				}
			}
		}
	}
}

pagecentric.validateElements
=
function ( elements )
{
    var validated = true;
	if ( elements )
	{
        var n = elements.length;
        
        for ( var i=0; i < n; i++ )
        {
			var element = elements[i];
		
			if ( "true" == element.getAttribute( "data-required" ) )
			{
				if ( "selected" == element.tagName.toLowerCase() && ! element.selectedIndex )
				{
					validated = false;
				}
				else if ( "validation" == element.getAttribute( "data-action" ) )
				{
					if ( ! pagecentric.hasClass( element, "valid" ) && ! pagecentric.hasClass( element, "ticked" ) )
					{
						validated = false;
						break;
					}
                }
				else if ( "" == element.value )
				{
					validated = false;
				}
            }
        }
	}
	else
	{
		validated = false;
	}
	return validated;
}

//-----------------------------------------------------------------------------
//	Warnings
//-----------------------------------------------------------------------------

pagecentric.hideWarning
=
function ( element )
{
	var self = pagecentric.self( this );

	var warning_target = element.getAttribute( "data-warning-target" );
	if ( ! warning_target )
	{
		warning_target = "hidden-" + element.name;
	}

	var  target = pagecentric.findWarning( element, warning_target );
	if ( target )
	{
		pagecentric.removeClass( target, "warning" );
	}
}

pagecentric.showWarnings
=
function ( modalID )
{
	var status  = pagecentric.showWarningsFor( document.getElementsByTagName( "input"    ) );
	    status &= pagecentric.showWarningsFor( document.getElementsByTagName( "select"   ) );
	    status &= pagecentric.showWarningsFor( document.getElementsByTagName( "textarea" ) );

	if ( status )
	{
		pagecentric.showmodal.byId( modalID );
	}
}

pagecentric.showWarningsForFormOf
=
function ( submit )
{
	var status = true;

	var form = pagecentric.forms.ancestorForm( submit );
	if ( form )
	{
		status &= pagecentric.showWarningsFor( form.getElementsByTagName( "input"     ) );
		status &= pagecentric.showWarningsFor( form.getElementsByTagName( "selects"   ) );
		status &= pagecentric.showWarningsFor( form.getElementsByTagName( "textareas" ) );
	}
	return status;
}

pagecentric.showWarningsFor
=
function ( elements )
{
	var status = true;

	if ( elements )
	{
		var n = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			var element  = elements[i];
			
			var required = ("true" == element.getAttribute( "data-required" ));
			
			if ( required )
			{
				var warning_target = element.getAttribute( "data-warning-target" );
				if ( ! warning_target )
				{
					warning_target = "hidden-" + element.name;
				}

				var  target = pagecentric.findWarning( element, warning_target );
				if ( target )
				{
					if ( "select" == element.tagName.toLowerCase() )
					{
						if ( element.selectedIndex )
						{
							pagecentric.removeClass( target, "warning" );
						}
						else
						{
							pagecentric.addClass( target, "warning" );
							status = false;
						}
					}
					else
					{
						if ( pagecentric.hasClass( element, "ticked" ) )
						{
							pagecentric.removeClass( target, "warning" );
						}
						else
						{
							pagecentric.addClass( target, "warning" );
							status = false;
						}
					}
				}
			}
		}
	}
	return status;
}

pagecentric.findWarning
=
function ( element, warning_target )
{
	var target = null;
	{
		var form = pagecentric.forms.ancestorForm( element );
		if ( form )
		{
			var divs = form.getElementsByTagName( "div" );
			var n    = divs.length;
			
			for ( var i=0; i < n; i++ )
			{
				var  data_warning_id = divs[i].getAttribute( "data-warning-id" );
				if ( data_warning_id && (data_warning_id == warning_target) )
				{
					target = divs[i];
				}
			}
		}
	}
	return target;
}


