@echo off
set puerto=54321
if not "%~1"=="" set puerto=%1

"Express.exe" -port %puerto% -path "./" | "C:\Program Files\Google\Chrome\Application\chrome_proxy.exe" --profile-directory=Default --app="http://localhost:%puerto%/"