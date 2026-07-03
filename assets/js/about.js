
document.addEventListener("DOMContentLoaded", function () {
    const faqItems = document.querySelectorAll(".about-faq-item");

    faqItems.forEach(item => {
        const question = item.querySelector(".about-faq-question");

        question.addEventListener("click", () => {
            const isActive = item.classList.contains("active");

            faqItems.forEach(otherItem => otherItem.classList.remove("active"));

            if (!isActive) {
                item.classList.add("active");
            }
        });
    });
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
