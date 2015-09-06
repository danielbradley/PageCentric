<?php

namespace pagecentric\payments\models;

class Plans
{
	//	Called from cronjob
	static function Replace( $plan, $out, $debug )
	{
		$id                    = $plan->id;
		$billingDayOfMonth     = $plan->billingDayOfMonth;
		$billingFrequency      = $plan->billingFrequency;
		$currencyIsoCode       = $plan->currencyIsoCode;
		$description           = $plan->description;
		//$discounts             = $plan->discounts;
		$name                  = $plan->name;
		$numberOfBillingCycles = $plan->numberOfBillingCycles;
		$price                 = $plan->price;
		$trialDuration         = $plan->trialDuration;
		$trialDurationUnit     = $plan->trialDurationUnit;
		$trialPeriod           = $plan->trialPeriod;
		$createdAt             = $plan->createdAt->format( "Y-m-d H:i:s" );
		$updatedAt             = $plan->updatedAt->format( "Y-m-d H:i:s" );

		$sql    = "Payments_Plans_Replace( '0', '$id', '$billingDayOfMonth', '$billingFrequency', '$currencyIsoCode', '$description', '$name', '$numberOfBillingCycles', '$price', '$trialDuration', '$trialDurationUnit', '$trialPeriod', '$createdAt', '$updatedAt' )";
		$result = \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );

		if ( "OK" == $result->status )
		{
			$out->println( "Updated: " . $id );
		}
		else
		{
			$out->println( "Error: " . $sql );
		}
	}
	
	static function Delete( $sid, $request, $debug )
	{
	}
	
	static function Retrieve( $sid, $request, $debug )
	{
		$order  = array_get( $request, "order"  );
		$limit  = array_get( $request, "limit"  );
		$offset = array_get( $request, "offset" );
	
		$sql = "Payments_Plans_Retrieve( '$sid', '$order', '$limit', '$offset' )";

		error_log( $sql );
	
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
}
