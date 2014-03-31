
function convert_to_id( label )
{
	var id = label.toLowerCase();
	    id = id.replace(/ /g,  "_" );
		id = id.replace(/\//g, "-" );
		
		return id;
}

function multiSelect()
{
	var greatgrandfather = this.parentNode.parentNode.parentNode;
	var id               = convert_to_id( this.value );
	var target           = this.getAttribute( 'data-target' );
	var attributes       = this.getAttribute( 'data-attributes' );
	var selects          = document.getElementsByName( target );
	var n                = selects.length;
	var first            = -1;
	var found            = false;
	
	if ( "" == id ) id = "?";
	
	for ( var i=0; i < n; i++ )
	{
		if ( selects[i] && (greatgrandfather == selects[i].parentNode.parentNode.parentNode) )
		{
			if ( -1 == selects[i].getAttribute( "data-id" ).indexOf( id ) )
			{
				selects[i].className = attributes + " none";
				selects[i].disabled  = true;
				first = i;
			}
			else
			{
				selects[i].className = attributes;
				selects[i].disabled  = false;
				found = true;
			}
		}
	}
	
	if ( !found && (-1 != first) )
	{
		selects[first].className = attributes;
		selects[first].disabled  = false;
	}
}

function setup_multiselect()
{
	return;

	{
		var selects = document.getElementsByName( 'state' );
		var n = selects.length;
		
		for ( var i=0; i < n; i++ )
		{
			selects[i].onchange = multiSelect;
		}
	}
	{
		var selects = document.getElementsByName( 'industry' );
		var n = selects.length;
		
		for ( var i=0; i < n; i++ )
		{
			selects[i].onchange = multiSelect;
		}
	}
}

//	setup_multiselect();