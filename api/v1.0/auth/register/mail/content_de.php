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
        <a href="https://app.osis.fit/auth/verify?mail='.$Auth->user->mail . '">app.osis.fit/auth/verify</a>: 
        <br/><br/>
        <strong>'.$Auth->verify_code . '</strong>
    ',
    "header.heading" => "Willkommen bei Osis.fit!",
    "header.subheading" => "Verifiziere deine E-Mail-Adresse",
    "content.heading" => "Hallo " . $Auth->user->firstname . "!",
    "content.inner" => "
        Wir haben gerade eine Registrierung über deine E-Mail-Adresse erhalten.
        Falls du das warst, folge bitte den Anweisungen weiter unten, um dein Konto zu aktivieren.
        Andernfalls kannst du dieses E-Mail einfach ignorieren.
    ",
    "button.inner" => "Konto bestätigen &rarr;",
    "button.href" => "https://app.osis.fit/auth/verify?mail=" . $Auth->user->mail . "&code=" . $Auth->verify_code
];

$Mailer->body["footer.inner"] = Env::mail_page_name . " - " . Env::mail_slogan_de . " <br/> <i>" . Env::mail_creator_de . "</i>";