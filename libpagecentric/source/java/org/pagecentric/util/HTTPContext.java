package org.pagecentric.util;

import java.util.*;
import java.io.*;

import org.pagecentric.multipart.*;

public class HTTPContext
{
	static private HTTPContext httpContext;
	
	private dictionary request;
	private dictionary server;
	private dictionary post;
	private dictionary get;

	public String AUTH_TYPE    = "";
	public String REDIRECT_URL = "";
	public String SERVER_NAME  = "";
	
	public static HTTPContext Singleton()
	{
		if ( null == httpContext )
		{
			httpContext = new HTTPContext();
		}
		return httpContext;
	}
	
protected HTTPContext()
{
	this.request = new dictionary();
	this.server  = new dictionary();
	this.post    = new dictionary();
	this.get     = new dictionary();
	
	initialise();
	initaliseConstants();
}

public dictionary getRequest()
{
	return request;
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

private void initaliseConstants()
{
	this.AUTH_TYPE    = InitialiseConstant( this.getServer().get( "AUTH_TYPE"    ) );
	this.REDIRECT_URL = InitialiseConstant( this.getServer().get( "REDIRECT_URL" ) );
	this.SERVER_NAME  = InitialiseConstant( this.getServer().get( "SERVER_NAME"  ) );
}

private void initialise()
{
	Map<String,String> map = ExtractDefines();// System.getenv();

	PopulateServer( this.server, ExtractDefines() );
	PopulateRequest( this.request, this.get, this.server  );

	String content_type = this.server.get( "CONTENT_TYPE" );
	if ( null != content_type )
	{
		if ( content_type.contentEquals( "application/x-www-form-urlencoded" ) )
		{
			PopulatePost( this.request, this.post );
		}
		else
		if ( content_type.startsWith( "multipart/form-data" ) )
		{
			PopulateFromMultipart( this.request, this.post, content_type );
		}
	}
}

	private static void PopulateServer( dictionary server, Map<String,String> defines )
	{
		Set<Map.Entry<String,String> >      set = defines.entrySet();
		Iterator<Map.Entry<String,String> > it  = set.iterator();
	
		while ( it.hasNext() )
		{
			Map.Entry<String,String> e = it.next();
	
			String key = e.getKey();
			String val = e.getValue();
	
			server.put( key, val );
		}
	}

	private static void PopulateRequest( dictionary request, dictionary get, dictionary server )
	{
		String query_string = server.get( "QUERY_STRING" );
	
		if ( null != query_string )
		{
			StringTokenizer st = new StringTokenizer( query_string, "&", false );
	
			while ( st.hasMoreTokens() )
			{
				String keyval = st.nextToken();
				int    del    = keyval.indexOf( "=" );
				String key    = Input.sanitise( keyval.substring( 0, del  ) );
				String val    = Input.sanitise( keyval.substring( del + 1 ) );
	
				request.put( key, val );
				get.put    ( key, val );
			}
		}
	}

	private static void PopulatePost( dictionary request, dictionary post )
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


	private static void PopulateFromMultipart( dictionary request, dictionary post, String content_type )
	{
		InputStream in        = System.in;
		String      boundary  = ExtractBoundary( content_type );
	
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

		private static String ExtractBoundary( String content_type )
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

	
private String convertToHex( InputStream in )
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


private Map<String,String> ExtractDefines()
{
	Map<String,String> map = new HashMap<String,String>();
	{
		map.put(         "AUTH_TYPE", System.getProperty( "cgi.auth_type"         ) );
		map.put(    "CONTENT_LENGTH", System.getProperty( "cgi.content_length"    ) );
		map.put(      "CONTENT_TYPE", System.getProperty( "cgi.content_type"      ) );
		map.put( "GATEWAY_INTERFACE", System.getProperty( "cgi.gateway_interface" ) );
		map.put(         "PATH_INFO", System.getProperty( "cgi.path_info"         ) );
		map.put(   "PATH_TRANSLATED", System.getProperty( "cgi.path_translated"   ) );
		map.put(      "QUERY_STRING", System.getProperty( "cgi.query_string"      ) );
		map.put(    "REMOTE_ADDRESS", System.getProperty( "cgi.remote_address"    ) );
		map.put(       "REMOTE_HOST", System.getProperty( "cgi.remote_host"       ) );
		map.put(      "REMOTE_IDENT", System.getProperty( "cgi.remote_ident"      ) );
		map.put(       "REMOTE_USER", System.getProperty( "cgi.remote_user"       ) );
		map.put(    "REQUEST_METHOD", System.getProperty( "cgi.request_method"    ) );
		map.put(       "SCRIPT_NAME", System.getProperty( "cgi.script_name"       ) );
		map.put(       "SERVER_NAME", System.getProperty( "cgi.server_name"       ) );
		map.put(       "SERVER_PORT", System.getProperty( "cgi.server_port"       ) );
		map.put(   "SERVER_PROTOCOL", System.getProperty( "cgi.server_protocol"   ) );
		map.put(       "REQUEST_URI", System.getProperty( "cgi.request_uri"       ) );
		map.put(      "REDIRECT_URL", System.getProperty( "cgi.redirect_url"      ) );
	}
	return map;
}






private static String InitialiseConstant( String constant )
{
//    if ( constant.endsWith( ".class" ) )
//    {
//    	constant = constant.dirname();
//    }

	if ( ! constant.endsWith( "/" ) )
	{
        constant = constant + "/";
    }

    return constant;
}





}
