/* =========================================================
   DOCTOR SLIDER
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  const track = document.querySelector(".doctor-slider-track");
  const prevButton = document.querySelector(".doctor-arrow-left");
  const nextButton = document.querySelector(".doctor-arrow-right");

  if (!track) return;

  const SLIDE_TIME = 700;
  const AUTO_SLIDE_TIME = 3000;

  let autoSlide = null;
  let isAnimating = false;
  let resizeTimer = null;

  function getVisibleCards() {
    if (window.innerWidth <= 600) return 1;
    if (window.innerWidth <= 900) return 2;

    return 3;
  }

  function getCardStep() {
    const card = track.querySelector(".doctor-card");

    if (!card) return 0;

    const cardWidth = card.offsetWidth;

    const trackStyle = window.getComputedStyle(track);

    const gap = parseFloat(trackStyle.gap) || 0;

    return cardWidth + gap;
  }

  function setActiveCard(index) {
    const cards = Array.from(track.querySelectorAll(".doctor-card"));

    cards.forEach((card) => {
      card.classList.remove("active");
    });

    if (cards[index]) {
      cards[index].classList.add("active");
    }
  }

  function updateActiveCard() {
    const visibleCards = getVisibleCards();

    if (visibleCards === 3) {
      setActiveCard(1);
    } else {
      setActiveCard(0);
    }
  }

  function setPosition(offset, animate = true) {
    track.style.transition = animate
      ? `transform ${SLIDE_TIME}ms ease`
      : "none";

    track.style.transform = `translate3d(${offset}px, 0, 0)`;
  }

  function resetPosition() {
    setPosition(0, false);
    updateActiveCard();
  }

  function nextSlide() {
    if (isAnimating) return;

    const step = getCardStep();

    if (!step) return;

    isAnimating = true;

    const firstCard = track.querySelector(".doctor-card");

    if (!firstCard) {
      isAnimating = false;
      return;
    }

    const firstClone = firstCard.cloneNode(true);

    firstClone.classList.remove("active");

    track.appendChild(firstClone);

    const visibleCards = getVisibleCards();

    if (visibleCards === 3) {
      setActiveCard(2);
    } else {
      setActiveCard(1);
    }

    setPosition(-step, true);

    setTimeout(() => {
      firstCard.remove();
      firstClone.remove();

      track.appendChild(firstCard);

      resetPosition();

      isAnimating = false;
    }, SLIDE_TIME);
  }

  function previousSlide() {
    if (isAnimating) return;

    const step = getCardStep();

    if (!step) return;

    isAnimating = true;

    const cards = track.querySelectorAll(".doctor-card");

    const lastCard = cards[cards.length - 1];

    if (!lastCard) {
      isAnimating = false;
      return;
    }

    const lastClone = lastCard.cloneNode(true);

    lastClone.classList.remove("active");

    track.insertBefore(lastClone, track.firstElementChild);

    setPosition(-step, false);

    track.offsetHeight;

    const visibleCards = getVisibleCards();

    if (visibleCards === 3) {
      setActiveCard(1);
    } else {
      setActiveCard(0);
    }

    setPosition(0, true);

    setTimeout(() => {
      lastClone.remove();

      track.insertBefore(lastCard, track.firstElementChild);

      resetPosition();

      isAnimating = false;
    }, SLIDE_TIME);
  }

  function startAutoSlide() {
    stopAutoSlide();

    autoSlide = setInterval(() => {
      nextSlide();
    }, AUTO_SLIDE_TIME);
  }

  function stopAutoSlide() {
    if (autoSlide !== null) {
      clearInterval(autoSlide);
      autoSlide = null;
    }
  }

  if (nextButton) {
    nextButton.addEventListener("click", () => {
      nextSlide();
      startAutoSlide();
    });
  }

  if (prevButton) {
    prevButton.addEventListener("click", () => {
      previousSlide();
      startAutoSlide();
    });
  }

  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);

    resizeTimer = setTimeout(() => {
      setPosition(0, false);
      updateActiveCard();
    }, 150);
  });

  setPosition(0, false);
  updateActiveCard();
  startAutoSlide();
});

/* =========================================================
   APPOINTMENT — LOAD DOCTORS
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  const department = document.getElementById("department");

  const doctor = document.getElementById("doctor");

  // This allows Functioning.js to work
  // on pages without the appointment form.

  if (!department || !doctor) {
    return;
  }

  department.addEventListener("change", async () => {
    const departmentId = department.value;

    doctor.innerHTML = '<option value="">Loading doctors...</option>';

    doctor.disabled = true;

    if (!departmentId) {
      doctor.innerHTML = '<option value="">Select a department first</option>';

      return;
    }

    try {
      // Detect if page is in html/ subfolder and adjust path accordingly
      const basePath = window.location.pathname.includes("/html/") ? "../" : "";

      const response = await fetch(
        basePath + "appointments/get-doctors.php?department_id=" + departmentId,
      );

      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }

      const data = await response.json();

      doctor.innerHTML = '<option value="">Select a doctor</option>';

      if (!data.success || !data.doctors || data.doctors.length === 0) {
        doctor.innerHTML = '<option value="">No doctors available</option>';

        return;
      }

      data.doctors.forEach((item) => {
        const option = document.createElement("option");

        option.value = item.doctor_id;

        option.textContent = item.full_name + " — " + item.specialization;

        doctor.appendChild(option);
      });

      doctor.disabled = false;
    } catch (error) {
      console.error(error);

      doctor.innerHTML = '<option value="">Unable to load doctors</option>';

      doctor.disabled = true;
    }
  });
});

/* =========================================================
   APPOINTMENT FORM VALIDATION
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("appointmentForm");

  if (!form) return;

  const nameInput = document.getElementById("patientName");
  const phoneInput = document.getElementById("patientPhone");
  const dobInput = document.getElementById("dateOfBirth");
  const appointmentDateInput = document.getElementById("appointmentDate");

  /* =====================================================
       DATE LIMITS
    ===================================================== */

  const today = new Date();

  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");

  const todayString = `${year}-${month}-${day}`;

  /*
   * DOB:
   * Maximum = today
   * Minimum = 120 years ago
   */

  const minimumDOB = new Date(
    today.getFullYear() - 120,
    today.getMonth(),
    today.getDate(),
  );

  const minDOBYear = minimumDOB.getFullYear();

  const minDOBMonth = String(minimumDOB.getMonth() + 1).padStart(2, "0");

  const minDOBDay = String(minimumDOB.getDate()).padStart(2, "0");

  const minDOBString = `${minDOBYear}-${minDOBMonth}-${minDOBDay}`;

  if (dobInput) {
    dobInput.min = minDOBString;
    dobInput.max = todayString;
  }

  /*
   * Appointment date:
   * Cannot be in the past.
   */

  if (appointmentDateInput) {
    appointmentDateInput.min = todayString;
  }

  /* =====================================================
       NAME VALIDATION
    ===================================================== */

  if (nameInput) {
    nameInput.addEventListener("input", function () {
      /*
       * Remove numbers and unwanted symbols
       * while typing.
       */

      this.value = this.value.replace(/[^A-Za-zÀ-ÿ .'-]/g, "");
    });
  }

  /* =====================================================
       PHONE VALIDATION
    ===================================================== */

  if (phoneInput) {
    phoneInput.addEventListener("input", function () {
      /*
       * Only allow:
       * numbers
       * +
       * spaces
       * -
       * (
       * )
       */

      this.value = this.value.replace(/[^0-9+\- ()]/g, "");
    });
  }

  /* =====================================================
       FORM SUBMISSION VALIDATION
    ===================================================== */

  form.addEventListener("submit", function (event) {
    const name = nameInput.value.trim();

    const phone = phoneInput.value.trim();

    const dob = dobInput.value;

    const appointmentDate = appointmentDateInput.value;

    /* =================================================
           NAME
        ================================================= */

    const nameRegex = /^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ .'-]{1,99}$/;

    if (!nameRegex.test(name)) {
      event.preventDefault();

      alert(
        "Please enter a valid name. Numbers and special characters are not allowed.",
      );

      nameInput.focus();

      return;
    }

    /* =================================================
           PHONE
        ================================================= */

    /*
     * Accept:
     *
     * 03001234567
     * +923001234567
     */

    const cleanedPhone = phone.replace(/[\s\-()]/g, "");

    const phoneRegex = /^(03[0-9]{9}|\+923[0-9]{9})$/;

    if (!phoneRegex.test(cleanedPhone)) {
      event.preventDefault();

      alert("Please enter a valid Pakistani mobile number.");

      phoneInput.focus();

      return;
    }

    /* =================================================
           DATE OF BIRTH
        ================================================= */

    if (dob) {
      const dobDate = new Date(dob + "T00:00:00");

      const minDate = new Date(
        today.getFullYear() - 120,
        today.getMonth(),
        today.getDate(),
      );

      if (dobDate > today || dobDate < minDate) {
        event.preventDefault();

        alert(
          "Please enter a valid date of birth. Age must be between 0 and 120 years.",
        );

        dobInput.focus();

        return;
      }
    }

    /* =================================================
           APPOINTMENT DATE
        ================================================= */

    if (appointmentDate) {
      const selectedDate = new Date(appointmentDate + "T00:00:00");

      if (
        selectedDate <
        new Date(today.getFullYear(), today.getMonth(), today.getDate())
      ) {
        event.preventDefault();

        alert("Appointment date cannot be in the past.");

        appointmentDateInput.focus();

        return;
      }
    }
  });
});

