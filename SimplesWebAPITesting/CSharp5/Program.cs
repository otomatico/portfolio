// Este codigo esta escrito en c#5.0
//C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe /target:winexe /reference:Newtonsoft.Json.dll /out:GraphQLClient.exe Program.cs

using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Text;
using System.Windows.Forms;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;

namespace HttpTestingClient
{
    public class MainForm : Form
    {
        private FlowLayoutPanel flowHeaders;
        private TextBox txtUrl;
        private ComboBox comboMethod;
        private TextBox txtBody;
        private TextBox outputBox;
        private List<HeaderRow> headerRows = new List<HeaderRow>();

        public MainForm()
        {
            InitializeComponent();
        }

        private void InitializeComponent()
        {
            // Configuración de la Ventana Principal
            this.Text = "API Professional Client - C# Edition";
            this.Size = new Size(750, 720);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.BackColor = Color.FromArgb(224, 224, 224);
            this.Font = new Font("Segoe UI", 9);
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedSingle;
            this.Margin = new System.Windows.Forms.Padding(4);
            //this.AutoScaleDimensions = new System.Drawing.SizeF(10F, 24F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ResumeLayout(false);
            this.PerformLayout();

            // Menú
            var menuStrip = new MenuStrip();
            var menuArchivo = new ToolStripMenuItem("Archivo");

            var itemOpen = new ToolStripMenuItem("Abrir Configuración");
            itemOpen.Click += (sender, e) => OpenConfiguration();

            var itemSave = new ToolStripMenuItem("Guardar Configuración");
            itemSave.Click += (sender, e) => SaveConfiguration();

            menuArchivo.DropDownItems.Add(itemOpen);
            menuArchivo.DropDownItems.Add(itemSave);
            menuStrip.Items.Add(menuArchivo);
            this.MainMenuStrip = menuStrip;
            this.Controls.Add(menuStrip);

            // Contenedor Principal
            var mainContainer = new Panel
            {
                Location = new Point(20, 40),
//                Size = new Size(700, 650),
                BackColor = Color.White,
                Dock = System.Windows.Forms.DockStyle.Fill,
                AutoSize = true
            };
            this.Controls.Add(mainContainer);

            // Método y URL
            var lblMethod = new Label
            {
                Text = "Método:",
                Location = new Point(10, 7),
                Size = new Size(60, 20)
            };
            mainContainer.Controls.Add(lblMethod);

            comboMethod = new ComboBox
            {
                Items = { "POST", "GET", "PUT", "DELETE" },
                SelectedIndex = 0,
                Location = new Point(10, 30),
                Size = new Size(80, 25),
                DropDownStyle = ComboBoxStyle.DropDownList
            };
            mainContainer.Controls.Add(comboMethod);

            var lblUrl = new Label
            {
                Text = "URL:",
                Location = new Point(100, 7)
            };
            mainContainer.Controls.Add(lblUrl);

            txtUrl = new TextBox
            {
                Location = new Point(100, 30),
                Size = new Size(450, 25),
                Text = "http://localhost:8080/api/graphql"
            };
            mainContainer.Controls.Add(txtUrl);

            var btnSend = new Button
            {
                Text = "Enviar",
                Location = new Point(560, 28),
                Size = new Size(120, 28),
                BackColor = ColorTranslator.FromHtml("#0078d4"),
                ForeColor = Color.White,
                FlatStyle = System.Windows.Forms.FlatStyle.Flat,
                Font = new Font("Segoe UI", 9, FontStyle.Bold)
            };
            btnSend.FlatAppearance.BorderSize = 0;
            btnSend.Click += (sender, e) => ExecuteRequest();
            mainContainer.Controls.Add(btnSend);

            // Headers
            var lblHeaders = new Label
            {
                Text = "Headers (Key / Value):",
                Location = new Point(10, 70),
                Size = new Size(200, 20)
            };
            mainContainer.Controls.Add(lblHeaders);

            flowHeaders = new FlowLayoutPanel
            {
                Location = new Point(10, 90),
                Size = new Size(530, 100),
                //AutoSize = true,
                AutoScroll = true,
                BorderStyle = BorderStyle.Fixed3D,
                BackColor = Color.DarkGray,
                FlowDirection = System.Windows.Forms.FlowDirection.LeftToRight,
                Dock = System.Windows.Forms.DockStyle.None
            };
            mainContainer.Controls.Add(flowHeaders);

            var btnAddHeader = new Button
            {
                Text = "+ Añadir",
                Location = new Point(560, 90),
                Size = new Size(120, 28),
                BackColor = ColorTranslator.FromHtml("#28a745"),
                ForeColor = Color.White,
                FlatStyle = System.Windows.Forms.FlatStyle.Flat
            };
            btnAddHeader.FlatAppearance.BorderSize = 0;
            btnAddHeader.Click += (sender, e) => AddHeaderRow();
            mainContainer.Controls.Add(btnAddHeader);

            // Body
            var lblBody = new Label
            {
                Text = "Body (JSON):",
                Location = new Point(10, 207)
            };
            mainContainer.Controls.Add(lblBody);

            txtBody = new TextBox
            {
                Multiline = true,
                Location = new Point(10, 230),
                Size = new Size(680, 150),
                Font = new Font("Consolas", 10),
                ScrollBars = ScrollBars.Vertical
            };
            mainContainer.Controls.Add(txtBody);

            // Output
            outputBox = new TextBox
            {
                Multiline = true,
                Location = new Point(10, 390),
                Size = new Size(680, 250),
                BackColor = Color.Black,
                ForeColor = Color.White,
                Font = new Font("Consolas",10),
                ReadOnly = true,
                ScrollBars = ScrollBars.Both
            };
            mainContainer.Controls.Add(outputBox);

            // Iniciar con una fila de header
            //AddHeaderRow("Content-Type", "application/json");

        }

