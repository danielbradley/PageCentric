
pagecentric.hideChildDivsByGroup
=
function( parentID, dataGroup )
{
	var parent = document.getElementById( parentID );
	if ( parent )
	{
		children = parent.getElementsByTagName( "div" );
		if ( children )
		{
			var n = children.length;
			for ( var i=0; i < n; i++ )
			{
				var data_group = children[i].getAttribute( "data-group" );
				if ( data_group && data_group === dataGroup )
				{
					children[i].style.display = "none";
				}
			}
		}
	}
}

pagecentric.showhide
=
function ()
{
	var targetID  = this.getAttribute( "data-target" );
	var parentID  = this.getAttribute( "data-parent" );
	var dataGroup = this.getAttribute( "data-group"  );

	if ( parentID )
	{
		pagecentric.hideChildDivsByGroup( parentID, dataGroup );
	}

	if ( targetID )
	{
		var target = document.getElementById( targetID );
		    target.style.display = "block";
	}
	return false;
}

pagecentric.showhideSetup
=
function ()
{
	var elements = document.getElementsByTagName( 'a' );
	if ( elements )
	{
		var n = elements.length;

		for ( var i=0; i < n; i++ )
		{
			var value = elements[i].getAttribute( 'data-action' );

			if ( 'showhide' == value )
			{
				elements[i].onclick = pagecentric.showhide;
			}
		}
	}
}

pagecentric.showhide.showSiblings
=
function ( self, tag )
{
	var  p = self.parentNode;
	if ( p )
	{
		var  div = p.parentNode;
		if ( div )
		{
			var elements = div.getElementsByTagName( tag );
			var n        = elements.length;
			
			for ( var i=0; i < n; i++ )
			{
				var px = elements[i];
				
				if ( px === p )
				{
					px.style.display = "none";
				}
				else
				{
					px.style.display = "block";
				}
			}
		}
	}
	return false;
}

