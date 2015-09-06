package org.pagecentric.content;

import java.io.*;

public class Content
{
	public static String GetHTMFor( String page_id, String filename )
	{
		String content  = "";
		String BASE     = "/Users/daniel/Sites/share/content/";
		String filepath = BASE + "/" + page_id + "/" + filename + ".htm";
		
		try
		{
			content = ReadAll( new BufferedReader( new FileReader( filepath ) ) );
		}
		catch ( Exception ex )
		{
			content = "<!-- Could not find: " + filepath + " -->";
		}
		return content;
	}
	
	public static String ReadAll( BufferedReader reader ) throws IOException
	{
		StringBuffer sb = new StringBuffer();

		String str;
		while ( null != (str = reader.readLine()) )
		{
			sb.append( str + System.lineSeparator() );
		}
		
		return sb.toString();
	}
}
