<?php

$Mailer1->subject = "Osis.fit | Support request";

$Mailer1->body = [
    "header.heading" => "New Support Request",
    "header.subheading" => "Someone just sent a request",
    "content.heading" => "Request Details",
    "content.inner" => "
        
        From: <i>".$data->firstname." ".$data->lastname."</i> <br/>
        E-Mail: <i>".$data->mail."</i> <br/>
        Subscription-Status: <i>".$uDetail->subscription->status."</i> <br/>
        
        <br/>
        Subject: <i>".$data->subject."</i> <br/>
        Message: <br/>
        <i>".$data->message."</i> <br/><br/>

        <b>Details</b> <br/>
        User-ID: <i>".$uDetail->account->id."</i> <br/>
        Username: <i>".$uDetail->account->username."</i> <br/><br/>
        Subscription-ID: <i>".$uDetail->subscription->id."</i> <br/>
        Subscription-Status: <i>".$uDetail->subscription->status."</i> <br/>
        Subscription-Deleted: <i>".$uDetail->subscription->deleted."</i> <br/>
        Subscription-Plan: <i>".$uDetail->subscription->plan."</i> <br/>

    "
];