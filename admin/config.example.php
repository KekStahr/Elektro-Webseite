<?php
// ===== Admin-Zugang (Laden) – HIER ändern =====
const ADMIN_USER = 'ElektroLaden';
const ADMIN_PASS = 'HIER-STARKES-PASSWORT';          // bitte nach dem Livegang ändern
const TWOFA_EMAIL = 'onemillionbaby100@gmail.com'; // hierhin geht der Login-Code

// ===== Super-Admin (voller Zugriff) – eigener Zugang mit eigener 2FA-Adresse =====
const SUPER_USER = 'Reso';
const SUPER_PASS = 'HIER-STARKES-PASSWORT';
const SUPER_EMAIL = 'ahmed.bilgic1998@gmail.com'; // 2FA-Code für den Super-Admin

// Alle Mails (Kunden-Bestätigung, Anfragen, Login-Codes) kommen von dieser Adresse.
const MAIL_FROM = 'info@scholz-hausgeraeteservice.de';
const MAIL_FROM_NAME = 'Scholz & Partner Hausgeräte-Service';

// ===== E-Mail-Versand über das Postfach info@ bei netcup (empfohlen) =====
// Unten bei SMTP_PASS das Passwort des Postfachs info@scholz-hausgeraeteservice.de eintragen
// (dasselbe wie beim Anmelden im netcup-Webmail). Es bleibt in dieser PHP-Datei, Besucher sehen es nie.
// Bleibt SMTP_PASS leer, verschickt der netcup-Webserver selbst (lokal: Test-Postfach).
const SMTP_HOST = 'mx2f0e.netcup.net'; // Mailserver der Domain laut DNS (gültiges Zertifikat)
const SMTP_PORT = 465;
const SMTP_USER = 'info@scholz-hausgeraeteservice.de';
const SMTP_PASS = '';

const CODE_TTL = 600;          // Code gültig: 10 Minuten
const SESSION_TTL = 7200;      // automatisch abmelden nach 2 Std. Inaktivität
const MAX_FAILS = 5;           // Fehlversuche pro IP ...
const LOCK_SECONDS = 900;      // ... dann 15 Minuten Sperre

define('ROOT', dirname(__DIR__));
define('PRODUCTS_FILE', ROOT . '/data/products.json');
define('UPLOAD_DIR', ROOT . '/uploads/products');
const UPLOAD_URL = '/uploads/products';
const MAX_IMAGES = 8;
const MAX_IMAGE_BYTES = 8 * 1024 * 1024;
