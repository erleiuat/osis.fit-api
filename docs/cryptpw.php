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
    #crypted {
        text-align: center;
        margin-left: auto;
        margin-right: auto;
    }
    #form {
        text-align: center;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 20px;
    }
    #form input, #form select {
        min-width: 300px;
    }
    </style>
</head>
<body>

    <div id="form">
        <form action="cryptpw.php" method="post">
            Password to encrypt: 
            <br /><br />
            <input type="text" name="passtocrypt">
            <br /><br />
            <select name="crypttype">
                <option value="PASSWORD_BCRYPT">PASSWORD_BCRYPT</option>
                <option value="PASSWORD_ARGON2I">PASSWORD_ARGON2I</option>
                <option value="PASSWORD_ARGON2ID">PASSWORD_ARGON2ID</option>
                <option value="PASSWORD_DEFAULT">PASSWORD_DEFAULT (BCRYPT)</option>
            </select>
            <br /><br />
            <input type="submit">
            <br /><br />
        </form>
    </div>

    <div id="crypted">
    <?php

        if (isset($_POST["passtocrypt"])) {

            if (isset($_POST["crypttype"])) $ctype = $_POST["crypttype"];
            else $ctype = PASSWORD_BCRYPT;

            $pw_clear = $_POST["passtocrypt"];
            $pw_hast = password_hash($pw_clear, PASSWORD_BCRYPT);
            echo $pw_hast;

        } else {
            echo '<i>Enter Password and click send</i>';
        }

    ?>
    </div>

</body>
</html>

