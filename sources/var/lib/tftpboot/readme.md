#Génération du noyau de boot ipxe avec les bonnes options : 

# undionly

"https://rom-o-matic.eu/build.fcgi?BINARY=ipxe.kpxe&BINDIR=bin&REVISION=master&DEBUG=&EMBED.00script.ipxe=%23%21ipxe%0Aset%20user-class%20sambaedu%0Aautoboot&general.h/PXE_STACK:=1&general.h/PXE_MENU:=1&general.h/PARAM_CMD:=1&general.h/CONSOLE_CMD:=1&console.h/CONSOLE_FRAMEBUFFER:=1&console.h/KEYBOARD_MAP=fr&"


#uefi_x64


https://rom-o-matic.eu/build.fcgi?BINARY=snponly.efi&BINDIR=bin-x86_64-efi&REVISION=master&DEBUG=&EMBED.00script.ipxe=%23%21ipxe%0Aset%20user-class%20sambaedu%0Aautoboot&console.h/CONSOLE_FRAMEBUFFER:=1&console.h/KEYBOARD_MAP=fr&general.h/PARAM_CMD:=1&general.h/CONSOLE_CMD:=1&