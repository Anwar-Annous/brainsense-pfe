<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
  /* Styling for profile container */
.profile-container {
    max-width: 900px;
    margin: 20px auto;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    font-family: 'Arial', sans-serif;
}

/* Profile Picture Section */
.profile-pic {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
}

.profile-img {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #4CAF50;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.change-pic-btn {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.change-pic-btn:hover {
    background-color: #45a049;
    transform: scale(1.05);
}

/* Personal Information Section */
.personal-info h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
}

.info {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
}

.info label {
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
}

.profile-container input {
    background-color: #f4f4f4;
    border: 1px solid #ddd;
    margin: 8px 0;
    padding: 12px 15px;
    font-size: 14px;
    border-radius: 8px;
    width: 100%;
    outline: none;
    transition: all 0.3s ease;
}

.profile-container input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
}

/* Edit Profile Button */
.edit-profile {
    margin-top: 40px;
    display: flex;
    justify-content: flex-end;
}

#editProfileBtn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
}

#editProfileBtn:hover {
    background-color: #45a049;
    transform: scale(1.05);
}

/* Update Profile Section */
.update-profile.hidden {
    display: none;
}

.update-profile {
    margin-top: 30px;
}

.update-profile h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

.update-profile .info {
    margin-bottom: 15px;
}

.update-profile input {
    background-color: #f4f4f4;
    border: 1px solid #ddd;
    margin: 8px 0;
    padding: 12px 15px;
    font-size: 14px;
    border-radius: 8px;
    width: 100%;
    outline: none;
    transition: all 0.3s ease;
}

.update-profile input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
}

.update-profile button {
    padding: 12px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    margin-right: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.update-profile button:hover {
    background-color: #45a049;
    transform: scale(1.05);
}

.cancel-btn {
    padding: 12px 20px;
    background-color: #e74c3c;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cancel-btn:hover {
    background-color: #c0392b;
    transform: scale(1.05);
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .profile-container {
        padding: 20px;
        max-width: 100%;
    }

    .profile-pic {
        margin-bottom: 20px;
    }

    .profile-img {
        width: 120px;
        height: 120px;
    }

    .change-pic-btn {
        margin-top: 10px;
        font-size: 13px;
    }

    .info {
        margin-bottom: 15px;
    }

    .update-profile button {
        width: 100%;
    }

    .cancel-btn {
        width: 100%;
    }
}

/* To hide the update section initially */
.update-profile.hidden {
    display: none;
}
/* From Uiverse.io by Yaya12085 */ 
.form {
  background-color: #fff;
  box-shadow: 0 10px 60px rgb(218, 229, 255);
  border: 1px solid rgb(159, 159, 160);
  border-radius: 20px;
  padding: 2rem .7rem .7rem .7rem;
  text-align: center;
  font-size: 1.125rem;
  max-width: 320px;
  margin-left: 250px;
}

.form-title {
  color: #000000;
  font-size: 1.8rem;
  font-weight: 500;
}

.form-paragraph {
  margin-top: 10px;
  font-size: 0.9375rem;
  color: rgb(105, 105, 105);
}

.drop-container {
  background-color: #fff;
  position: relative;
  display: flex;
  gap: 10px;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 10px;
  margin-top: 2.1875rem;
  
  border-radius: 10px;
  border: 2px dashed rgb(171, 202, 255);
  color: #444;
  cursor: pointer;
  transition: background .2s ease-in-out, border .2s ease-in-out;
}

.drop-container:hover {
  background: rgba(0, 140, 255, 0.164);
  border-color: rgba(17, 17, 17, 0.616);
}

.drop-container:hover .drop-title {
  color: #222;
}

.drop-title {
  color: #444;
  font-size: 20px;
  font-weight: bold;
  text-align: center;
  transition: color .2s ease-in-out;
}

#file-input {
  width: 350px;
  max-width: 100%;
  color: #444;
  padding: 2px;
  background: #fff;
  border-radius: 10px;
  border: 1px solid rgba(8, 8, 8, 0.288);
}

