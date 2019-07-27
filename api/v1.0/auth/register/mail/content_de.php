<?php

$_Mailer->subject = "Aktiviere dein Konto bei Osis.fit!";
$_Mailer->body = [
    "content.inner2" => '
        <br/>
        <table bgcolor="{{button.bgcolor}}" border="0" cellspacing="0" cellpadding="0">
            <tr><td align="center" height="45" class="button">
                <a href="{{button.href}}" style="color: {{button.inner.color}}">
                    {{button.inner}}
                </a>
            </td></tr>
        </table><br/><br/>
        Oder nutze diesen Code auf <a href="https://app.osis.fit/auth/verify">app.osis.fit/auth/verify</a>: <br/><br/>
        <strong>'.$_Auth->verify_code.'</strong>
    ',
    "header.heading1" => "Willkommen bei Osis.fit!",
    "header.heading2" => "Konto Aktivierung",
    "content.heading" => "Hallo ". $_Auth->firstname."!",
    "content.inner" => "
        Wir haben gerade eine Registrierung über deine E-Mail Adresse erhalten.
        Falls du das warst, folge bitte den Anweisungen weiter unten, um dein Konto zu aktivieren.
        Andernfalls kannst du dieses E-Mail einfach ignorieren.
    ",
    "button.inner" => "KONTO AKTIVIEREN &rarr;",
    "button.href" => "https://app.osis.fit/auth/verify?mail=".$_Auth->mail."&code=".$_Auth->verify_code
];