/* =========================================================
   ABOUT PAGE - SCROLL COUNTERS
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  const counters = document.querySelectorAll(".counter");
  const letterCounters = document.querySelectorAll(".letter-counter");

  const observer = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const counter = entry.target;

        const target = Number(counter.dataset.target);
        const suffix = counter.dataset.suffix || "";

        let start = 0;

        const duration = 1800;
        const startTime = performance.now();

        function animate(currentTime) {
          const elapsed = currentTime - startTime;

          const progress = Math.min(elapsed / duration, 1);

          /*
           * Ease-out effect.
           * Starts fast and slows down naturally.
           */
          const easedProgress = 1 - Math.pow(1 - progress, 3);

          const currentValue = Math.floor(target * easedProgress);

          counter.textContent = currentValue + suffix;

          if (progress < 1) {
            requestAnimationFrame(animate);
          } else {
            counter.textContent = target + suffix;
          }
        }

        requestAnimationFrame(animate);

        observer.unobserve(counter);
      });
    },
    {
      threshold: 0.5,
    },
  );

  counters.forEach((counter) => {
    observer.observe(counter);
  });

  /* =====================================================
   UAE LETTER ANIMATION
===================================================== */

  const letterObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const counter = entry.target;
        const target = counter.dataset.text;

        const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        let iteration = 0;

        const interval = setInterval(() => {
          counter.textContent = target
            .split("")
            .map((letter, index) => {
              if (index < Math.floor(iteration)) {
                return target[index];
              }

              return letters[Math.floor(Math.random() * letters.length)];
            })
            .join("");

          iteration += 0.35;

          if (iteration >= target.length) {
            counter.textContent = target;

            clearInterval(interval);
          }
        }, 80);

        observer.unobserve(counter);
      });
    },
    {
      threshold: 0.5,
    },
  );

  letterCounters.forEach((counter) => {
    letterObserver.observe(counter);
  });
});

