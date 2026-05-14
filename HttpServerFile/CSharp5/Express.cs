// Este codigo esta escrito en c#5.0
//C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe /target:exe /out:Express.exe Express.cs
// $>.\Espress.exe -port 54321 -path "$((Get-Location).Path)\Views\"
using System;
using System.Diagnostics;
using System.Threading.Tasks;
using System.Threading;
using System.IO;
using System.Net;

public class Program
{
	static HttpListener listener;
	static Process p;
	public static void Main(string[] args)
	{
		  Application.EnableVisualStyles();
  		Application.SetCompatibleTextRenderingDefault(false);
		int port = 4321;
		string localPath = Directory.GetCurrentDirectory();
		try
		{

			for (int count = 0; count < args.Length; count++)
			{
				if (args[count].ToLower() == "-port")
				{
					port = int.Parse(args[++count]);
				}

				if (args[count].ToLower() == "-path")
				{
					localPath = args[++count];
				}
			}

			//string url = String.Format("http://*:{0}/",port);
			string url = String.Format("http://localhost:{0}/", port);
			listener = new HttpListener();
			listener.Prefixes.Add(url);
			listener.Start();
			Console.WriteLine("Listening at {0}... -> [{1}]", url, localPath);
			ExecuteHTA(url + "index.html");

			while (listener.IsListening)
			{
				var context = listener.GetContext();
				var requestUrl = context.Request.Url;
				var response = context.Response;

				Console.WriteLine("{0:yyyy.MM.dd - HH:mm:ss}>\t{1}", DateTime.UtcNow, requestUrl);

				string localFile = context.Request.RawUrl;
				if (localFile == "/")
				{
					localFile = "index.html";
				}
				if(localFile == "/quit"){
					listener.Abort ();
					
					//response.Close();
					//listener.Stop();
					listener.Close();
				}
				//string currentFile = Path.Combine(localPath,localFile);
				string currentFile = String.Format("{0}{1}", localPath, localFile);
				Console.WriteLine("{0:yyyy.MM.dd - HH:mm:ss}>\t{1}", DateTime.UtcNow, currentFile);
				if (File.Exists(currentFile))
				{
					var bytes = File.ReadAllBytes(currentFile);
					response.ContentLength64 = bytes.Length;
					response.ContentType = GetMimeType(Path.GetExtension(currentFile));
					response.OutputStream.Write(bytes, 0, bytes.Length);
				}
				else
				{
					response.StatusCode = 400;
				}
				response.Close();
				Console.WriteLine("{0:yyyy.MM.dd - HH:mm:ss}>\t{1}", DateTime.UtcNow, response.StatusCode);
			}

		}
		catch (Exception ex)
		{
			Console.WriteLine("{0}", ex);
		}
	}

	public static string GetMimeType(string fileName)
	{
		string mimeTypeDefault = "application/octet-stream";
		string ext = System.IO.Path.GetExtension(fileName).ToLower();
		Microsoft.Win32.RegistryKey regKey = Microsoft.Win32.Registry.ClassesRoot.OpenSubKey(ext);
		string _mimeType = "";
		if (regKey != null && regKey.GetValue("Content Type") != null)
		{
			_mimeType = regKey.GetValue("Content Type").ToString();
		}
		return (_mimeType == null || _mimeType == "") ? (ext == ".js" ? "text/javascript" : mimeTypeDefault) : _mimeType;
	}
	public static void ExecuteHTA(string url)
	{
		string chrome = "C:/Program Files/Google/Chrome/Application/chrome_proxy.exe";
		p = new Process();
		//p.StartInfo.WindowStyle = ProcessWindowStyle.Hidden;
		p.StartInfo.FileName = chrome;
		p.StartInfo.Arguments = "--profile-directory=Default --app=\"" + url + "\"";
		//p.StartInfo.UseShellExecute = false;
		//p.EnableRaisingEvents = true;
		//p.StartInfo.RedirectStandardOutput = true;
		//p.StartInfo.RedirectStandardError = true;
		//p.Exited += new EventHandler(stopedServer);
		p.Start();
	}
	private static void stopedServer(object sender, System.EventArgs e)
	{

		Console.WriteLine(
			"Exit time    : " + p.ExitTime + "\n" +
			"Exit code    : " + p.ExitCode + "\n" +
			"Elapsed time : " + Math.Round((p.ExitTime - p.StartTime).TotalMilliseconds));
		if(p.HasExited){
			listener.Stop();
		}
		//listener.Close();
		//eventHandled.TrySetResult(true);
	}
}

