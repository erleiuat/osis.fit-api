<?php

$Mailer->subject = "Dein neues Osis.fit Passwort";

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
        Oder nutze diesen Link: <br/>
        <a href="https://app.osis.fit/auth/pwreset?mail='.$User->mail.'&code='.$Auth->password_code.'">
            app.osis.fit/auth/pwreset?mail='.$User->mail.'&code='.$Auth->password_code.'
        </a>
    ',
    "header.heading" => "Passwort vergessen? ",
    "header.subheading" => "Hier kannst du es zurücksetzen",
    "content.heading" => "Hallo ".$User->firstname."!",
    "content.inner" => "
        Wir haben gerade eine Anfrage zum Zurücksetzen deines Passworts bekommen. <br/>
        Ist dir dein Passwort wieder eingefallen? Dann kannst du diese Nachricht ignorieren 
        und dein Passwort bleibt gleich. Wenn du keinen Passwort-Reset angefordert hast, 
        <a href='https://app.osis.fit/help/contact'>lass es uns wissen</a>. <br/><br/>
    ",
    "button.inner" => "Passwort ändern &rarr;",
    "button.href" => "https://app.osis.fit/auth/pwreset?mail=".$User->mail."&code=".$Auth->password_code
];

$Mailer->body["footer.inner"] = Env::mail_page_name." - ".Env::mail_slogan_de." <br/> <i>".Env::mail_creator_de."</i>";