/* =========================================================
   SCROLL REVEAL ANIMATIONS
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  const revealElements = document.querySelectorAll(
    ".about-intro, " +
      ".about-introduction, " +
      ".history-heading, " +
      ".history-item, " +
      ".khalifa-container, " +
      ".legacy-heading, " +
      ".legacy-content, " +
      ".legacy-quote, " +
      ".about-cta-container, " +
      ".appointment-header, " +
      ".appointment-card, " +
      ".hero-next-section, " +
      ".specialists-section, " +
      ".why-choose-section, " +
      ".emergency-container",
  );

  revealElements.forEach((element) => {
    element.classList.add("scroll-reveal");
  });

  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.classList.add("scroll-visible");

        observer.unobserve(entry.target);
      });
    },
    {
      threshold: 0.15,
    },
  );

  revealElements.forEach((element) => {
    revealObserver.observe(element);
  });
});

/* =========================================================
   GLOBAL SCROLL REVEAL
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  const revealElements = document.querySelectorAll(".reveal");

  if (!revealElements.length) return;

  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.classList.add("visible");

        observer.unobserve(entry.target);
      });
    },
    {
      threshold: 0.15,
    },
  );

  revealElements.forEach((element) => {
    revealObserver.observe(element);
  });
});

/* =========================================================
   CONTACT FORM — MESSAGE CHARACTER COUNTER
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  const messageInput = document.getElementById("contactMessage");

  const messageCounter = document.getElementById("messageCounter");

  if (!messageInput || !messageCounter) return;

  const MAX_MESSAGE_LENGTH = 2000;

  function updateMessageCounter() {
    const currentLength = messageInput.value.length;

    const remaining = MAX_MESSAGE_LENGTH - currentLength;

    messageCounter.textContent = `${currentLength} / ${MAX_MESSAGE_LENGTH}`;

    messageCounter.classList.remove(
      "counter-green",
      "counter-yellow",
      "counter-orange",
      "counter-red",
    );

    /*
     * Green:
     * More than 300 characters remaining
     */

    if (remaining > 300) {
      messageCounter.classList.add("counter-green");
    } else if (remaining > 100) {
      /*
       * Yellow:
       * 101–300 characters remaining
       */
      messageCounter.classList.add("counter-yellow");
    } else if (remaining > 20) {
      /*
       * Orange:
       * 21–100 characters remaining
       */
      messageCounter.classList.add("counter-orange");
    } else {
      /*
       * Red:
       * 20 characters or fewer remaining
       */
      messageCounter.classList.add("counter-red");
    }
  }

  messageInput.addEventListener("input", updateMessageCounter);

  /*
   * Set the initial counter.
   */

  updateMessageCounter();
});



