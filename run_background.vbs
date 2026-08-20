Set WshShell = CreateObject("WScript.Shell")
strPath = WshShell.CurrentDirectory
WshShell.Run "cmd /c """ & strPath & "\start_app.bat""", 0, False
