if exist  %windir%\wpkg-gpo.txt Del /F /Q %WinDir%\wpkg-client.vbs
if exist  %windir%\wpkg-gpo.txt Del /F /Q %WinDir%\wpkg-client.txt
if exist  %windir%\wpkg-gpo.txt Del /F /Q %WinDir%\wpkg-client.log
if not exist %WinDir%\wpkg-client.vbs copy /Y /V /B \\se4fs\install\wpkg\wpkg-client.vbs %WinDir%\wpkg-client.vbs
if exist %WinDir%\wpkg-client.vbs echo gpo > %windir%\wpkg-gpo.txt
%WinDir%\system32\cscript.exe //NoLogo //B %windir%\wpkg-client.vbs /NoTempo>%windir%\wpkg-gpo.txt

