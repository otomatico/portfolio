
param(
    [Parameter(Position = 0)]
    [string]$Modo = "",

    [Parameter(Position = 1)]
    [string]$Entrada = "",

    [Parameter(Position = 2)]
    [string]$Salida = ""
)

$ErrorActionPreference = "Stop"

Add-Type -AssemblyName System.Drawing

# ============================================================
# INTERFAZ DE TERMINAL
# ============================================================

function Write-Title {
    Clear-Host
    Write-Host ""
    Write-Host "  ==========================================" -ForegroundColor Cyan
    Write-Host "          CONVERTIDOR BIN <-> PNG" -ForegroundColor Cyan
    Write-Host "  ==========================================" -ForegroundColor Cyan
    Write-Host "       PowerShell Edition 1.0" -ForegroundColor DarkGray
    Write-Host ""
}

function Write-Info([string]$Mensaje) {
    Write-Host "  [>] " -ForegroundColor Cyan -NoNewline
    Write-Host $Mensaje
}

function Write-OK([string]$Mensaje) {
    Write-Host "  [OK] " -ForegroundColor Green -NoNewline
    Write-Host $Mensaje
}

function Write-ErrorMessage([string]$Mensaje) {
    Write-Host "  [ERROR] " -ForegroundColor Red -NoNewline
    Write-Host $Mensaje
}

function Format-Bytes([long]$Bytes) {
    if ($Bytes -ge 1MB) {
        return ("{0:N2} MB" -f ($Bytes / 1MB))
    }
    if ($Bytes -ge 1KB) {
        return ("{0:N2} KB" -f ($Bytes / 1KB))
    }
    return "$Bytes bytes"
}

# ============================================================
# FUNCIONES PNG: METADATOS PARA CONSERVAR EL TAMAÑO ORIGINAL
# ============================================================

function Get-CRC32([byte[]]$Bytes) {
    [long]$crc = 4294967295L

    foreach ($b in $Bytes) {
        $crc = $crc -bxor [long]$b

        for ($i = 0; $i -lt 8; $i++) {
            if (($crc -band 1) -ne 0) {
                $crc = ($crc -shr 1) -bxor 3988292384L
            }
            else {
                $crc = $crc -shr 1
            }
            $crc = $crc -band 4294967295L
        }
    }

    return [uint32](($crc -bxor 4294967295L) -band 4294967295L)
}

function ConvertTo-BigEndian([uint32]$Valor) {
    $b = [BitConverter]::GetBytes($Valor)
    [Array]::Reverse($b)
    return ,$b
}

function ConvertFrom-BigEndian([byte[]]$Bytes, [int]$Offset) {
    [byte[]]$b = $Bytes[$Offset..($Offset + 3)]
    [Array]::Reverse($b)
    return [BitConverter]::ToUInt32($b, 0)
}

function Add-PngOriginalSize([string]$Path, [long]$OriginalSize) {
    [byte[]]$png = [IO.File]::ReadAllBytes($Path)

    # Chunk PNG tEXt: palabra clave + NUL + valor ASCII
    [byte[]]$textData = [Text.Encoding]::ASCII.GetBytes(
        "ConvertidorBytes" + [char]0 + $OriginalSize.ToString(
            [Globalization.CultureInfo]::InvariantCulture
        )
    )

    [byte[]]$chunkType = [Text.Encoding]::ASCII.GetBytes("tEXt")
    [byte[]]$crcInput = $chunkType + $textData
    [uint32]$crc = Get-CRC32 $crcInput

    [byte[]]$chunk = @(
        (ConvertTo-BigEndian ([uint32]$textData.Length))
    ) + $chunkType + $textData + @(
        (ConvertTo-BigEndian $crc)
    )

    # Insertar el metadato antes del chunk IEND
    $offset = 8
    $iendOffset = -1

    while ($offset + 12 -le $png.Length) {
        [uint32]$length = ConvertFrom-BigEndian $png $offset
        $type = [Text.Encoding]::ASCII.GetString(
            $png, $offset + 4, 4
        )

        if ($type -eq "IEND") {
            $iendOffset = $offset
            break
        }

        $offset += [int]$length + 12
    }

    if ($iendOffset -lt 0) {
        throw "No se encontró el final del PNG."
    }

    [byte[]]$result = @()
    if ($iendOffset -gt 0) {
        $result += $png[0..($iendOffset - 1)]
    }
    $result += $chunk
    $result += $png[$iendOffset..($png.Length - 1)]

    [IO.File]::WriteAllBytes($Path, $result)
}

