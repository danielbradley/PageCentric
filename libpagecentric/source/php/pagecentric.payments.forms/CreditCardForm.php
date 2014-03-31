<?php

class CreditCardForm extends Form
{
	function __construct( $iv, $tuple )
	{
		$this->tuple = $tuple;

		$this->profile = array_get( $tuple, "profile" );

		unset( $iv->request["number"] );
		unset( $iv->request["cvv"]    );
		unset( $iv->request["month"]  );
		unset( $iv->request["year"]   );
		
		$this->USER   = array_get( $tuple, "USER" );
		$this->number = new TextInput( $iv, "Credit card number", "", "data-action='creditcard' size='20' autocomplete='off' data-encrypted-name='number'"                                       );
		$this->ccv    = new TextInput( $iv,                "CVV", "", "data-action='creditcard' size='4'  autocomplete='off' data-encrypted-name='cvv'"                                          );
		$this->month  = new TextInput( $iv,               "Exp.", "", "data-action='creditcard' size='2'  autocomplete='off' data-encrypted-name='month' placeholder='mm'   style='width:30px;'" );
		$this->year   = new TextInput( $iv,             "&nbsp;", "", "data-action='creditcard' size='4'  autocomplete='off' data-encrypted-name='year'  placeholder='yyyy' style='width:40px;'" );
	}

	function render( $out )
	{
		$out->inprint( "<form method='post' action='./' id='braintree-payment-form'>" );
		{
			$out->inprint( "<div>" );
			{
				$out->println( "<input type='hidden' name='action'     value='payments_credit_cards_replace'>" );
				$out->println( "<input type='hidden' name='USER'       value='$this->USER'>" );
				$out->println( "<input type='hidden' name='final_four' value='1234' id='creditcard-final_four'>" );
				$out->println( "<input type='hidden' name='profile'    value='$this->profile'>" );
			}
			$out->outprint( "</div>" );
		
			$out->inprint( "<div class='span8'>" );
			{
				$out->inprint( "<div class='row'>" );
				{
					$out->inprint( "<div class='span'>" );
					{
						$this->number->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span'>" );
					{
						$this->ccv->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span'>" );
					{
						$this->month->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span'>" );
					{
						$this->year->render( $out );
					}
					$out->outprint( "</div>" );
				}
				$out->outprint( "</div>");
			}
			$out->outprint( "</div>" );

			$out->println( "<hr>" );
			
			$out->println( "<input id='creditcard-submit' class='red span2 button' type='submit' name='submit' value='Save' disabled>" );
			$out->println( "&nbsp;&nbsp;&nbsp;&nbsp;<a class='gray span2 button' data-toggle='modal' href='#'>Cancel</a>" );
		}
		$out->outprint( "</form>" );
	}
}