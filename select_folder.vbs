' Select Folder Dialog for Git Buddy
Set objShell = CreateObject("Shell.Application")
Set objFolder = objShell.BrowseForFolder(0, "Selecciona la carpeta de tu proyecto", 0, 0)

' Obtener la ruta donde está el script
Dim scriptPath
scriptPath = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)

Dim fso, file
Set fso = CreateObject("Scripting.FileSystemObject")
Set file = fso.CreateTextFile(scriptPath & "\selected_path.txt", True)

If objFolder Is Nothing Then
    file.WriteLine "CANCEL"
Else
    file.WriteLine objFolder.Self.Path
End If

file.Close