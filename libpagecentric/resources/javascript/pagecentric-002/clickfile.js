
pagecentric.clickfile
=
function ()
{
	var parent = this.parentNode;
	if ( parent )
	{
		var children = parent.childNodes;
		var n        = children.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( "file" == children[i].className )
			{
				var file_div = children[i];
				var children = file_div.childNodes;
				var m        = children.length;
				
				for ( var j=0; j < m; j++ )
				{
					var child = children[j];
				
					if ( "file" == child.type )
					{
						child.click();
						break;
					}
				}
				break;
			}
		}
	}
	return true;
}

pagecentric.clickfileSetup
=
function ()
{
	var inputs = document.getElementsByTagName( 'input' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'clickfile' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onclick = pagecentric.clickfile;
		}
	}
}
