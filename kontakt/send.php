<?php
// Kontaktformular -> E-Mail an den Laden. Antwortet mit JSON.
ini_set('display_errors', '0');
require __DIR__ . '/../admin/mailer.php';
header('Content-Type: application/json; charset=utf-8');
session_start();

function out($ok, $error = '') { echo json_encode(['ok' => $ok, 'error' => $error]); exit; }
function field($k) { return is_string($_POST[$k] ?? null) ? $_POST[$k] : ''; } // Listen/Manipulationen = leer
function clean($s, $max) { return mb_substr(trim(str_replace(["\r", "\0"], '', (string)$s)), 0, $max); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Ungültige Anfrage.');
if (!empty($_POST['website'])) out(true); // Honeypot: Bot bekommt "ok", es wird nichts versendet
if (isset($_SESSION['last_contact']) && time() - $_SESSION['last_contact'] < 30) out(false, 'Bitte warten Sie einen Moment, bevor Sie erneut senden.');

// Sperre pro IP (wirkt auch, wenn ein Bot das Cookie wegwirft): max. 1 Anfrage pro 60 Sekunden, 10 pro Stunde
$rateFile = __DIR__ . '/../admin/storage/contact.php';
$ipKey = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? ''));
$rf = fopen($rateFile, 'c+');
if ($rf) {
  flock($rf, LOCK_EX);
  $rates = json_decode(substr((string)stream_get_contents($rf), strlen('<?php exit; ?>')), true) ?: [];
  $rates = array_filter(array_map(fn($ts) => array_values(array_filter((array)$ts, fn($t) => time() - $t < 3600)), $rates));
  $mine = $rates[$ipKey] ?? [];
  if (($mine && time() - max($mine) < 60) || count($mine) >= 10) { flock($rf, LOCK_UN); out(false, 'Bitte warten Sie einen Moment, bevor Sie erneut senden.'); }
  $rates[$ipKey] = array_merge($mine, [time()]);
  ftruncate($rf, 0); rewind($rf); fwrite($rf, '<?php exit; ?>' . json_encode($rates));
  flock($rf, LOCK_UN); fclose($rf);
}

$TO = 'info@scholz-hausgeraeteservice.de';
$topics = ['reparatur' => 'Reparatur', 'preisanfrage' => 'Preisanfrage', 'reservierung' => 'Reservierung', 'beratung' => 'Beratung vor Ort', 'kueche' => 'Küchenstudio', 'sonstiges' => 'Sonstiges'];

$name = clean(field('name'), 100);
$tel = clean(field('tel'), 40);
$email = clean(field('email'), 150);
$geraet = clean(field('geraet'), 200);
$text = clean(field('nachricht'), 4000);
$rueckruf = clean(field('rueckruf'), 60);
$topic = $topics[field('anliegen')] ?? 'Sonstiges';
$orte = ['geschaeft' => 'Kunde bringt das Gerät vorbei', 'vor-ort' => 'Techniker-Termin beim Kunden'];
$ort = $orte[field('ort')] ?? '';
// Techniker-Termin: Adresse ist Pflicht, Pauschale nach Postleitzahl (Liste wie SITE.porzPlz in assets/js/main.js)
$PORZ_PLZ = ['51143', '51145', '51147', '51149'];
$vorOrt = $topic === 'Reparatur' && field('ort') === 'vor-ort';
$strasse = $vorOrt ? clean(field('strasse'), 120) : '';
$plz = $vorOrt ? clean(field('plz'), 10) : '';
$stadt = $vorOrt ? clean(field('stadt'), 80) : '';
$pauschale = $vorOrt ? (in_array($plz, $PORZ_PLZ, true) ? '79 € (innerhalb Köln-Porz)' : '99 € (außerhalb Köln-Porz)') : '';

if ($name === '' || $tel === '' || $email === '' || $text === '' || empty($_POST['datenschutz'])) out(false, 'Bitte füllen Sie alle Pflichtfelder aus.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) out(false, 'Die E-Mail-Adresse ist ungültig.');
if ($vorOrt && ($strasse === '' || $stadt === '' || !preg_match('/^\d{5}$/', $plz))) out(false, 'Bitte geben Sie Ihre vollständige Adresse an (Straße, 5-stellige PLZ, Ort).');

$oneLine = fn($s) => str_replace("\n", ' ', $s); // keine Zeilenumbrüche in Headern / Betreff
$subject = "Website-Anfrage: $topic, " . $oneLine($name);
$body = "Neue Anfrage über die Website\n\n"
  . "Anliegen:  $topic\nName:      $name\nTelefon:   $tel\nE-Mail:    " . ($email ?: '-') . "\n"
  . "Gerät:     " . ($geraet ?: '-') . "\n" . ($ort ? "Reparatur: $ort\n" : '')
  . ($vorOrt ? "Adresse:   $strasse, $plz $stadt\nPauschale: $pauschale\n" : '')
  . "Rückruf:   $rueckruf\n\nNachricht:\n$text\n";

if (!send_mail($TO, $subject, $body, $email)) out(false, 'Die Nachricht konnte nicht gesendet werden.');
$_SESSION['last_contact'] = time();

// Eingangsbestätigung an den Kunden. Fehler hier blockieren die Anfrage nicht.
{
  require __DIR__ . '/bestaetigung.php';
  $ortKunde = ['geschaeft' => 'Sie bringen das Gerät vorbei', 'vor-ort' => 'Techniker-Termin bei Ihnen zu Hause'][field('ort')] ?? '';
  // Freitext von Besuchern geht nur geprüft in die Mail an fremde Adressen: keine Links, keine langen Texte (Schutz vor Phishing über unser Formular)
  $safe = fn($s, $max) => (mb_strlen($s) <= $max && !preg_match('~(https?:|www\.|[a-z0-9-]\.[a-z]{2,}|@|[<>{}\[\]])~iu', $s)) ? $s : '';
  $rueckrufOk = in_array($rueckruf, ['Egal, jederzeit', 'Vormittags (9–13 Uhr)', 'Nachmittags (14–18 Uhr)'], true) ? $rueckruf : '';
  [$cText, $cHtml] = confirmation_mail(['name' => $safe($oneLine($name), 60), 'topic' => $topic, 'geraet' => $safe($oneLine($geraet), 80), 'ort' => $ortKunde,
    'tel' => preg_replace('~[^0-9 +()/-]~', '', $tel), 'rueckruf' => $rueckrufOk,
    'adresse' => $vorOrt ? $safe($oneLine("$strasse, $plz $stadt"), 140) : '', 'pauschale' => $pauschale]);
  send_mail($email, 'Ihre Anfrage ist bei uns angekommen | Scholz & Partner', $cText, MAIL_FROM, $cHtml); // Antworten gehen an info@
}
out(true);
