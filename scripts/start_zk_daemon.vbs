Set WshShell = CreateObject("WScript.Shell")
Dim fso, scriptDir, projectDir
Set fso = CreateObject("Scripting.FileSystemObject")
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
projectDir = fso.GetParentFolderName(scriptDir)

' Jalankan python scripts/fetch_presensi.py --daemon secara tersembunyi dengan logging
WshShell.Run "cmd /c cd /d """ & projectDir & """ && python scripts\fetch_presensi.py --daemon >> zk_daemon.log 2>&1", 0, False
