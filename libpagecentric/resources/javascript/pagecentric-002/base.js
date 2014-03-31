
function IE8()
{
	var isIE8 = navigator.userAgent.match(/MSIE 8.0/g) ||
				navigator.userAgent.match(/MSIE 7.0/g) ||
				navigator.userAgent.match(/MSIE 6.0/g);
	
	return isIE8;
}

pagecentric       = {};
pagecentric.setup = {};

pagecentric.self
=
function ( self )
{
	var self = self;
	
	if ( window === self ) self = window.event.srcElement;

	return self;
}

pagecentric.addEventListener
=
function ( element, eventName, func )
{
	if ( element.addEventListener )
	{
		element.addEventListener( eventName, func, false );
	}
	else
	{
		element.attachEvent( "on" + eventName, func );
	}
}

pagecentric.scrollOffsetX
=
function()
{
	//	Modified from: http://stackoverflow.com/questions/10286162/pageyoffset-scrolling-and-animation-in-ie8

	var x = -1;

	if ( window.pageXOffset )
	{
		x = window.pageXOffset;
	}
	else
	if ( window.document.compatMode === "CSS1Compat" )
	{
		x = window.document.documentElement.scrollLeft;
	}
	else
	{
		x = window.document.body.scrollLeft;
	}
	return x;
}

pagecentric.scrollOffsetY
=
function()
{
	//	Modified from: http://stackoverflow.com/questions/10286162/pageyoffset-scrolling-and-animation-in-ie8

	var y = -1;

	if ( window.pageYOffset )
	{
		y = window.pageYOffset;
	}
	else
	if ( window.document.compatMode === "CSS1Compat" )
	{
		y = window.document.documentElement.scrollTop;
	}
	else
	{
		y = window.document.body.scrollTop;
	}
	return y;
}


pagecentric.preventDefault
=
function( event )
{
	if ( event && event.preventDefault )
	{
		event.preventDefault();
	}
	else
	{
		window.event.returnValue = false;
	}
}

pagecentric.stopPropagation
=
function( event )
{
	if ( event && event.stopPropagation )
	{
		event.stopPropagation();
	}
	else
	{
		event = window.event;
		event.cancelBubble = true;
	}
}

pagecentric.countChildDivs
=
function( element )
{
	var n   = 0;
	var len = element.childNodes.length;
	for ( var i=0; i < len; i++ )
	{
		if ( "DIV" == element.childNodes[i].tagName ) n++;
	}
	return n;
}

pagecentric.setYOffset
=
function()
{
	var y_offset = pagecentric.scrollOffsetY();
	
	var inputs   = document.getElementsByTagName( "input" );
	var n        = inputs.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( "pageyoffset" == inputs[i].getAttribute( "data-group" ) )
		{
			inputs[i].value = y_offset;
		}
	}
}

pagecentric.scrollByOffset
=
function()
{
	var pageyoffset = document.body.getAttribute( "data-pageyoffset" );
	var offset      = parseInt( pageyoffset );

	if ( !(NaN == offset) )
	{
		window.scrollBy( 0, offset );
	}
}

pagecentric.hasClass
=
function ( element, cls )
{
	var classes = element.className;
	
	return (-1 != classes.indexOf( cls ));
}

pagecentric.addClass
=
function ( element, cls )
{
	if ( element && cls )
	{
		var classes = element.className;
		
		if ( -1 == classes.indexOf( cls ) )
		{
			element.className += (" " + cls);
		}
	}
	else
	{
		console.log( "Could not find my self" );
	}
}

pagecentric.removeClass
=
function ( element, cls )
{
	var classes = element.className;
	var f = 0;
	var n = cls.length;

	if ( (-1 != classes.indexOf( " " + cls )) || (-1 != classes.indexOf( cls + " " )) || (-1 != classes.indexOf( cls )) )
	{
		var f = classes.indexOf( cls );

		if ( (0 < f) && (' ' == classes[f - 1]) ) f--;
	
		element.className = classes.substring( 0, f ) + classes.substring( f + n + 1 );
	}
}

pagecentric.contains
=
function ( haystack, needle )
{
	return (-1 != haystack.indexOf( needle ));
}

pagecentric.isAlphanumeric
=
function ( string )
{
	//	Adapted from:
	//	http://stackoverflow.com/questions/4434076/best-way-to-alphanumeric-check-in-javascript

	var reg_exp = /^[A-Za-z0-9]+$/;
	return (string.match( reg_exp ));
}

pagecentric.isNumeric
=
function ( string )
{
	//	Adapted from:
	//	http://stackoverflow.com/questions/4434076/best-way-to-alphanumeric-check-in-javascript

	var reg_exp = /^[0-9]+$/;
	return (string.match( reg_exp ));
}

pagecentric.isLikeDomain
=
function ( string )
{
	//	Adapted from:
	//	http://stackoverflow.com/questions/13027854/javascript-regular-expression-validation-for-domain-name

	var reg_exp = /^[a-zA-Z0-9._-]+\\[a-zA-Z0-9.-]$/;
	return (string.match( reg_exp ));
}

//pagecentric.isCountryCodeDomain
//=
//function( cc )
//{
//	return (2 == cc.length);
//}
//
//pagecentric.isSecondLevelDomain
//=
//function( sld )
//{
//	switch ( sld )
//	{
//	case "co":
//	case "com":
//	case "gov":
//	case "org":
//	case "edu":
//		return true;
//		
//	default
//		return false;
//	}
//}
//
//pagecentric.isTopLevelDomain
//=
//function( sld )
//{
//	switch ( sld )
//	{
//	case "co":
//	case "com":
//	case "gov":
//	case "org":
//	case "edu":
//	case "info":
//		return true;
//		
//	default
//		return false;
//	}
//}
//
//pagecentric.isValidDomainPair
//=
//function( cc, sld )	// Country code domain & Second level domain
//{
//	return pagecentric.isCountryCodeDomain( cc ) && pagecentric.isSecondLevelDomain( sld );
//}
//
//pagecentric.isValidInternetDomain
//=
//function( string )
//{
//	var success = false;
//	{
//		if ( pagecentric.isLikeDomain( string ) )
//		{
//			var bits = string.split('.');
//			var n    = bits.length;
//
//			if ( n > 1 )
//			{
//				success = pagecentric.isValidDomainPair( bits[n-1], bits[n-2] );
//				
//				if ( ! success )
//				{
//					success = pagecentric.isValidDomain( bits[n-1] );
//				}
//			}
//		}
//	}
//	return success;
//}
//
//pagecentric.extractEmailDomain
//=
//function( domain )
//{
//	var domain = "";
//	{
//		var bits = string.split('.');
//		var n    = bits.length;
//		
//		success = pagecentric.isTopLevelDomain( bits[n-1] );
//	}
//	return domain;
//}







