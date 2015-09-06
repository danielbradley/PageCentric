<?php

class InitialisationControl extends View
{
	function __construct( $page, $debug )
	{
	
		$ctrl = new InitialisationController();
		$this->installed = $ctrl->perform( $page->viva->session, $page->request, $debug );

		$dirs[] = BASE . "/share/sql/" . DB . "/" . DB_VERSION . "/01Tables";
		$dirs[] = BASE . "/share/sql/" . DB . "/" . DB_VERSION . "/02Views";
		$dirs[] = BASE . "/share/sql/" . DB . "/" . DB_VERSION . "/03Data";
		$dirs[] = BASE . "/share/sql/" . DB . "/" . DB_VERSION . "/04StoredProcedures";
		$dirs[] = BASE . "/share/sql/" . DB . "/" . DB_VERSION . "/05Final";
	
		$this->view = new DBCredentialsView( $page, $debug );
		$this->view->setSQLDirs( $dirs );
	}
	
	function render( $out )
	{
		$out->inprint( "<div class='InitialisationControl'>" );
		{
			if ( $this->installed )
			{
				$out->println( "<table>" );
				{
					foreach ( $this->installed as $row )
					{
						$out->println( $row );
					}
				}
				$out->println( "</table>" );
				$out->println( "<p>Go to <a href='../'>home page</a>.</p>" );
			}
			else
			{
				$out->inprint( "<div class='pad'>" );
				{
					$this->view->render( $out );
				}
				$out->outprint( "</div>" );
			}
		}
		$out->outprint( "</div>" );
	}
	
}
