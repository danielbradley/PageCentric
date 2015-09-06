<?php

namespace pagecentric\users\models;

class Users
{
	function Retrieve( $sid, $request, $debug )
	{
		$USER      = array_get( $request, "USER"      );
		$user_hash = array_get( $request, "user_hash" );
		$order     = array_get( $request, "order"     );
		$limit     = array_get( $request, "limit"     );
		$offset    = array_get( $request, "offset"    );

		$sql = "Users_Retrieve( '$sid', '$USER', '$user_hash', '$order', '$limit', '$offset' )";

		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Create( $sid, $request, $debug )
	{
		$email       = array_get( $request, "email"       );
		$password    = array_get( $request, "password"    );
		$given_name  = array_get( $request, "given_name"  );
		$family_name = array_get( $request, "family_name" );
		$user_type   = array_get( $request, "user_type"   );

		if ( ("" == $given_name) && ("" == $family_name) )
		{
			$person_name = new \PersonName( array_get( $request, "name" ) );
		
			$given_name  = $person_name->given_name;
			$family_name = $person_name->family_name;
		}

		if ( ! $user_type ) $user_type = "DEFAULT";
	
		$sql = "Users_Create( '$email', '$password', '$given_name', '$family_name', '$user_type' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Update( $sid, $request, $debug )
	{
		$USER        = array_get( $request, "USER"        );
		$email       = array_get( $request, "email"       );
		$given_name  = array_get( $request, "given_name"  );
		$family_name = array_get( $request, "family_name" );
	
		$sql = "Users_Update( '$USER', '$email', '$given_name', '$family_name' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function VerifyCredentials( $sid, $request, $debug )
	{
		$email    = array_get( $request, "email"    );
		$password = array_get( $request, "password" );
	
		$sql = "Users_Verify_Credentials( '$email', '$password' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function CheckPassword( $sid, $request, $debug )
	{
		$USER     = array_get( $request, "USER"     );
		$password = array_get( $request, "password" );
	
		$sql = "Users_Check_Password( '$email', '$password' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function ChangePassword( $sid, $request, $debug )
	{
		$email        = array_get( $request, "email"        );
		$old_password = array_get( $request, "old_password" );
		$new_password = array_get( $request, "new_password" );
	
		$sql = "Users_Change_Password( '$email', '$old_password', '$new_password' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	static function ExtractGivenName()
	{


		if ( "" == $given_name  ) $given_name  = self::ExtractGivenName( array_get( $request, "given_name"  ) );
		if ( "" == $family_name ) $family_name = self::ExtractGivenName( array_get( $request, "family_name" ) );
	}

	static function ExtractFamilyName()
	{


		if ( "" == $given_name  ) $given_name  = self::ExtractGivenName( array_get( $request, "given_name"  ) );
		if ( "" == $family_name ) $family_name = self::ExtractGivenName( array_get( $request, "family_name" ) );
	}
}


