<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=mediatek86;charset=utf8', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['code' => 500, 'message' => 'Erreur de connexion BDD', 'result' => '']);
    exit;
}

// Récupération des données JSON envoyées
$donnees = json_decode(file_get_contents("php://input"), true);

// Vérification des paramètres
if (!isset($donnees['login']) || !isset($donnees['pwd'])) {
    echo json_encode(['code' => 400, 'message' => 'Paramètres manquants', 'result' => '']);
    exit;
}

$login = $donnees['login'];
$pwd = $donnees['pwd'];

// Requête SQL pour trouver l'utilisateur
$req = $bdd->prepare("SELECT id, nom, prenom, login, pwd, idservice FROM employe WHERE login = :login AND pwd = :pwd");
$req->bindParam(':login', $login);
$req->bindParam(':pwd', $pwd);
$req->execute();

if ($req->rowCount() == 1) {
    $user = $req->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['code' => 200, 'message' => 'OK', 'result' => $user]);
} else {
    echo json_encode(['code' => 401, 'message' => 'authentification incorrecte', 'result' => '']);
}
?>
