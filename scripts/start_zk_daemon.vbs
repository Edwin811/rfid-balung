Set WshShell = CreateObject("WScript.Shell")
Dim fso, scriptDir, projectDir, pythonExe, drive, subFolder, found
Set fso = CreateObject("Scripting.FileSystemObject")
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
projectDir = fso.GetParentFolderName(scriptDir)

pythonExe = "python"
found = False

' PYTHON_BIN pada .env adalah sumber kebenaran. Sebelumnya daemon memilih
' Python AppData terlebih dahulu, sehingga dapat berbeda dari tarik manual.
Dim envFile, envStream, envLine, envValue
envFile = projectDir & "\.env"
If fso.FileExists(envFile) Then
    Set envStream = fso.OpenTextFile(envFile, 1)
    Do Until envStream.AtEndOfStream
        envLine = Trim(envStream.ReadLine)
        If UCase(Left(envLine, 11)) = "PYTHON_BIN=" Then
            envValue = Trim(Mid(envLine, 12))
            envValue = Replace(envValue, """", "")
            If fso.FileExists(envValue) Then
                pythonExe = """" & envValue & """"
                found = True
            Else
                WshShell.Run "cmd /c echo [FATAL] PYTHON_BIN tidak ditemukan: " & envValue & " >> """ & projectDir & "\zk_daemon.log""", 0, False
            End If
            Exit Do
        End If
    Loop
    envStream.Close
End If

' Fallback hanya bila PYTHON_BIN belum diatur. Laragon didahulukan karena
' installer juga menggunakannya; AppData dipakai paling akhir.
If Not found Then
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
End If

If Not found Then
    Dim userProfile, appDataPy
    userProfile = WshShell.ExpandEnvironmentStrings("%USERPROFILE%")
    appDataPy = userProfile & "\AppData\Local\Programs\Python"
    If fso.FolderExists(appDataPy) Then
        For Each subFolder In fso.GetFolder(appDataPy).SubFolders
            If fso.FileExists(subFolder.Path & "\python.exe") Then
                pythonExe = """" & subFolder.Path & "\python.exe"""
                found = True
                Exit For
            End If
        Next
    End If
End If

If Not found Then
    WshShell.Run "cmd /c echo [FATAL] Python tidak ditemukan. Atur PYTHON_BIN pada .env lalu jalankan update_application.bat. >> """ & projectDir & "\zk_daemon.log""", 0, False
    WScript.Quit 1
End If

' Jalankan daemon tersembunyi dengan Python yang sama seperti tarik manual.
WshShell.Run "cmd /c cd /d """ & projectDir & """ && " & pythonExe & " scripts\fetch_presensi.py --daemon >> zk_daemon.log 2>&1", 0, False
