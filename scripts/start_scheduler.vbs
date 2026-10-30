Set fso = CreateObject("Scripting.FileSystemObject")
Set shell = CreateObject("WScript.Shell")
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
projectDir = fso.GetParentFolderName(scriptDir)

' Safety net Laravel: hanya menarik data bila daemon realtime tidak sehat.
shell.Run "cmd /c cd /d """ & projectDir & """ && php artisan schedule:work >> scheduler.log 2>&1", 0, False
