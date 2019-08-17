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
        <a href="'.Env_mail::app_url.'/auth/forgotten?mail='.$Auth->account->mail . '&code=' . $code . '">
            '.Env_mail::app_url.'/auth/forgotten?mail='.$Auth->account->mail . '&code=' . $code . '
        </a>
    ',
    "header.heading" => "Passwort vergessen? ",
    "header.subheading" => "Hier kannst du es zurücksetzen",
    "content.heading" => "Hallo " . $User->firstname . "!",
    "content.inner" => "
        Wir haben gerade eine Anfrage zum Zurücksetzen deines Passworts bekommen. <br/>
        Ist dir dein Passwort wieder eingefallen? Dann kannst du diese Nachricht ignorieren 
        und dein Passwort bleibt gleich. Wenn du keinen Passwort-Reset angefordert hast, 
        <a href='".Env_mail::app_url."/help/contact'>lass es uns wissen</a>. <br/><br/>
    ",
    "button.inner" => "Passwort ändern &rarr;",
    "button.href" => Env_mail::app_url."/auth/forgotten?mail=" . $Auth->account->mail . "&code=" . $code
];

$Mailer->body["footer.inner"] = Env_mail::page_name . " - " . Env_mail::slogan_de . " <br/> <i>" . Env_mail::creator_de . "</i>";