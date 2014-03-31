
pagecentric.articleSetup
=
function()
{
	var article = document.getElementById( "article" );
	if ( article )
	{
		pagecentric.transform( article );
	}
}

pagecentric.resource2date
=
function( resource_id )
{
	var dateray = resource_id.split( "-" );
	var date    = new Date( dateray[0], dateray[1], dateray[2] );

	return date.toLocaleDateString( "en-us", { month: "long" });
}

pagecentric.transform
=
function( article )
{
	var host        = article.getAttribute( "data-host"       );
	var app         = article.getAttribute( "data-app"        );
	var category    = article.getAttribute( "data-category"   );
	var article_id  = article.getAttribute( "data-article-id" );
	var width       = article.getAttribute( "data-width"      );
	var height      = article.getAttribute( "data-height"     );

	var base_path   = host;
	    base_path +=        app ? "/" +        app : "";
	    base_path += "/articles";
	    base_path +=   category ? "/" + category : "";
	    base_path += article_id ? "/" + article_id : "";

	var image_url = base_path + "/topimage-" + width + "x" + height + ".png";

	var addresses = article.getElementsByTagName( "address" );
	if ( 0 < addresses.length )
	{
		var address   = addresses[0];
		var twitter   = address.getAttribute( "data-twitter" );
		var author    = address.innerHTML;

		var picture  = "<img alt='author picture' width='44' height='44' src='/resources/images/articles/authors/" + twitter + ".png'>";
		var byline   = "by <span class='red'>" + author + "</span>";
		var twitline = "<img style='width=20;padding-right:5px;' src='/resources/images/elements/bird.png'><a target='_blank' class='red' href='https://twitter.com/" + twitter + "'>@" + twitter + "</a>";
		var date     = pagecentric.resource2date( article_id );
		var space    = "&nbsp;&nbsp;&nbsp;&nbsp;";
		var arrow    = "<span class='arrow'><img style='padding-bottom:5px;' src='/resources/images/elements/arrow.png'></span>";

		address.innerHTML = picture + space + byline + arrow + twitline + arrow + date + "";
	}

	var sharing = document.getElementById( "article-sharing" );
	if ( sharing )
	{
		sharing.innerHTML  = "<div class='social_buttons'></div>";
		sharing.innerHTML += "<img width='" + width + "' height='" + height + "' alt='Poster' src='" + image_url + "'>";
	}

	var article_video = document.getElementById( "article-video" );
	if ( article_video )
	{
		var token = category ? "articles/" + category + "/" + article_id : "articles/" +article_id;
		var file  = "video";
	
		var social = "<div class='social_buttons'></div>";
		var video  = "<a data-action='selectvideo' data-target='article-video-div' href='#'";
			video += " data-host='"   + host + "'";
			video += " data-app='"    + app        + "'";
			video += " data-token='"  + token      + "'";
			video += " data-width='"  + width      + "'";
			video += " data-height='" + height     + "'";
			video += " data-file='"   + file       + "'";
			video += ">";
			video += "<div id='article-video-div' class='article_video relative'><div class='play'></div><img width='" + width + "' height='" + height + "' alt='Poster' src='" + image_url + "'></div></a>";
	
		article_video.innerHTML = social + video;
		
		var anchors = article_video.getElementsByTagName( "a" );
		if ( 0 < anchors.length )
		{
			pagecentric.addEventListener( anchors[0], "click", selectvideo );
		}

		//article_video.innerHTML = "<video width=580 controls autoplay'><source src='" + videopath + "' type='video/mp4'></video>";
	}
}