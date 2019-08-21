<?php

class ENV_sec {

    /* General */
    const phrase = "89:js0@89111_LOCAL";
    const encryption = PASSWORD_BCRYPT;
    const pw_encryption = PASSWORD_BCRYPT;

    /* Subscriptions */
    const sub_site = "osis-fit-test";
    const sub_tkn = "test_bEyMPSgEjzZx4cn7q1avZiPwp6XtPOSx";
    const sub_plan = "premium";

    /* Cookie */
    const c_name = "_osisFit_api_local_";
    const c_secure = false;
    const c_domain = "";
    const c_path = "/";

    /* Tokens */
    const t_issuer = "osis.fit Application";
    const t_algorithm = ['HS256'];

    const t_access_lifetime = (1*60*60); // 1 Hour
    const t_access_secret = "as89Jmncpo:@[]Mm7Hbeo";

    const t_secure_lifetime = (1*60*60); // 1 Hour
    const t_secure_secret = "1@78jmKHpx[89jkHBJ781";

    const t_refresh_lifetime = (7*24*60*60); // 7 Days
    const t_refresh_secret = "1@78jmx[89jkHBJ781";

}
