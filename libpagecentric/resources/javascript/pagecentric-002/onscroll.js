
function getYOffset( div )
{
	var offset_parent = div.offsetParent;
	var sum = div.offsetTop;
	
	if ( offset_parent )
	{
		sum += getYOffset( offset_parent );
	}
	return sum;
}

//D.null		//	return 0;
//30			
//D			//	return 0 + 30;
//50
//D			//	return 0 + 30 + 50;
//80
//D			//	return 0 + 30 + 50 + 80;








function onscroll()
{
//	var div  = document.getElementById( "nav_favicon" );
//	if ( div )
//	{
//		var appear_at = 0;
//		var appear_at_txt = div.getAttribute( "data-appear-at" );
//		if ( appear_at_txt )
//		{
//			appear_at = parseInt( appear_at_txt );
//		}
//		else
//		{
//			appear_at = 297;
//		}
//
//		if ( window.pageYOffset > appear_at )
//		{
//			 div.style.display = "block";
//		}
//		else
//		{
//			 div.style.display = "none";
//		}
//	}
//	
//	var middle       = document.getElementById( "middle" );
//	var middle_right = document.getElementById( "middle-right" );
//	var sidebar      = document.getElementById( "sidebar" );
//
//	if ( middle && middle_right && sidebar )
//	{
//		var height = middle.clientHeight;
//	
//		middle_right.style.height = height + "px";
//		
//		var y1_middle_right = getYOffset( middle_right );
//		var y2_middle_right = y1_middle_right + height;
//		//var y1_sidebar      = getYOffset( sidebar ) + window.pageYOffset;
//		var y1_sidebar      = 75 + window.pageYOffset;
//		var y2_sidebar      = y1_sidebar + sidebar.clientHeight;
//		
//		if ( y2_middle_right < y2_sidebar )
//		{
//			sidebar.style.position = "absolute";
//			sidebar.style.bottom   = "20px;";
//		}
//		else
//		{
//			sidebar.style.position = "fixed";
//			sidebar.style.top      = "75px";
//		}
//	}






//		var y1_footer  = getYOffset( footer  );
//		var y1_sidebar = getYOffset( sidebar );
//		var y2_sidebar = y1_sidebar + sidebar.offsetHeight;
//		var y3_sidebar = y2_sidebar + window.pageYOffset + 20;
//
//		if ( y1_footer < y3_sidebar )
//		{
//		
//			sidebar.style.position = "absolute";
//			sidebar.style.bottom   = "20px";
//			//alert( "Warning Will Robinson" );
//		}
//		else
//		{
//			sidebar.style.position = "fixed";
//			sidebar.style.top      = "75px";
//		}
//	}
}

function setup_onscroll()
{
//	document.onscroll = onscroll;
}

//	setup_toggle();