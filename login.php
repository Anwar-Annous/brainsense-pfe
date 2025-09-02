<?php
session_start();
include('config/database.php');

// Initialize error messages array if not set
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

// Handle Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Removed password hashing
    $date_of_birth = $_POST['date_of_birth'];
    $phone_number = $_POST['phone_number'];
    $username = explode('@', $email)[0];

    try {
        // Check if the email already exists
        $check_sql = "SELECT * FROM users WHERE email = :email";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $_SESSION['errors'][] = "L'email est déjà utilisé. Essayez un autre.";
        } else {
            // Start transaction
            $conn->beginTransaction();

            // Insert into users table with correct fields
            $user_sql = "INSERT INTO users (username, password, full_name, email, role, Profile, status) 
                        VALUES (:username, :password, :full_name, :email, 'patient', 'default.jpg', 'active')";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bindParam(':username', $username);
            $user_stmt->bindParam(':password', $password);
            $user_stmt->bindParam(':full_name', $full_name);
            $user_stmt->bindParam(':email', $email);
            $user_stmt->execute();
            $user_id = $conn->lastInsertId();

            // Insert into patients table with all required fields
            $patient_sql = "INSERT INTO patients (
                user_id, 
                full_name, 
                date_of_birth, 
                gender,
                email, 
                phone,
                password,
                profile_photo,
                created_at,
                updated_at,
                last_password_change
            ) VALUES (
                :user_id, :full_name, :date_of_birth, 'other', :email, :phone, :password, 
                'default.jpg', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_DATE
            )";
            
            $patient_stmt = $conn->prepare($patient_sql);
            $patient_stmt->bindParam(':user_id', $user_id);
            $patient_stmt->bindParam(':full_name', $full_name);
            $patient_stmt->bindParam(':date_of_birth', $date_of_birth);
            $patient_stmt->bindParam(':email', $email);
            $patient_stmt->bindParam(':phone', $phone_number);
            $patient_stmt->bindParam(':password', $password);
            $patient_stmt->execute();

            $conn->commit();
            $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['errors'][] = "Erreur lors de l'inscription: " . $e->getMessage();
    }
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Update last login
            $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();

            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    header("Location: admin part/index.php");
                    break;
                case 'doctor':
                    header("Location: Doctor part/index.php");
                    break;
                case 'patient':
                    header("Location: Patient Part/index.php");
                    break;
                case 'secretary':
                    header("Location: Secretry part/index.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['errors'][] = "Email ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $_SESSION['errors'][] = "Erreur lors de la connexion: " . $e->getMessage();
    }
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="asset/style.css">
    <title>BrainSense - Page de Connexion</title>
</head>

<body>

    <div class="container" id="container">



        <!-- Sign-Up Form -->
        <div class="form-container sign-up">
            <form method="POST" action="login.php">
                <h1>Créer un compte</h1>
                <span>ou utilisez votre email pour vous inscrire</span>
                <input type="text" name="full_name" placeholder="Nom complet" required>
                <input type="email" name="email" placeholder="Adresse email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <input type="date" name="date_of_birth" placeholder="Date de naissance" required>
                <input type="tel" name="phone_number" placeholder="Numéro de téléphone" required>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>

        <!-- Sign-In Form -->
        <div class="form-container sign-in">
            <form method="POST" action="login.php">
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
                        <img src="asset/Logo(BrainSense ).png" alt="BrainSense Logo" class="logo">
                    </div>
                    <h1>Bon retour !</h1>
                    <p>Entrez vos informations personnelles pour utiliser toutes les fonctionnalités de BrainSense.</p>
                    <button class="hidden" id="login">Connexion</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <div class="logo-container">
                        <img src="Asset/Logo(BrainSense ).png" alt="BrainSense Logo" class="logo">
                    </div>
                    <h1>Bienvenue !</h1>
                    <p>Inscrivez-vous pour rejoindre notre communauté et bénéficier de tous nos services.</p>
                    <button class="hidden" id="register">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>
  
    <script src="asset/script.js"></script>
</body>

</html>
