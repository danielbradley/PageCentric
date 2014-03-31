package org.pagecentric.util;

import java.util.*;

import java.io.*;

import org.pagecentric.multipart.*;




public class HTTPContext
{
private dictionary request;

private dictionary server;

private dictionary post;

private dictionary get;


public HTTPContext()
{
	this.request = new dictionary();
	this.server  = new dictionary();
	this.post    = new dictionary();
	this.get     = new dictionary();
	
	initialise();
}

public dictionary getRequest()
{
	return request;
}

public void populateServer( dictionary request, Map<String,String> env )
{
	Set<Map.Entry<String,String> > set = env.entrySet();
	Iterator<Map.Entry<String,String> > it = set.iterator();

	while ( it.hasNext() )
	{
		Map.Entry<String,String> e = it.next();

		String key = e.getKey();
		String val = e.getValue();

		request.put( key, val );
	}
}

public void initialise()
{
	Map<String,String> map = System.getenv();

	populateServer( this.server, map );
	populateRequest( this.request, this.get  );

	String content_type = this.server.get( "CONTENT_TYPE" );
	if ( null != content_type )
	{
		if ( content_type.contentEquals( "application/x-www-form-urlencoded" ) )
		{
			populatePost( this.request, this.post );
		}
		else
		if ( content_type.startsWith( "multipart/form-data" ) )
		{
			populateFromMultipart( this.request, this.post, content_type );
		}
	}
}

public void populateRequest( dictionary request, dictionary get )
{
	String query_string = this.server.get( "QUERY_STRING" );

	if ( null != query_string )
	{
		StringTokenizer st = new StringTokenizer( query_string, "&", false );

		while ( st.hasMoreTokens() )
		{
			String keyval = st.nextToken();
			int    del    = keyval.indexOf( "=" );
			String key    = Input.sanitise( keyval.substring( 0, del  ) );
			String val    = Input.sanitise( keyval.substring( del + 1 ) );

			Input i;

			request.put( key, val );
			get.put( key, val );
		}
	}
}

public void populatePost( dictionary request, dictionary post )
{
	StringBuffer sb = new StringBuffer();
	String       key = null;
	String       val = null;

	int ch;

	try
	{
		boolean loop = true;

		while ( loop )
		{
			ch = System.in.read();

			switch ( ch )
			{
			case '=':
				key = sb.toString();
				sb.setLength( 0 );
				break;

			case -1:
				loop = false;
				// intentional fallthrough.

			case '&':
				if ( null == key )
				{
					key = Input.sanitise( sb.toString() );
					val = new String();
				}
				else
				{
					val = Input.sanitise( sb.toString() );
				}
				sb.setLength( 0 );

				post.put( key, val );

				key = null;
				val = null;
				break;

			default:
				sb.append( (char) ch );
			}
		}
	}
	catch ( IOException ex )
	{
	}
}

public dictionary getPost()
{
	return post;
}

public dictionary getServer()
{
	return server;
}

public dictionary getGet()
{
	return get;
}

public void populateFromMultipart( dictionary request, dictionary post, String content_type )
{
	InputStream in        = System.in;
	String      boundary  = extractBoundary( content_type );

	post.put( "boundary", boundary );

	if ( null != boundary )
	{
		Multipart multipart = new Multipart( in, boundary );

		String[] parts = multipart.getParts();
		for ( int i=0; i < parts.length; i++ )
		{
			post.put( "part" + i, parts[i] );
		}
	}
}
public String convertToHex( InputStream in )
{
	StringBuffer sb = new StringBuffer();

	try
	{
		boolean loop = true;
		int     ch;

		while ( loop )
		{
			ch = System.in.read();

			switch ( ch )
			{
			case -1:
				loop = false;
				break;

			default:
				String hex = Integer.toHexString( System.in.read() );
				sb.append( hex );
			}
		}
	}
	catch ( Exception ex )
	{}

	return sb.toString();
}

public String extractBoundary( String content_type )
{
	StringTokenizer st = new StringTokenizer( content_type, "=", false );

	if ( st.hasMoreTokens() ) st.nextToken();

	if ( st.hasMoreTokens() )
	{
		return st.nextToken();
	}
	else
	{
		return null;
	}
}












}
