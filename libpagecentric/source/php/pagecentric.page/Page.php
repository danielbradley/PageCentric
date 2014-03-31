<?php

include_once( "pagecentric.util/DBi.php" );
include_once( "pagecentric.util/HelperFunctions.php" );
include_once( "pagecentric.util/HTML.php" );
include_once( "pagecentric.util/MVC.php" );
include_once( "pagecentric.objects/SQL.php" );

class Page
{
	var $requestURI;
	var $viva;
	var $title;
	var $modals;

    static function initialise_constant( $constant )
    {
        if ( '.php' == substr( $constant, -4 ) )
        {
            $constant = dirname( $constant );
        }

        if ( '/' != substr( $constant, -1 ) )
        {
            $constant .= "/";
        }

        return $constant;

    }

	static function initialise()
	{
		define( "REQUEST_URI",     Page::initialise_constant( $_SERVER["REQUEST_URI"]  ) );
		define( "REDIRECT_URL",    Page::initialise_constant( $_SERVER["REDIRECT_URL"] ) );
		define( "HTTP_USER_AGENT", $_SERVER["HTTP_USER_AGENT"] );

		if ( ! defined( "DEBUG" ) ) define( "DEBUG", false );
	}

	function __construct()
	{
		$this->out = new Printer();
		$this->debug = ( DEBUG ) ? new Printer() : new NullPrinter();
		$this->debug->startBuffering();

		$this->request  = Input::FilterInput( $_REQUEST, $this->debug );
		$this->pageId   = Page::_generatePageId();
		$this->pagePath = Page::generatePagePath();
		$this->browser  = Page::determineBrowser( HTTP_USER_AGENT );

		$this->viva = new Viva( $this->request, $this->debug );
		$this->title = "";
		$this->modals = array();

		$this->isSupportedBrowser();
	}

	function addModal( $modal )
	{
		$this->modals[] = $modal;
	}

	function showModal( $modal_id )
	{
		$this->showModal = $modal_id;
	}

	function getUser()
	{
		return $this->viva->getUser();
	}

	function getSession()
	{
		return $this->viva->getSession();
	}

	function getSessionId()
	{
		return $this->viva->getSessionId();
	}

	function getIDType()
	{
		return $this->viva->getIDType();
	}

	function getUserType()
	{
		return $this->viva->getIDType();
	}

	function getUserHash()
	{
		return $this->viva->getUserHash();
	}

	function getEmail()
	{
		return $this->viva->getEmail();
	}

	function getGivenName()
	{
		return $this->viva->getGivenName();
	}

	function getFamilyName()
	{
		return $this->viva->getFamilyName();
	}

	function setTitle( $title )
	{
		$this->title = $title;
	}

	function getPageId()
	{
		return $this->pageId;
	}

	function getPagePath()
	{
		return $this->pagePath;
	}

	function getRequest( $name )
	{
		return array_get( $name, $this->request );
	}
	
	function isAuthenticated()
	{
		return $this->viva->isAuthenticated();
	}

	function isAdmin()
	{
		return ("ADMIN" == $this->getIDType());
	}
	
	function isHomePage()
	{
		$this->debug->println( "<!-- " . $_SERVER["SCRIPT_NAME"] . "-->" );
	
		$ret = False;
	
		switch ( $_SERVER["SCRIPT_NAME"] )
		{
		case "/index.php":
			$ret = True;
			break;
		}
		return $ret;
	}

	function isSupportedBrowser()
	{
		$supported = true;
		
		if
		(
			string_contains( HTTP_USER_AGENT, "Trident/5"       )
		||	string_contains( HTTP_USER_AGENT, "Trident/4"       )
		||	string_contains( HTTP_USER_AGENT, "Trident/3"       )
		||	string_contains( HTTP_USER_AGENT, "Trident/2"       )
		||	string_contains( HTTP_USER_AGENT, "Trident/1"       )
		)
		{
			$supported = False;
		}

		if ( array_key_exists( "browser-warning-ignored", $_COOKIE ) )
		{
			$supported = True;
		}
		else
		{
			header( "Set-Cookie: browser-warning-ignored=; expires=Thu, 1-Jan-2100 01:01:01 GMT; path=/" );
		}

		return $supported;
	}

	function logout()
	{
		$this->viva->logout( $this->debug );
	}
	
	function render()
	{
		$this->redirect( $this->debug );
		$this->presync( $this->debug );

		$this->headers( $this->out );
		$this->doctype( $this->out );
		$this->html   ( $this->out );
		
		$this->releaseDB( $this->debug );
	}
	
	function redirect( $debug )
	{
		//	This method may be overridden to redirect to another page.
	}

	function presync( $debug )
	{
		//	This method may be overriden.
	}

	function headers( $out )
	{
		header( "Content-type: text/html\n" );
	}

	function doctype( $out )
	{
		$out->println( "<!DOCTYPE html>" );
	}

	function html( $out )
	{
		$this->htmlStart( $out );
		$this->htmlContent( $out );
		$this->htmlEnd( $out );
	}

	function htmlStart( $out )
	{
		$out->println( "<html>" );
	}

	function htmlContent( $out )
	{
		$this->headStart( $out );
		$this->headContent( $out );
		$this->headEnd( $out );
		
		$this->sync( $this->debug );

		$this->debug->writeBuffer();

		$this->bodyStart( $out );
		$this->bodyContent( $out );
		$this->bodyModals( $out );
		$this->bodyPopups( $out );
		$this->finalJavascript( $out );
		$this->bodyEnd( $out );
	}

