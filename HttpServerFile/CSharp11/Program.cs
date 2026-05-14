using System;
using System.Threading;

class Program
{
    static void Main(string[] args)
    {
        string ip = "localhost";
        int port = 8080;
        string path = ".";

        // Procesar argumentos de línea de comandos
        for (int i = 0; i < args.Length; i++)
        {
            switch (args[i].ToLower())
            {
                case "-ip":
                    if (i + 1 < args.Length) ip = args[++i];
                    break;
                case "-port":
                    if (i + 1 < args.Length) int.TryParse(args[++i], out port);
                    break;
                case "-path":
                    if (i + 1 < args.Length) path = args[++i];
                    break;
                case "-help":
                case "-h":
                case "-?":
                    ShowHelp();
                    return;
            }
        }

        using (var server = new HttpFileServer(ip, port, path))
        {
            // Iniciar el servidor en un hilo separado
            Thread serverThread = new Thread(server.Start);
            serverThread.Start();
            Console.WriteLine($"http://{ip}:{port}/\t [Path]{path}");
            Console.WriteLine("Presione 'q' + Enter para detener el servidor...");

            // Esperar a que el usuario escriba 'q' + Enter
            while (true)
            {
                string input = Console.ReadLine();
                if (input?.ToLower() == "q")
                {
                    server.Stop();
                    Console.WriteLine("Servicio finalizado por el usuario.");
                    break;
                }
            }

            // Esperar a que el hilo del servidor termine
            serverThread.Join();
        }
    }

    static void ShowHelp()
    {
        Console.WriteLine("Uso: HttpFileServer [OPCIONES]");
        Console.WriteLine();
        Console.WriteLine("Opciones:");
        Console.WriteLine("  -ip <dirección>    Especifica la dirección IP del servidor (por defecto: localhost).");
        Console.WriteLine("  -port <puerto>    Especifica el puerto del servidor (por defecto: 8080).");
        Console.WriteLine("  -path <ruta>      Especifica el directorio raíz para servir archivos (por defecto: directorio actual).");
        Console.WriteLine();
        Console.WriteLine("Ejemplo:");
        Console.WriteLine("  Express -ip 127.0.0.1 -port 8080 -path C:\\MiSitioWeb");
        Console.WriteLine();
        Console.WriteLine("Durante la ejecución:");
        Console.WriteLine("  Presione 'q' + Enter para detener el servidor.");
    }
}
//dotnet publish -c Release -r win-x64 --self-contained false -o ./publish