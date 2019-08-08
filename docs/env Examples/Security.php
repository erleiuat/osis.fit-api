<?php

class Env_sec {

    const phrase = "89:js0@89111";
    const encryption = PASSWORD_BCRYPT;

    /* Cookie */
    const c_name = "_osisFit_apiT_";
    const c_secure = false;
    const c_domain = "";
    const c_path = "/";

    /* Tokens */
    const t_issuer = "osis.fit Application";
    const t_algorithm = ['HS256'];
    const t_access_lifetime = (1*60*60); // 1 Hour
    const t_access_secret_sec = "1@78jmKHpx[89jkHBJ781";
    const t_access_secret_app = "as89Jmncpo:@[]Mm7Hbeo";
    const t_refresh_lifetime = (7*24*60*60); // 7 Days
    const t_refresh_secret = "1@78jmx[89jkHBJ781";

}
