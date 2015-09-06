<?php

class Phase0Plans
{
	function perform( $out, $debug )
	{
		$now = date( "Y-m-d H:i:s", time() );

		$out->println( $now . ", 'Updating plans'" );

		$plans = Braintree_Plan::all();

		foreach ( $plans as $plan )
		{
			\pagecentric\payments\models\Plans::Replace( $plan, $out, $debug );
		}
	}
}

?>