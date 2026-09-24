# criar_atalho.ps1 - Cria o atalho do aplicativo administrativo na Area de Trabalho
# (chamado pelo instalar_aplicativo.bat)

$pasta = Split-Path -Parent $MyInvocation.MyCommand.Path

# pythonw abre o aplicativo sem a janela preta do terminal
$python = (Get-Command pythonw.exe -ErrorAction SilentlyContinue).Source
if (-not $python) { $python = (Get-Command pyw.exe -ErrorAction SilentlyContinue).Source }
if (-not $python) {
    Write-Host "Python nao encontrado. Instale o Python (marcando 'Add Python to PATH') e rode de novo."
    exit 1
}

$areaDeTrabalho = [Environment]::GetFolderPath('Desktop')
$atalho = (New-Object -ComObject WScript.Shell).CreateShortcut((Join-Path $areaDeTrabalho 'Camilopolis Admin.lnk'))
$atalho.TargetPath = $python
$atalho.Arguments = '"' + (Join-Path $pasta 'main.py') + '"'
$atalho.WorkingDirectory = $pasta
$atalho.IconLocation = (Join-Path $pasta 'img\icone_app.ico')
$atalho.Description = 'Painel administrativo da Associacao Amigos de Camilopolis'
$atalho.Save()

Write-Host "Atalho 'Camilopolis Admin' criado na Area de Trabalho!"
