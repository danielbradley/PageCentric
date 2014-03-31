<?php

class TransactionsTable
{
	function __construct( $page )
	{
		$this->tuples = PaymentsController::retrieveTransactionsByUser( $page->getSessionId(), $page->getUser(), $page->debug );
	}

	function render( $out )
	{
		$out->inprint( "<table data-class='TransactionsTable' class='sm-table' style='table-layout:fixed;width:100%;'>" );
		{
			$this->printHead( $this->tuples, $out );
			$this->printBody( $this->tuples, $out );
		}
		$out->outprint( "</table>" );
	}
	
	function printHead( $tuples, $out )
	{
		$out->inprint( "<thead>" );
		{
			$out->inprint( "<tr>" );
			{
				$out->println( "<th style='width:110px'>Date           </th>" );
				$out->println( "<th style='width: 80px'>Amount         </th>" );
				$out->println( "<th style='width: auto'>Description    </th>" );
				$out->println( "<th style='width:200px'>Status         </th>" );
			}
			$out->outprint( "</tr>" );
		}
		$out->outprint( "</tr>" );
	}
	
	function printBody( $tuples, $out )
	{
		$out->inprint( "<tbody>" );
		{
			foreach ( $tuples as $tuple )
			{
				$date        = array_get( $tuple, "date"        );
				$description = array_get( $tuple, "description" );
				$amount      = array_get( $tuple, "amount"      );
				$status      = array_get( $tuple, "status"      );

				$date = date_conversion( $date, "dS M Y" );
			
				$out->inprint( "<tr>" );
				{
					$out->println( "<td>$date       </td>" );
					$out->println( "<td>$amount     </td>" );
					$out->println( "<td>$description</td>" );
					$out->println( "<td>$status     </td>" );
				}
				$out->outprint( "</tr>" );
			}
		}
		$out->outprint( "</tbody>" );
	}
}
