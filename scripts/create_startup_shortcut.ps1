$startupFolder = [Environment]::GetFolderPath('Startup')
$shortcutPath = Join-Path $startupFolder "PresensiBalunglor.lnk"
$appDir = $PSScriptRoot | Split-Path -Parent

$wshShell = New-Object -ComObject WScript.Shell
$shortcut = $wshShell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = Join-Path $appDir "run_background.vbs"
$shortcut.WorkingDirectory = [string]$appDir
$shortcut.Save()

Write-Host "[OK] Windows Startup shortcut created successfully at: $shortcutPath"
