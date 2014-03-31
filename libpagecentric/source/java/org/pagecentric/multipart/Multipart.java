package org.pagecentric.multipart;

import java.util.*;

import java.io.*;



public class Multipart
{
private Vector<String> parts;

private String boundary;


public Multipart( InputStream in, String boundary )
{
	this.boundary = boundary;
	this.parts    = new Vector<String>();

	scanIntoParts( parts, boundary, in );
}

public static void scanIntoParts( Vector<String> parts, String boundary, InputStream in )
{
	BufferedInputStream is = new BufferedInputStream( in );

	StringBuffer sb = new StringBuffer();

	System.err.println( "scanIntoParts" );

	if ( matchBoundary( boundary, is ) )
	{
		boolean loop = true;

		while ( loop )
		{
 			if ( matchBoundary( boundary, is ) )
			{
				System.err.println( "matched, adding: " + sb.toString() );

				parts.add( sb.toString() );
				sb.setLength(0);
			}
			else
			{
				try
				{
					int ch = is.read();
					switch ( ch )
					{
					case -1:
						loop = false;
						break;

					default:
						sb.append( Integer.toHexString( ch ) );
					}
				}
				catch ( IOException ex )
				{
					System.err.println( "IOException: " + ex.getMessage() );

					parts.add( sb.toString() );
					loop = false;
				}
			}
		}
	}
}

public static boolean matchBoundary( String boundary, BufferedInputStream is )
{
	boolean matched = true;

	try
	{
		is.mark( 1000 );

		int len = boundary.length();
		for ( int i=0; (i < len) && matched; i++ )
		{
			matched &= (boundary.charAt( i ) == is.read());
		}
	}
	catch ( IOException ex )
	{
		matched = false;
	}

	try
	{
		if ( matched )
		{
			is.mark( 1000 );
		}
		else
		{
			is.reset();
		}
	}
	catch ( IOException ex )
	{}

	return matched;
}

public String[] getParts()
{
	String[] array = new String[0];

	return parts.toArray( array );
}




}
