<?php
    $host = "kark.uit.no";
    $dbname = "stud_v20_aaoyen";
    $username = "stud_v20_aaoyen";
    $password = "passord";

    try
    {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    }
    catch (PDOException $e)
    {
        print($e->getMessage());
    }
?>