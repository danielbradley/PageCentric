<?php

class DBCredentialsView extends View
{
	function __construct( $page, $debug )
	{
		$this->page    = $page;
		$this->openpageSQLDirs[] = "_SQL";

		$this->pcSQLDirs[] = BASE . "/dep/libpagecentric/share/sql/pagecentric/" . PC_VERSION . "/01Tables";
		$this->pcSQLDirs[] = BASE . "/dep/libpagecentric/share/sql/pagecentric/" . PC_VERSION . "/02Views";
		$this->pcSQLDirs[] = BASE . "/dep/libpagecentric/share/sql/pagecentric/" . PC_VERSION . "/04StoredProcedures";

		$this->webAppSQLDirs = array();
	}
	
	function setSQLDirs( $dirs )
	{
		$this->webAppSQLDirs = $dirs;
	}
	
	function render( $out )
	{
		$db = DB . DB_VERSION;
	
?>
		<form method='post' action='/initialise/'>
			<fieldset>
				<div>
					<input type='hidden' name='action' value='initialise_db'>

					<div class='row'>
						<div class='span span3'>
							<label>
								<tt>Target Database</tt>
								<input type='text' name='' value='<?php echo $db ?>' disabled='disabled'><br>
							</label>
						</div>

						<div class='span span3'>
							<label>
								<tt>DB Admin Username</tt>
								<input type='text' name='dbadmin' value=''><br>
							</label>
						</div>

						<div class='span span3'>
							<label>
								<tt>DB Admin Password</tt>
								<input type='password' name='dbpassword' value=''><br>
							</label>
						</div>
					</div>

					<label>&nbsp;</label>
					<input class='button' type='submit' name='submit' value='Initialise DB'><br>
				</div>
			</fieldset>
			
			<?php $this->writeSQLDirs( "PageCentric SQL", "v", $this->pcSQLDirs,     $out ) ?>
			<?php $this->writeSQLDirs(      "WebApp SQL", "w", $this->webAppSQLDirs, $out ) ?>
		
		</form>
<?php
	}
	
	function writeSQLDirs( $label, $x, $dirs, $out )
	{
		$out->println( "<br>" );
		$out->println( "<fieldset><legend>$label</legend>" );
		$out->indent();
		{
			$out->println( "<div>" );
			$out->indent();
			{
				$i = 1;
				foreach ( $dirs as $dir )
				{
					$out->inprint( "<label>" );
					{
						$out->println( "<span>Dir $i</span>" );
						$out->println( "<input class='text' style='width:600px' name='$x$i' value='$dir'><br>" );
					}
					$out->outprint( "</label>" );
					
					$i++;
				}
			}
			$out->outdent();
			$out->println( "</div>" );
		}
		$out->outdent();
		$out->println( "</fieldset>" );
	}
}

?>