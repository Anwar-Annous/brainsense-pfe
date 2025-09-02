const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

// Add event listeners for switching between the forms
document.getElementById('register').addEventListener('click', function () {
    // Clear error messages displayed on the page when switching to register form
    clearErrors();
    // Show the signup form and hide the login form
    document.querySelector('.sign-up').style.display = 'block';
    document.querySelector('.sign-in').style.display = 'none';
});

document.getElementById('login').addEventListener('click', function () {
    // Clear error messages displayed on the page when switching to login form
    clearErrors();
    // Show the login form and hide the signup form
    document.querySelector('.sign-in').style.display = 'block';
    document.querySelector('.sign-up').style.display = 'none';
});

// Function to clear error messages displayed on the page
function clearErrors() {
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(function (message) {
        message.remove(); // Remove each error message
    });
}

// Function to display error messages
function displayError(message) {
    // Create a div for the error message
    const errorDiv = document.createElement('div');
    errorDiv.classList.add('error-message');
    errorDiv.textContent = message;

    // Append the error message to the body
    document.body.appendChild(errorDiv);

    // Show the error message by setting display to block
    errorDiv.style.display = 'block';

    // Remove the error message after 5 seconds (optional)
    setTimeout(function() {
        errorDiv.style.display = 'none'; // Hide after 5 seconds
        document.body.removeChild(errorDiv); // Remove from DOM
    }, 5000);
}
