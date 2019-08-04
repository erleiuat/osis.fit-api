<?php

class Env {

    /* General */
    const api_env = "test"; // test, prod
    const api_version = "1.0";   
    const api_name = "osis.fit";   
    const api_timezone = "Europe/Zurich";
    const api_static_url = "http://localhost/osis.io-static/usercontent";
    const api_static_path = "../../../../osis.io-static/usercontent";

    /* Mail */
    const mail_from_name = "Osis.fit";
    const mail_from_adress = "noreply@osis.fit";
    const mail_logo_url = "";
    const mail_page_name = "Osis.fit";
    const mail_slogan = "Get fit faster!";
    const mail_creator = "Developed with passion in Basel";
    const mail_slogan_de = "Schneller fit werden!";
    const mail_creator_de = "Mit Leidenschaft in Basel entwickelt";

    /* Security */
    const sec_cors = "http://localhost:8080";
    const sec_phrase = "89:js0@89111";
    const sec_error_reports = E_ALL;
    const sec_encryption = PASSWORD_BCRYPT;

    /* Database */
    const db_host = "localhost";                        
    const db_database = "app.osis.fit";                        
    const db_user = "root";                            
    const db_password = "";   

    /* Cookie */
    const coo_name = "_osisFit_apiT_";
    const coo_secure = false;
    const coo_domain = "";
    const coo_path = "/";

    /* Tokens */
    const tkn_issuer = "osis.fit Application";
    const tkn_algorithm = ['HS256'];
    const tkn_access_lifetime = (1*60*60); // 1 Hour
    const tkn_access_secret_sec = "1@78jmKHpx[89jkHBJ781";
    const tkn_access_secret_app = "as89Jmncpo:@[]Mm7Hbeo";
    const tkn_refresh_lifetime = (7*24*60*60); // 7 Days
    const tkn_refresh_secret = "1@78jmx[89jkHBJ781";

}