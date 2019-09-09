<?php

$Mailer2->subject = "Osis.fit | Deine Kontaktanfrage";

$Mailer2->body = [
    "header.heading" => "Vielen Dank!",
    "header.subheading" => "Wir werden dein Anliegen schnellstmöglich bearbeiten",
    "content.heading" => "Hallo " . $data->firstname . "!",
    "content.inner" => "
        Wir haben deine Anfage durch unser Kontaktformular erhalten und werden so schnell wie möglich 
        mit dir in Kontakt treten. Je nach aktueller Auslastung und Verfügbarkeit von unserem Support 
        kann die Bearbeitung deiner Anfrage einen Tag oder bis zu einer Woche dauern. Wir priorisieren
        ausserdem Anfragen von Nutzern mit einem Premium-Abonnement.
        <br/><br/>
        
        Hier nochmals die Details zu deiner Anfrage:<br/>
        Name: <i>".$data->firstname." ".$data->lastname."</i> <br/>
        E-Mail: <i>".$data->mail."</i> <br/><br/>
        Betreff: <i>".$data->subject."</i> <br/>
        Nachricht: <br/>
        <i>".$data->message."</i> <br/><br/>

        Mit besten Grüssen, <br/>
        <b>Dein Osis.fit-Team</b>
    "
];

$Mailer2->body["footer.inner"] = Env_mail::page_name . " - " . Env_mail::slogan_de . " <br/> <i>" . Env_mail::creator_de . "</i>";