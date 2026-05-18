console.log("validateRegister.js loaded!");

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");

    if (!form) {
        console.error("registerForm not found");
        return;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        validateRegister();
    });
});

function clearErrors() {
    document.getElementById("nameError").innerHTML = "";
    document.getElementById("emailError").innerHTML = "";
    document.getElementById("phoneError").innerHTML = "";
    document.getElementById("nationalityError").innerHTML = "";
    document.getElementById("passwordError").innerHTML = "";
    document.getElementById("confirmError").innerHTML = "";
}

function validateRegister() {
    console.log("Create Account clicked");

    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let nationality = document.getElementById("nationality").value;
    let password = document.getElementById("password").value;
    let confirm = document.getElementById("confirm_password").value;

    let xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function () {
        if (this.readyState === 4) {
            console.log("Status:", this.status);
            console.log("Response:", this.responseText);

            if (this.status !== 200) {
                alert("Server error. Status: " + this.status);
                return;
            }

            let data;
            try {
                data = JSON.parse(this.responseText.trim());
            } catch (e) {
                console.error("Invalid JSON:", this.responseText);
                alert("PHP error ache. Response check koro Console/Network tab e.");
                return;
            }

            clearErrors();

            if (data.success) {
                alert("Account created successfully");
                window.location.href = "login.php";
            } else {
                if (data.errors.name) document.getElementById("nameError").innerHTML = data.errors.name;
                if (data.errors.email) document.getElementById("emailError").innerHTML = data.errors.email;
                if (data.errors.phone) document.getElementById("phoneError").innerHTML = data.errors.phone;
                if (data.errors.nationality) document.getElementById("nationalityError").innerHTML = data.errors.nationality;
                if (data.errors.password) document.getElementById("passwordError").innerHTML = data.errors.password;
                if (data.errors.confirm) document.getElementById("confirmError").innerHTML = data.errors.confirm;
                if (data.errors.general) alert(data.errors.general);
            }
        }
    };

    xhttp.open("POST", "../../controllers/registerController.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhttp.send(
        "name=" + encodeURIComponent(name) +
        "&email=" + encodeURIComponent(email) +
        "&phone=" + encodeURIComponent(phone) +
        "&nationality=" + encodeURIComponent(nationality) +
        "&password=" + encodeURIComponent(password) +
        "&confirm_password=" + encodeURIComponent(confirm)
    );
}
