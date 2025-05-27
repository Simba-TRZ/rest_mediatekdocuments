<?php
/*
 * index.php : point d’entrée de l’API REST
 */

// Chargement des dépendances (Composer, Dotenv, etc.)
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

include_once("Url.php");
include_once("Controle.php");

// Crée les objets
$url = Url::getInstance();
$controle = new Controle();

// ---------- ROUTAGE DES URL TYPE /abonnement/10002 ----------

$uri = $_SERVER['REQUEST_URI'] ?? '';
$matches = [];

// Cas : /abonnement/10002
if (preg_match('#/abonnement/([0-9]+)$#', $uri, $matches)) {
    $_GET['table'] = 'abonnement';
    $_GET['id'] = $matches[1];
}
// Cas générique : /table/id
elseif (preg_match('#/([a-zA-Z0-9_]+)/([0-9]+)$#', $uri, $matches)) {
    $_GET['table'] = $matches[1];
    $_GET['id'] = $matches[2];
}
// Cas table avec champs encodé
elseif (preg_match('#/([a-zA-Z0-9_]+)\?champs=([^&]+)#', $uri, $matches)) {
    $_GET['table'] = $matches[1];
    $_GET['champs'] = $matches[2];
}
// Cas simple : /table
elseif (preg_match('#/([a-zA-Z0-9_]+)$#', $uri, $matches)) {
    $_GET['table'] = $matches[1];
}

// ---------- GESTION DE LA REQUÊTE ----------

// Authentification
if (!$url->authentification()) {
    $controle->unauthorized();
} else {
    $methodeHTTP = $url->recupMethodeHTTP();
    $table = $url->recupVariable("table");
    $id = $url->recupVariable("id");

    // Cas spécial : GET avec champs encodés en base64 (comme commandedocument)
    if (isset($_GET['champs'])) {
        $champsBase64 = $_GET['champs'];
        $champsJson = base64_decode($champsBase64);
        $champs = json_decode($champsJson, true);
    }
    // Cas courant : GET avec ?id=xxxxx
    elseif (!empty($id)) {
        $champs = ["id" => $id];
    }
    else {
        $champs = null;
    }

    if (empty($table)) {
        echo json_encode([
            "erreur" => "Erreur API",
            "code" => 400,
            "message" => "Paramètre 'table' manquant dans l'URL"
        ]);
        exit;
    }

    $controle->demande($methodeHTTP, $table, $id, $champs);
}
