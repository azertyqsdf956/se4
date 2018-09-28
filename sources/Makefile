#!/usr/bin/make -f
# Makefile for sambaedu

all:

install:
	mkdir -p $(DESTDIR)/etc/sambaedu
	mkdir -p $(DESTDIR)/usr/share/sambaedu/sbin/
	mkdir -p $(DESTDIR)/usr/share/sambaedu/scripts/	
	mkdir -p $(DESTDIR)/usr/share/sambaedu/includes/
	mkdir -p $(DESTDIR)/usr/share/doc/sambaedu/
	mkdir -p $(DESTDIR)/var/www/boot/diconf
	mkdir -p $(DESTDIR)/var/www/sambaedu
	mkdir -p $(DESTDIR)/var/www/sambaedu/html2pdf
	mkdir -p $(DESTDIR)/var/lib/tftpboot/
	mkdir -p $(DESTDIR)/usr/share/sambaedu/shares/shares.avail/

	install -m 755 usr/share/sambaedu/sbin/* $(DESTDIR)/usr/share/sambaedu/sbin/
	install -m 755 usr/share/sambaedu/scripts/* $(DESTDIR)/usr/share/sambaedu/scripts/
	cp -R usr/share/sambaedu/includes/* $(DESTDIR)/usr/share/sambaedu/includes/
	cp -R usr/share/doc/sambaedu/* $(DESTDIR)/usr/share/doc/sambaedu/
	cp -R etc/sambaedu/* $(DESTDIR)/etc/sambaedu/
	cp -R var/www/boot/* $(DESTDIR)/var/www/boot/
	cp -R var/www/sambaedu/* $(DESTDIR)/var/www/sambaedu/
	cp -R libs/html2pdf/* $(DESTDIR)/var/www/sambaedu/html2pdf/
	cp -R var/lib/tftpboot/* $(DESTDIR)/var/lib/tftpboot/
	cp -R usr/share/sambaedu/shares/shares.avail/* $(DESTDIR)/usr/share/sambaedu/shares/shares.avail
 
html2pdf:
#	git clone  	
	composer install --no-dev
		
clean:
