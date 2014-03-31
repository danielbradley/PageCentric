<?php

class PaymentPlanControl extends Control
{
	function __construct( $page )
	{
		$this->sid  = $page->getSessionId();
		$this->USER = $page->getUser();
		
		switch( $page->getRequest( "action" ) )
		{
		case "payments_plans_replace":
			$ctrl = new PaymentsController();
			$ctrl->perform( $this->sid, $page->request, $page->debug );
			break;
		}
		$tuple = PaymentsController::retrievePaymentPlan( $this->sid, $this->USER, $page->debug );
		
		$this->plan_id = array_get( $tuple, "plan_id" );
	}

	function render( $out )
	{
		$tier1 = "0";
		$tier2 = "99";
		$tier3 = "199";
	
		$out->inprint( "<div class='w1000 center'>" );
		{
			$out->inprint( "<div class='w940 center'>" );
			{
				$out->inprint( "<div class='row' style=''>" );
				{
					$out->inprint( "<div class='span span4'>" );
					{
						$out->inprint( "<div class='p20' style='border:solid 1px #000;'>" );
						{
							$out->println( "<h2>Free</h2>" );
							$out->println( "<div class='mtop20'><big>$$tier1</big></div>" );
							
							$out->inprint( "<form method='post' class='mtop20'>" );
							{
								$out->inprint( "<div>" );
								{
									$out->println( "<input type='hidden' name='action'  value='payments_plans_replace'>" );
									$out->println( "<input type='hidden' name='USER'    value='$this->USER'>" );
									$out->println( "<input type='hidden' name='plan_id' value='FREE'>" );
									$out->println( "<input type='hidden' name='amount' value='$tier1'>" );
									
									$this->printButtonFor( "FREE", $this->plan_id, $out );
								}
								$out->outprint( "</div>" );
							}
							$out->outprint( "</form>" );
						}
						$out->outprint( "</div>" );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span span4'>" );
					{
						$out->inprint( "<div class='p20' style='border:solid 1px #000;'>" );
						{
							$out->println( "<h2>Premium</h2>" );
							$out->println( "<div class='mtop20'><big>$$tier2</big></div>" );

							$out->inprint( "<form method='post' class='mtop20'>" );
							{
								$out->inprint( "<div>" );
								{
									$out->println( "<input type='hidden' name='action'  value='payments_plans_replace'>" );
									$out->println( "<input type='hidden' name='USER'    value='$this->USER'>" );
									$out->println( "<input type='hidden' name='plan_id' value='PREMIUM13'>" );
									$out->println( "<input type='hidden' name='amount' value='$tier2'>" );

									$this->printButtonFor( "PREMIUM13", $this->plan_id, $out );
								}
								$out->outprint( "</div>" );
							}
							$out->outprint( "</form>" );
						}
						$out->outprint( "</div>" );
					}
					$out->outprint( "</div>" );

					$out->inprint( "<div class='span span4'>" );
					{
						$out->inprint( "<div class='p20' style='border:solid 1px #000;'>" );
						{
							$out->println( "<h2>Unlimited</h2>" );
							$out->println( "<div class='mtop20'><big>$$tier3</big></div>" );

							$out->inprint( "<form method='post' class='mtop20'>" );
							{
								$out->inprint( "<div>" );
								{
									$out->println( "<input type='hidden' name='action'  value='payments_plans_replace'>" );
									$out->println( "<input type='hidden' name='USER'    value='$this->USER'>" );
									$out->println( "<input type='hidden' name='plan_id' value='UNLIMITED13'>" );
									$out->println( "<input type='hidden' name='amount' value='$tier3'>" );

									$this->printButtonFor( "UNLIMITED13", $this->plan_id, $out );
								}
								$out->outprint( "</div>" );
							}
							$out->outprint( "</form>" );
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

	function printButtonFor( $plan, $current, $out )
	{
		switch ( $plan )
		{
		case "FREE":
			switch ( $current )
			{
			case "FREE":
				$out->println( "<input type='submit' name='submit'  value='Current' disabled style='width:260px;'>" );
				break;
				
			default:
				$out->println( "<input type='submit' name='submit'  value='Downgrade To' style='width:260px;'>" );
			}
			break;

		case "PREMIUM13":
			switch ( $current )
			{
			case "PREMIUM13":
				$out->println( "<input type='submit' name='submit'  value='Current' disabled style='width:260px;'>" );
				break;

			case "UNLIMITED13":
				$out->println( "<input type='submit' name='submit'  value='Downgrade To' style='width:260px;'>" );
				break;

			case "FREE":
			default:
				$out->println( "<input type='submit' name='submit'  value='Upgrade To' style='width:260px;'>" );
				break;
			}
			break;

		case "UNLIMITED13":
			switch ( $current )
			{
			case "UNLIMITED13":
				$out->println( "<input type='submit' name='submit'  value='Current' disabled style='width:260px;'>" );
				break;

			case "PREMIUM13":
			case "FREE":
			default:
				$out->println( "<input type='submit' name='submit'  value='Upgrade To' style='width:260px;'>" );
				break;
			}
			break;
		}
	}
}