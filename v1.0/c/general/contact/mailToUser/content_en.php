<?php

$Mailer2->subject = "Osis.fit | Your contact request";

$Mailer2->body = [
    "header.heading" => "Thank you!",
    "header.subheading" => "We will process your request as soon as possible",
    "content.heading" => "Hello " . $data->firstname . "!",
    "content.inner" => "
        We have received your request through our contact form and will contact you as 
        soon as possible. Depending on the current workload and availability of our 
        support, the processing of your request may take a day or up to a week. 
        We also prioritize requests from users with a premium subscription.
        <br/><br/>

        The details of your request:<br/>
        Name: <i>".$data->firstname." ".$data->lastname."</i> <br/>
        E-Mail: <i>".$data->mail."</i> <br/><br/>
        Subject: <i>".$data->subject."</i> <br/>
        Message: <br/>
        <i>".$data->message."</i> <br/><br/>

        Best regards, <br/>
        <b>Your Osis.fit-Team</b>
    "
];