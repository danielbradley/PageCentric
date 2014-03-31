<?php

class AccountDetailsForm extends Form
{
	function __construct( $iv, $tuple )
	{
		$this->tuple = $tuple;
		
		$this->USER     = array_get( $tuple, "USER" );
		$this->given    = new TextInput( $iv,  "Given name",  "given_name", "class='span4'" );
		$this->family   = new TextInput( $iv, "Family name", "family_name", "class='span4'" );
		$this->address  = new TextInput( $iv,     "Address",     "address", "class='span8'" );
		$this->address2 = new TextInput( $iv,            "",    "address2", "class='span8'" );
		$this->suburb   = new TextInput( $iv,      "Suburb",      "suburb", "class='span4'" );
		$this->state    = new TextInput( $iv,       "State",       "state", "class='span4'" );
		$this->country  = new TextInput( $iv,     "Country",     "country", "class='span4'" );
		$this->postcode = new TextInput( $iv,    "Postcode",    "postcode", "class='span4'" );
	}

	function render( $out )
	{
		$out->inprint( "<form method='post'>" );
		{
			$out->inprint( "<div>" );
			{
				$out->println( "<input type='hidden' name='action' value='payments_details_replace'>" );
				$out->println( "<input type='hidden' name='USER'   value='$this->USER'>" );
			}
			$out->outprint( "</div>" );
		
			$out->inprint( "<div class='span8'>" );
			{
				$out->inprint( "<div class='row'>" );
				{
					$out->inprint( "<div class='span'>" );
					{
						$this->given->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span'>" );
					{
						$this->family->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span field'>" );
					{
						$this->address->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span'>" );
					{
						$this->address2->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span field'>" );
					{
						$this->suburb->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span field'>" );
					{
						$this->postcode->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span field'>" );
					{
						$this->state->render( $out );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span field'>" );
					{
						$this->country->render( $out );
					}
					$out->outprint( "</div>" );

				}
				$out->outprint( "</div>");

				$out->inprint( "<label class='field'>" );
				{
					$out->println( "<input type='submit' name='submit' value='Save' class='span2'>" );
				}
				$out->outprint( "</label>" );
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</form>" );
	}
}