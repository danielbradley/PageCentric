<?php
//	Copyright (c) 2013 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

//include_once( $SOURCE . "/pagecentric.util/HTML.php" );

class LoginForm extends Form
{
	function __construct( $request )
	{
//		$this->page    = $page;
//		$this->request = $page->request;
//
//		switch ( array_get( "action", $this->request ) )
//		{
//		case "users_login":
//			$required = array(
//				"username" => "",
//				"password" => ""
//			);
//			
//			$this->iv = new InputValidation( $this->request, $required );
//			if ( $this->iv->validate() )
//			{
//				if ( ! $page->isAuthenticated() )
//				{
//					$this->iv->flag( "unknown" );
//				}
//			}
//			break;
//		
//		default:
//			$this->iv = new InputValidation( $this->request, array() );
//		}
//		
//		$this->field1 = new BootstrapTextInput( $this->iv, "Email",    "username", "class='span3'" );
//		$this->field2 = new BootstrapPasswordInput( $this->iv, "Password", "password", "class='span3' type='password'" );
	}
	
	function render( $out )
	{
		$out->inprint( "<form method='post'>" );
		{
			$out->inprint( "<div>" );
			{
				$out->println( "<input type='hidden' name='action' value='users_login'>" );
			}
			$out->outprint( "</div>" );

			$out->in( "<div class='row'>" );
			{
				$out->in( "<label class='span'>" );
				{
					$out->println( "<tt>Email</tt>" );
					$out->println( "<input class='span3' type='text' name='username' value='' placeholder='Your email address'>" );
				}
				$out->out( "</label>" );

				$out->in( "<label class='span'>" );
				{
					$out->println( "<tt>Password</tt>" );
					$out->println( "<input class='span3' type='password' name='password' value='' placeholder='Your password'>" );
				}
				$out->out( "</label>" );

				$out->in( "<div class='field span span6'>" );
				{
					$out->println( "<input class='' type='submit' name='submit' value='Login'>" );
				}
				$out->out( "</div>" );
			}
			$out->outprint( "</div>" );

			$out->in( "<div class='field'>" );
			{
				$out->println( "<hr>" );
				$out->println( "<p><a class='form floatr' href='../reset_my_password/'>Reset My Password</a></p>" );
			}
			$out->out( "</div>" );
		}
		$out->outprint( "</form>" );
	}
}

?>