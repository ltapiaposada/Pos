@echo off
setlocal
cd /d "%~dp0\.."

echo Iniciando servidor POS para celular por USB...
echo URL local: http://127.0.0.1:8000
echo URL por red/USB: http://IP_DE_TU_PC:8000
echo.
echo Mantenga esta ventana abierta mientras usa el lector remoto.
echo Presione Ctrl+C para detener.
echo.

php -S 0.0.0.0:8000 -t public

endlocal
