<?php

class APIPage extends Page
{
	function __construct()
	{
		parent::__construct();
	}

	function render()
	{
		$out      = $this->out;
		$debug    = $this->debug;
		$sid      = $this->getSessionId();
		$USER     = $this->getUser();
		$action   = $this->getRequest( "action" );
		$postdata = file_get_contents("php://input");

		//error_log( $postdata );

		switch ( REDIRECT_URL )
		{

		//	Preregistrations
		case "/api/preregistrations/":
			echo JSON3::Encode( Preregistrations::Retrieve( $sid, $this->request, $debug ) );
			break;

		case "/api/preregistrations/replace/":
			echo JSON3::Encode( Preregistrations::Replace( $sid, $this->request, $debug ) );
			break;

		case "/api/preregistrations/unsent/":
			echo JSON3::Encode( Preregistrations::Unsent( $sid, $this->request, $debug ) );
			break;

		case "/api/preregistrations/sent/":
			echo JSON3::Encode( Preregistrations::Sent( $sid, $this->request, $debug ) );
			break;

		//	Users
		case "/api/users/":
			echo JSON3::Encode( \pagecentric\users\models\Users::Retrieve( $sid, $this->request, $debug ) );
			break;

		case "/api/users/create/":
			echo JSON3::Encode( \pagecentric\users\models\Users::Create( $sid, $this->request, $debug ) );
			break;

		case "/api/users/create_and_login/":
			$result = \pagecentric\users\models\Users::Create( $sid, $this->request, $debug );
			if ( "OK" == $result->status )
			{
				echo JSON3::Encode( \pagecentric\users\models\Sessions::Replace( $sid, $this->request, $debug ) );
			}
			else
			{
				echo JSON3::Encode( $result );
			}
			break;

		case "/api/users/update/":
			echo JSON3::Encode( \pagecentric\users\models\Users::Create( $sid, $this->request, $debug ) );
			break;

		case "/api/users/verify_credentials/":
			echo JSON3::Encode( \pagecentric\users\models\Users::VerifyCredentials( $sid, $this->request, $debug ) );
			break;

		case "/api/users/check_password/":
			echo JSON3::Encode( \pagecentric\users\models\Users::CheckPassword( $sid, $this->request, $debug ) );
			break;

		//	Users Sessions
		case "/api/users/sessions/":
			echo JSON3::Encode( \pagecentric\users\models\Sessions::Retrieve( $sid, $this->request, $debug ) );
			break;

		case "/api/users/sessions/current/":
			echo JSON3::Encode( \pagecentric\users\models\Sessions::Current( $sid, $this->request, $debug ) );
			break;

		case "/api/users/sessions/replace/":
			echo JSON3::Encode( \pagecentric\users\models\Sessions::Replace( $sid, $this->request, $debug ) );
			break;

		case "/api/users/sessions/terminate/":
			echo JSON3::Encode( \pagecentric\users\models\Sessions::Terminate( $sid, $this->request, $debug ) );
			break;


		//	Payments
		case "/api/payments/plans/":
			echo JSON3::Encode( \pagecentric\payments\models\Plans::Retrieve( $sid, $this->request, $debug ) );
			break;

		case "/api/payments/customers/":
			break;

		case "/api/payments/customers/replace/":
			echo JSON3::Encode( \pagecentric\payments\models\Customers::Replace( $sid, $this->request, $debug ) );
			break;

		case "/api/payments/customers/delete/":
			echo JSON3::Encode( \pagecentric\payments\models\Customers::Delete( $sid, $this->request, $debug ) );
			break;

		case "/api/payments/customers/by_user/":
			echo JSON3::Encode( \pagecentric\payments\models\Customers::Retrieve( $sid, $this->request, $debug ) );
			break;

		case "/api/payments/credit_cards/replace/":
			echo JSON3::Encode( \pagecentric\payments\models\CreditCards::Replace( $sid, $this->request, $debug ) );
			break;

		


		//	Articles
		case "/api/articles/":
			echo JSON3::Encode( Articles::RetrieveSubset( $sid, $this->request, $debug ) );
			break;

		case "/api/articles/subjects/":
			echo JSON3::Encode( Articles::RetrieveSubjects( $sid, $this->request, $debug ) );
			break;

		case "/api/articles/info/":
			echo JSON3::Encode( Articles::RetrieveInfo( $sid, $this->request, $debug ) );
			break;

		//	Deprecated
		case "/api/accounts/create/":
			echo JSON3::Encode( Accounts::Create( $sid, $this->request, $debug ) );
			break;

		case "/api/accounts/create_and_login/":
			echo JSON3::Encode( Accounts::CreateAndLogin( $this, $sid, $this->request, $debug ) );
			break;

		case "/api/status/":
			if ( array_key_exists( "sessionid", $_COOKIE ) ) echo $_COOKIE["sessionid"];
			break;



		//	Selects
		case "/api/multiselect/":
			echo JSON3::Encode( Selects::RetrieveMulti( $sid, $this->request, $debug ) );
			break;






		default:
			echo "{ 'status' : 'ERROR', 'message', 'INVALID_API_ENDPOINT' }";
			http_response_code( 404 );
		}
	}
}
