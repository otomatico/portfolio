<#
	Author: Otavio Ferreira Rosa
	Version:2020.12.03
	Description: Servidor HTTP de ficheros, para pruebas de paginas Webs
	Dependence: PowerShell 5.0 por el *Class*

	Ejemplo: PS> & .\Express.ps1 -port 8080 -path "P:\Proyectos\WebApp"

#>
# Nota Mental: Nunca jamas llame una variables de $Host. Ya existe una en el sistema y es necesaria.
param($ip ="localhost", $port="8081", $Path=".")
begin{
	Write-Host "Inicializando configuraciones"
}
process{
	#Estrutura de dato para la Respuesta al navegador
	Class ActionResult {
        [string] $contentType = ""
        [int] $statusCode = 0
        [byte[]] $body =$Null
	}
	function listenerContext($context,$debug=$False){
		$result = New-Object ActionResult
		
		$Request = $context.Request
		$localpath = $Request.url.localpath
		
		if($debug){
			Write-Host $("`t{0} {1}" -f $Request.httpmethod, $Request.url.localpath)
		}
		<# 
		#	Esto es para una REST API para poder procesar POST|PUT
		$actionArgs = ($Request.url.localpath -split "/"|Where-Object {$_ -ne ""})
		if ($Request.httpmethod -eq "POST" -or $Request.httpmethod -eq "PUT")
		{
			$documentContents = $(Get-Content $Request.InputStream -Encoding utf8)
			$actionArgs = $documentContents -split "&"| Where-Object {$_ -ne ""}
		}
		if($debug){
			Write-Host $($actionArgs)
		}
		#>

		$pathFile = $("$Path$localpath" -replace "(\\)|(//)", "/")

		if(!(test-path -path $pathFile)){
			$msg = $pathFile + "`t NO EXISTE"
			$result.statusCode = 404
			$result.contentType = "text/plain"
			$result.body = @()
			Write-Warning $msg
			return $result
		}

		if($debug){
			Write-Host "`t`t$pathFile"
		}
		#Todos los datos se pasa como Bytes dá igual que sea un json o xml hay que basar a bytes[]
		$result.body = Get-Content $pathFile -Encoding byte
		$result.statusCode = 200

		$file = Get-ChildItem -Path $pathFile
		switch($file.Extension){
			".html" {$result.contentType='text/html;charset="utf-8"';break}
			".htm"  {$result.contentType='text/html;charset="utf-8"';break}
			".js"   {$result.contentType='application/javascript;charset="utf-8"';break}
			".css"  {$result.contentType='text/css;charset="utf-8"';break}
			".txt"  {$result.contentType='text/plain';break}
			default {
				# Las imagenes tipo gif/png/jpg/icon se pasan con este formato sin problema
				$result.contentType = "application/octet-stream"
			}
		}

		return $result
	}

	function Main(){
		$listener = New-Object System.Net.HttpListener
		$listener.Prefixes.Add("http://$($ip):$($port)/")
		$listener.Start()
		Write-Host "Servicio abierto en el Port $port"
		try
		{
			while ($listener.IsListening) {
				$context = $listener.GetContext()
				# Condicion de salida forzosa 
				if($('{0} {1}' -f $context.Request.httpmethod, $context.Request.url.localpath) -eq "GET /quit"){
					break
				}
				$buffer = $(listenerContext -context $context -debug $True)
			
				$context.Response.ContentType = $buffer.contentType
				$context.Response.ContentLength64 = $buffer.body.length
				if($buffer.body.length){
					$context.Response.OutputStream.Write($buffer.body, 0, $buffer.body.length)
				}
				$context.Response.StatusCode = $buffer.statusCode;
				$context.Response.Close()
			}
		}
		finally
		{
			$listener.Stop()
			Write-Host "Servicio Finalizado"
		}
	}
	Main
}
End{write-host "Matando todos los procesos"}