#file-input::file-selector-button {
  margin-right: 20px;
  border: none;
  background: #084cdf;
  padding: 10px 20px;
  border-radius: 10px;
  color: #fff;
  cursor: pointer;
  transition: background .2s ease-in-out;
}

#file-input::file-selector-button:hover {
  background: #0d45a5;
}

    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon">
                            <img src="Logo(BrainSense )(justbrain).png" class="logo" alt="">
                        </span>
                        <span class="titleLogo">Brain<strong>Sense</strong></span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Customers</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="chatbubble-outline"></ion-icon>
                        </span>
                        <span class="title">Messages</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="help-outline"></ion-icon>
                        </span>
                        <span class="title">Help</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="settings-outline"></ion-icon>
                        </span>
                        <span class="title">Settings</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                        </span>
                        <span class="title">Password</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon">
                        <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title"> Profile</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" placeholder="Search here">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>

                <div class="user">
                    <img src="assets/imgs/customer01.jpg" alt="">
                </div>
            </div>
            <div class="profile-container">
    <h1>Profile Information</h1>

    <!-- Profile Picture Section -->
    <div class="profile-pic">
        <img src="assets/imgs/customer01.jpg" alt="Profile Picture" class="profile-img">
        <form class="form">
  <span class="form-title">Téléchargez votre fichier</span>
  <p class="form-paragraph">
      Le fichier doit être une image
  </p>
  <label for="file-input" class="drop-container">
    <span class="drop-title">Déposez les fichiers ici</span>
    ou
    <input type="file" accept="image/*" required="" id="file-input">
  </label>
</form>

    </div>

    <!-- Personal Information Section -->
<div class="personal-info">
    <h2>Informations personnelles</h2>
    <div class="info">
        <label for="full_name">Nom complet</label>
        <input type="text" id="full_name" name="full_name" value="John Doe" disabled>
    </div>
    <div class="info">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="johndoe@example.com" disabled>
    </div>
    <div class="info">
        <label for="date_of_birth">Date de naissance</label>
        <input type="date" id="date_of_birth" name="date_of_birth" value="1990-01-01" disabled>
    </div>
    <div class="info">
        <label for="phone_number">Numéro de téléphone</label>
        <input type="tel" id="phone_number" name="phone_number" value="+123456789" disabled>
    </div>
    <div class="info">
        <label for="user_type">Type d'utilisateur</label>
        <input type="text" id="user_type" name="user_type" value="Admin" disabled>
    </div>
</div>

<!-- Edit Profile Section -->
<div class="edit-profile">
    <button id="editProfileBtn">Modifier</button>
</div>

<!-- Update Profile Section -->
<div class="update-profile hidden">
    <h2>Mettez à jour vos informations</h2>
    <div class="info">
        <label for="new_full_name">Nom complet</label>
        <input type="text" id="new_full_name" name="new_full_name" value="John Doe">
    </div>
    <div class="info">
        <label for="new_email">Email</label>
        <input type="email" id="new_email" name="new_email" value="johndoe@example.com">
    </div>
    <div class="info">
        <label for="new_date_of_birth">Date de naissance</label>
        <input type="date" id="new_date_of_birth" name="new_date_of_birth" value="1990-01-01">
    </div>
    <div class="info">
        <label for="new_phone_number">Numéro de téléphone</label>
        <input type="tel" id="new_phone_number" name="new_phone_number" value="+123456789">
    </div>
    <div class="info">
        <label for="new_user_type">Type d'utilisateur</label>
        <input type="text" id="new_user_type" name="new_user_type" value="Admin">
    </div>
    <button id="updateProfileBtn">Mettre à jour le profil</button>
    <button id="cancelUpdateBtn" class="cancel-btn">Annuler</button>
</div>

           
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>