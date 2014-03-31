<?php

class AdminUsersView extends View
{
	function __construct( $page )
	{
		$tuples = AdminUsers::retrieveUsers( $page->getSessionId(), $page->debug );

		$this->tableAdminUsers = new AdminUsersTable( $tuples );
	}

	function render( $out )
	{
		$out->inprint( "<div data-class='AdminUsersView'>" );
		{
			$this->tableAdminUsers->render( $out );
		}
		$out->outprint( "</div>" );
	}
}
