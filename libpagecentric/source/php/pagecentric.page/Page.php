<?php

include_once( "pagecentric.util/DBi.php" );
include_once( "pagecentric.util/HelperFunctions.php" );
include_once( "pagecentric.util/HTML.php" );
include_once( "pagecentric.util/MVC.php" );
include_once( "pagecentric.objects/SQL.php" );

class Page
{
	var $request;
	var $out;
	var $debug;
	var $viva;

	private $requestURI;
	private $title;
	private $modals;
	private $pageId;
	private $pagePath;
	private $browser;
	private $classes;
	
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
		$redirect_url = array_key_exists( "REDIRECT_URL", $_SERVER ) ? $_SERVER["REDIRECT_URL"] : "";

		define( "REQUEST_URI",     Page::initialise_constant( $_SERVER["REQUEST_URI"]  ) );
		define( "REDIRECT_URL",    Page::initialise_constant( $redirect_url            ) );
		define( "HTTP_USER_AGENT", array_get( $_SERVER, "HTTP_USER_AGENT" ) );

		if ( ! defined( "DEBUG" ) ) define( "DEBUG", false );
	}

	function __construct()
	{
		$this->out = new Printer();
		$this->debug = ( DEBUG ) ? new Printer() : new NullPrinter();
		$this->debug->startBuffering();

		$this->checkDB();

		$this->classes  = "";
		$this->request  = Input::FilterInput( $_REQUEST, $this->debug );
		$this->pageId   = Page::_generatePageId();
		$this->pagePath = Page::generatePagePath();
		$this->browser  = Page::determineBrowser ( HTTP_USER_AGENT );
		$this->isMobile = Page::determineIfMobile( HTTP_USER_AGENT );

		$this->appendClass( $this->browser );

		$this->viva = new Viva( $this->request, $this->debug );
		$this->title = "";
		$this->modals = array();

		$this->isSupportedBrowser();

		//$this->visiting( $this->debug );
	}

	function checkDB()
	{
		return;
	
		$this->debug->inprint( "<!-- Checking DB" );
		{
			$result = \replicantdb\ReplicantDB::CallFunction( DB, "Users_Exists( 'admin' )", $this->debug );
			if ( "OK" == $result->status )
			{
				if ( $result->value )
				{
					$this->debug->println( "Admin account exists" );
				}
				else
				{
					$this->debug->println( "Admin account does not exist!!!" );
				}
			}
			else
			{
				$this->debug->println( "Hostname: " . $result->hostname );
				$this->debug->println( "Status:   " . $result->status   );
				$this->debug->println( "Failover: " . $result->failover );
				$this->debug->println( "Warning:  " . $result->warning  );
				$this->debug->println( "Message:  " . $result->message  );
				$this->debug->println( "Error:    " . $result->error    );
			}

			$result = \replicantdb\ReplicantDB::CallProcedure( DB, "Users_Retrieve_All( '' )", $this->debug );
			if ( "OK" == $result->status )
			{
				if ( is_array( $result->set ) )
				{
					$this->debug->println( "Got set" );
				}
				else
				{
					$this->debug->println( "No set!!!" );
				}
			}
			else
			{
				$this->debug->println( "Hostname: " . $result->hostname );
				$this->debug->println( "Status:   " . $result->status   );
				$this->debug->println( "Failover: " . $result->failover );
				$this->debug->println( "Warning:  " . $result->warning  );
				$this->debug->println( "Message:  " . $result->message  );
				$this->debug->println( "Error:    " . $result->error    );
			}
		}
		$this->debug->outprint( "-->" );
	}

	function addDialog( $dialog )
	{
		$this->addModal( $dialog );
	}

	function addModal( $modal )
	{
		$this->modals[] = $modal;
	}

	function addClass( $cls )
	{
		$this->appendClass( $cls );
	}

	function appendClass( $cls )
	{
		$this->classes = $this->classes ? $this->classes . " " . $cls : $cls;
	}

	function showDialog( $dialog_id )
	{
		$this->showModal( $dialog_id );
	}

	function showModal( $modal_id )
	{
		$this->showModal = $modal_id;
	}

	function getAuthenticationStatus()
	{
		return $this->viva->getAuthenticationStatus();
	}

	function getBrowser()
	{
		return $this->browser;
	}

	function isMobile()
	{
		return $this->isMobile;
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

	function isReadOnly()
	{
		return $this->viva->isReadOnly();
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

	function setCookie( $name, $value, $path = "/", $httpOnly = TRUE )
	{
		$http_only = $httpOnly ? "HttpOnly" : "";
	
		$cookie = "Set-Cookie: $name=$value" . "; path=$path; $http_only";
		
		header( $cookie );
	}

//	function setJSCookie( $name, $value, $path = "/", $httpOnly = TRUE, $expiry = "" )
//	{
//		$cookie = "$name=$value" . "; path=$path";
//
//		if ( $expiry   ) $cookie .= "; $expiry";
//		if ( $httpOnly ) $cookie .= "; HttpOnly";
//
//		$this->out->inprint( "<script type='text/javascript'>" );
//		{
//			$this->out->println( "document.cookie=$cookie" );
//		}
//		$this->out->outprint( "</script>" );
//	}

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

		if ( ! array_key_exists( "no-modal", $this->request ) )
		{
			$show = $this->getRequest( "show-modal" );
			$show = isset( $this->showModal ) ? $this->showModal : $show;
		}
		$out->inprint( "<body data-class='Page' style='min-height:100vh;margin:0;' id='$page_id' data-show='$show' class='$this->classes' data-browser='$this->browser' data-hostname='$hostname' data-pageyoffset='$pageyoffset'>" );
	}

	function bodyContent( $out )
	{
		$out->inprint( "<div id='body-content' style='min-height:100vh;'>" );
		{
			$out->inprint( "<div id='page-width' style='min-height:100vh;position:relative;'>" );
			{
				$this->pageBehind( $out );
			
				$out->inprint( "<div id='page'>" );
				{
					$this->pageContent( $out );
				}
				$out->outprint( "</div>" );

				$this->bodyFooter( $out );
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</div><!-- body-content -->" );

		$this->bodyNavigation( $out );

		$this->bodyModalBackground( $out );
	}

	function pageBehind( $out )
	{}

	function pageContent( $out )
	{
		$out->inprint( "<div id='page-content'>" );
		{
			$this->bodyNotifications( $out );
			$this->bodyBreadcrumbs( $out );
			$this->bodyHeader( $out );
			$this->bodyMiddle( $out );
		}
		$out->outprint( "</div>" );
	}


	function bodyEnd( $out )
	{
		$out->outprint( "</body>" );
	}

	function bodyNotifications( $out )
	{}

	function bodyNavigation( $out )
	{}

	function bodyBackground( $out )
	{}

	function bodyBreadcrumbs( $out )
	{}

	function bodyHeader( $out )
	{}

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

	function bodyModalBackground( $out )
	{
		$out->println( "<div id='modal-bg'></div>" );
	}
	
	function bodyModals( $out )
	{
		foreach ( $this->modals as $modal )
		{
			$out->println( "<!-- modal start -->" );
		
			$modal->render( $out );

			$out->println( "<!-- modal end -->" );
		}
	}

	function bodyPopups( $out )
	{
		$out->println( "<div id='popup' class='popup'></div>" );
	}

	function visiting( $debug )
	{
		if ( DB )
		{
			$ip_address = $_SERVER["REMOTE_ADDR"];
			$session    = $this->retrieveSession();

			Impressions::Replace( $ip_address, $session, $debug );
		
			if ( ! Visits::Exists( $ip_address, $debug ) )
			{
				Visits::Replace( $ip_address, $debug );
			}
		}
	}

	function retrieveSession()
	{
		$session = "";

		if ( array_key_exists( "session", $this->request ) )
		{
			$session = $this->getRequest( "session" );
		}
		else
		{
			$session = md5(time());
			$this->setCookie( "session", $session, $path = "/", $httpOnly = TRUE );
		}
		return $session;
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

	static function determineIfMobile( $http_user_agent )
	{
		//	See:
		//	http://stackoverflow.com/questions/6636306/mobile-browser-detection
	
		return (bool)preg_match('#\b(ip(hone|od)|android\b.+\bmobile|opera m(ob|in)i|windows (phone|ce)|blackberry'.
                    '|s(ymbian|eries60|amsung)|p(alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
                    '|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', HTTP_USER_AGENT );
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