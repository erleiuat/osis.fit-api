<?php

$Mailer->subject = "Confirm your account at Osis.fit!";

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
        Or use this code on 
        <a href="https://app.osis.fit/auth/verify?mail='.$data->mail . '">app.osis.fit/auth/verify</a>: 
        <br/><br/>
        <strong>'.$verify_code . '</strong>
    ',
    "header.heading" => "Welcome to Osis.fit!",
    "header.subheading" => "Verify your email address",
    "content.heading" => "Hello " . $data->firstname . "!",
    "content.inner" => "
        We have just received a registration via your email address.
        If this was you, please follow the instructions below to activate your account.
        Otherwise, you can simply ignore this email.
    ",
    "button.inner" => "Confirm Account &rarr;",
    "button.href" => "https://app.osis.fit/auth/verify?mail=" . $data->mail . "&code=" . $verify_code
];