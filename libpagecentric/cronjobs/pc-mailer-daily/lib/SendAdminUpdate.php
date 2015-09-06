<?php

include_once( "SendMessages.php" );

class SendAdminUpdate extends SendMessages
{
	function sendMessages( $out, $debug )
	{
		$kind   = "AdminStatusUpdate";
		$tuple  = first( DBi_callProcedure( DB, "Statistics_Retrieve", $debug ) );

		if ( is_array( $tuple ) )
		{
			$now = date( "Y-m-d H:i:s", time() );

			if ( $this->sendEmail( ADMIN_EMAIL, $kind, $tuple, $out, $debug ) )
			{
				$out->println( "$now, '$kind', ok" );
			}
			else
			{
				$out->println( "$now, '$kind', error" );
			}
		}
	}

	function sendEmail( $email, $kind, $tuple, $out, $debug )
	{
		$success = false;

		$app_name = APP_NAME;
	
		$subject  = file_get_contents( BASE . "/share/templates/" . APP_NAME . "/$kind.Subject.txt", false );
		$text     = $this->getTemplate( $kind, "txt", $tuple );
		$html     = $this->getTemplate( $kind, "htm", $tuple );

		if ( $this->sendMessage( BRAND_NAME, TEAM_EMAIL, ADMIN_EMAIL, $subject, $text, $html, TECH_EMAIL ) )
		{
			$success = true;
		}
		return $success;
	}

	function getTemplate( $kind, $format, $tuple )
	{
		$content = file_get_contents( BASE . "/share/templates/" . APP_NAME . "/$kind.$format", false );

		$impressions          = array_get( $tuple,          "impressions" );
		$new_visitors         = array_get( $tuple,         "new_visitors" );
		$new_users            = array_get( $tuple,            "new_users" );
		$total_visitors       = array_get( $tuple,       "total_visitors" );
		$total_users          = array_get( $tuple,          "total_users" );
		$daily_active_users   = array_get( $tuple,   "daily_active_users" );
		$weekly_active_users  = array_get( $tuple,  "weekly_active_users" );
		$monthly_active_users = array_get( $tuple, "monthly_active_users" );

		$average_visit_duration  = sprintf( "%5.2f", array_get( $tuple, "average_visit_duration"  ) / 60 );
		$average_visits_per_user = sprintf( "%5.2f", array_get( $tuple, "average_visits_per_user" )      );

		if ( 0 < ($new_visitors * $total_users) )
		{
			$c_percent = (0 < $new_visitors) ? ($new_users / $new_visitors) * 100 : 0;

			$d_percent = ($daily_active_users   /  $total_users) * 100;
			$w_percent = ($weekly_active_users  /  $total_users) * 100;
			$m_percent = ($monthly_active_users /  $total_users) * 100;

			$c_percent_fmt = sprintf( "%2.1f%%", $c_percent );
			$d_percent_fmt = sprintf( "%2.1f%%", $d_percent );
			$w_percent_fmt = sprintf( "%2.1f%%", $w_percent );
			$m_percent_fmt = sprintf( "%2.1f%%", $m_percent );

			$content = str_replace(                  '%IMPRESSIONS%',                           "$impressions", $content );
			$content = str_replace(                 '%NEW_VISITORS%',                          "$new_visitors", $content );
			$content = str_replace(                    '%NEW_USERS%',                             "$new_users", $content );

			$content = str_replace(               '%TOTAL_VISITORS%',                        "$total_visitors", $content );
			$content = str_replace(                  '%TOTAL_USERS%',                           "$total_users", $content );

			$content = str_replace(           '%DAILY_ACTIVE_USERS%',                    "$daily_active_users", $content );
			$content = str_replace(          '%WEEKLY_ACTIVE_USERS%',                         "$w_percent_fmt", $content );
			$content = str_replace(         '%MONTHLY_ACTIVE_USERS%',                         "$m_percent_fmt", $content );

			$content = str_replace(       '%AVERAGE_VISIT_DURATION%',           "$average_visit_duration min.", $content );
			$content = str_replace( '%AVERAGE_SITE_VISITS_PER_USER%',               "$average_visits_per_user", $content );

			$content = str_replace(          '%SERVER_NAME%',  SERVER_NAME, $content );
		}

		if ( string_contains( $format, "htm" ) )
		{
			$css = file_get_contents( BASE . "/share/templates/" . APP_NAME . "/_template.css", false );
			
			$content = str_replace( "%TEMPLATE_CSS%", $css, $content );
		}

		return $content;
	}
}

?>