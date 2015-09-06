
function AppleDateTime( appledatetime )
{
	this.date = "0000-00-00";
	this.time = "00:00:00";
	this.zone = "+00:00";

	if ( appledatetime )
	{
		var adt = appledatetime.replace( ' ', '+' );
		
		var bits = adt.split( 'T' );

		if ( 0 < bits.length )
		{
			this.date = bits[0].substring( 0, 10 );
		}
		
		if ( 1 < bits.length )
		{
			var time_bits = bits[1].split( '+' );
			if ( 0 < time_bits.length )
			{
				this.time = time_bits[0].substring( 0, 8 );
			}
			if ( 1 < time_bits.length )
			{
				this.zone = '+' + time_bits[1].substring( 0, 2 ) + ':' + time_bits[1].substring( 2, 2 )
			}
		}
	}
}

AppleDateTime.prototype.getDate
=
function()
{
	return this.date;
}

AppleDateTime.prototype.getTime
=
function()
{
	return this.time;
}

AppleDateTime.prototype.getTimeZone
=
function()
{
	return this.zone;
}

AppleDateTime.prototype.getZone
=
function()
{
	return this.zone;
}

AppleDateTime.prototype.isValid
=
function()
{
	return ("0000-00-00" != this.date);
}