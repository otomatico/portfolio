using System;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;

public class ActionResult
{
    public string ContentType { get; set; } = "";
    public int StatusCode { get; set; } = 0;
    public byte[] Body { get; set; } = null;
}

public class HttpFileServer : IDisposable
{
    private readonly string _ip;
    private readonly int _port;
    private readonly string _path;
    private HttpListener _listener;
    private bool _isRunning = true;

    public HttpFileServer(string ip = "localhost", int port = 8080, string path = ".")
    {
        _ip = ip;
        _port = port;
        _path = Path.GetFullPath(path);
    }

    private ActionResult ListenerContext(HttpListenerContext context, bool debug = false)
    {
        var result = new ActionResult();
        var request = context.Request;
        var localPath = request.Url.LocalPath;

        if (debug)
        {
            Console.WriteLine($"\t{request.HttpMethod} {localPath}");
        }

        var pathFile = Path.Combine(_path, localPath.TrimStart('/')).Replace("\\", "/");

        if (!File.Exists(pathFile) && !Directory.Exists(pathFile))
        {
            string msg = $"{pathFile}\t NO EXISTE";
            result.StatusCode = 404;
            result.ContentType = "text/plain";
            result.Body = new byte[0];
            Console.WriteLine(msg);
            return result;
        }

        if (debug)
        {
            Console.WriteLine($"\t\t{pathFile}");
        }

        if (Directory.Exists(pathFile))
        {
            pathFile = Path.Combine(pathFile, "index.html");
            if (!File.Exists(pathFile))
            {
                result.StatusCode = 403;
                result.ContentType = "text/plain";
                result.Body = Encoding.UTF8.GetBytes("Directory listing denied");
                return result;
            }
        }

        result.Body = File.ReadAllBytes(pathFile);
        result.StatusCode = 200;

        var fileInfo = new FileInfo(pathFile);
        switch (fileInfo.Extension.ToLower())
        {
            case ".html":
                result.ContentType = "text/html;charset=\"utf-8\"";
                break;
            case ".htm":
                result.ContentType = "text/html;charset=\"utf-8\"";
                break;
            case ".js":
                result.ContentType = "application/javascript;charset=\"utf-8\"";
                break;
            case ".css":
                result.ContentType = "text/css;charset=\"utf-8\"";
                break;
            case ".txt":
                result.ContentType = "text/plain";
                break;
            default:
                result.ContentType = "application/octet-stream";
                break;
        }

        return result;
    }

    public void Start()
    {
        _listener = new HttpListener();
        _listener.Prefixes.Add($"http://{_ip}:{_port}/");
        _listener.Start();

        while (_isRunning && _listener.IsListening)
        {
            var context = _listener.GetContext();
            ThreadPool.QueueUserWorkItem((o) => ProcessRequest(o), context);
        }
    }

    public void Stop()
    {
        _isRunning = false;
        _listener?.Stop();
    }

    private void ProcessRequest(object state)
    {
        var context = (HttpListenerContext)state;
        var buffer = ListenerContext(context, true);

        context.Response.ContentType = buffer.ContentType;
        context.Response.ContentLength64 = buffer.Body.Length;
        if (buffer.Body.Length > 0)
        {
            context.Response.OutputStream.Write(buffer.Body, 0, buffer.Body.Length);
        }
        context.Response.StatusCode = buffer.StatusCode;
        context.Response.Close();
    }

    public void Dispose()
    {
        _listener?.Stop();
        _listener?.Close();
    }
}