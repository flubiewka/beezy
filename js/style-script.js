function updateGradient() 
{
    const login_background = document.getElementById("login-background");
    const hour = new Date().getHours();

    let gradient = "";

    if (hour >= 6 && hour < 12) {
        gradient = "linear-gradient(135deg,rgba(255, 255, 163, 1) 0%, rgba(255, 164, 54, 1) 39%, rgba(255, 117, 248, 1) 100%)";
    } else if (hour >= 12 && hour < 18) {
        gradient = "linear-gradient(135deg,rgba(186, 255, 163, 1) 0%, rgba(156, 255, 217, 1) 39%, rgba(117, 198, 255, 1) 100%)";
    } else if (hour >= 18 && hour < 22) {
        gradient = "linear-gradient(90deg, rgba(183, 0, 255, 1) 0%, rgba(83, 178, 237, 1) 100%)";
    } else {
        gradient = "linear-gradient(315deg,rgba(2, 0, 36, 1) 0%, rgba(0, 0, 133, 1) 60%, rgba(0, 118, 145, 1)";
    }

    login_background.style.background = gradient;
}


updateGradient();

	
setInterval(updateGradient, 100); /*60000 - 1 minuta*/
