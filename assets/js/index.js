
let slideIndex = 0;
let slideInterval;

function showSlide(index) {
    const slides = document.querySelectorAll(".slide");
    if (slides.length === 0) return;

    if (index >= slides.length) {
        slideIndex = 0;
    } else if (index < 0) {
        slideIndex = slides.length - 1;
    } else {
        slideIndex = index;
    }
    slides.forEach(slide => slide.classList.remove("active"));
    slides[slideIndex].classList.add("active");
}

function changeSlide(step) {
    showSlide(slideIndex + step);
    resetAutoSlide();
}

function autoSlide() {
    slideInterval = setInterval(() => {
        showSlide(slideIndex + 1);
    }, 5000);
}

function resetAutoSlide() {
    clearInterval(slideInterval);
    autoSlide();
}

document.addEventListener("DOMContentLoaded", () => {
    showSlide(0);
    autoSlide();
});

// Offline
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/service-worker.js")
      .then(reg => console.log("Service Worker registered:", reg))
      .catch(err => console.error("Service Worker registration failed:", err));
  });
}
