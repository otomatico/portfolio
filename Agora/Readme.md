# Ágora

## Introducción 

Este software esta escrito en netCore 6 y tiene configurado para que funcione en docker, IIS o como aplicacion.

Se ta tomado la decision de usar unicamente las herramientas basicas como html, css, js para el front y c# , netCore6, Dapper y MySQL o SQLServer.

El propósito de esta decisión es la de reducir el "bagaje cognitivo" al minimo.

## Instalacion

En la carpeta backupDB encontraras una copia de Base de Datos, con las estructuras de tablas e información de configuración.
Encontraras una versión para MySQL (mariaDB-10.+) y otra para SQLServer (SQLServer-17.+).

Para configurar la cadena de conexión habrá que ir al fichero appsettings.json que se encuentra en la carpeta de WebApi.

## Mas Informacion

Los ficheros Directory.build* y el directorio build contiene las dependencias de la aplicacion y la version, de esta forma se centralizado en "un solo punto" la gestion de paquetes nuget.

Es preparado pero deshabilitado el uso de session encuanto se realiza el desarrollo y no se tiene claro que sistema se utilizará.

Para caso de dudas del funcionamiento de los metodos y verbosHttp se ha instalado y habilitado en modo development la libreria swagger.


