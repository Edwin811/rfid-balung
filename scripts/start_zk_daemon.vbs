Set WshShell = CreateObject("WScript.Shell")
Dim fso, scriptDir, projectDir, pythonExe, drive, subFolder, found
Set fso = CreateObject("Scripting.FileSystemObject")
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
projectDir = fso.GetParentFolderName(scriptDir)

pythonExe = "python"
found = False

' Cari Python di Laragon (C, D, E, F) agar tidak terhambat PATH Windows
For Each drive In Array("C", "D", "E", "F")
    If fso.FolderExists(drive & ":\laragon\bin\python") Then
        For Each subFolder In fso.GetFolder(drive & ":\laragon\bin\python").SubFolders
            If fso.FileExists(subFolder.Path & "\python.exe") Then
                pythonExe = """" & subFolder.Path & "\python.exe"""
                found = True
                Exit For
            End If
        Next
    End If
    If found Then Exit For
Next

' Jalankan python scripts/fetch_presensi.py --daemon secara tersembunyi dengan logging
WshShell.Run "cmd /c cd /d """ & projectDir & """ && " & pythonExe & " scripts\fetch_presensi.py --daemon >> zk_daemon.log 2>&1", 0, False
