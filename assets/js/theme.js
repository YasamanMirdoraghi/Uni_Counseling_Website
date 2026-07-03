const btn = document.getElementById("themeToggle");
const icon = btn.querySelector("i");

/* load saved theme */
const savedTheme = localStorage.getItem("theme");

if (savedTheme === "light") {
    document.body.classList.add("light-theme");
    icon.className = "fas fa-sun"; 
} else {
    icon.className = "fas fa-moon"; 
}

/* toggle theme */
btn.onclick = () => {
    document.body.classList.toggle("light-theme");

    if (document.body.classList.contains("light-theme")) {
        localStorage.setItem("theme", "light");
        icon.className = "fas fa-sun"; //روز
    } else {
        localStorage.setItem("theme", "dark");
        icon.className = "fas fa-moon"; // شب
    }
};

// Offline
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/service-worker.js")
            .then(reg => console.log("Service Worker registered:", reg))
            .catch(err => console.error("Service Worker registration failed:", err));
    });
}