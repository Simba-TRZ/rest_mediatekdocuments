<?php
/**
 * Point d'entrée principal de l'API : Contrôleur général qui traite toutes les requêtes entrantes
 * et formate les réponses en JSON.
 */

header('Content-Type: application/json');

include_once("MyAccessBDD.php");

/**
 * Classe Controle : reçoit les demandes HTTP, les transmet au modèle MyAccessBDD
 * et renvoie les réponses normalisées au format JSON.
 */
class Controle {

    /**
     * Objet d'accès à la base de données
     * @var MyAccessBDD
     */
    private MyAccessBDD $myAaccessBDD;

    /**
     * Constructeur : initialise l'accès BDD et gère les erreurs de connexion
     */
    public function __construct() {
        try {
            $this->myAaccessBDD = new MyAccessBDD();
        } catch (Exception $e) {
            $this->reponse(500, "erreur serveur", $e->getMessage());
            die();
        }
    }

    /**
     * Reçoit une demande API, délègue à la couche modèle et gère la réponse
     *
     * @param string $methodeHTTP Méthode HTTP utilisée (GET, POST, PUT, DELETE)
     * @param string $table Nom de la table concernée (ex : livre, abonnement...)
     * @param string|null $id Identifiant (optionnel)
     * @param array|null $champs Données envoyées (optionnel)
     */
    public function demande(string $methodeHTTP, string $table, ?string $id, ?array $champs) {
        $result = $this->myAaccessBDD->demande($methodeHTTP, $table, $id, $champs);
        $this->controleResult($result);
    }

    /**
     * Envoie une réponse HTTP au format JSON
     *
     * @param int $code Code de statut HTTP (ex : 200, 401, 500)
     * @param string $message Message associé au code (ex : OK, erreur serveur)
     * @param array|int|string|null $result Données retournées (facultatives)
     */
    private function reponse(int $code, string $message, array|int|string|null $result = "") {
        $retour = [
            'code' => $code,
            'message' => $message,
            'result' => $result
        ];
        echo json_encode($retour, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Vérifie le résultat de la requête : envoie une réponse 200 ou 400 selon le cas
     *
     * @param array|int|null $result Résultat retourné par le modèle
     */
    private function controleResult(array|int|null $result) {
        if (!is_null($result)) {
            $this->reponse(200, "OK", $result);
        } else {
            $this->reponse(400, "requete invalide");
        }
    }

    /**
     * Réponse standard en cas d'échec d'authentification
     */
    public function unauthorized() {
        $this->reponse(401, "authentification incorrecte");
    }
}
