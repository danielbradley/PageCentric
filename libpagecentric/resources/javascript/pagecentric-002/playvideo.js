/*
<video width="320" height="240" controls>
  <source src="movie.mp4" type="video/mp4">
  <source src="movie.ogg" type="video/ogg">
  <source src="movie.webm" type="video/webm">
  <object data="movie.mp4" width="320" height="240">
    <embed src="movie.swf" width="320" height="240">
  </object> 
</video>
*/

function playvideo()
{
	if ( this.paused )
	{
		this.play();
	}
	else
	{
		this.pause();
	}
}

function setup_playvideo()
{
    var elements = document.getElementsByTagName( 'video' );
    var n        = elements.length;
	
	for ( var i=0; i < n; i++ )
	{
		if ( "playvideo" == elements[i].getAttribute( "data-action" ) )
		{
			elements[i].onclick = playvideo;
		}
	}
}
