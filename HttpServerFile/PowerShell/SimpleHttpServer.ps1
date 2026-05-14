param([int]$port=4321, $localPath = ((Get-Location).Path))

function Load-Packages
{
    param ([string] $directory = 'Packages')
    $assemblies = Get-ChildItem $directory -Recurse -Filter '*.dll' | Select -Expand FullName
    foreach ($assembly in $assemblies) { [System.Reflection.Assembly]::LoadFrom($assembly) }
}
function Get-MimeType()
{
  param($extension = $null);
  $mimeType = $null;
  if ( $null -ne $extension )
  {
    $drive = Get-PSDrive HKCR -ErrorAction SilentlyContinue;
    if ( $null -eq $drive )
    {
      $drive = New-PSDrive -Name HKCR -PSProvider Registry -Root HKEY_CLASSES_ROOT
    }
    $mimeType = (Get-ItemProperty HKCR:$extension)."Content Type";
  }
  $mimeType;
}

Load-Packages

$url = "http://*:$port/"
$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add($url)
$listener.Start()

Write-Host "Listening at $url... -> [$localPath]"

while ($listener.IsListening)
{
    $context = $listener.GetContext()
    $requestUrl = $context.Request.Url
    $response = $context.Response

    Write-Host ''
    Write-Host "> $requestUrl"
	
	$localFile = $context.Request.RawUrl
	if($localFile -eq "/"){
		$localFile ="index.html"
	}

	$currentFile = "$localPath\$localFile"

	if( Test-Path -Path $currentFile -PathType Leaf){
		$bytes = Get-Content -Path $currentFile -AsByteStream -Raw
		$response.ContentLength64 = $bytes.Length
		$response.ContentType = Get-MimeType((Get-ChildItem $currentFile).Extension)
		$response.OutputStream.Write($bytes, 0, $bytes.Length)
	}else{
		$response.StatusCode =400
	}

    $response.Close()

    $responseStatus = $response.StatusCode
    Write-Host "< $responseStatus"
}

#.\SimpleHttpServer.ps1 -port 3080 -localPath "$((Get-Location).Path)\Views\"