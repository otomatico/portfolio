// Este codigo esta escrito en c#5.0
//C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe /target:winexe /reference:Newtonsoft.Json.dll /out:GraphQLClient.exe Program.cs
using System;
using System.Windows.Forms;

namespace HttpTestingClient
{
    internal static class Program
    {
        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }
}