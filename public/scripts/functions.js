const togglePassword = () => {
  const password = document.querySelector("#user_password");
  const eyeIcon = document.querySelector("#eye");
  const eyeIconSlash = document.querySelector("#eye-slash");

  password.type === "text"
    ? (password.type = "password")
    : (password.type = "text");

  eyeIcon.style.display === "none"
    ? (eyeIcon.style.display = "block")
    : (eyeIcon.style.display = "none");

  eyeIconSlash.classList.contains("d-none")
    ? eyeIconSlash.classList.remove("d-none")
    : eyeIconSlash.classList.add("d-none");
};
