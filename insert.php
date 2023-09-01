<?php

require ('action/database.php');

for ($i = 0; $i < 200; $i++) {
    $temperature = rand(1, 30);
    $humidite = rand(1, 100);
    $datetime = new DateTime('now');
    $add_minutes = $i*30;
    $date = $datetime->add(new DateInterval('PT'. $add_minutes .'M'));
    $stmt = $pdo->prepare("INSERT INTO meteo (temperature, humidite, date_heure) VALUES (:temperature, :humidite, :date_heure);");
    $date = $date->format('Y-m-d H:i:s');
    $stmt->bindParam(':temperature', $temperature);
    $stmt->bindParam(':humidite', $humidite);
    $stmt->bindParam(':date_heure', $date);
    $stmt->execute();
}