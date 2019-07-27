<?php

class Env {

    /* General */
    const api_env = "test"; // test, prod
    const api_version = "1.0";   
    const api_name = "osis.fit";   
    const api_timezone = "Europe/Zurich";
    
    /* Mail */
    const mail_from_name = "Osis.fit";
    const mail_from_adress = "noreply@osis.fit";
    
    /* Security */
    const sec_cors = "http://localhost:8080";
    const sec_phrase = "89:js0@89111";
    const sec_error_reports = E_ALL;
    const sec_encryption = PASSWORD_BCRYPT;

    /* Database */
    const db_host = "localhost";                        
    const db_database = "osis.fit";                        
    const db_user = "root";                            
    const db_password = "";   

    /* Cookie */
    const coo_lifetime = (3*60*60);                 /* Lifetime in seconds */
    const coo_name = "_osisFit_apiT_";
    const coo_secure = false;
    const coo_domain = "";
    const coo_path = "/";

    /* Access Token */
    const tkn_lifetime = (3);
    //const tkn_lifetime = (3*60*60);                 /* Lifetime in seconds */
    const tkn_issuer = "osis.fit Application";
    const tkn_algorithm = ['HS256'];
    const tkn_secret_sec = "1@78jmKHpx[89jkHBJ781";
    const tkn_secret_app = "as89Jmncpo:@[]Mm7Hbeo";

    /* Refresh Token */
    const rtkn_lifetime = (3*60*60);                /* Lifetime in seconds */
    const rtkn_issuer = "osis.fit Application";
    const rtkn_algorithm = ['HS256'];
    const rtkn_secret = "1@78jmx[89jkHBJ781";

}