function Get-PngOriginalSize([string]$Path) {
    [byte[]]$png = [IO.File]::ReadAllBytes($Path)

    if ($png.Length -lt 8) {
        throw "El archivo no es un PNG válido."
    }

    $offset = 8

    while ($offset + 12 -le $png.Length) {
        [uint32]$length = ConvertFrom-BigEndian $png $offset
        $type = [Text.Encoding]::ASCII.GetString(
            $png, $offset + 4, 4
        )

        if ([long]$offset + 12 + $length -gt $png.Length) {
            throw "El PNG contiene un bloque incompleto."
        }

        if ($type -eq "tEXt" -and $length -le 1048576) {
            $text = [Text.Encoding]::ASCII.GetString(
                $png, $offset + 8, [int]$length
            )

            if ($text.StartsWith("ConvertidorBytes" + [char]0)) {
                $value = $text.Substring(17)

                [long]$size = 0
                $valid = [long]::TryParse(
                    $value,
                    [Globalization.NumberStyles]::None,
                    [Globalization.CultureInfo]::InvariantCulture,
                    [ref]$size
                )

                if ($valid -and $size -ge 1) {
                    return $size
                }
            }
        }

        if ($type -eq "IEND") {
            break
        }

        $offset += [int]$length + 12
    }

    return $null
}

# ============================================================
# BINARIO -> PNG
# ============================================================

function Convert-BinToPng(
    [string]$InputPath,
    [string]$OutputPath
) {
    [byte[]]$bytes = [IO.File]::ReadAllBytes($InputPath)
    [long]$count = $bytes.Length

    if ($count -eq 0) {
        throw "El archivo de entrada está vacío."
    }

    # Tres bytes por píxel: rojo, verde y azul.
    [int]$width = [Math]::Max(
        1, [int][Math]::Ceiling([Math]::Sqrt($count / 3.0))
    )
    [int]$height = [Math]::Max(
        1, [int][Math]::Ceiling($count / (3.0 * $width))
    )

    Write-Info "Tamaño original: $(Format-Bytes $count)"
    Write-Info "Dimensiones PNG: ${width} x ${height}"
    Write-Info "Codificando bytes..."

    $bitmap = New-Object System.Drawing.Bitmap($width, $height)

    try {
        [long]$index = 0

        for ($y = 0; $y -lt $height; $y++) {
            for ($x = 0; $x -lt $width; $x++) {
                [int]$r = 0
                [int]$g = 0
                [int]$b = 0

                if ($index -lt $count) {
                    $r = $bytes[$index]
                    $index++
                }
                if ($index -lt $count) {
                    $g = $bytes[$index]
                    $index++
                }
                if ($index -lt $count) {
                    $b = $bytes[$index]
                    $index++
                }

                $bitmap.SetPixel(
                    $x, $y,
                    [System.Drawing.Color]::FromArgb($r, $g, $b)
                )
            }
        }

        $bitmap.Save(
            $OutputPath,
            [System.Drawing.Imaging.ImageFormat]::Png
        )
    }
    finally {
        $bitmap.Dispose()
    }

    # Guardar el tamaño exacto dentro del PNG.
    Add-PngOriginalSize $OutputPath $count

    Write-OK "PNG creado correctamente."
    Write-Info "Salida: $OutputPath"
    Write-Info "Tamaño: $(Format-Bytes (Get-Item $OutputPath).Length)"
}

# ============================================================
# PNG -> BINARIO
# ============================================================

