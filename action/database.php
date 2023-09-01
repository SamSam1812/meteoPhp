<?php

try{
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=gestion_temperature", 'root', '');
}
catch(PDOException $e){
    printf("Échec de la connexion : %s\n", $e->getMessage());
    exit();
}
