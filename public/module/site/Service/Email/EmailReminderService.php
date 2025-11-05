<?php
/**
 * Service d'envoi d'emails de relance pour dossiers incomplets
 * 
 * Ce service utilise l'API Mailjet pour envoyer des emails de relance
 * aux étudiants ayant des dossiers incomplets.
 * 
 * @package Service\Email
 * @author  Adam Kuropatwa-Butté
 * @version 1.0.0
 */

namespace Service\Email;

use Mailjet\Client;
use Mailjet\Resources;

/**
 * Service de gestion des emails de relance
 * 
 * Permet d'envoyer des emails de relance personnalisés aux étudiants
 * via l'API Mailjet avec un template HTML responsive.
 */
class EmailReminderService
{
    /**
     * Adresse email d'envoi
     * 
     * @var string
     */
    private static string $fromEmail = 'relance-iut-amu@ri-amu.app';

    /**
     * Nom de l'expéditeur affiché
     * 
     * @var string
     */
    private static string $fromName = 'IUT Aix - Gestion Dossiers';

    /**
     * Envoie un email de relance pour un dossier incomplet
     * 
     * Cette méthode construit et envoie un email personnalisé via Mailjet
     * pour informer un étudiant que son dossier est incomplet et liste
     * les pièces manquantes.
     * 
     * @param string       $toEmail         Email du destinataire
     * @param int|string   $dossierId       Identifiant du dossier
     * @param string       $studentName     Nom de l'étudiant (optionnel)
     * @param array<int,string> $itemsToComplete Liste des pièces à compléter
     * 
     * @return bool True si l'email a été envoyé avec succès, false sinon
     * 
     * @throws \Exception Si une erreur survient lors de l'envoi
     * 
     * @example
     * ```php
     * $success = EmailReminderService::sendRelance(
     *     'etudiant@example.com',
     *     123,
     *     'Jean Dupont',
     *     ['Carte d\'identité', 'Certificat de scolarité']
     * );
     * ```
     */
    public static function sendRelance(
        string $toEmail,
        int|string $dossierId,
        string $studentName = '',
        array $itemsToComplete = []
    ): bool {
        try {
            // Initialiser le client Mailjet
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );

            $subject = "Relance : dossier incomplet (n°{$dossierId})";
            $htmlMessage = self::buildMessage($dossierId, $studentName, $itemsToComplete);

            // Construire le payload Mailjet
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => self::$fromEmail,
                            'Name' => self::$fromName
                        ],
                        'To' => [
                            [
                                'Email' => $toEmail,
                                'Name' => $studentName ?: ''
                            ]
                        ],
                        'Subject' => $subject,
                        'HTMLPart' => $htmlMessage,
                        'TextPart' => strip_tags($htmlMessage)
                    ]
                ]
            ];

            // Envoyer via l'API Mailjet
            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                error_log("✅ Email envoyé avec succès à {$toEmail} via Mailjet");
                return true;
            } else {
                error_log("❌ Erreur Mailjet: " . json_encode($response->getData()));
                return false;
            }
        } catch (\Exception $e) {
            error_log("❌ Exception Mailjet pour {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Construit le message HTML de l'email de relance
     * 
     * Génère un template HTML responsive avec les informations du dossier
     * et la liste des pièces à compléter. Le contenu est sanitisé pour
     * prévenir les attaques XSS.
     * 
     * @param int|string   $dossierId       Identifiant du dossier
     * @param string       $studentName     Nom de l'étudiant
     * @param array<int,string> $itemsToComplete Liste des pièces manquantes
     * 
     * @return string HTML complet de l'email
     * 
     * @internal Cette méthode est privée et utilisée uniquement par sendRelance()
     */
    private static function buildMessage(
        int|string $dossierId,
        string $studentName,
        array $itemsToComplete
    ): string {
        $safeName = htmlspecialchars(
            trim($studentName ?: ''),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        
        $itemsHtml = '';

        if (!empty($itemsToComplete)) {
            $itemsHtml .= '<ul style="margin:0 0 16px 20px;">';
            foreach ($itemsToComplete as $it) {
                $itemsHtml .= '<li>' . htmlspecialchars(
                    (string)$it,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</li>';
            }
            $itemsHtml .= '</ul>';
        } else {
            $itemsHtml = '<p>Merci de compléter les pièces manquantes de votre dossier.</p>';
        }

        $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $encodedId = urlencode((string)$dossierId);
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$encodedId}";

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Relance dossier</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">Relance - Dossier Incomplet</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Votre dossier n°<strong>{$dossierId}</strong> est actuellement <strong>incomplet</strong>.</p>
      {$itemsHtml}
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Accéder à mon dossier</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">Pour toute question, contactez le service RI.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Mail automatique • Service RI</p>
    </div>
  </div>
</body>
</html>";
    }
}

// ========================================
// Model\Folder\RelanceModel
// ========================================

/**
 * Modèle de gestion des relances de dossiers
 * 
 * Gère la récupération des dossiers incomplets et l'enregistrement
 * des relances dans la base de données.
 * 
 * @package Model\Folder
 * @author  Votre Nom
 * @version 1.0.0
 */

namespace Model\Folder;

use Database;
use PDO;

/**
 * Classe modèle pour la gestion des relances
 * 
 * Fournit les méthodes pour interagir avec les tables dossiers,
 * etudiants et relances de la base de données.
 */
class RelanceModel
{
    /**
     * Connexion PDO à la base de données
     * 
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructeur du modèle
     * 
     * Initialise la connexion PDO via la classe singleton Database
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Récupère tous les dossiers incomplets avec les informations des étudiants
     * 
     * Effectue une jointure entre les tables dossiers et etudiants pour
     * récupérer toutes les informations nécessaires à l'envoi de relances.
     * 
     * @return array<int,array{
     *     dossier_id: int,
     *     etudiant_id: int,
     *     email_responsable: string|null,
     *     email_etudiant: string,
     *     nom: string,
     *     prenom: string
     * }> Liste des dossiers incomplets avec leurs informations
     * 
     * @example
     * ```php
     * $model = new RelanceModel();
     * $dossiers = $model->getIncompleteDossiers();
     * foreach ($dossiers as $dossier) {
     *     echo "Dossier {$dossier['dossier_id']} - {$dossier['nom']} {$dossier['prenom']}";
     * }
     * ```
     */
    public function getIncompleteDossiers(): array
    {
        $sql = "
            SELECT d.id AS dossier_id,
                   d.etudiant_id,
                   d.email_responsable,
                   e.email AS email_etudiant,
                   e.nom, e.prenom
            FROM dossiers d
            LEFT JOIN etudiants e ON e.id = d.etudiant_id
            WHERE d.iscomplet = 0
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enregistre une nouvelle relance dans la base de données
     * 
     * Insère une entrée dans la table relances avec la date automatique.
     * Utilisé pour tracer l'historique des relances envoyées.
     * 
     * @param int         $dossierId   ID du dossier concerné
     * @param string      $message     Description de la relance
     * @param int|null    $envoyePar   ID de l'administrateur (null si automatique)
     * 
     * @return bool True si l'insertion a réussi, false sinon
     * 
     * @example
     * ```php
     * // Relance automatique
     * $model->insertRelance(123, 'Relance automatique par cron', null);
     * 
     * // Relance manuelle
     * $model->insertRelance(456, 'Relance manuelle', 42);
     * ```
     */
    public function insertRelance(int $dossierId, string $message, ?int $envoyePar = null): bool
    {
        $sql = "INSERT INTO relances (dossier_id, message, envoye_par) 
                VALUES (:dossier_id, :message, :envoye_par)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':dossier_id' => $dossierId,
            ':message' => $message,
            ':envoye_par' => $envoyePar,
        ]);
    }

    /**
     * Vérifie si une relance récente existe pour un dossier
     * 
     * Permet d'éviter d'envoyer trop de relances à un même étudiant
     * en vérifiant si une relance a déjà été envoyée dans la période spécifiée.
     * 
     * @param int $dossierId ID du dossier à vérifier
     * @param int $days      Nombre de jours à vérifier (ex: 7 pour la dernière semaine)
     * 
     * @return bool True si une relance existe dans la période, false sinon
     * 
     * @example
     * ```php
     * if (!$model->lastRelanceWithinDays(123, 7)) {
     *     // Pas de relance dans les 7 derniers jours, on peut envoyer
     *     EmailReminderService::sendRelance(...);
     * }
     * ```
     */
    public function lastRelanceWithinDays(int $dossierId, int $days): bool
    {
        $sql = "SELECT 1 FROM relances 
                WHERE dossier_id = :dossier_id 
                AND date_relance >= (NOW() - INTERVAL :days DAY) 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':dossier_id', $dossierId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }
}