Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

# --- Configuración de la Ventana Principal ---
$form = New-Object System.Windows.Forms.Form
$form.Text = "API Professional Client - PowerShell Edition"
$form.Size = New-Object System.Drawing.Size(750, 720)
$form.StartPosition = "CenterScreen"
$form.BackColor = [System.Drawing.Color]::FromArgb(224, 224, 224)
$form.Font = New-Object System.Drawing.Font("Segoe UI", 9)

# --- Variables de Estado ---
$headerRows = New-Object System.Collections.Generic.List[Object]

# --- Funciones de Lógica ---

function Add-HeaderRow {
    param($Key = "", $Value = "")
    
    $panel = New-Object System.Windows.Forms.Panel
    $panel.Size = New-Object System.Drawing.Size(660, 35)
    $panel.Margin = New-Object System.Windows.Forms.Padding(0)

    $txtKey = New-Object System.Windows.Forms.TextBox
    $txtKey.Location = New-Object System.Drawing.Point(3, 5)
    $txtKey.Size = New-Object System.Drawing.Size(200, 25)
    $txtKey.Text = $Key

    $txtVal = New-Object System.Windows.Forms.TextBox
    $txtVal.Location = New-Object System.Drawing.Point(207, 5)
    $txtVal.Size = New-Object System.Drawing.Size(350, 25)
    $txtVal.Text = $Value

    $btnDel = New-Object System.Windows.Forms.Button
    $btnDel.Text = "X"
    $btnDel.Location = New-Object System.Drawing.Point(567, 5)
    $btnDel.Size = New-Object System.Drawing.Size(30, 20)
    $btnDel.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#dc3545");
    $btnDel.ForeColor = [System.Drawing.Color]::White
    $rowObj = [PSCustomObject]@{ Panel = $panel; Key = $txtKey; Val = $txtVal }
	# TRUCO: Guardamos la referencia del objeto en la propiedad .Tag del botón
	$btnDel.Tag = $rowObj

    $btnDel.Add_Click({
        # Recuperamos el objeto desde el 'sender' (el botón que disparó el clic)
        $currentBtn = $this
        $currentRow = $currentBtn.Tag
        
        # 1. Eliminar el panel visualmente
        $flowHeaders.Controls.Remove($currentRow.Panel)
        
        # 2. Eliminar de la lista lógica (usando la referencia guardada)
        # Usamos $script: para asegurar que afectamos a la lista del ámbito superior
        $script:headerRows.Remove($currentRow) | Out-Null
        
    })

    $panel.Controls.Add($txtKey)
    $panel.Controls.Add($txtVal)
    $panel.Controls.Add($btnDel)
    
    $flowHeaders.Controls.Add($panel)
    
    
    $script:headerRows.Add($rowObj)
}

function Execute-Request {
    $outputBox.ForeColor = [System.Drawing.Color]::LightGreen
    $outputBox.Text = "Enviando petición..."
    
    try {
        # Construir Headers
        $headers = @{ "Content-Type" = "application/json" }
        foreach ($row in $headerRows) {
            if ($row.Key.Text -ne "") {
                $headers[$row.Key.Text] = $row.Val.Text
            }
        }

        $method = $comboMethod.SelectedItem.ToString()
        $url = $txtUrl.Text
        $body = $txtBody.Text

        $params = @{
            Uri = $url
            Method = $method
            Headers = $headers
        }
        if ($method -ne "GET" -and $body -ne "") { $params.Body = $body }

        # Protocolo de Seguridad
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

        $response = Invoke-RestMethod @params
        $outputBox.ForeColor = [System.Drawing.Color]::White
        $outputBox.Text = $response | ConvertTo-Json -Depth 10
    }
    catch {
        $outputBox.ForeColor = [System.Drawing.Color]::LightPink
        $outputBox.Text = "ERROR:`r`n" + $_.Exception.Message
    }
}

# --- Menú ---
$menuStrip = New-Object System.Windows.Forms.MenuStrip
$menuArchivo = New-Object System.Windows.Forms.ToolStripMenuItem("Archivo")

$itemOpen = New-Object System.Windows.Forms.ToolStripMenuItem("Abrir Configuración", $null, {
    $fd = New-Object System.Windows.Forms.OpenFileDialog
    $fd.Filter = "JSON Files (*.json)|*.json"
    if ($fd.ShowDialog() -eq "OK") {
        $data = Get-Content $fd.FileName | ConvertFrom-Json
        $txtUrl.Text = $data.url
        $comboMethod.SelectedItem = $data.method
        $txtBody.Text = $data.body
        $flowHeaders.Controls.Clear()
        $headerRows.Clear()
        foreach ($h in $data.headers) { Add-HeaderRow $h.key $h.val }
    }
})