	function finalJavascript( $out )
	{}

	function htmlEnd( $out )
	{
		$out->println( "</html>" );
	}

	function headStart( $out )
	{
		$out->println( "<head>" );
	}

	function headContent( $out )
	{
		$this->title( $out );
		$this->meta( $out );
		$this->stylesheets( $out );
		$this->javascript( $out );
	}

	function headEnd( $out )
	{
		$out->println( "</head>" );
	}

	function title( $out )
	{
		$title = $this->getPageTitle();

		$out->println( "<title>" . $title . "</title>" );
	}

		function getPageTitle() { return $this->title; }

	function meta( $out )
	{
		//	This method may be overridden. This is it.
	}

	function stylesheets() {}

	function javascript() {}

	function sync( $debug )
	{
		//	Override this method.
	}

	function bodyStart( $out )
	{
		$page_id     = $this->pageId;
		$pageyoffset = $this->getRequest( "pageyoffset" );
		$hostname    = HOST_NAME;

		$show = $this->getRequest( "show-modal" );
		$show = isset( $this->showModal ) ? $this->showModal : $show;
	
		$out->inprint( "<body data-class='Page' style='overflow-y:scroll;' id='$page_id' data-show='$show' class='$this->browser' data-browser='$this->browser' data-hostname='$hostname' data-pageyoffset='$pageyoffset'>" );
	}

	function bodyContent( $out )
	{
		$out->inprint( "<div id='body-content'>" );
		{
			$this->bodyNavigation( $out );
			$this->bodyBackground( $out );
			$this->bodyBreadcrumbs( $out );
			$this->bodyHeader( $out );
			$this->bodyMiddle( $out );
			$this->bodyFooter( $out );
		}
		$out->outprint( "</div><!-- body-content -->" );
	}

	function bodyEnd( $out )
	{
		$out->outprint( "</body>" );
	}

	function bodyNavigation( $out )
	{
	}

	function bodyBackground( $out )
	{
	}

	function bodyBreadcrumbs( $out )
	{
	}

	function bodyHeader( $out )
	{
	}

	function bodyMiddle( $out )
	{
		$out->in( "<div id='middle'>" );
		{
			$this->middleContent( $out );
		}
		$out->out( "</div>" );
	}
	
	function middleContent( $out ) {}

	function bodyFooter( $out )
	{
	}
	
	function bodyModals( $out )
	{
		foreach ( $this->modals as $modal )
		{
			$out->inprint( "<!-- modal start -->" );
		
			$modal->render( $out );

			$out->outprint( "<!-- modal end -->" );
		}
	}

	function bodyPopups( $out )
	{
		$out->println( "<div id='popup' class='popup'></div>" );
	}

	function releaseDB( $debug )
	{
		//DBi_close( $debug );
	}

	/**************************************************************************
	 *  Below here are private helper methods.
	 **************************************************************************/

	/*
	 *  Converts uri to form 'page-subpage-index', used to unique identify pages.
	 */
	static function _generatePageId()
	{
		$uri = REDIRECT_URL;
		$uri = substr( $uri, 1 );
		$uri = str_replace( "/", "-", $uri );
		$uri = $uri . "index";
		
		return $uri;

		//$id  = (0 == stripos( $uri, "/page/" )) ? $uri : substr( $uri, stripos( $uri, "/page/" ) + 5 );

		//$page_id = substr( $uri, 1 );
		//return str_replace( "/", "-", $uri );
	}

	static function GeneratePageId( $uri )
	{
		$uri = substr( $uri, 1 );
		$uri = str_replace( "/", "-", $uri );
		$uri = $uri . "index";
		
		return $uri;

		//$id  = (0 == stripos( $uri, "/page/" )) ? $uri : substr( $uri, stripos( $uri, "/page/" ) + 5 );

		//$page_id = substr( $uri, 1 );
		//return str_replace( "/", "-", $uri );
	}

	/*
	 *  Converts each element of uri to Title Case e.g. 'Page/Subpage'.
	 */
	static function generatePagePath()
	{
		$path = "";
		{
			$uri  = REDIRECT_URL;
			$uri  = substr( $uri, 1, -1 );
		
			$bits = explode( "/", $uri );
			foreach ( $bits as $bit )
			{
				$path .= "/" . Page::toTitleCase( $bit );
			}
		}
		return $path;
	}
		
		static function toTitleCase( $string )
		{
			$ret = "";
		
			$bits = explode( "_", $string );
			foreach ( $bits as $bit )
			{
				if ( !empty( $bit ) ) $ret .= " " . strtoupper( $bit[0] ) . substr( $bit, 1 );
			}
			return substr( $ret, 1 );
		}

	static function determineBrowser( $http_user_agent )
	{
		$type = "XXX";
		if ( string_contains( $http_user_agent, "Trident" ) )
		{
			$type = "IE";
		}
		else
		if ( string_contains( $http_user_agent, "Chrome" ) )
		{
			if ( string_contains( $http_user_agent, "Windows" ) )
			{
				$type = "CHROME";
			}
			else
			{
				$type = "WEBKIT";
			}
		}
		else
		if ( string_contains( $http_user_agent, "WebKit" ) )
		{
			$type = "WEBKIT";
		}
		else
		{
			$type = "MOZ";
		}
		return $type;
	}
}

?>