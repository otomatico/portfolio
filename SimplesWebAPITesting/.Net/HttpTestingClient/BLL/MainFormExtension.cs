
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Drawing;
using System.IO;
using System.Net;
using System.Text;
using System.Windows.Forms;

namespace HttpTestingClient
{
    public partial class MainForm
    {
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
                Margin = new Padding(1),
                Dock = DockStyle.Right
            };

            var txtVal = new TextBox
            {
                //Location = new Point(217, 5),
                Size = new Size(380, 25),
                Text = value,
                Margin = new Padding(4),
                Dock = DockStyle.Right
            };

            var btnDel = new Button
            {
                //Location = new Point(567, 5),
                Size = new Size(24, 20),
                //Font = new Font("Segoe UI", 9),
                Margin = new Padding(2),
                BackColor = Color.IndianRed,//ColorTranslator.FromHtml("#dc3545"),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Text = "X",
                Dock = DockStyle.Right
            };
            btnDel.FlatAppearance.BorderSize = 0;

            btnDel.Click += new EventHandler(this.btnDel_Click);
            var headerRow = new HeaderRow { Panel = panel, Key = txtKey, Val = txtVal };
            btnDel.Tag = headerRow;

            panel.Controls.Add(txtKey);
            panel.Controls.Add(txtVal);
            panel.Controls.Add(btnDel);

            this.flowHeaders.Controls.Add(panel);
            this.headerRows.Add(headerRow);
        }

        private void ExecuteRequest()
        {
            this.outputBox.ForeColor = Color.LightGreen;
            this.outputBox.Text = "Enviando petición...";

            try
            {
                var method = this.comboMethod.SelectedItem.ToString();
                var url = this.txtUrl.Text;
                var body = this.txtBody.Text;

                //ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
                var request = (HttpWebRequest)WebRequest.Create(url);
                request.Method = method;

                // 👇 NO agregar Content-Type a Headers. Usar la propiedad ContentType.
                if ((method == "POST" || method == "PUT") && !string.IsNullOrEmpty(body))
                {
                    request.ContentType = "application/json";
                }

                // Agregar los headers personalizados (excepto Content-Type)
                foreach (var row in this.headerRows)
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
                    this.outputBox.ForeColor = Color.White;
                    this.outputBox.Text = JToken.Parse(responseText).ToString(Formatting.Indented);
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
                        this.outputBox.ForeColor = Color.LightPink;
                        this.outputBox.Text = "ERROR " + ex.Status + ":\r\n" + errorText;
                    }
                }
                else
                {
                    this.outputBox.ForeColor = Color.LightPink;
                    this.outputBox.Text = "ERROR:\r\n" + ex.Message;
                }
            }
            catch (Exception ex)
            {
                this.outputBox.ForeColor = Color.LightPink;
                this.outputBox.Text = "ERROR:\r\n" + ex.Message;
            }
        }

        private void OpenConfiguration()
        {
            var fd = new OpenFileDialog { Filter = "JSON Files (*.json)|*.json" };
            if (fd.ShowDialog() == DialogResult.OK)
            {
                var json = File.ReadAllText(fd.FileName);
                var data = JsonConvert.DeserializeObject<dynamic>(json);

                this.txtUrl.Text = data.url.ToString();
                this.comboMethod.SelectedItem = data.method.ToString();
                this.txtBody.Text = data.body.ToString();

                this.flowHeaders.Controls.Clear();
                this.headerRows.Clear();

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
                    url = this.txtUrl.Text,
                    method = this.comboMethod.SelectedItem.ToString(),
                    body = this.txtBody.Text,
                    headers = this.headerRows.ConvertAll(r => new { key = r.Key.Text, val = r.Val.Text })
                };

                File.WriteAllText(fd.FileName, JsonConvert.SerializeObject(config, Formatting.Indented));
                MessageBox.Show("Guardado con éxito");
            }
        }
    }
}
