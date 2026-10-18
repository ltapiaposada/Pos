@echo off
setlocal
cd /d "%~dp0\.."

set "PHP_EXE=C:\Users\Luis tapia\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
set "CLOUDFLARED_EXE=C:\Progra~2\cloudflared\cloudflared.exe"

if not exist "%PHP_EXE%" goto :php_missing

if not exist "%CLOUDFLARED_EXE%" goto :cloudflared_missing

echo ===========================================
echo Iniciando servidor local POS en 127.0.0.1:8000
echo ===========================================
start "POS Local Server" cmd /k ""%PHP_EXE%" -S 127.0.0.1:8000 -t public"

timeout /t 2 >nul

echo.
echo ===========================================
echo Abriendo tunel HTTPS con cloudflared...
echo ===========================================
echo Copia la URL https://*.trycloudflare.com que aparezca abajo.
echo Mantenga esta ventana abierta mientras escanea.
echo.
"%CLOUDFLARED_EXE%" tunnel --url http://127.0.0.1:8000 --no-autoupdate
set "ERR=%ERRORLEVEL%"
echo.
echo cloudflared finalizo con codigo: %ERR%
if not "%ERR%"=="0" (
  echo Si no ves URL HTTPS, revisa internet/firewall o ejecuta este archivo como administrador.
)
pause

endlocal
exit /b 0

:php_missing
echo No se encontro PHP en:
echo %PHP_EXE%
pause
endlocal
exit /b 1

:cloudflared_missing
echo No se encontro cloudflared en:
echo %CLOUDFLARED_EXE%
pause
endlocal
exit /b 1
