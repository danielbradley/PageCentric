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

function closevideo()
{
	var video_frame = this.parentNode;
	video_frame.innerHTML = "";

	this.activatingAnchor.setAttribute( 'href', '#' );
	this.activatingAnchor.onclick = selectvideo;
}

function selectvideo( event )
{
	//var this = event.addEventListener ? this : event.srcElement;

	var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );

	var target = this.getAttribute( "data-target" );
    var host   = this.getAttribute( "data-host"   ); // "http://download.local";
    var app    = this.getAttribute( "data-app"    );
    var token  = this.getAttribute( "data-token"  );
    var file   = this.getAttribute( "data-file"   );
    var width  = this.getAttribute( "data-width"  );
    var height = this.getAttribute( "data-height" );
    var suffix = ".mp4";
	var poster = host + '/' + app + '/' + token + '/MP4/'  + width + 'x' + height + '.png';
    var url1   = host + '/' + app + '/' + token + '/MP4/'  + file;
    var url2   = host + '/' + app + '/' + token + '/WEBM/' + file;
    var url3   = host + '/' + app + '/' + token + '/OGG/'  + file;
    var uri    = encodeURIComponent( url1 + ".mp4" );

    var div = document.getElementById( target );
	if ( div )
	{
		var html = "";
		if ( IE8() )
		{
			html += "<object type='application/x-shockwave-flash' data='http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf' width='%WIDTH%' height='%HEIGHT%'>";
			html += "<param name='movie' value='http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf' />";
			html += "<param name='allowFullScreen' value='true' />";
			html += "<param name='wmode' value='transparent' />";
			html += "<param name='flashVars' value='config={\"playlist\":[{\"url\":\"%URI%\",\"autoPlay\":false}]}' />";
			html += "<span title='No video playback capabilities, please install or update Adobe Flash'>Video</span>";
			html += "</object>";
		}
		else
		{
			html = "<video style='z-index:1000;' width='%WIDTH%' height='%HEIGHT%' poster='%POSTER%' controls autoplay><source src='%URL2%.webm' type='video/webm'><source src='%URL1%.mp4' type='video/mp4'><source src='%URL3%.ogv' type='video/ogg'><embed src='%URL1%.mp4' width='%WIDTH%' height='%HEIGHT%'></video>";
		}

		html   = html.replace(/%POSTER%/, poster );
	    html   = html.replace(/%URL1%/g,  url1   );
	    html   = html.replace(/%URL2%/g,  url2   );
	    html   = html.replace(/%URL3%/g,  url3   );
	    html   = html.replace(/%URI%/g,   uri    );

	    html   = html.replace( /%WIDTH%/g, width  );
	    html   = html.replace(/%HEIGHT%/g, height );

		//if ( event && event.addEventListener )
		{
			div.innerHTML = html;
		}
		
//		var videos = div.getElementsByTagName( "VIDEO" );
//		if ( videos[0] )
//		{
//			//	Add this to remove video when it has finished.
//			//
//			//videos[0].addEventListener( 'ended', closevideo, false);
//			videos[0].activatingAnchor = this;
//		}
		this.onclick = null;//removeEventListener( 'click', selectvideo );

		pagecentric.preventDefault( event );

//		if ( this.removeAttribute )
//		{
//			this.removeAttribute( 'href' );
//		}
	}

}

function selectvideooption()
{
	var self = event.eventListener ? this : window.event.srcElement;

	var option = this.options.item( self.selectedIndex );
		option.onselect();
}

function setup_selectvideo()
{
	{
		var elements = document.getElementsByTagName( 'a' );
		var n        = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( "selectvideo" == elements[i].getAttribute( "data-action" ) )
			{
				elements[i].onclick = selectvideo;
//				pagecentric.addEventListener( elements[i], "click", selectvideo );
			}
		}
	}
	{
		var elements = document.getElementsByTagName( 'option' );
		var n        = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( "selectvideo" == elements[i].getAttribute( "data-action" ) )
			{
				elements[i].onselect = selectvideo;
//				pagecentric.addEventListener( elements[i], "select", selectvideo );
			}
		}
	}
	{
		var elements = document.getElementsByTagName( 'select' );
		var n        = elements.length;
		
		for ( var i=0; i < n; i++ )
		{
			if ( "selectvideo" == elements[i].getAttribute( "data-action" ) )
			{
				elements[i].onchange = selectvideooption;
//				pagecentric.addEventListener( elements[i], "change", selectvideooption );
			}
		}
	}
}
