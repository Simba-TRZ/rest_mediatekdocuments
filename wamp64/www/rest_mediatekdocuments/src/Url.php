<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

/**
 * Singleton car la récupération des données ne peut se faire qu'une fois
 * Permet de gérer le contenu de l'URL qui sollicite l'API
 */
class Url {

    /**
     * instance de la classe actuelle
     * @var Url
     */
    private static $instance = null;

    /**
     * tableau contenant toutes les variables transmises
     * @var array
     */
    private $data = [];

    /**
     * constructeur privé
     * récupère les variables d'environnement et les données de l'URL
     */
    private function __construct() {
        // Chargement du fichier .env depuis le dossier parent
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // récupération des données de la requête
        $this->data = $this->recupAllData();
    }

    /**
     * méthode statique de création de l'instance unique
     * @return Url
     */
    public static function getInstance(): Url {
        if (self::$instance === null) {
            self::$instance = new Url();
        }
        return self::$instance;
    }

    /**
     * récupère la méthode HTTP utilisée
     * @return string
     */
    public function recupMethodeHTTP(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * récupère une variable (string ou json)
     * @param string $nom
     * @param string $format
     * @return string|array|null
     */
    public function recupVariable(string $nom, string $format = "string"): string|array|null {
        $variable = $this->data[$nom] ?? '';
        if ($format === "json") {
            return $variable ? json_decode($variable, true) : null;
        }
        return $variable;
    }

    /**
     * vérifie le mode d'authentification défini dans le .env
     * @return bool
     */
    public function authentification(): bool {
        $authentification = $_ENV['AUTHENTIFICATION'] ?? '';
        switch ($authentification) {
            case '':
                return true;
            case 'basic':
                return self::basicAuthentification();
            default:
                return true;
        }
    }

    /**
     * Authentification basique par identifiants
     * @return bool
     */
    private function basicAuthentification(): bool {
        $expectedUser = $_ENV['AUTH_USER'] ?? '';
        $expectedPw = $_ENV['AUTH_PW'] ?? '';
        $authUser = $_SERVER['PHP_AUTH_USER'] ?? '';
        $authPw = $_SERVER['PHP_AUTH_PW'] ?? '';
        return ($authUser === $expectedUser && $authPw === $expectedPw);
    }

    /**
     * récupération de toutes les données envoyées
     * @return array
     */
    private function recupAllData(): array {
        $data = [];

        // Tentative de récupération de données JSON brutes
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        if (is_array($json)) {
            $data = $json;
        } else {
            // fallback GET/POST
            if (!empty($_POST)) {
                $data = $_POST;
            } elseif (!empty($_GET)) {
                $data = $_GET;
            }
        }

        return $data;
    }
}
