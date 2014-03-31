<?php
//	Copyright (c) 2013 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class CreateAccountForm extends Form
{
	function __construct( $page, $tuple, $debug )
	{
		$tuple["user_type"] = defined( "USER_TYPE" ) ? USER_TYPE : "DEFAULT";

		$this->form = $this->createForm( $tuple );
	}
	
	function render( $out )
	{
		$out->println( $this->form );
	}
	
	static function createForm( $tuple )
	{
		$given_name  = array_get( $tuple, "given_name"  );
		$family_name = array_get( $tuple, "family_name" );
		$email       = array_get( $tuple, "email"       );
		$password    = array_get( $tuple, "password"    );
	
		return
"
<form method='post' data-class='CreateAccountForm'>
	<div>
		<input type='hidden' name='action' value='users_create'>
		<input type='hidden' name='user_type' value='DEFAULT'>
	</div>
	<div class='row'>
		<div class='span span3'>
			<label >
				<tt>Given name</tt>
				<input class='span3' type='text'     placeholder='Your given name'    name='given_name'  value='$given_name'>
			</label>
		</div>
		<div class='span span3'>
			<label >
				<tt>Family name</tt>
				<input class='span3' type='text'     placeholder='Your family name'   name='family_name' value='$family_name'>
			</label>
		</div>
		<div class='span span3 field'>
			<label >
				<tt>Email</tt>
				<input class='span3' type='text'     placeholder='Your email address' name='email'       value='$email'>
			</label>
		</div>
		<div class='span span3 field'>
			<label >
				<tt>Password</tt>
				<input class='span3' type='password' placeholder='A secure password'  name='password'    value='$password'>
			</label>
		</div>
		<div class='span span6 field'>
			<label>
				<input class='btn btn-success' type='submit' name='submit' value='Create Account'>
			</label>
		</div>
	</div>
	<hr class='field'>
	<p>
		<span>Already have an account?</span>&nbsp;<a href='/login/'>Login</a>
	</p>
</form>
";
	}
	
	
	
}

?>