function Convert-PngToBin(
    [string]$InputPath,
    [string]$OutputPath
) {
    Write-Info "Leyendo metadatos del PNG..."

    $originalSize = Get-PngOriginalSize $InputPath

    if ($null -eq $originalSize) {
        Write-Host "  [AVISO] " -ForegroundColor Yellow -NoNewline
        Write-Host "El PNG no tiene el tamaño original guardado."
        Write-Host "  Se exportarán los tres canales RGB de todos los píxeles."
    }
    else {
        Write-Info "Tamaño original: $(Format-Bytes $originalSize)"
    }

    $bitmap = New-Object System.Drawing.Bitmap($InputPath)

    try {
        [long]$capacity = [long]$bitmap.Width * $bitmap.Height * 3

        if ($null -ne $originalSize -and $originalSize -gt $capacity) {
            throw "El tamaño guardado supera la capacidad de la imagen."
        }

        if ($null -eq $originalSize) {
            $originalSize = $capacity
        }

        if ($originalSize -gt [int]::MaxValue) {
            throw "La salida es demasiado grande para esta versión del script."
        }

        Write-Info "Dimensiones: $($bitmap.Width) x $($bitmap.Height)"
        Write-Info "Extrayendo datos RGB..."

        [byte[]]$result = New-Object byte[] ([int]$originalSize)
        [long]$index = 0

        for ($y = 0; $y -lt $bitmap.Height -and $index -lt $originalSize; $y++) {
            for ($x = 0; $x -lt $bitmap.Width -and $index -lt $originalSize; $x++) {
                $pixel = $bitmap.GetPixel($x, $y)

                $result[$index] = $pixel.R
                $index++

                if ($index -lt $originalSize) {
                    $result[$index] = $pixel.G
                    $index++
                }

                if ($index -lt $originalSize) {
                    $result[$index] = $pixel.B
                    $index++
                }
            }
        }
    }
    finally {
        $bitmap.Dispose()
    }

    [IO.File]::WriteAllBytes($OutputPath, $result)

    Write-OK "Binario generado correctamente."
    Write-Info "Bytes escritos: $($result.Length)"
    Write-Info "Salida: $OutputPath"
}

# ============================================================
# VALIDACIÓN Y EJECUCIÓN
# ============================================================

function Invoke-Conversion {
    param(
        [string]$SelectedMode,
        [string]$InputPath,
        [string]$OutputPath
    )

    if ($SelectedMode -notin @("--bin", "--img")) {
        throw "Modo inválido. Utiliza --bin o --img."
    }

    if (-not (Test-Path -LiteralPath $InputPath -PathType Leaf)) {
        throw "No existe el archivo de entrada: $InputPath"
    }

    $InputPath = (Resolve-Path -LiteralPath $InputPath).Path
    $OutputPath = [IO.Path]::GetFullPath($OutputPath)

    if ($InputPath -eq $OutputPath) {
        throw "La entrada y la salida no pueden ser el mismo archivo."
    }

    if (Test-Path -LiteralPath $OutputPath) {
        $answer = Read-Host "La salida ya existe. ¿Sobrescribir? (s/N)"
        if ($answer -notmatch '^(s|si|sí)$') {
            Write-Host "  Operación cancelada." -ForegroundColor Yellow
            return
        }
    }

    if ($SelectedMode -eq "--bin") {
        if ([IO.Path]::GetExtension($OutputPath) -ne ".png") {
            throw "La salida debe tener extensión .png"
        }

        Convert-BinToPng $InputPath $OutputPath
    }
    else {
        if ([IO.Path]::GetExtension($InputPath) -ne ".png") {
            throw "La entrada debe tener extensión .png"
        }

        Convert-PngToBin $InputPath $OutputPath
    }
}

try {
    if ($Modo -eq "") {
        Write-Title

        Write-Host "  [1] " -ForegroundColor Cyan -NoNewline
        Write-Host "Binario -> PNG"
        Write-Host "  [2] " -ForegroundColor Cyan -NoNewline
        Write-Host "PNG -> Binario"
        Write-Host "  [0] " -ForegroundColor Cyan -NoNewline
        Write-Host "Salir"
        Write-Host ""

        $choice = Read-Host "Selecciona una opción"

        if ($choice -eq "0") {
            return
        }

        switch ($choice) {
            "1" { $Modo = "--bin" }
            "2" { $Modo = "--img" }
            default { throw "Opción no válida." }
        }

        $Entrada = Read-Host "Ruta del archivo de entrada"
        $Salida = Read-Host "Ruta del archivo de salida"
    }

    Write-Title
    Invoke-Conversion $Modo $Entrada $Salida
}
catch {
    Write-ErrorMessage $_.Exception.Message
    exit 1
}

Write-Host ""
Write-Host "  Pulsa una tecla para finalizar..." -ForegroundColor DarkGray
if ([Environment]::UserInteractive -and [Console]::KeyAvailable -eq $false) {
    # No bloquear una ejecución redirigida desde otra aplicación.
    if ($Host.Name -eq "ConsoleHost") {
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    }
}
