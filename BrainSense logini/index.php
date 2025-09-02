<?php
session_start();
include('db.php'); // Include database connection

// Initialize error messages array if not set
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

// Handle Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // No password hash used
    $date_of_birth = $_POST['date_of_birth'];
    $phone_number = $_POST['phone_number'];
    $user_type = $_POST['user_type'];

    // Check if the email already exists
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['errors'][] = "L'email est déjà utilisé. Essayez un autre.";
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO users (full_name, email, password, date_of_birth, phone_number, user_type) 
                VALUES ('$full_name', '$email', '$password', '$date_of_birth', '$phone_number', '$user_type')";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header("Location: succ.php"); // Redirect after success
            exit();
        } else {
            $_SESSION['errors'][] = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email exists
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Compare the plain password
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            header("Location: succ.php"); // Redirect to dashboard after login
            exit();
        } else {
            $_SESSION['errors'][] = "Mot de passe incorrect.";
        }
    } else {
        $_SESSION['errors'][] = "Aucun utilisateur trouvé avec cet email.";
    }
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="Login&Regsiter/style.css">
    <title>BrainSense - Page de Connexion</title>
</head>

<body>

    <div class="container" id="container">



        <!-- Sign-Up Form -->
        <div class="form-container sign-up">
            <form method="POST" action="index.php">
                <h1>Créer un compte</h1>
                <span>ou utilisez votre email pour vous inscrire</span>
                <input type="text" name="full_name" placeholder="Nom complet" required>
                <input type="email" name="email" placeholder="Adresse email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <input type="date" name="date_of_birth" placeholder="Date de naissance" required>
                <input type="tel" name="phone_number" placeholder="Numéro de téléphone" required>
                <select name="user_type" required>
                    <option value="" disabled selected>Type d'utilisateur</option>
                    <option value="Administrateur">Administrateur</option>
                    <option value="Doctor">Docteur</option>
                    <option value="Patient">Patient</option>
                    <option value="Secretaire">Secrétaire</option>
                </select>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>

        <!-- Sign-In Form -->
        <div class="form-container sign-in">
            <form method="POST" action="index.php">
                <h1>Connexion</h1>
                <span>ou utilisez votre email et mot de passe</span>
                <input type="email" name="email" placeholder="Adresse email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <a href="#">Mot de passe oublié ?</a>
                <button type="submit" name="login">Se connecter</button>
                <?php
        // Display success and error messages
        if (isset($_SESSION['errors']) && count($_SESSION['errors']) > 0) {
            foreach ($_SESSION['errors'] as $error) {
                echo "<div class='error-message'>{$error}</div>";
            }
            unset($_SESSION['errors']); // Clear errors after displaying
        }

        if (isset($_SESSION['success'])) {
            echo "<div class='success-message'>{$_SESSION['success']}</div>";
            unset($_SESSION['success']); // Clear success message after displaying
        }
        ?>
            </form>
        </div>

        <!-- Toggle Panels -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <div class="logo-container">
                        <img src="img/Logo(BrainSense )png.png" alt="BrainSense Logo" class="logo">
                    </div>
                    <h1>Bon retour !</h1>
                    <p>Entrez vos informations personnelles pour utiliser toutes les fonctionnalités de BrainSense.</p>
                    <button class="hidden" id="login">Connexion</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <div class="logo-container">
                        <img src="img/Logo(BrainSense )png.png" alt="BrainSense Logo" class="logo">
                    </div>
                    <h1>Bienvenue !</h1>
                    <p>Inscrivez-vous pour rejoindre notre communauté et bénéficier de tous nos services.</p>
                    <button class="hidden" id="register">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>
  
    <script src="Login&Regsiter/script.js"></script>
</body>

</html>
