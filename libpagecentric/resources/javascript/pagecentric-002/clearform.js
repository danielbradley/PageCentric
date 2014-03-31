

function openpage_clearform()
{
	var parent = this.parentNode;
	while ( parent && ("FORM" != parent.tagName) )
	{
		parent = parent.parentNode;
	}

	var elements = parent.getElementsByTagName( 'input' );
	var n        = elements.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( "text" == elements[i].type ) elements[i].value = "";
	}
	
	var elements = parent.getElementsByTagName( 'select' );
	var n        = elements.length;

	for ( var i=0; i < n; i++ )
	{
		elements[i].selectedIndex = 0;
	}
}

function setup_openpage_clearform()
{
	var inputs = document.getElementsByTagName( 'input' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'clearform' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onclick = openpage_clearform;
		}
	}
}

//setup_checklimit();
