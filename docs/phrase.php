<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
    body {
        font-family: sans-serif;
        padding-top:30px;
        width: 100%;
    }
    #codes {
        text-align: center;
        margin-left: auto;
        margin-right: auto;
    }
    </style>
</head>
<body>
    <div id="codes">
    <?php

        function generateRandomString($length = 10) {
            $chars = '@:/\?!56789efghijklmnopqrstuvwxyzGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($chars);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $chars[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        function getPhrase(){

            sleep(0.2);
            $unique = uniqid('', true);
            $time = date('Y_m_d_H_i_s', time());
            $phrase = hash('ripemd160', $time.':'.$unique);
            $random = generateRandomString(20);

            return $random.$phrase;

        }

        for($i=0;$i<=20;$i++){
            echo getPhrase() . "<br/><br/>";
        }

    ?>
    </div>
</body>
</html>