        private void AddHeaderRow(string key = "", string value = "")
        {
            var panel = new Panel
            {
                Size = new Size(500, 20),
                Margin = new Padding(4)
            };

            var txtKey = new TextBox
            {
                //Location = new Point(3, 5),
                Size = new Size(100, 25),
                Text = key,
                Margin = new System.Windows.Forms.Padding(1),
                Dock = System.Windows.Forms.DockStyle.Right
            };

            var txtVal = new TextBox
            {
                //Location = new Point(217, 5),
                Size = new Size(380, 25),
                Text = value,
                Margin = new System.Windows.Forms.Padding(4),
                Dock = System.Windows.Forms.DockStyle.Right
            };

            var btnDel = new Button
            {
                //Location = new Point(567, 5),
                Size = new Size(24, 20),
                //Font = new Font("Segoe UI", 9),
                Margin = new System.Windows.Forms.Padding(2),
                BackColor = System.Drawing.Color.IndianRed,//ColorTranslator.FromHtml("#dc3545"),
                ForeColor = Color.White,
                FlatStyle = System.Windows.Forms.FlatStyle.Flat,
                Text = "X",
                Dock = System.Windows.Forms.DockStyle.Right                
            };
            btnDel.FlatAppearance.BorderSize = 0;

            btnDel.Click += (sender, e) =>
            {
                var currentBtn = (Button)sender;
                var currentRow = (HeaderRow)currentBtn.Tag;

                flowHeaders.Controls.Remove(currentRow.Panel);
                headerRows.Remove(currentRow);
            };
            var headerRow = new HeaderRow { Panel = panel, Key = txtKey, Val = txtVal };
            btnDel.Tag = headerRow;

            panel.Controls.Add(txtKey);
            panel.Controls.Add(txtVal);
            panel.Controls.Add(btnDel);

            flowHeaders.Controls.Add(panel);
            headerRows.Add(headerRow);
        }


