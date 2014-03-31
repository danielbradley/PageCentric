<?php

class AdminUsersTable extends Table
{
	function __construct( $tuples )
	{
		$this->tuples = $tuples;
	}

	function render( $out )
	{
		$out->inprint( "<table data-class='AdminUsersTable' class='admin-users'>" );
		{
			$out->inprint( "<tbody>" );
			{
				$out->println( $this->createTableHead() );
			
				foreach ( $this->tuples as $tuple )
				{
					$this->renderTableRow( $tuple, $out );
				}
			}
			$out->outprint( "</tbody>" );
		}
		$out->outprint( "</table>" );
	}

	static function createTableHead()
	{
		return
"
<thead>
	<tr>
		<th>Type             </td>
		<th>User ID          </td>
		<th>Invalid<br>logins</td>
		<th>Created          </td>
		<th>Sent             </td>
		<th>Verified         </td>
		<th>Completion       </td>
		<th>Status/<br>Verified</td>
		<th>IV Scheduled     </td>
		<th>IV Completed     </td>
	</tr>
</thead>
";
	}

	static function renderTableRow( $tuple, $out )
	{
		$USER           = array_get( $tuple, "USER"           );
		$email          = array_get( $tuple, "email"          );
		$created        = array_get( $tuple, "created"        );
		$last_login     = array_get( $tuple, "last_login"     );
		$invalid_logins = array_get( $tuple, "invalid_logins" );
		$profile        = array_get( $tuple, "profile"        );
		$user_status    = array_get( $tuple, "user_status"    );
		$sent           = array_get( $tuple, "sent"           );
		$type           = array_get( $tuple, "type"           );
		$completion     = array_get( $tuple, "completion"     );
		$status         = array_get( $tuple, "status"         );
		$day            = array_get( $tuple, "day"            );
		$timeslot       = array_get( $tuple, "timeslot"       );
		$completed      = array_get( $tuple, "completed"      );

		$cls_sent       = ("0" == $sent)                                       ? "warn" : "ok";
		$cls_verified   = ("CONFIRMED" != $user_status)                        ? "warn" : "ok";
		$cls_invalid    = ( $invalid_logins > 3)                               ? "warn" : "ok";
		$cls_completion = ( $completion < 70)                                  ? "warn" : "ok";
		$cls_status     = ( ("" == $status) && ("CONFIRMED" == $user_status) ) ? "warn" : "ok";

		switch ( $type )
		{
		case "JOBSEEKER":
			$cls_iv         = ( ($completion > 70) && ("" == $day) && ("" != $status) )  ? "warn" : "ok";
			$cls_completed  = ( ("" != $day) && ("" == $completed )) ? "warn" : "ok";

			$profile_link   = "<a href='./view_profile/?profile=$profile'>$USER</a>";
			break;
		
		case "EMPLOYER":
			$cls_iv         = "";
			$cls_completed  = "";

			$profile_link   = "<a href='./view_employer/?USER=$USER'>$USER</a>";
			break;
		}

		$created        = substr( $created, 0, 10 );
		$sent           = ("1" == $sent)                ? "sent" : "UNSENT";
		$iv             = "$day @ $timeslot";

		$out->inprint( "<tr>" );
		{
			$out->println( "<td                              >$type          </td>" );
			$out->println( "<td class='c link' title='$email'>$profile_link  </td>" );
			$out->println( "<td class='$cls_invalid c'       >$invalid_logins</td>" );
			$out->println( "<td                              >$created       </td>" );
			$out->println( "<td class='$cls_sent'            >$sent          </td>" );
			$out->println( "<td class='$cls_verified'        >$user_status   </td>" );
			$out->println( "<td class='$cls_completion c'    >$completion    </td>" );
			$out->println( "<td class='$cls_status'          >$status        </td>" );
			$out->println( "<td class='$cls_iv' title='$iv'  >$day           </td>" );
			$out->println( "<td class='$cls_completed'       >$completed     </td>" );
		}
		$out->outprint( "</tr>" );
	}
}

