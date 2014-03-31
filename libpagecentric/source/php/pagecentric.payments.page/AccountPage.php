<?php

class AccountPageX extends Page
{
	function __construct()
	{
		parent::__construct();
		
		$this->controlPaymentPlan    = new PaymentPlanControl   ( $this );
		$this->controlAccountDetails = new AccountDetailsControl( $this );

		$this->tableTransactions     = new TransactionsTable    ( $this );
	}

	function middleContent( $out )
	{
		$this->controlPaymentPlan->render   ( $out );
		$this->controlAccountDetails->render( $out );
		$this->tableTransactions->render    ( $out );
	}
	
	function todo( $out )
	{
?>
<h1>Todo</h1>
<ol>
</ol>
<?php
	}
}