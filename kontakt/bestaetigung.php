<?php
// Eingangsbestätigung an den Kunden (Text + HTML), Absender info@. Absichtlich OHNE den Freitext der Nachricht:
// sonst könnte jemand das Formular nutzen, um beliebigen Text an fremde Adressen zu schicken.
function confirmation_mail(array $d): array {
  $rows = array_filter([
    'Anliegen' => $d['topic'],
    'Gerät / Produkt' => $d['geraet'],
    'Reparatur' => $d['ort'],
    'Adresse' => $d['adresse'] ?? '',
    'Vor-Ort-Pauschale' => $d['pauschale'] ?? '',
    'Telefon' => $d['tel'],
    'Rückruf' => $d['rueckruf'],
  ], fn($v) => $v !== '');

  $text = ($d['name'] !== '' ? "Guten Tag {$d['name']}," : 'Guten Tag,') . "\n\n"
    . "vielen Dank, dass Sie sich an uns gewandt haben. Ihre Anfrage ist gut bei uns angekommen.\n\n"
    . "Unser Fachpersonal sieht sich Ihr Anliegen persönlich an und meldet sich so schnell wie möglich bei Ihnen.\n\n"
    . "Ihre Angaben:\n" . implode('', array_map(fn($k, $v) => "  $k: $v\n", array_keys($rows), $rows))
    . "\nMöchten Sie noch etwas ergänzen, zum Beispiel ein Foto des Geräts, die Modellnummer oder einen Wunschtermin?\n"
    . "Dann antworten Sie einfach auf diese E-Mail. Ihre Nachricht landet direkt bei unserem Team.\n\n"
    . "Eilt es? Dann rufen Sie uns gern direkt an: (02203) 540 41\n\n"
    . "Wir freuen uns darauf, Ihnen weiterzuhelfen.\n\n"
    . "Mit freundlichen Grüßen\nIhr Team von Scholz & Partner\n\n"
    . "--\nScholz & Partner Elektro-Hausgeräte-Service · Inh. Joachim Scholz\n"
    . "Kaiserstraße 40 · 51145 Köln-Porz\nTelefon (02203) 540 41 · info@scholz-hausgeraeteservice.de\n"
    . "www.scholz-hausgeraeteservice.de\n";

  $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  $name = $e($d['name']);
  $hello = $name !== '' ? "Guten Tag $name," : 'Guten Tag,';
  $thanks = $name !== '' ? "Vielen Dank, $name." : 'Vielen Dank.';
  $tr = '';
  foreach ($rows as $k => $v) {
    $tr .= '<tr><td style="padding:8px 0;border-bottom:1px solid #e3e5ee;color:#5d6178;font-size:14px;width:40%;vertical-align:top">' . $e($k) . '</td>'
      . '<td style="padding:8px 0;border-bottom:1px solid #e3e5ee;color:#171a4f;font-size:14px;font-weight:600">' . $e($v) . '</td></tr>';
  }
  $font = "font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
  $p = 'margin:0 0 14px;font-size:16px;line-height:1.65;color:#3a3d52';
  $html = <<<HTML
<!DOCTYPE html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ihre Anfrage ist bei uns angekommen</title></head>
<body style="margin:0;padding:0;background:#f6f7fb;$font">
<div style="display:none;max-height:0;overflow:hidden">$thanks Ihre Anfrage ist angekommen, unser Fachpersonal meldet sich so schnell wie möglich.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;padding:32px 12px">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(23,26,79,.08)">
  <tr><td style="height:6px;line-height:6px;font-size:0;background:linear-gradient(90deg,#65b32e 0 25%,#ffcc00 25% 50%,#e3051c 50% 75%,#0069b4 75%);background-color:#262a7a">&nbsp;</td></tr>
  <tr><td style="padding:28px 32px 8px">
    <table role="presentation" cellpadding="0" cellspacing="0"><tr>
      <td style="padding-right:14px;vertical-align:middle"><img src="cid:logo" width="52" height="52" alt="Scholz &amp; Partner" style="display:block;border:0"></td>
      <td style="vertical-align:middle"><div style="font-size:20px;font-weight:800;color:#171a4f;line-height:1.1">Scholz &amp; Partner</div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#d7262e;margin-top:3px">Elektro-Hausgeräte-Service</div></td>
    </tr></table>
  </td></tr>
  <tr><td style="padding:20px 32px 6px">
    <div style="display:inline-block;background:#e7f7ea;color:#1d6b29;font-size:13px;font-weight:700;padding:6px 12px;border-radius:999px">&#10003; Ihre Anfrage ist angekommen</div>
    <h1 style="margin:16px 0 14px;font-size:24px;line-height:1.25;color:#171a4f">$hello</h1>
    <p style="$p">vielen Dank, dass Sie sich an uns gewandt haben. Ihre Anfrage ist gut bei uns angekommen.</p>
    <p style="$p">Unser Fachpersonal sieht sich Ihr Anliegen persönlich an und <strong style="color:#171a4f">meldet sich so schnell wie möglich bei Ihnen</strong>.</p>
  </td></tr>
  <tr><td style="padding:6px 32px 0">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;border-radius:12px;padding:6px 18px 10px">
      <tr><td colspan="2" style="padding:12px 0 4px;font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#262a7a">Ihre Angaben</td></tr>
      $tr
    </table>
  </td></tr>
  <tr><td style="padding:20px 32px 0">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef0fb;border-radius:12px">
      <tr><td style="padding:16px 18px;font-size:15px;line-height:1.6;color:#3a3d52">
        <strong style="color:#171a4f">Möchten Sie noch etwas ergänzen?</strong><br>
        Ein Foto des Geräts, die Modellnummer oder ein Wunschtermin helfen uns weiter.
        <strong style="color:#171a4f">Antworten Sie einfach auf diese E-Mail</strong>, Ihre Nachricht landet direkt bei unserem Team.</td></tr>
    </table>
  </td></tr>
  <tr><td style="padding:12px 32px 0">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #e3e5ee;border-radius:12px">
      <tr><td style="padding:16px 18px;font-size:15px;color:#3a3d52">Eilt es? Dann rufen Sie uns gern direkt an:<br>
        <a href="tel:+49220354041" style="font-size:20px;font-weight:800;color:#d7262e;text-decoration:none">(02203) 540 41</a></td></tr>
    </table>
  </td></tr>
  <tr><td style="padding:24px 32px 28px;font-size:16px;line-height:1.65;color:#3a3d52">Wir freuen uns darauf, Ihnen weiterzuhelfen.<br><br>
    Mit freundlichen Grüßen<br><strong style="color:#171a4f">Ihr Team von Scholz &amp; Partner</strong></td></tr>
  <tr><td style="background:#171a4f;padding:24px 32px;color:#b9bde8;font-size:13px;line-height:1.7">
    <strong style="color:#ffffff">Scholz &amp; Partner Elektro-Hausgeräte-Service</strong><br>
    Inh. Joachim Scholz · Kaiserstraße 40 · 51145 Köln-Porz<br>
    Telefon <a href="tel:+49220354041" style="color:#ffffff;text-decoration:none">(02203) 540 41</a> ·
    <a href="mailto:info@scholz-hausgeraeteservice.de" style="color:#ffffff;text-decoration:none">info@scholz-hausgeraeteservice.de</a><br>
    <a href="https://scholz-hausgeraeteservice.de/home/" style="color:#ffffff">www.scholz-hausgeraeteservice.de</a><br>
    <span style="color:#8f94c9">Mo bis Do 9:00–13:00 &amp; 14:00–18:00 · Fr 9:00–13:00 &amp; 14:00–17:00</span>
  </td></tr>
</table>
<p style="max-width:560px;margin:16px auto 0;font-size:12px;line-height:1.5;color:#8a8ea6;text-align:center">Sie erhalten diese E-Mail, weil über unsere Website eine Anfrage mit dieser Adresse gestellt wurde. Falls Sie das nicht waren, können Sie diese Nachricht einfach ignorieren.</p>
</td></tr></table>
</body></html>
HTML;
  return [$text, $html];
}
