<?php

class InvoicesView extends Control
{
	function __construct( $page )
	{
		$this->tuples = PaymentsController::retrieveInvoices( $page->getSessionId(), $page->getUser(), $page->debug );
	}

	function render( $out )
	{
		$out->inprint( "<div data-class='InvoicesView'>" );
		{
			$out->println( "<h2>Invoices</h2>" );
			$this->printTable( $this->tuples, $out );
		}
		$out->outprint( "</div>" );
	}
	
	function printTable( $tuples, $out )
	{
		$out->inprint( "<table class='mtop30' style='table-layout:fixed;width:100%;'>" );
		{
			$out->inprint( "<thead>" );
			{
				$out->inprint( "<tr>" );
				{
					$out->println( "<td>INVOICE#       </td>" );
					$out->println( "<td>Date raised    </td>" );
					$out->println( "<td>Currency       </td>" );
					$out->println( "<td>Amount         </td>" );
					$out->println( "<td>GST            </td>" );
					$out->println( "<td>Total          </td>" );
					$out->println( "<td>Paid           </td>" );
					$out->println( "<td>Time transacted</td>" );
				}
				$out->outprint( "</tr>" );
			}
			$out->outprint( "</tr>" );

			$out->inprint( "<tbody>" );
			{
				foreach ( $tuples as $tuple )
				{
					$INVOICE    = array_get( $tuple, "INVOICE"    );
					$raised     = array_get( $tuple, "raised"     );
					$currency   = array_get( $tuple, "currency"   );
					$amount     = array_get( $tuple, "amount"     );
					$gst        = array_get( $tuple, "gst"        );
					$total      = array_get( $tuple, "total"      );
					$paid       = array_get( $tuple, "paid"       );
					$transacted = array_get( $tuple, "transacted" );

					$raised     = date_conversion( $raised, "dS M Y" );
				
					$out->inprint( "<tr>" );
					{
						$out->println( "<td>$INVOICE   </td>" );
						$out->println( "<td>$raised    </td>" );
						$out->println( "<td>$currency  </td>" );
						$out->println( "<td>$amount    </td>" );
						$out->println( "<td>$$gst      </td>" );
						$out->println( "<td>$$total    </td>" );
						$out->println( "<td>$paid      </td>" );
						$out->println( "<td>$transacted</td>" );
					}
					$out->outprint( "</tr>" );
				}
			}
			$out->outprint( "</tbody>" );
		}
		$out->outprint( "</table>" );
	}
}