        private void ExecuteRequest()
        {
            outputBox.ForeColor = Color.LightGreen;
            outputBox.Text = "Enviando petición...";

            try
            {
                var method = comboMethod.SelectedItem.ToString();
                var url = txtUrl.Text;
                var body = txtBody.Text;

                //ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
                var request = (HttpWebRequest)WebRequest.Create(url);
                request.Method = method;

                // 👇 NO agregar Content-Type a Headers. Usar la propiedad ContentType.
                if ((method == "POST" || method == "PUT") && !string.IsNullOrEmpty(body))
                {
                    request.ContentType = "application/json"; 
                }

                // Agregar los headers personalizados (excepto Content-Type)
                foreach (var row in headerRows)
                {
                    if (!string.IsNullOrEmpty(row.Key.Text) && row.Key.Text.ToLower() != "content-type")
                    {
                        request.Headers.Add(row.Key.Text, row.Val.Text);
                    }
                }

                // Si el método NO es GET o DELETE y hay cuerpo, enviar el cuerpo
                if ((method == "POST" || method == "PUT") && !string.IsNullOrEmpty(body))
                {
                    var bytes = Encoding.UTF8.GetBytes(body);
                    request.ContentLength = bytes.Length;

                    using (var stream = request.GetRequestStream())
                    {
                        stream.Write(bytes, 0, bytes.Length);
                    }
                }

                using (var response = (HttpWebResponse)request.GetResponse())
                using (var stream = response.GetResponseStream())
                using (var reader = new StreamReader(stream))
                {
                    var responseText = reader.ReadToEnd();
                    outputBox.ForeColor = Color.White;
                    outputBox.Text = JValue.Parse(responseText).ToString(Formatting.Indented);
                }
            }
            catch (WebException ex)
            {
                if (ex.Response != null)
                {
                    using (var stream = ex.Response.GetResponseStream())
                    using (var reader = new StreamReader(stream))
                    {
                        var errorText = reader.ReadToEnd();
                        outputBox.ForeColor = Color.LightPink;
                        outputBox.Text = "ERROR "+ex.Status+":\r\n"+errorText;
                    }
                }
                else
                {
                    outputBox.ForeColor = Color.LightPink;
                    outputBox.Text = "ERROR:\r\n"+ex.Message;
                }
            }
            catch (Exception ex)
            {
                outputBox.ForeColor = Color.LightPink;
                outputBox.Text = "ERROR:\r\n"+ex.Message;
            }
        }
        private void OpenConfiguration()
        {
            var fd = new OpenFileDialog { Filter = "JSON Files (*.json)|*.json" };
            if (fd.ShowDialog() == DialogResult.OK)
            {
                var json = File.ReadAllText(fd.FileName);
                var data = JsonConvert.DeserializeObject<dynamic>(json);

                txtUrl.Text = data.url.ToString();
                comboMethod.SelectedItem = data.method.ToString();
                txtBody.Text = data.body.ToString();

                flowHeaders.Controls.Clear();
                headerRows.Clear();

                foreach (var h in data.headers)
                {
                    AddHeaderRow(h.key.ToString(), h.val.ToString());
                }
            }
        }

        private void SaveConfiguration()
        {
            var fd = new SaveFileDialog { Filter = "JSON Files (*.json)|*.json" };
            if (fd.ShowDialog() == DialogResult.OK)
            {
                var config = new
                {
                    url = txtUrl.Text,
                    method = comboMethod.SelectedItem.ToString(),
                    body = txtBody.Text,
                    headers = headerRows.ConvertAll(r => new { key = r.Key.Text, val = r.Val.Text })
                };

                File.WriteAllText(fd.FileName, JsonConvert.SerializeObject(config, Formatting.Indented));
                MessageBox.Show("Guardado con éxito");
            }
        }

        [STAThread]
        public static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }

    public class HeaderRow
    {
        public Panel Panel { get; set; }
        public TextBox Key { get; set; }
        public TextBox Val { get; set; }
    }
}