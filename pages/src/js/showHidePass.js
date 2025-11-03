function showHidePassword(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon = document.getElementById(iconId);

    if (input.type === "password") {
        input.type = "text";
        icon.src = "pages/src/media/eye-off.svg";
    } else {
        input.type = "password";
        icon.src = "pages/src/media/eye.svg";
    }
}