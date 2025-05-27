<?php
include_once("AccessBDD.php");

class MyAccessBDD extends AccessBDD {

    public function __construct() {
        try {
            parent::__construct();
        } catch(\Exception $e) {
            throw $e;
        }
    }

    protected function traitementSelect(string $table, ?array $champs) : ?array {
        switch($table) {
            case "livre":
                return $this->selectAllLivres();
            case "dvd":
                return $this->selectAllDvd();
            case "revue":
                return $this->selectAllRevues();
            case "exemplaire":
                return $this->selectExemplairesRevue($champs);
            case "abonnement": 
                return $this->selectAbonnementsByRevue($champs);
            case "commande":
                return $this->selectCommandesByDocument($champs);
            case "commandedocument":
                return $this->selectCommandesByDocument($champs);
            case "genre":
            case "public":
            case "rayon":
            case "etat":
            case "suivi":    
                return $this->selectTableSimple($table);
            default:
                return $this->selectTuplesOneTable($table, $champs);
        }
    }

    protected function traitementInsert(string $table, ?array $champs) : ?int {
        if ($table === "commandelivredvd") {
            try {
                $this->conn->beginTransaction();

                // Insertion dans la table commande
                $commandeData = [
                    "id" => $champs["id"],
                    "dateCommande" => $champs["dateCommande"],
                    "montant" => $champs["montant"]
                ];
                $requeteCommande = "INSERT INTO commande (id, dateCommande, montant) 
                                    VALUES (:id, :dateCommande, :montant);";
                $this->conn->updateBDD($requeteCommande, $commandeData);

                // Insertion dans la table commandedocument
                $commandeDocData = [
                    "id" => $champs["id"],
                    "nbExemplaire" => $champs["nbExemplaire"],
                    "idLivreDvd" => $champs["idLivreDvd"],
                    "idSuivi" => $champs["idSuivi"]
                ];
                $requeteDoc = "INSERT INTO commandedocument (id, nbExemplaire, idLivreDvd, idSuivi)
                               VALUES (:id, :nbExemplaire, :idLivreDvd, :idSuivi);";
                $this->conn->updateBDD($requeteDoc, $commandeDocData);

                $this->conn->commit();
                return 1;
            } catch (Exception $e) {
                $this->conn->rollBack();
                echo json_encode(["erreurSQL" => $e->getMessage()]);
                return null;
            }
        } else {
            return $this->insertOneTupleOneTable($table, $champs);
        }
    }

    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int {
        return $this->updateOneTupleOneTable($table, $id, $champs);
    }

    protected function traitementDelete(string $table, ?array $champs) : ?int {
        return $this->deleteTuplesOneTable($table, $champs);
    }

    private function selectTuplesOneTable(string $table, ?array $champs) : ?array {
        try {
            if (empty($champs)) {
                $requete = "SELECT * FROM $table;";
                return $this->conn->queryBDD($requete);
            } else {
                return null;
            }
        } catch (Exception $e) {
            echo json_encode(["erreurSQL" => $e->getMessage()]);
            return null;
        }
    }

    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int {
        if (empty($champs)) return null;
        $cols = implode(",", array_keys($champs));
        $params = implode(",", array_map(fn($k) => ":$k", array_keys($champs)));
        $requete = "INSERT INTO $table ($cols) VALUES ($params);";
        return $this->conn->updateBDD($requete, $champs);
    }

    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if (empty($champs) || is_null($id)) return null;
        $set = implode(",", array_map(fn($k) => "$k=:$k", array_keys($champs)));
        $requete = "UPDATE $table SET $set WHERE id=:id;";
        $champs["id"] = $id;
        return $this->conn->updateBDD($requete, $champs);
    }

    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int {
        if (empty($champs)) return null;
        $conditions = implode(" AND ", array_map(fn($k) => "$k=:$k", array_keys($champs)));
        $requete = "DELETE FROM $table WHERE $conditions;";
        return $this->conn->updateBDD($requete, $champs);
    }

    private function selectTableSimple(string $table) : ?array {
        $requete = "SELECT * FROM $table ORDER BY libelle;";
        return $this->conn->queryBDD($requete);
    }

    private function selectAllLivres() : ?array {
        $requete = "SELECT l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, d.idrayon, d.idpublic, d.idgenre,
            g.libelle as genre, p.libelle as lePublic, r.libelle as rayon
            FROM livre l
            JOIN document d ON l.id=d.id
            JOIN genre g ON g.id=d.idGenre
            JOIN public p ON p.id=d.idPublic
            JOIN rayon r ON r.id=d.idRayon
            ORDER BY titre;";
        return $this->conn->queryBDD($requete);
    }

    private function selectAllDvd() : ?array {
        $requete = "SELECT l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, d.idrayon, d.idpublic, d.idgenre,
            g.libelle as genre, p.libelle as lePublic, r.libelle as rayon
            FROM dvd l
            JOIN document d ON l.id=d.id
            JOIN genre g ON g.id=d.idGenre
            JOIN public p ON p.id=d.idPublic
            JOIN rayon r ON r.id=d.idRayon
            ORDER BY titre;";
        return $this->conn->queryBDD($requete);
    }

    private function selectAllRevues() : ?array {
        $requete = "SELECT l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, d.idrayon, d.idpublic, d.idgenre,
            g.libelle as genre, p.libelle as lePublic, r.libelle as rayon
            FROM revue l
            JOIN document d ON l.id=d.id
            JOIN genre g ON g.id=d.idGenre
            JOIN public p ON p.id=d.idPublic
            JOIN rayon r ON r.id=d.idRayon
            ORDER BY titre;";
        return $this->conn->queryBDD($requete);
    }

    private function selectExemplairesRevue(?array $champs) : ?array {
        if(empty($champs) || !array_key_exists('id', $champs)) return null;
        $requete = "SELECT e.id, e.numero, e.dateAchat, e.photo, e.idEtat
            FROM exemplaire e
            JOIN document d ON e.id=d.id
            WHERE e.id = :id
            ORDER BY e.dateAchat DESC;";
        return $this->conn->queryBDD($requete, ['id' => $champs['id']]);
    }

    private function selectAbonnementsByRevue(?array $champs) : ?array {
        if (empty($champs) || !array_key_exists('id', $champs)) return null;
        $requete = "SELECT a.id, a.dateCommande, a.dateFinAbonnement, a.montant, a.stade, a.idSuivi
                    FROM abonnement a
                    WHERE a.idRevue = :id
                    ORDER BY a.dateFinAbonnement DESC;";

        return $this->conn->queryBDD($requete, ['id' => $champs['id']]);
    }

    private function selectCommandesByDocument(?array $champs): ?array {
        if (empty($champs) || !array_key_exists('id', $champs)) return null;
        $requete = "SELECT c.id, c.dateCommande, c.montant, cd.nbExemplaire, cd.idSuivi, s.libelle AS stade
                    FROM commande c
                    JOIN commandedocument cd ON c.id = cd.id
                    JOIN suivi s ON cd.idSuivi = s.idSuivi
                    WHERE cd.idLivreDvd = :id
                    ORDER BY c.dateCommande DESC;";
        return $this->conn->queryBDD($requete, ['id' => $champs['id']]);
    }
}
