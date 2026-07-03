document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("signupForm");

  if (!form) {
    console.log("فرم ثبت‌نام پیدا نشد!");
    return;
  }

  const fullName = document.getElementById("fullName");
  const email = document.getElementById("email");
  const username = document.getElementById("username");
  const studentId = document.getElementById("studentId");
  const mobile = document.getElementById("mobile");
  const birthYear = document.getElementById("birthYear");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirmPassword");
  const successMessage = document.getElementById("successMessage");

  function showError(input, message) {
    if (!input) return;
    const errorElement = input.nextElementSibling;
    if (errorElement) {
      errorElement.textContent = message;
      errorElement.style.color = "red";
      input.classList.add("input-error");
    }
  }

  function clearError(input) {
    if (!input) return;
    const errorElement = input.nextElementSibling;
    if (errorElement) {
      errorElement.textContent = "";
      input.classList.remove("input-error");
    }
  }

  function isPersianOrLatinLettersAndSpace(value) {
    return /^[\p{L}\s]+$/u.test(value.trim());
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
  }

  function isValidMobile(value) {
    return /^09\d{9}$/.test(value.trim());
  }

  function isValidStudentId(value) {
    return /^\d{8,10}$/.test(value.trim());
  }

  function isValidBirthYear(value) {
    const year = Number(value);
    return year >= 1300 && year <= new Date().getFullYear();
  }

  function validateForm() {
    let isValid = true;
    if (successMessage) successMessage.textContent = "";

    // clear previous errors
    [fullName, email, username, studentId, mobile, birthYear, password, confirmPassword].forEach(clearError);

    // full name
    if (!fullName || !fullName.value.trim()) {
      showError(fullName, "وارد کردن نام و نام خانوادگی الزامی است.");
      isValid = false;
    } else if (fullName && !isPersianOrLatinLettersAndSpace(fullName.value)) {
      showError(fullName, "نام و نام خانوادگی فقط باید شامل حروف و فاصله باشد.");
      isValid = false;
    }

    // email
    if (!email || !email.value.trim()) {
      showError(email, "وارد کردن ایمیل الزامی است.");
      isValid = false;
    } else if (email && !isValidEmail(email.value)) {
      showError(email, "فرمت ایمیل صحیح نیست.");
      isValid = false;
    }

    // username
    if (!username || !username.value.trim()) {
      showError(username, "وارد کردن نام کاربری الزامی است.");
      isValid = false;
    }

    // student id
    if (!studentId || !studentId.value.trim()) {
      showError(studentId, "وارد کردن شماره دانشجویی الزامی است.");
      isValid = false;
    } else if (studentId && !isValidStudentId(studentId.value)) {
      showError(studentId, "شماره دانشجویی وارد شده معتبر نیست.");
      isValid = false;
    }

    // mobile
    if (!mobile || !mobile.value.trim()) {
      showError(mobile, "وارد کردن شماره موبایل الزامی است.");
      isValid = false;
    } else if (mobile && !isValidMobile(mobile.value)) {
      showError(mobile, "شماره موبایل باید 11 رقم و با 09 شروع شود.");
      isValid = false;
    }

    // birth year
    if (!birthYear || !birthYear.value.trim()) {
      showError(birthYear, "وارد کردن سال تولد الزامی است.");
      isValid = false;
    } else if (birthYear && !isValidBirthYear(birthYear.value)) {
      showError(birthYear, "سال تولد وارد شده معتبر نیست.");
      isValid = false;
    }

    // password
    if (!password || !password.value) {
      showError(password, "وارد کردن رمز عبور الزامی است.");
      isValid = false;
    } else if (password && password.value.length < 6) {
      showError(password, "رمز عبور باید حداقل 6 کاراکتر داشته باشد.");
      isValid = false;
    }

    // confirm password
    if (!confirmPassword || !confirmPassword.value) {
      showError(confirmPassword, "تکرار رمز عبور الزامی است.");
      isValid = false;
    } else if (confirmPassword && confirmPassword.value !== password.value) {
      showError(confirmPassword, "تکرار رمز عبور باید با رمز عبور یکسان باشد.");
      isValid = false;
    }

    if (isValid && successMessage) {
      successMessage.textContent = " ثبت نام شما با موفقیت انجام شد";
      successMessage.style.color = "green";
      successMessage.style.background = "rgba(76, 175, 80, 0.2)";
      successMessage.style.padding = "10px";
      successMessage.style.borderRadius = "8px";
    }

    return isValid;
  }

  //  اعتبارسنجی سمت کلاینت
  form.addEventListener("submit", function (e) {
    const valid = validateForm();
    if (!valid) {
      e.preventDefault(); 
    }
  });

  // رویداد input برای پاک کردن خطاها
  [fullName, email, username, studentId, mobile, birthYear, password, confirmPassword].forEach(input => {
    if (input) {
      input.addEventListener("input", function () {
        clearError(input);
        if (successMessage) successMessage.textContent = "";
      });
    }
  });

});

// Offline - Service Worker 
// if ("serviceWorker" in navigator) {
//   window.addEventListener("load", () => {
//     navigator.serviceWorker
//       .register("/service-worker.js")
//       .then(reg => console.log("Service Worker registered:", reg))
//       .catch(err => console.error("Service Worker registration failed:", err));
//   });
// }