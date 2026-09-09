$startupFolder = [Environment]::GetFolderPath('Startup')
$shortcutPath = Join-Path $startupFolder "PresensiBalunglor.lnk"
$appDir = $PSScriptRoot | Split-Path -Parent

$wshShell = New-Object -ComObject WScript.Shell

# 1. Shortcut di folder Windows Startup (otomatis aktif di background saat PC hidup)
$shortcut = $wshShell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = Join-Path $appDir "run_background.vbs"
$shortcut.WorkingDirectory = [string]$appDir
$shortcut.Save()

# Hapus shortcut desktop lama jika ada agar bersih (karena user akan install via Chrome PWA)
$desktopFolder = [Environment]::GetFolderPath('Desktop')
$desktopShortcutPath = Join-Path $desktopFolder "Presensi Balunglor.lnk"
if (Test-Path $desktopShortcutPath) {
    Remove-Item -Path $desktopShortcutPath -Force -ErrorAction SilentlyContinue
}

Write-Host "[OK] Windows Startup background auto-start registered successfully!"
