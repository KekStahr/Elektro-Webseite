# Scholz & Partner – Website

Statische Seiten + kleines PHP-Backend (läuft auf jedem normalen PHP-Webhosting, keine Datenbank nötig).

## Seiten

| URL | Ordner |
|---|---|
| `/home/` | Startseite (`/` leitet dorthin weiter) |
| `/service/` | Reparatur (Vergleich Geschäft / Vor Ort), Kaffeemaschinen, Lieferung, Beratung, Küche |
| `/shop/` | Shop mit Kategorien + Filtern (Preis, Marke, Farbe, Zustand, Größe) |
| `/shop/produkt/?id=…` | Produktdetail |
| `/ueber-uns/` | Über uns, `#kuechenstudio`, `#standort`, `#presse` |
| `/kontakt/` | Kontaktformular (`send.php` mailt an info@) |
| `/impressum/`, `/datenschutz/` | Rechtliches |
| `/admin/` | Admin-Bereich (Login + 2FA-Code per E-Mail) |

Jeder Seitenordner hat `index.html`, `style.css`, `script.js` und `img/`, `video/`, `gif/` für eigene Medien.
Gemeinsames liegt in `/assets/` (Design, Header/Footer, Firmendaten in `assets/js/main.js` → `SITE`, `BRANDS`).

## Admin

> **Zugangsdaten stehen nur in `admin/config.php`.** Diese Datei ist per `.gitignore` vom Repository ausgeschlossen, Vorlage: `admin/config.example.php`. Passwörter nie in README, Chat oder Commits schreiben.

Aufruf: `/admin/`. Anmeldung in zwei Schritten: ID + Passwort, dann 6-stelliger Code aus der E-Mail.

Zwei Zugänge (in `admin/config.php`):
- **Laden-Admin:** Benutzer und Passwort in `admin/config.php` (`ADMIN_USER`/`ADMIN_PASS`), Code geht an `TWOFA_EMAIL`. Kann Geräte anlegen, bearbeiten, online/offline schalten und löschen.
- **Super-Admin:** `SUPER_USER`/`SUPER_PASS` in `admin/config.php`, Code geht an `SUPER_EMAIL`. Hat alle Rechte des Laden-Admins plus den Knopf „Beispielgeräte entfernen“ (löscht alle `demo-…` auf einmal). Oben rechts erscheint das Abzeichen „Super-Admin“.

- Produkte landen in `data/products.json`, Bilder in `uploads/products/`.
- Die 8 Produkte mit `demo-…` sind **Beispiele** – vor dem Livegang im Admin löschen/ersetzen (als Super-Admin mit einem Klick).
- Ordner `data/`, `uploads/products/`, `admin/storage/` müssen vom Webserver **beschreibbar** sein.

### Schutz gegen Angriffe
Login mit 2-Faktor-Code, CSRF-Token bei jeder Änderung, Sperre nach 5 Fehlversuchen (15 Min), automatische Abmeldung nach 2 Std. Hochgeladene Bilder werden auf echten Bildtyp geprüft und per `.htaccess` nie als Skript ausgeführt. Alle Eingaben werden maskiert ausgegeben (kein XSS). **Nach dem Livegang beide Passwörter in `admin/config.php` ändern.**

## Hochladen

Alles per FTP in das Web-Verzeichnis der Domain kopieren (inkl. `.htaccess`-Dateien), **außer** den Ordnern `_lokal-testen` und `.claude`. Voraussetzungen: PHP ≥ 7.4 mit `fileinfo` und `openssl`.

## E-Mail-Versand (Login-Codes, Anfragen, Kunden-Bestätigung)

Alle Mails kommen von **info@scholz-hausgeraeteservice.de** und gehen über den Mailserver der Domain bei netcup (`mx2f0e.netcup.net`, Port 465, verschlüsselt).
Einmalig einrichten: In `admin/config.php` bei `SMTP_PASS` das **Passwort des Postfachs info@** eintragen (dasselbe wie im netcup-Webmail).

Ist `SMTP_PASS` leer, verschickt der netcup-Webserver selbst (ebenfalls als info@). Lokal landen die Mails dann im Test-Postfach unter http://localhost:8025.

| Mail | Empfänger |
|---|---|
| Login-Code Laden-Admin | `TWOFA_EMAIL` (onemillionbaby100@gmail.com) |
| Login-Code Super-Admin | `SUPER_EMAIL` (ahmed.bilgic1998@gmail.com) |
| Neue Anfrage aus dem Kontaktformular | info@ (Antworten gehen direkt an den Kunden) |
| Eingangsbestätigung mit Logo | der Kunde (E-Mail ist im Formular Pflicht); antwortet er, landet es bei info@ |

Kommt nichts an: zuerst Spam-Ordner prüfen. Meldet die Seite „E-Mail konnte nicht gesendet werden“, stimmt das Postfach-Passwort nicht.

## Vor dem Livegang noch zu tun

- Echte Fotos einfügen (Hero, Inhaber, Ausstellung), Marken-Logos in `BRANDS` (`logo: '/assets/img/brands/miele.svg'`).
- Echte Google-Bewertungen in `data/reviews.json` eintragen: `[{"name":"…","stars":5,"text":"…","source":"Google"}]`
- Datenschutzerklärung rechtlich prüfen lassen; AGB und Batteriehinweis von der alten Seite übernehmen.
- Admin-Passwort in `admin/config.php` ändern.

## Lokal testen (auf einem beliebigen Windows-PC)

**Einmalig installieren:**
1. **PHP:** https://windows.php.net/download → bei „VS16 x64 Non Thread Safe“ das **Zip** laden, nach `C:\php` entpacken.
   Dann `C:\php` zur Windows-Umgebungsvariable **Path** hinzufügen (Startmenü → „Umgebungsvariablen“ → Path → Neu).
2. **Python:** https://www.python.org/downloads/ → installieren, dabei **„Add python.exe to PATH“** anhaken.
3. Prüfen in einer neuen PowerShell: `php -v` und `python --version` zeigen jeweils eine Version.

**Starten:** Doppelklick auf `_lokal-testen\START.bat`. Es öffnen sich zwei schwarze Fenster (Server) und der Browser.
Beenden: beide schwarzen Fenster schließen.

| Was | Adresse |
|---|---|
| Startseite | http://localhost:8081/home/ |
| Service | http://localhost:8081/service/ |
| Shop | http://localhost:8081/shop/ |
| Produktseite (Beispiel) | http://localhost:8081/shop/produkt/?id=demo-wm-1 |
| Über uns | http://localhost:8081/ueber-uns/ |
| Kontakt | http://localhost:8081/kontakt/ |
| Impressum / Datenschutz | http://localhost:8081/impressum/ · http://localhost:8081/datenschutz/ |
| Admin | http://localhost:8081/admin/ |
| Test-Postfach | http://localhost:8025 |

Der Ordner `_lokal-testen` ist nur zum Testen und wird **nicht** auf den Webserver hochgeladen.
