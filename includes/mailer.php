<?php
/** Construit un template HTML d'email avec l'identité visuelle du projet */
function build_email_template($titre, $contenuHtml, $boutonTexte = null, $boutonLien = null) {
    $bouton = '';
    if ($boutonTexte && $boutonLien) {
        $bouton = '
        <tr>
          <td style="padding:24px 0;text-align:center;">
            <a href="' . htmlspecialchars($boutonLien) . '"
               style="background:#FF6B4A;color:#ffffff;text-decoration:none;font-weight:700;
                      padding:12px 28px;border-radius:8px;font-family:Arial,sans-serif;font-size:14px;display:inline-block;">
              ' . htmlspecialchars($boutonTexte) . '
            </a>
          </td>
        </tr>';
    }
//chaque email envoyé est aussi sauvegardé en HTML dans le  dossier /emails_log

    return '
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F6;padding:40px 0;font-family:Arial,sans-serif;">
      <tr>
        <td align="center">
          <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:14px;overflow:hidden;">
            <tr>
              <td style="background:#1B1F3B;padding:22px 28px;">
                <span style="color:#ffffff;font-size:18px;font-weight:700;">Social<span style="color:#FF6B4A;">Net</span> ESGIS</span>
              </td>
            </tr>
            <tr>
              <td style="padding:28px;">
                <h2 style="color:#1B1F3B;font-size:18px;margin:0 0 12px;">' . htmlspecialchars($titre) . '</h2>
                <div style="color:#4A4E69;font-size:14px;line-height:1.6;">' . $contenuHtml . '</div>
              </td>
            </tr>
            ' . $bouton . '
            <tr>
              <td style="padding:16px 28px;background:#F7F8FB;color:#8A8FA3;font-size:11px;">
                SocialNet ESGIS 
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>';
}

/** Envoie un email HTML, avec sauvegarde locale systématique  */
function send_email($to, $subject, $htmlBody) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: SocialNet  <no-reply@socialnet.local>\r\n";

    $sent = @mail($to, $subject, $htmlBody, $headers);

    // Sauvegarde locale pour démonstration / défense orale
    $logDir = __DIR__ . '/../emails_log'; //chemin relatif du dossier email_log
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);// en cas d'absence du dossier on cree le dossier 
    }
    $filename = $logDir . '/' . date('Y-m-d_His') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $to) . '.html';
    file_put_contents($filename, $htmlBody);

    return $sent;
}
