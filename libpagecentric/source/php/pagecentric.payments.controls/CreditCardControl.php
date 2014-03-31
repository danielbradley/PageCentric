<?php

class CreditCardControl extends Control
{
	function __construct( $page )
	{
		$sid  = $page->getSessionId();
		$USER = $page->getUser();
	
		$iv    = new InputValidation( $page->request, array() );
		switch ( $page->getRequest( "action" ) )
		{
		case "payments_credit_cards_replace":
			$iv = new InputValidation( $page->request, PaymentsController::getRequired( "payments_credit_cards_replace" ) );
			if ( $iv->validate() )
			{
				$ctrl = new PaymentsController();
				$ctrl->perform( $sid, $page->request, $page->debug );
			}
			else
			{
				$page->showModal( "modal-credit_card" );
			}
			break;

		case "payments_delete":
			$ctrl = new PaymentsController();
			$ctrl->perform( $sid, $page->request, $page->debug );
			break;
			
		default:
			$page->request["USER"] = $USER;
		}

		$this->tuple            = PaymentsController::retrievePayment    ( $sid, $USER, $page->debug );
		$this->tuplePaymentPlan = PaymentsController::retrievePaymentPlan( $sid, $USER, $page->debug );

		$form  = new CreditCardForm( $iv, $page->request );
		$modal = new FormModal( $form, "Change credit card" );
		$page->addModal( new ModalView( "", "modal-credit_card", $modal, array() ) );


		$params["action"] = "payments_delete";
		$params["USER"]   = $page->getUser();

		$form2  = new YesNoForm( $iv, $params );
		$modal2 = new FormModal( $form2, "Do you want to remove this credit card?" );
		$page->addModal( new ModalView( "", "modal-remove_card", $modal2, array() ) );
	}

	function render( $out )
	{
		$created     = array_get( $this->tuple, "created"     );
		$customer_id = array_get( $this->tuple, "customer_id" );
		$final_four  = array_get( $this->tuple, "final_four"  );
		
		$cost        = array_get( $this->tuplePaymentPlan, "cost"     );
		$switched    = array_get( $this->tuplePaymentPlan, "switched" );
		$pay_day     = date_conversion( $switched, "dS" );

		$protected   = "####-####-####-" . $final_four;
	
		$out->inprint( "<div class='span4'>" );
		{
			if ( $created )
			{
				$date = date_conversion( $created, "d M Y" );
			
				$out->inprint( "<div class='p20' style='border:solid 1px #666;border-radius:10px;'>" );
				{
					$out->println( "<div><b>Account created on:</b><br>$date</div>" );
					$out->println( "<p><a href='./invoices/'>View invoices</a></p>" );
				}
				$out->outprint( "</div>" );
			}

			$out->inprint( "<div class='mtop20 p20' style='border:solid 1px #666;border-radius:10px;'>" );
			{
				if ( ! $final_four )
				{
					$out->println( "<a data-toggle='modal' class='button spanX' style='width:260px;' href='#modal-credit_card'>Add Credit Card</a>" );
				}
				else
				{
					if ( $customer_id )
					{
						$out->println( $protected );
					}
					else
					{
						$out->println( "Authorizing: " . $protected );
					}
					$out->println( "&nbsp;&nbsp;<a data-toggle='modal' href='#modal-remove_card'>Remove card</a>" );
				
					$out->println( "<p>The card above will be charged $$cost on the $pay_day of each month." );

					$out->println( "<p><a data-toggle='modal' class='button' style='width:260px;' href='#modal-credit_card'>Change Credit Card</a></p>" );
				}
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</div>" );
	}
}