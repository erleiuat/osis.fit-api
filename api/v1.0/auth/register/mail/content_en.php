<?php

$_MailEngine->subject = "Activate your Osis.fit Account!";
$_MailEngine->body = [
    "content.inner2" => '
        <br/>
        <table bgcolor="{{button.bgcolor}}" border="0" cellspacing="0" cellpadding="0">
            <tr><td align="center" height="45" class="button">
                <a href="{{button.href}}" style="color: {{button.inner.color}}">
                    {{button.inner}}
                </a>
            </td></tr>
        </table><br/><br/>
        Or use the following code on <a href="https://app.osis.fit/auth/verify">app.osis.fit/auth/verify</a>: <br/><br/>
        <strong>'.$_Auth->verify_code.'</strong>
    ',
    "header.heading1" => "Welcome to Osis.fit!",
    "header.heading2" => "Activate your Account",
    "content.heading" => "Hello ". $_Auth->firstname."!",
    "content.inner" => "
        We just received a registration with your E-Mail Address.
        If this was you, please follow to instructions below to finalize your registration.
        If not, you can just ignore this Mail. Click the link below to activate your Account or use the code 
    ",
    "button.inner" => "ACTIVATE ACCOUNT &rarr;",
    "button.href" => "https://app.osis.fit/auth/verify?mail=".$_Auth->mail."&code=".$_Auth->verify_code,
];