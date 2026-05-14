// Este codigo esta escrito en c#5.0
using System.Collections.Generic;
using System.Drawing;
using System.Windows.Forms;

namespace HttpTestingClient
{
    partial class MainForm
    {
        /// <summary>
        /// Variable del diseñador necesaria.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Limpiar los recursos que se estén usando.
        /// </summary>
        /// <param name="disposing">true si los recursos administrados se deben desechar; false en caso contrario.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Código generado por el Diseñador de Windows Forms

        /// <summary>
        /// Método necesario para admitir el Diseñador. No se puede modificar
        /// el contenido de este método con el editor de código.
        /// </summary>
        private FlowLayoutPanel flowHeaders;
        private TextBox txtUrl;
        private ComboBox comboMethod;
        private TextBox txtBody;
        private TextBox outputBox;
        private Button btnSend;
        private List<HeaderRow> headerRows = new List<HeaderRow>();

        private void InitializeComponent()
        {
            // Configuración de la Ventana Principal
            this.Text = "HttpTesting Client - C# Edition";
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
            itemOpen.Click += new System.EventHandler(this.itemOpen_Click);

            var itemSave = new ToolStripMenuItem("Guardar Configuración");
            itemSave.Click += new System.EventHandler(this.itemSave_Click);


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

            this.btnSend = new Button
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
            btnSend.Click += new System.EventHandler(this.btnSend_Click);
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
            btnAddHeader.Click += new System.EventHandler(this.btnAddHeader_Click);
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
                Font = new Font("Consolas", 10),
                ReadOnly = true,
                ScrollBars = ScrollBars.Both
            };
            mainContainer.Controls.Add(outputBox);
            // Iniciar con una fila de header
            AddHeaderRow("Content-Type", "application/json");
        }
        #endregion
    }
}