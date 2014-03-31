
//----------------------------------------------------------------------------
//	Sticky
//----------------------------------------------------------------------------

pagecentric.sticky = {}

pagecentric.setup.sticky
=
function()
{
	if ( ! IE8() )
	{
		pagecentric.sticky.stickies = new Array();

		var divs = document.getElementsByTagName( "div" );
		var n    = divs.length;

		for ( var i=0; i < n; i++ )
		{
			if ( pagecentric.hasClass( divs[i], "sticky" ) )
			{
				pagecentric.sticky.stickies.push( divs[i] );

				divs[i].findOffsetParent = pagecentric.sticky.findOffsetParent;
				divs[i].style.position   = "fixed";
				
				console.log( "Adding sticky" );
			}
		}

		pagecentric.addEventListener( window, "scroll", pagecentric.sticky.onscroll );
	}
}

pagecentric.sticky.onscroll
=
function()
{
	var stickies = pagecentric.sticky.stickies;
	var n        = stickies.length;

	for ( var i=0; i < n; i++ )
	{
		var div = stickies[i];

		pagecentric.sticky.processDiv( div );
	}
}

pagecentric.sticky.processDiv
=
function( div )
{
	var  parent = div.findOffsetParent();
	if ( parent )
	{
		var hp = parent.clientHeight;
		var h  = div.clientHeight;
		var y1 = div.offsetTop;
		var y2 = pagecentric.scrollOffsetY();

		if ( y2 > (hp - h) )
		{
			div.style.position = "absolute";
			div.style.bottom   = "0px";
		}
		else if ( "absolute" == div.style.position )
		{
			div.style.position = "fixed";
			div.style.bottom   = "";
		}
	}
}

pagecentric.sticky.findOffsetParent
=
function()
{
	var div = this;
	
	while ( (div = div.parentNode) )
	{
		if ( pagecentric.hasClass( div, "relative" ) )
		{
			break;
		}
	}

	return div;
}


