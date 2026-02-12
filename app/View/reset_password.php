<?php
/**
 * @var bool $isTritanopia
 * @var string|null $error
 * @var string|null $success
 * @var string|null $token
 */
?>
<!DOCTYPE html><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Password reset - AMU International Relations Service">
    <title>Password Reset - AMU</title>
    <link rel="stylesheet" href="/styles/login.css">
    <link rel="icon" type="image/png" href="/img/favicon.webp"/>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">
<div class="login-container">
    <div class="login-card">
        <h1>New Password</h1>

        <?php if (!empty($error)) : ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="/index.php?page=login" class="btn-primary">Log in</a>
            </div>
        <?php else : ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           minlength="8"
                           placeholder="Minimum 8 characters">
                    <small>Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password"
                           id="password_confirm"
                           name="password_confirm"
                           required
                           placeholder="Confirm your password">
                </div>

                <button type="submit" name="submit_reset" class="btn-primary">
                    Reset Password
                </button>
            </form>

            <div class="login-links">
                <a href="/index.php?page=login">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>