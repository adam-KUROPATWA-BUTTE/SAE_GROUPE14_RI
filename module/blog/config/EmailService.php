<?php
namespace Service;

class EmailService
{
    private static $fromEmail = 'noreply@ri-amu.app';
    private static $fromName = 'RI AMU - Réinitialisation';
    
    public static function sendPasswordReset($toEmail, $resetToken)
    {
        $resetLink = "https://ri-amu.app/index.php?page=reset&token=" . $resetToken;
        
        $subject = 'Réinitialisation de votre mot de passe';
        
        // Headers
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: ' . self::$fromName . ' <' . self::$fromEmail . '>';
        $headers[] = 'Reply-To: ' . self::$fromEmail;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        // Corps du message HTML
        $message = self::getResetPasswordTemplate($resetLink);
        
        // Envoi
        $result = mail($toEmail, $subject, $message, implode("\r\n", $headers));
        
        if (!$result) {
            error_log("Erreur envoi email à : $toEmail");
        }
        
        return $result;
    }
    
    private static function getResetPasswordTemplate($resetLink)
    {
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
        <h2 style='color: #4CAF50;'>Réinitialisation de mot de passe</h2>
        <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
        <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='$resetLink' 
               style='background-color: #4CAF50; 
                      color: white; 
                      padding: 12px 30px; 
                      text-decoration: none; 
                      border-radius: 5px;
                      display: inline-block;'>
                Réinitialiser mon mot de passe
            </a>
        </div>
        <p style='color: #666; font-size: 14px;'>
            Ce lien est valable pendant 1 heure.<br>
            Si vous n'avez pas demandé cette réinitialisation, ignorez ce message.
            Nous vous recommandons de changer votre mot de passe si vous pensez que votre compte a été compromis.
        </p>
        <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;'>
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
            <a href='$resetLink' style='color: #4CAF50;'>$resetLink</a>
        </p>
    </div>
</body>
</html>
        ";
    }
}