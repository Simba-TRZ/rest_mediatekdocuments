<?php
/**
 * Classe de connexion à la BDD MySQL utilisant le pattern Singleton.
 * Fournit des méthodes pour exécuter des requêtes SELECT (LID) et INSERT/UPDATE/DELETE (LMD).
 * Retourne les résultats sous forme de tableau associatif ou de nombre de lignes affectées.
 * En cas d'erreur, retourne null.
 */
class Connexion {

    /**
     * Instance unique de la classe Connexion
     * @var Connexion|null
     */
    private static ?Connexion $instance = null;

    /**
     * Objet PDO utilisé pour se connecter à la base de données
     * @var \PDO|null
     */
    private ?\PDO $conn = null;

    /**
     * Constructeur privé : initialise la connexion PDO
     *
     * @param string $login Nom d'utilisateur
     * @param string $pwd Mot de passe
     * @param string $bd Nom de la base de données
     * @param string $server Adresse du serveur (ex: localhost)
     * @param string $port Port de connexion MySQL
     *
     * @throws \Exception Si la connexion échoue
     */
    private function __construct(string $login, string $pwd, string $bd, string $server, string $port) {
        try {
            $this->conn = new \PDO("mysql:host=$server;dbname=$bd;port=$port", $login, $pwd);
            $this->conn->query('SET CHARACTER SET utf8');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retourne l'instance unique de Connexion (Singleton)
     *
     * @param string $login
     * @param string $pwd
     * @param string $bd
     * @param string $server
     * @param string $port
     *
     * @return Connexion
     */
    public static function getInstance(string $login, string $pwd, string $bd, string $server, string $port) : Connexion {
        if (self::$instance === null) {
            self::$instance = new Connexion($login, $pwd, $bd, $server, $port);
        }
        return self::$instance;
    }

    /**
     * Exécute une requête INSERT/UPDATE/DELETE
     *
     * @param string $requete Requête SQL
     * @param array|null $param Paramètres nommés (facultatif)
     *
     * @return int|null Nombre de lignes affectées ou null si erreur
     */
    public function updateBDD(string $requete, ?array $param = null) : ?int {
        try {
            $result = $this->prepareRequete($requete, $param);
            $reponse = $result->execute();
            return $reponse === true ? $result->rowCount() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Exécute une requête SELECT (lecture) et retourne un tableau associatif
     *
     * @param string $requete Requête SQL
     * @param array|null $param Paramètres nommés (facultatif)
     *
     * @return array|null Résultats sous forme de tableau ou null si erreur
     */
    public function queryBDD(string $requete, ?array $param = null) : ?array {
        try {
            $result = $this->prepareRequete($requete, $param);
            $reponse = $result->execute();
            if ($reponse === true) {
                return $result->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                throw new \Exception("Échec de l'exécution SQL.");
            }
        } catch (\Exception $e) {
            echo json_encode([
                "erreur" => "Erreur SQL",
                "requete" => $requete,
                "param" => $param,
                "message" => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Prépare une requête SQL avec ses paramètres (bind)
     *
     * @param string $requete Requête SQL à préparer
     * @param array|null $param Paramètres nommés à binder (facultatif)
     *
     * @return \PDOStatement Requête préparée
     *
     * @throws \Exception En cas d'erreur de préparation
     */
    private function prepareRequete(string $requete, ?array $param = null) : \PDOStatement {
        try {
            $requetePrepare = $this->conn->prepare($requete);
            if ($param !== null && is_array($param)) {
                foreach ($param as $key => &$value) {
                    $requetePrepare->bindParam(":$key", $value);
                }
            }
            return $requetePrepare;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
