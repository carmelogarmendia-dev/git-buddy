@echo off
cd /d "%~dp0"

:: Matar procesos huérfanos previos
taskkill /f /im php.exe 2>nul

:: Iniciar servidor PHP
start /b .\php\php.exe -S localhost:8888 -t .\app\public

:: Verificar si Chrome está instalado
set CHROME_EXISTS=0
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" set CHROME_EXISTS=1
if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" set CHROME_EXISTS=1
if exist "%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe" set CHROME_EXISTS=1

:: Abrir el navegador adecuado
if %CHROME_EXISTS%==1 (
    start chrome --app=http://localhost:8888
) else (
    start msedge --app=http://localhost:8888
)

:: Esperar a que el navegador arranque
timeout /t 2 /nobreak >nul

:: Obtener el PID del navegador que se abrió
set NAV_PID=
for /f "tokens=2" %%a in ('tasklist /fi "imagename eq chrome.exe" /nh 2^>nul') do set NAV_PID=%%a
if "%NAV_PID%"=="" (
    for /f "tokens=2" %%a in ('tasklist /fi "imagename eq msedge.exe" /nh 2^>nul') do set NAV_PID=%%a
)

:: Guardar el PID en carpeta temporal de Windows
echo %NAV_PID% > "%TEMP%\gitbuddy_pid.txt"

exit