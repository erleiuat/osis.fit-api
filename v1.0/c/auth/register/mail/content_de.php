<?php

$Mailer->subject = "Bestätige dein Konto bei Osis.fit!";

$Mailer->body = [
    "content.inner2" => '
        <br/>
        <table bgcolor="{{button.bgcolor}}" border="0" cellspacing="0" cellpadding="0">
            <tr><td align="center" height="45" class="button">
                <a href="{{button.href}}" style="color: {{button.inner.color}}">
                    {{button.inner}}
                </a>
            </td></tr>
        </table><br/><br/>
        Oder nutze diesen Code auf 
        <a href="'.Env_mail::app_url.'/auth/verify?mail='.$data->mail . '">app.osis.fit/auth/verify</a>: 
        <br/><br/>
        <strong>'.$verify_code . '</strong>
    ',
    "header.heading" => "Willkommen bei Osis.fit!",
    "header.subheading" => "Verifiziere deine E-Mail-Adresse",
    "content.heading" => "Hallo " . $data->firstname . "!",
    "content.inner" => "
        Wir haben gerade eine Registrierung über deine E-Mail-Adresse erhalten.
        Falls du das warst, folge bitte den Anweisungen weiter unten, um dein Konto zu aktivieren.
        Andernfalls kannst du dieses E-Mail einfach ignorieren.
    ",
    "button.inner" => "Konto bestätigen &rarr;",
    "button.href" => Env_mail::app_url."/auth/verify?mail=" . $data->mail . "&code=" . $verify_code
];

$Mailer->body["footer.inner"] = Env_mail::page_name . " - " . Env_mail::slogan_de . " <br/> <i>" . Env_mail::creator_de . "</i>";