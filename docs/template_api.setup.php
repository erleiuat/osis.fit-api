<?php

class Setup {

    /* General */
    const api_name = "osis.fit";   
    const api_env = "localhost";
    const api_error_reports = E_ALL; 
    const api_timezone = "Europe/Zurich";

    /* Security */
    const sec_cors = "http://localhost:8080";
    const sec_phrase = "89:js0@891";

    /* Database */
    const db_host = "localhost";                        
    const db_database = "osis.fit";                        
    const db_user = "root";                            
    const db_password = "";   

    /* Cookie */
    const coo_lifetime = (3*60*60);             /* Lifetime in seconds */
    const coo_name = "_osis.fit_apiT_";
    const coo_secure = false;
    const coo_domain = "";
    const coo_path = "/";

    /* Auth Token */
    const tkn_lifetime = (3*60*60);             /* Lifetime in seconds */
    const tkn_issuer = "Osis.fit Application";
    const tkn_algorithm = ['HS256'];
    const tkn_secret_sec = "1@78jmKHpx[89jkHBJ781";
    const tkn_secret_app = "as89Jmncpo:@[]Mm7Hbeo";

    /* Refresh Token */
    const rtkn_validity = (3*60*60);             /* Validity in seconds */
    const rtkn_issuer = "Osis.fit Application";
    const rtkn_algorithm = ['HS256'];
    const rtkn_secret = "1@78jmKHpx[89jkHBJ781";

}