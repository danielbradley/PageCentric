<?php

class AccountDetailsControl extends Control
{
	function __construct( $page )
	{
		$sid = $page->getSessionId();

		switch ( $page->getRequest( "action" ) )
		{
		case "payments_details_replace":
			$ctrl = new PaymentsController();
			$ctrl->perform( $sid, $page->request, $page->debug );
			break;
			
		default:
			$page->request["USER"] = $page->getUser();
		}
	
		$tuple = PaymentsController::retrievePaymentDetails( $sid, $page->getUser(), $page->debug );
		$iv    = new InputValidation( $tuple, array() );

		$this->formAccountDetails = new AccountDetailsForm( $iv, $tuple );
	
		$this->controlCreditCard  = new CreditCardControl( $page );
	}

	function render( $out )
	{
		$out->inprint( "<div class='w1000 center mtop50'>" );
		{
			$out->inprint( "<div style='border:solid 1px #666;'>" );
			{
				$out->inprint( "<div class='w940 center mtop30 mbot30'>" );
				{
					$out->inprint( "<div class='row'>" );
					{
						$out->inprint( "<div class='span span8'>" );
						{
							if ( isset( $this->formAccountDetails ) ) $this->formAccountDetails->render( $out );
						}
						$out->outprint( "</div>" );

						$out->inprint( "<div class='span span4'>" );
						{
							if ( isset( $this->controlCreditCard ) ) $this->controlCreditCard->render( $out );
						}
						$out->outprint( "</div>" );
					}
					$out->outprint( "</div>" );
				}
				$out->outprint( "</div>" );
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</div>" );
	}
}