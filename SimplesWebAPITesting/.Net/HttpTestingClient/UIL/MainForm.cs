// Este codigo esta escrito en c#5.0
using System;
using System.Windows.Forms;

namespace HttpTestingClient
{
    public partial class MainForm : Form
    {
        public MainForm()
        {
            InitializeComponent();
        }
        private void itemSave_Click(object sender, EventArgs e)
        {
            SaveConfiguration();
        }
        private void itemOpen_Click(object sender, EventArgs e)
        {
            this.OpenConfiguration();
        }
        private void btnAddHeader_Click(object sender, EventArgs e)
        {
            AddHeaderRow();
        }
        private void btnDel_Click(object sender, EventArgs e)
        {
            var currentBtn = (Button)sender;
            var currentRow = (HeaderRow)currentBtn.Tag;
            this.flowHeaders.Controls.Remove(currentRow.Panel);
            this.headerRows.Remove(currentRow);
        }

        private void btnSend_Click(object sender, EventArgs e)
        {
            ExecuteRequest();
        }

    }
}