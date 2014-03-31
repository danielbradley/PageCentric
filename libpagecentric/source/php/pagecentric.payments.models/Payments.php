<?php

class Payments extends Model
{
	static function retrieveCreditCards( $page )
	{
		return PaymentsController::retrieveCreditCards( $page->getSessionId(), $page->getUser(), $page->debug );
	}

	static function retrievePaymentPlan( $page )
	{
		return PaymentsController::retrievePaymentPlan( $page->getSessionId(), $page->getUser(), $page->debug );
	}
	
	static function hasCreditCard( $page )
	{
		return PaymentsController::hasCreditCard( $page->getSessionId(), $page->getUser(), $page->debug );
	}
}