$itemSave = New-Object System.Windows.Forms.ToolStripMenuItem("Guardar Configuración", $null, {
    $fd = New-Object System.Windows.Forms.SaveFileDialog
    $fd.Filter = "JSON Files (*.json)|*.json"
    if ($fd.ShowDialog() -eq "OK") {
        $config = @{
            url = $txtUrl.Text
            method = $comboMethod.SelectedItem.ToString()
            body = $txtBody.Text
            headers = foreach($r in $headerRows) { @{ key = $r.Key.Text; val = $r.Val.Text } }
        }
        $config | ConvertTo-Json | Out-File $fd.FileName
        [System.Windows.Forms.MessageBox]::Show("Guardado con éxito")
    }
})

$menuArchivo.DropDownItems.Add($itemOpen) | Out-Null
$menuArchivo.DropDownItems.Add($itemSave) | Out-Null
$menuStrip.Items.Add($menuArchivo) | Out-Null
$form.MainMenuStrip = $menuStrip
$form.Controls.Add($menuStrip)

# --- UI Controls ---
$mainContainer = New-Object System.Windows.Forms.Panel
$mainContainer.Location = "20, 40"
$mainContainer.Size = "700, 750"
$mainContainer.BackColor = [System.Drawing.Color]::White
$form.Controls.Add($mainContainer)

# Método y URL
$lblMethod = New-Object System.Windows.Forms.Label
$lblMethod.Text = "Método:"
$lblMethod.Location = "10, 7"
$lblMethod.Size = "60, 20"
$mainContainer.Controls.Add($lblMethod)

$comboMethod = New-Object System.Windows.Forms.ComboBox
$comboMethod.Items.AddRange(@("POST","GET","PUT","DELETE"))
$comboMethod.SelectedIndex = 0
$comboMethod.Location = "10, 30"
$comboMethod.Size = "80, 25"
$comboMethod.DropDownStyle = "DropDownList"
$mainContainer.Controls.Add($comboMethod)

$lblUrl = New-Object System.Windows.Forms.Label
$lblUrl.Text = "URL:"
$lblUrl.Location = "100, 7"
$mainContainer.Controls.Add($lblUrl)

$txtUrl = New-Object System.Windows.Forms.TextBox
$txtUrl.Location = "100, 30"
$txtUrl.Size = "450, 25"
$txtUrl.Text = "http://localhost:8080/api/graphql"
$mainContainer.Controls.Add($txtUrl)

$btnSend = New-Object System.Windows.Forms.Button
$btnSend.Text = "Enviar"
$btnSend.Location = "560, 28"
$btnSend.Size = "120, 28"
$btnSend.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#0078d4");
$btnSend.ForeColor = [System.Drawing.Color]::White
$btnSend.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Bold)
$btnSend.Add_Click({ Execute-Request })
$mainContainer.Controls.Add($btnSend)

# Headers
$lblHeaders = New-Object System.Windows.Forms.Label
$lblHeaders.Text = "Headers (Key / Value):"
$lblHeaders.Location = "10, 70"
$lblHeaders.Size = "200, 20"
$mainContainer.Controls.Add($lblHeaders)

$flowHeaders = New-Object System.Windows.Forms.FlowLayoutPanel
$flowHeaders.Location = "10, 90"
$flowHeaders.Size = "670, 100"
$flowHeaders.AutoScroll = $true
$flowHeaders.BorderStyle = "FixedSingle"
$flowHeaders.BackColor = [System.Drawing.Color]::DarkGray
$mainContainer.Controls.Add($flowHeaders)

$btnAddHeader = New-Object System.Windows.Forms.Button
$btnAddHeader.Text = "+ Añadir Header"
$btnAddHeader.Location = "10, 195"
$btnAddHeader.Size = "120, 25"
$btnAddHeader.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#28a745");
$btnAddHeader.ForeColor = [System.Drawing.Color]::White
$btnAddHeader.Add_Click({ Add-HeaderRow })
$mainContainer.Controls.Add($btnAddHeader)

# Body
$lblBody = New-Object System.Windows.Forms.Label
$lblBody.Text = "Body (JSON):"
$lblBody.Location = "10, 227"
$mainContainer.Controls.Add($lblBody)

$txtBody = New-Object System.Windows.Forms.TextBox
$txtBody.Multiline = $true
$txtBody.Location = "10, 250"
$txtBody.Size = "670, 150"
$txtBody.Font = "Consolas, 10"
$txtBody.ScrollBars = "Vertical"
$mainContainer.Controls.Add($txtBody)

# Output
$outputBox = New-Object System.Windows.Forms.TextBox
$outputBox.Multiline = $true
$outputBox.Location = "10, 410"
$outputBox.Size = "670, 200"
$outputBox.BackColor = [System.Drawing.Color]::Black
$outputBox.ForeColor = [System.Drawing.Color]::White
$outputBox.Font = "Consolas, 9"
$outputBox.ReadOnly = $true
$outputBox.ScrollBars = "Both"
$mainContainer.Controls.Add($outputBox)

# Iniciar con una fila de header
Add-HeaderRow "Content-Type" "application/json"

$form.ShowDialog()