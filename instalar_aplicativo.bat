@echo off
REM Instala as bibliotecas do aplicativo administrativo e cria o atalho na Area de Trabalho
cd /d "%~dp0"

echo Instalando as bibliotecas do aplicativo...
python -m pip install -r requirements.txt
if errorlevel 1 (
    echo.
    echo Erro ao instalar. Verifique se o Python esta instalado e marcado no PATH.
    pause
    exit /b 1
)

echo.
echo Criando o atalho na Area de Trabalho...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0criar_atalho.ps1"

echo.
pause
