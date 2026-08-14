/* ═══════════════════════════════════════════════════════════
   AcharyaSetu — Main JavaScript
   Dark/Light Theme | AJAX | Modals | OTP | Booking | Toast
   ═══════════════════════════════════════════════════════════ */

   "use strict";

   /* ── CSRF Token Helper ────────────────────────────────────── */
   function getCsrf() {
       return document.querySelector('meta[name="csrf-token"]')?.content || "";
   }
   
   /* ══════════════════════════════════════════════════════════
      GENERIC AJAX FUNCTION
      Usage:
        AjaxPost('/api/sessions', formData, {
          onSuccess: (data) => showToast('success','Booked!'),
          onError:   (err)  => showToast('error', err.message),
          loader:    true,
          btn:       submitBtn
        })
      ══════════════════════════════════════════════════════════ */
   window.AjaxPost = function (url, data, options = {}) {
       const { onSuccess, onError, loader = false, btn = null, method = "POST" } = options;
   
       if (loader) showLoader();
       if (btn) setButtonLoading(btn, true);
   
       const isFormData = data instanceof FormData;
       if (!isFormData && typeof data === "object") {
           data._token = getCsrf();
       }
   
       const fetchOptions = {
           method,
           headers: {
               "X-CSRF-TOKEN": getCsrf(),
               "X-Requested-With": "XMLHttpRequest",
               Accept: "application/json",
           },
           body: isFormData ? data : JSON.stringify(data),
       };
   
       if (!isFormData) {
           fetchOptions.headers["Content-Type"] = "application/json";
       } else {
           // Append CSRF to FormData
           if (!data.has("_token")) data.append("_token", getCsrf());
       }
   
       return fetch(url, fetchOptions)
           .then((res) => {
               if (!res.ok) {
                   return res.json().then(
                       (err) => {
                           throw {
                               message: err.message || Object.values(err.errors || {})[0]?.[0] || "An error occurred.",
                               errors: err.errors || {},
                               status: res.status,
                               topup_url: err.topup_url || null,
                               wallet_balance: err.wallet_balance,
                               required_amount: err.required_amount,
                           };
                       },
                       () => {
                           throw { message: "Server error. Please try again.", status: res.status };
                       }
                   );
               }
               const ct = res.headers.get("Content-Type") || "";
               return ct.includes("application/json") ? res.json() : res.text();
           })
           .then((data) => {
               if (onSuccess) onSuccess(data);
               return data;
           })
           .catch((err) => {
               console.error("[AjaxPost Error]", err);
               if (onError) onError(err);
               else showToast("error", err.message || "Something went wrong.");
               throw err;
           })
           .finally(() => {
               if (loader) hideLoader();
               if (btn) setButtonLoading(btn, false);
           });
   };
   
   /* GET helper */
   window.AjaxGet = function (url, options = {}) {
       const { onSuccess, onError, loader = false } = options;
       if (loader) showLoader();
   
       return fetch(url, {
           headers: {
               "X-CSRF-TOKEN": getCsrf(),
               "X-Requested-With": "XMLHttpRequest",
               Accept: "application/json",
           },
       })
           .then((res) => res.json())
           .then((data) => {
               if (onSuccess) onSuccess(data);
               return data;
           })
           .catch((err) => {
               if (onError) onError(err);
               else showToast("error", "Request failed.");
           })
           .finally(() => {
               if (loader) hideLoader();
           });
   };
   
   /* ── Button Loading State ─────────────────────────────────── */
   function setButtonLoading(btn, loading) {
       if (!btn) return;
       if (loading) {
           btn.disabled = true;
           btn.dataset.origText = btn.innerHTML;
           btn.innerHTML = `<span class="spinner spinner-sm spinner-inline"></span> <span>${btn.dataset.loadingText || "Please wait..."}</span>`;
       } else {
           btn.disabled = false;
           if (btn.dataset.origText) btn.innerHTML = btn.dataset.origText;
       }
   }
   
   /* ── Toast Notifications ─────────────────────────────────── */
   const TOAST_ICONS = { success: "✅", error: "❌", warning: "⚠️", info: "ℹ️" };
   
   function getToastContainer() {
       let c = document.querySelector(".toast-container");
       if (!c) {
           c = document.createElement("div");
           c.className = "toast-container";
           document.body.appendChild(c);
       }
       return c;
   }
   
   window.showToast = function (type = "info", message = "", title = "", duration = 4000) {
       const container = getToastContainer();
       if (!title) title = { success: "Success", error: "Error", warning: "Warning", info: "Info" }[type] || type;
   
       const toast = document.createElement("div");
       toast.className = `toast ${type}`;
       toast.innerHTML = `
       <div class="toast-icon">${TOAST_ICONS[type] || "💬"}</div>
       <div class="toast-content">
         <div class="toast-title">${title}</div>
         <div class="toast-msg">${message}</div>
       </div>
       <div class="toast-close" onclick="this.parentElement.remove()">✕</div>`;
       container.appendChild(toast);
       setTimeout(() => {
           toast.classList.add("removing");
           setTimeout(() => toast.remove(), 300);
       }, duration);
       return toast;
   };
   
   /* ── Loader (Full-screen) ─────────────────────────────────── */
   function getLoader() {
       let l = document.querySelector(".loader-overlay");
       if (!l) {
           l = document.createElement("div");
           l.className = "loader-overlay";
           l.innerHTML = '<div class="spinner"></div>';
           document.body.appendChild(l);
       }
       return l;
   }
   window.showLoader = () => getLoader().classList.add("show");
   window.hideLoader = () => getLoader().classList.remove("show");
   
   /* ── Dark / Light Theme ──────────────────────────────────── */
   (function initTheme() {
       const stored = localStorage.getItem("as_theme") || "dark";
       document.documentElement.setAttribute("data-theme", stored);
       updateThemeIcons(stored);
   })();
   
   function updateThemeIcons(theme) {
       document.querySelectorAll(".theme-btn").forEach((btn) => {
           btn.innerHTML = theme === "dark" ? "☀️" : "🌙";
           btn.title = theme === "dark" ? "Switch to Light" : "Switch to Dark";
       });
   }
   
   window.toggleTheme = function () {
       const current = document.documentElement.getAttribute("data-theme") || "dark";
       const next = current === "dark" ? "light" : "dark";
       document.documentElement.setAttribute("data-theme", next);
       localStorage.setItem("as_theme", next);
       updateThemeIcons(next);
   };
   
   /* ── Modal System ────────────────────────────────────────── */
   const modals = {};
   
   window.openModal = function (id) {
       const overlay = document.getElementById(id);
       if (!overlay) return;
       overlay.classList.add("open");
       document.body.style.overflow = "hidden";
   };
   
   window.closeModal = function (id) {
       const overlay = document.getElementById(id);
       if (!overlay) return;
       overlay.classList.remove("open");
       document.body.style.overflow = "";
   };
   
   window.closeAllModals = function () {
       document.querySelectorAll(".modal-overlay.open").forEach((m) => m.classList.remove("open"));
       document.body.style.overflow = "";
   };
   
   // Close on backdrop click
   document.addEventListener("click", (e) => {
       if (e.target.classList.contains("modal-overlay")) closeAllModals();
   });
   // Close on .modal-close click
   document.addEventListener("click", (e) => {
       if (e.target.classList.contains("modal-close") || e.target.closest(".modal-close-btn")) {
           const overlay = e.target.closest(".modal-overlay");
           if (overlay) {
               overlay.classList.remove("open");
               document.body.style.overflow = "";
           }
       }
   });
   document.addEventListener("keydown", (e) => {
       if (e.key === "Escape") closeAllModals();
   });
   
   /* ── Hamburger + Mobile Menu ─────────────────────────────── */
   function closeDashSidebar() {
       const dashSidebar = document.getElementById("dashSidebar");
       const backdrop = document.getElementById("sidebarBackdrop");
       const ham = document.querySelector(".hamburger");
       if (!dashSidebar) return;
       dashSidebar.classList.remove("is-open");
       if (backdrop) backdrop.classList.remove("is-open");
       if (ham) ham.classList.remove("open");
       document.body.style.overflow = "";
   }

   function toggleDashSidebar() {
       const dashSidebar = document.getElementById("dashSidebar");
       const backdrop = document.getElementById("sidebarBackdrop");
       const ham = document.querySelector(".hamburger");
       const mob = document.querySelector(".mobile-menu");
       if (!dashSidebar) return false;
       const open = !dashSidebar.classList.contains("is-open");
       dashSidebar.classList.toggle("is-open", open);
       if (backdrop) backdrop.classList.toggle("is-open", open);
       if (ham) ham.classList.toggle("open", open);
       if (mob) mob.classList.remove("open");
       document.body.style.overflow = open ? "hidden" : "";
       return true;
   }

   document.addEventListener("DOMContentLoaded", () => {
       const ham = document.querySelector(".hamburger");
       const mob = document.querySelector(".mobile-menu");
       const backdrop = document.getElementById("sidebarBackdrop");

       if (ham && mob) {
           ham.addEventListener("click", () => {
               if (document.getElementById("dashSidebar") && window.matchMedia("(max-width: 1024px)").matches) {
                   toggleDashSidebar();
                   return;
               }
               const open = mob.classList.toggle("open");
               ham.classList.toggle("open", open);
               closeDashSidebar();
           });
       }

       if (backdrop) {
           backdrop.addEventListener("click", closeDashSidebar);
       }

       document.querySelectorAll("#dashSidebar .sidebar-item").forEach((link) => {
           link.addEventListener("click", () => {
               if (window.matchMedia("(max-width: 1024px)").matches) {
                   closeDashSidebar();
               }
           });
       });

       const filterToggle = document.getElementById("searchFilterToggle");
       const filterSidebar = document.querySelector(".filter-sidebar");
       if (filterToggle && filterSidebar) {
           filterToggle.addEventListener("click", () => {
               filterSidebar.classList.toggle("is-collapsed");
               const collapsed = filterSidebar.classList.contains("is-collapsed");
               filterToggle.textContent = collapsed ? "Show filters" : "Hide filters";
           });
           if (window.matchMedia("(max-width: 1024px)").matches) {
               filterSidebar.classList.add("is-collapsed");
               filterToggle.textContent = "Show filters";
           }
       }
   
       // User dropdown
       document.querySelectorAll(".user-trigger").forEach((trigger) => {
           trigger.addEventListener("click", (e) => {
               e.stopPropagation();
               const dd = trigger.nextElementSibling;
               if (dd) dd.classList.toggle("open");
           });
       });
       document.addEventListener("click", () => {
           document.querySelectorAll(".user-dropdown").forEach((dd) => dd.classList.remove("open"));
       });
   
       // Flash messages → show as toast
       const flash = document.querySelector("[data-flash]");
       if (flash) {
           const { type, message } = JSON.parse(flash.dataset.flash);
           showToast(type, message);
       }
   });
   
   /* ── Banner Slider ───────────────────────────────────────── */
   window.initBanner = function (wrapSel = ".banner-wrap") {
       const wrap = document.querySelector(wrapSel);
       if (!wrap) return;
   
       const slides = wrap.querySelectorAll(".banner-slide");
       const dots = wrap.querySelectorAll(".banner-dot");
       if (!slides.length) return;
   
       let current = 0;
       let timer;
   
       function go(n) {
           slides[current].classList.remove("active");
           dots[current]?.classList.remove("active");
           current = (n + slides.length) % slides.length;
           slides[current].classList.add("active");
           dots[current]?.classList.add("active");
       }
   
       function startAuto() {
           clearInterval(timer);
           timer = setInterval(() => go(current + 1), 5000);
       }
   
       go(0);
       startAuto();
   
       wrap.querySelector(".banner-next")?.addEventListener("click", () => {
           go(current + 1);
           startAuto();
       });
       wrap.querySelector(".banner-prev")?.addEventListener("click", () => {
           go(current - 1);
           startAuto();
       });
       dots.forEach((dot, i) =>
           dot.addEventListener("click", () => {
               go(i);
               startAuto();
           })
       );
   };
   
   /* ── OTP Inputs ──────────────────────────────────────────── */
   window.initOtpInputs = function (containerSel) {
       const container = document.querySelector(containerSel || ".otp-grid");
       if (!container) return;
   
       const inputs = [...container.querySelectorAll(".otp-input")];
   
       inputs.forEach((inp, i) => {
           inp.addEventListener("keydown", (e) => {
               if (e.key === "Backspace" && !inp.value) {
                   inputs[i - 1]?.focus();
               }
           });
           inp.addEventListener("input", (e) => {
               const val = inp.value.replace(/\D/g, "").slice(-1);
               inp.value = val;
               if (val) {
                   inp.classList.add("filled");
                   inputs[i + 1]?.focus();
               } else {
                   inp.classList.remove("filled");
               }
           });
           inp.addEventListener("paste", (e) => {
               e.preventDefault();
               const text = (e.clipboardData || window.clipboardData).getData("text").replace(/\D/g, "");
               [...text].forEach((ch, j) => {
                   if (inputs[i + j]) {
                       inputs[i + j].value = ch;
                       inputs[i + j].classList.add("filled");
                   }
               });
               (inputs[i + text.length] || inputs[inputs.length - 1]).focus();
           });
       });
   };
   
   /* ── OTP Collect ─────────────────────────────────────────── */
   window.collectOtp = function (containerSel) {
       return [...document.querySelectorAll(`${containerSel} .otp-input`)].map((i) => i.value).join("");
   };
   
   /* ── Resend Timer ────────────────────────────────────────── */
   window.startResendTimer = function (linkSel, timerSel, seconds = 30) {
       const link = document.querySelector(linkSel);
       const timerEl = document.querySelector(timerSel);
       if (!link) return;
   
       link.style.pointerEvents = "none";
       link.style.opacity = ".4";
       if (timerEl) {
           timerEl.textContent = seconds;
           timerEl.closest("[data-resend-wrap]")?.classList.remove("hidden");
       }
   
       const interval = setInterval(() => {
           seconds--;
           if (timerEl) timerEl.textContent = seconds;
           if (seconds <= 0) {
               clearInterval(interval);
               link.style.pointerEvents = "auto";
               link.style.opacity = "1";
               timerEl?.closest("[data-resend-wrap]")?.classList.add("hidden");
           }
       }, 1000);
   };
   
   /* ── Multi-step Form ─────────────────────────────────────── */
   window.FormStepper = {
       current: 1,
       total: 0,
   
       init(totalSteps) {
           this.total = totalSteps;
           this.show(1);
       },
   
       show(n) {
           this.current = n;
           document.querySelectorAll("[data-step]").forEach((el) => {
               el.classList.toggle("hidden", parseInt(el.dataset.step) !== n);
           });
           document.querySelectorAll(".step-item").forEach((el, i) => {
               const num = i + 1;
               el.classList.toggle("done", num < n);
               el.classList.toggle("active", num === n);
           });
       },
   
       next(validate) {
           if (validate && !validate()) return false;
           if (this.current < this.total) this.show(this.current + 1);
           return true;
       },
   
       back() {
           if (this.current > 1) this.show(this.current - 1);
       },
   };
   
   /* ── Role Card Selection ─────────────────────────────────── */
   window.selectRole = function (card, role) {
       document.querySelectorAll(".role-card").forEach((c) => c.classList.remove("selected"));
       card.classList.add("selected");
       const input = document.querySelector('[name="role"]');
       if (input) input.value = role;
   };
   
   /* ── Booking Widget ──────────────────────────────────────── */
   window.BookingWidget = (function () {
       let selectedDate = null,
           selectedTime = null,
           selectedDuration = 30,
           ratePerMin = 0,
           mentorId = null,
           dayAvailability = {};

       const DEFAULT_SLOTS = ["09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00", "18:00"];

       function resolveMentorId() {
           return (
               mentorId ||
               document.getElementById("booking-mentor-id")?.value ||
               document.querySelector("[name='booking_mentor_id']")?.value ||
               document.querySelector("[data-mentor-id]")?.dataset.mentorId ||
               null
           );
       }

       function localDateString(d) {
           const y = d.getFullYear();
           const m = String(d.getMonth() + 1).padStart(2, "0");
           const day = String(d.getDate()).padStart(2, "0");
           return `${y}-${m}-${day}`;
       }

       function renderSlots(slots) {
           const grid = document.getElementById("timeGrid");
           if (!grid) return;
           grid.innerHTML = "";
           if (!slots || !slots.length) {
               grid.innerHTML =
                   '<div class="text-sm text-muted" style="grid-column:1/-1;text-align:center;padding:12px;">Mentor is not available on this day</div>';
               return;
           }
           slots.forEach((slot) => {
               const div = document.createElement("button");
               div.type = "button";
               div.className = "time-slot";
               div.textContent = slot;
               div.addEventListener("click", function (e) {
                   e.preventDefault();
                   e.stopPropagation();
                   document.querySelectorAll("#timeGrid .time-slot").forEach((s) => s.classList.remove("selected"));
                   div.classList.add("selected");
                   selectedTime = slot;
                   updateSummary();
               });
               grid.appendChild(div);
           });
       }

       function updateSummary() {
           const el = (id) => document.getElementById(id);
           if (el("bk-date")) el("bk-date").textContent = selectedDate || "—";
           if (el("bk-time")) el("bk-time").textContent = selectedTime || "—";
           if (el("bk-duration")) el("bk-duration").textContent = selectedDuration + " min";
           const total = selectedDuration * ratePerMin;
           if (el("bk-total")) el("bk-total").textContent = "₹" + total.toLocaleString("en-IN");
           const confirmTotal = document.querySelector("[data-confirm-total]");
           if (confirmTotal) confirmTotal.textContent = "₹" + total.toLocaleString("en-IN");
           ["date", "time", "duration", "amount"].forEach((k) => {
               const inp = document.querySelector(`[name="booking_${k}"]`);
               if (inp) {
                   inp.value =
                       k === "date"
                           ? selectedDate
                           : k === "time"
                             ? selectedTime
                             : k === "duration"
                               ? selectedDuration
                               : total;
               }
           });
       }

       function renderWeeklySummary(summary) {
           const wrap = document.getElementById("availabilitySummary");
           if (!wrap) return;
           if (!Array.isArray(summary) || !summary.length) {
               wrap.innerHTML = "";
               return;
           }
           const chips = summary
               .map((d) => {
                   const hours =
                       d.enabled && d.from && d.to
                           ? `${d.from}–${d.to}`
                           : d.enabled
                             ? "Open"
                             : "Off";
                   return `<span class="avail-chip ${d.enabled ? "is-on" : "is-off"}" title="${d.label}: ${hours}">
                        <strong>${d.label}</strong>
                        <small>${hours}</small>
                    </span>`;
               })
               .join("");
           wrap.innerHTML = `
                <p class="label-caps" style="margin-bottom:8px;">Mentor weekly availability</p>
                <div class="avail-chip-row">${chips}</div>
            `;
       }

       function markDateButtons() {
           document.querySelectorAll("#dateGrid .cal-day").forEach((btn) => {
               const info = dayAvailability[btn.dataset.date];
               btn.classList.remove("disabled", "unavailable", "has-slots");
               btn.removeAttribute("disabled");
               btn.title = "";
               if (!info) return;
               if (!info.available) {
                   btn.classList.add("disabled", "unavailable");
                   btn.disabled = true;
                   btn.title = "Mentor not available";
               } else {
                   btn.classList.add("has-slots");
                   btn.title = info.label
                       ? `${info.slot_count} slots · ${info.label}`
                       : `${info.slot_count} slots available`;
               }
           });
       }

       function selectDate(btn) {
           if (!btn || btn.classList.contains("disabled") || btn.disabled) return;
           document.querySelectorAll("#dateGrid .cal-day").forEach((c) => c.classList.remove("selected"));
           btn.classList.add("selected");
           selectedDate = btn.dataset.date;
           selectedTime = null;
           updateSummary();

           const info = dayAvailability[selectedDate];
           if (info && !info.available) {
               renderSlots([]);
               return;
           }

           const grid = document.getElementById("timeGrid");
           if (grid) {
               grid.innerHTML =
                   '<div class="text-sm text-muted" style="grid-column:1/-1;text-align:center;padding:12px;">Loading times…</div>';
           }
           loadSlots(selectedDate);
       }

       function loadSlots(date) {
           const id = resolveMentorId();
           if (!id || !date) return;

           fetch(`/api/mentors/${id}/availability?date=${encodeURIComponent(date)}`, {
               headers: {
                   "X-Requested-With": "XMLHttpRequest",
                   Accept: "application/json",
               },
           })
               .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
               .then(({ ok, data }) => {
                   if (!ok) {
                       renderSlots([]);
                       return;
                   }
                   if (dayAvailability[date]) {
                       dayAvailability[date].available = !!data.available && (data.slots || []).length > 0;
                       dayAvailability[date].slot_count = (data.slots || []).length;
                       if (data.label) dayAvailability[date].label = data.label;
                       markDateButtons();
                   }
                   renderSlots(Array.isArray(data.slots) ? data.slots : []);
               })
               .catch(() => renderSlots([]));
       }

       function loadWeekOverview() {
           const id = resolveMentorId();
           if (!id) return Promise.resolve();

           return fetch(`/api/mentors/${id}/availability?week=1&days=14`, {
               headers: {
                   "X-Requested-With": "XMLHttpRequest",
                   Accept: "application/json",
               },
           })
               .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
               .then(({ ok, data }) => {
                   if (!ok || !data) return;
                   dayAvailability = {};
                   (data.days || []).forEach((d) => {
                       dayAvailability[d.date] = d;
                   });
                   renderWeeklySummary(data.weekly_summary || []);
                   markDateButtons();

                   // Auto-select first available day
                   const firstOpen = document.querySelector("#dateGrid .cal-day.has-slots:not(.disabled)");
                   if (firstOpen && !selectedDate) {
                       selectDate(firstOpen);
                   }
               })
               .catch(() => {
                   /* keep calendar clickable; slots load per date */
               });
       }

       return {
           init(rate, id = null) {
               ratePerMin = Number(rate) || 0;
               mentorId = id != null && id !== "" ? String(id) : null;
               if (!mentorId) {
                   mentorId = resolveMentorId();
               }
               selectedDate = null;
               selectedTime = null;
               selectedDuration = 30;
               dayAvailability = {};

               document.querySelectorAll(".duration-btn").forEach((b) => b.classList.remove("selected"));
               document.querySelector('.duration-btn[data-min="30"]')?.classList.add("selected");

               const timeGrid = document.getElementById("timeGrid");
               if (timeGrid) {
                   timeGrid.innerHTML =
                       '<div class="text-sm text-muted" style="grid-column:1/-1;">Pick an available date</div>';
               }

               const summary = document.getElementById("availabilitySummary");
               if (summary) {
                   summary.innerHTML =
                       '<p class="avail-hint">Loading mentor availability…</p>';
               }

               const grid = document.getElementById("dateGrid");
               if (grid) {
                   grid.innerHTML = "";
                   const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
                   for (let i = 0; i < 14; i++) {
                       const d = new Date();
                       d.setHours(12, 0, 0, 0);
                       d.setDate(d.getDate() + i + 1);
                       const btn = document.createElement("button");
                       btn.type = "button";
                       btn.className = "cal-day";
                       btn.innerHTML = `<span class="cal-day-label">${days[d.getDay()]}</span>${d.getDate()}`;
                       btn.dataset.date = localDateString(d);
                       btn.addEventListener("click", function (e) {
                           e.preventDefault();
                           e.stopPropagation();
                           selectDate(btn);
                       });
                       grid.appendChild(btn);
                   }
               }
               updateSummary();
               loadWeekOverview();
           },

           selectDate,
           loadSlots,
           setDuration(min) {
               selectedDuration = Number(min) || 30;
               document.querySelectorAll(".duration-btn").forEach((b) => b.classList.remove("selected"));
               document.querySelector(`.duration-btn[data-min="${selectedDuration}"]`)?.classList.add("selected");
               updateSummary();
           },

           getBookingData() {
               if (!selectedDate) {
                   showToast("error", "Please select an available date.");
                   return null;
               }
               if (dayAvailability[selectedDate] && !dayAvailability[selectedDate].available) {
                   showToast("error", "Mentor is not available on this date.");
                   return null;
               }
               if (!selectedTime) {
                   showToast("error", "Please select a time slot.");
                   return null;
               }
               return {
                   date: selectedDate,
                   time: selectedTime,
                   duration: selectedDuration,
                   amount: selectedDuration * ratePerMin,
               };
           },
       };
   })();
   
   /* ── Search & Filter ─────────────────────────────────────── */
   window.MentorSearch = (function () {
       let searchTimer;
   
       function filterKey(el) {
           const key = el.dataset.filter;
           if (key === "experience") return "exp";
           return key;
       }
   
       function buildQuery(page) {
           const params = new URLSearchParams();
           const q = document.querySelector("#mentor-search-input")?.value?.trim();
           if (q) params.set("q", q);
   
           document.querySelectorAll("[data-filter]").forEach((el) => {
               const key = filterKey(el);
               if (!key || key === "session_type" || key === "availability") return;
   
               if (el.tagName === "SELECT") {
                   if (el.value) params.set(key, el.value);
                   return;
               }
   
               if ((el.type === "checkbox" || el.type === "radio") && el.checked && el.value) {
                   params.append(key, el.value);
               }
           });
   
           const sort = document.querySelector("[data-sort-select]")?.value || "best";
           params.set("sort", sort);
   
           const pg = page || document.getElementById("pagination-wrap")?.dataset?.page;
           if (pg && String(pg) !== "1") params.set("page", pg);
           return params;
       }
   
       function hydrateFromUrl() {
           const params = new URLSearchParams(window.location.search);
           const input = document.getElementById("mentor-search-input");
           if (input && params.has("q")) input.value = params.get("q") || "";
   
           const sortSel = document.querySelector("[data-sort-select]");
           if (sortSel && params.get("sort")) sortSel.value = params.get("sort");
   
           const selected = {
               domain: params.getAll("domain"),
               rate_range: params.getAll("rate_range"),
               min_rating: params.getAll("min_rating"),
               exp: params.getAll("exp").concat(params.getAll("experience")),
               rate_max: params.getAll("rate_max"),
           };
   
           document.querySelectorAll("[data-filter]").forEach((el) => {
               const key = filterKey(el);
               const values = selected[key] || [];
               if (el.tagName === "SELECT") {
                   if (values[0]) el.value = values[0];
                   return;
               }
               if (el.type === "checkbox" || el.type === "radio") {
                   el.checked = values.includes(el.value);
               }
           });
       }
   
       function renderMentors(data) {
           const grid = document.getElementById("mentors-grid");
           if (!grid) return;
           const count = document.getElementById("mentor-count");
           if (count && data.total !== undefined) count.textContent = data.total;
   
           const rows = data.data || data.mentors || [];
           if (!rows.length) {
               grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
           <div class="empty-state-icon">🔍</div>
           <h3>No mentors found</h3>
           <p>Try adjusting your filters or search term.</p>
         </div>`;
               return;
           }
   
           grid.innerHTML = rows
               .map((m) => {
                   const tags = Array.isArray(m.expertise) ? m.expertise : [];
                   const name = (m.name || "").replace(/'/g, "\\'");
                   const rate = m.rate_per_minute ?? 0;
                   return `
         <div class="mentor-card">
           <div class="mentor-card-head">
             <div class="mentor-avatar-lg">
               ${m.avatar_url ? `<img src="${m.avatar_url}" alt="${m.name}">` : (m.name || "?").charAt(0).toUpperCase()}
             </div>
             <div class="mentor-card-info">
               <div class="mentor-card-name">${m.name || ""}</div>
               <div class="mentor-card-role">${m.designation || ""}${m.company ? " · " + m.company : ""}</div>
             </div>
           </div>
           <div class="mentor-card-bio">${(m.bio || "").substring(0, 90)}${(m.bio || "").length > 90 ? "…" : ""}</div>
           <div class="mentor-tags">
             ${tags.slice(0, 4).map((e) => `<span class="tag">${e}</span>`).join("")}
           </div>
           <div class="mentor-card-meta">
             <span class="mentor-rate">₹${rate}/min</span>
             <span class="mentor-rating">⭐ ${m.rating || "—"} (${m.total_sessions || 0} sessions)</span>
           </div>
           <div class="mentor-card-actions">
             <a href="/mentors/${m.slug || m.id}" class="btn btn-outline btn-sm">View Profile</a>
             <button class="btn btn-primary btn-sm" onclick="openBookingModal(${m.id},'${name}',${rate})">Book Session</button>
           </div>
         </div>`;
               })
               .join("");
   
           // Pagination
           renderPagination(data);
       }
   
       function renderPagination(data) {
           const pg = document.getElementById("pagination-wrap");
           if (!pg || !data.last_page) return;
           let html = "";
           const { current_page: cp, last_page: lp } = data;
           html += `<a class="page-btn ${cp === 1 ? "disabled" : ""}" data-pg="${cp - 1}">‹</a>`;
           for (let i = 1; i <= lp; i++) {
               if (i === 1 || i === lp || Math.abs(i - cp) <= 2)
                   html += `<a class="page-btn ${i === cp ? "active" : ""}" data-pg="${i}">${i}</a>`;
               else if (Math.abs(i - cp) === 3) html += `<span class="page-btn disabled">…</span>`;
           }
           html += `<a class="page-btn ${cp === lp ? "disabled" : ""}" data-pg="${cp + 1}">›</a>`;
           pg.innerHTML = html;
           pg.querySelectorAll("[data-pg]:not(.disabled):not(.active)").forEach((a) => {
               a.addEventListener("click", () => {
                   doSearch(a.dataset.pg);
                   window.scrollTo({ top: 0, behavior: "smooth" });
               });
           });
       }
   
       function doSearch(page) {
           if (!page) {
               const wrap = document.getElementById("pagination-wrap");
               if (wrap) wrap.dataset.page = "1";
           } else {
               const wrap = document.getElementById("pagination-wrap");
               if (wrap) wrap.dataset.page = String(page);
           }
   
           const params = buildQuery(page);
           const qs = params.toString();
           history.replaceState(null, "", qs ? "?" + qs : window.location.pathname);
           AjaxGet("/mentors?" + qs, {
               onSuccess: renderMentors,
               loader: false,
           });
       }
   
       return {
           init() {
               hydrateFromUrl();
               const inp = document.getElementById("mentor-search-input");
               if (inp) {
                   inp.addEventListener("input", () => {
                       clearTimeout(searchTimer);
                       searchTimer = setTimeout(() => {
                           doSearch();
                       }, 400);
                   });
                   inp.addEventListener("keydown", (e) => {
                       if (e.key === "Enter") {
                           e.preventDefault();
                           doSearch();
                       }
                   });
               }
               document.querySelectorAll("[data-filter]").forEach((el) => {
                   el.addEventListener("change", () => doSearch());
               });
               const sortSel = document.querySelector("[data-sort-select]");
               if (sortSel) sortSel.addEventListener("change", () => doSearch());
   
               const rangeMin = document.getElementById("price-range-min");
               const rangeMax = document.getElementById("price-range-max");
               if (rangeMin)
                   rangeMin.addEventListener("input", () => {
                       clearTimeout(searchTimer);
                       searchTimer = setTimeout(() => doSearch(), 600);
                   });
               if (rangeMax)
                   rangeMax.addEventListener("input", () => {
                       clearTimeout(searchTimer);
                       searchTimer = setTimeout(() => doSearch(), 600);
                   });
           },
   
           submit: doSearch,
       };
   })();
   
   /* ── Form Validation ─────────────────────────────────────── */
   window.validateForm = function (formSel) {
       let valid = true;
       const form = document.querySelector(formSel);
       if (!form) return false;
   
       form.querySelectorAll("[data-required]").forEach((inp) => {
           const err = form.querySelector(`[data-error-for="${inp.name}"]`);
           if (!inp.value.trim()) {
               inp.classList.add("error");
               if (err) {
                   err.textContent = inp.dataset.required || "This field is required.";
                   err.style.display = "block";
               }
               valid = false;
           } else {
               inp.classList.remove("error");
               if (err) err.style.display = "none";
           }
       });
       return valid;
   };
   
   /* ── Confirmation Dialog ─────────────────────────────────── */
   window.confirm = function (message, onConfirm, options = {}) {
       const { title = "Are you sure?", confirmText = "Yes, proceed", cancelText = "Cancel", danger = false } = options;
       openModal("confirm-modal");
       const modal = document.getElementById("confirm-modal");
       if (!modal) {
           if (window.confirm(message)) onConfirm();
           return;
       }
       modal.querySelector(".confirm-title").textContent = title;
       modal.querySelector(".confirm-msg").textContent = message;
       const confirmBtn = modal.querySelector(".confirm-ok");
       if (confirmBtn) {
           confirmBtn.textContent = confirmText;
           confirmBtn.className = `btn ${danger ? "btn-danger" : "btn-primary"} btn-sm`;
           const newBtn = confirmBtn.cloneNode(true);
           confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
           newBtn.addEventListener("click", () => {
               closeModal("confirm-modal");
               onConfirm();
           });
       }
   };
   
   /* ── Profile Image Preview ───────────────────────────────── */
   window.previewImage = function (input, previewSel) {
       if (input.files && input.files[0]) {
           const reader = new FileReader();
           reader.onload = (e) => {
               const prev = document.querySelector(previewSel);
               if (prev) {
                   if (prev.tagName === "IMG") prev.src = e.target.result;
                   else prev.style.backgroundImage = `url(${e.target.result})`;
               }
           };
           reader.readAsDataURL(input.files[0]);
       }
   };
   
   /* ── Auto-init on DOM Ready ──────────────────────────────── */
   document.addEventListener("DOMContentLoaded", () => {
       // Banner
       initBanner(".banner-wrap");
   
       // OTP inputs
       initOtpInputs(".otp-grid");
   
       // Active nav links
       const path = window.location.pathname;
       document.querySelectorAll(".navbar-nav a, .sidebar-item").forEach((a) => {
           if (a.getAttribute("href") && path.startsWith(a.getAttribute("href")) && a.getAttribute("href") !== "/") {
               a.classList.add("active");
           } else if (a.getAttribute("href") === "/" && path === "/") {
               a.classList.add("active");
           }
       });
   
       // Generic form data-ajax
       document.querySelectorAll("[data-ajax-form]").forEach((form) => {
           form.addEventListener("submit", (e) => {
               e.preventDefault();
               const url = form.dataset.ajaxForm || form.action;
               const method = form.dataset.method || form.method || "POST";
               const btn = form.querySelector('[type="submit"]');
               const redirectTo = form.dataset.redirect;
               const successMsg = form.dataset.success || "Saved successfully.";
   
               AjaxPost(url, new FormData(form), {
                   method: method.toUpperCase(),
                   btn,
                   loader: true,
                   onSuccess: (data) => {
                       showToast("success", data.message || successMsg);
                       const loginRedirect = document.getElementById("login-redirect")?.value;
                       const target = loginRedirect || data.redirect || redirectTo;
                       if (target) setTimeout(() => (location.href = target), 1200);
                       if (form.dataset.resetOnSuccess !== undefined) form.reset();
                   },
                   onError: (err) => {
                       showToast("error", err.message || "Please check the form and try again.");
                       // Field errors
                       if (err.errors) {
                           Object.entries(err.errors).forEach(([field, messages]) => {
                               const inp = form.querySelector(`[name="${field}"]`);
                               if (inp) {
                                   inp.classList.add("error");
                                   let errEl = form.querySelector(`[data-error-for="${field}"]`);
                                   if (!errEl) {
                                       errEl = document.createElement("div");
                                       errEl.className = "form-error";
                                       errEl.dataset.errorFor = field;
                                       inp.parentNode.insertBefore(errEl, inp.nextSibling);
                                   }
                                   errEl.textContent = messages[0];
                               }
                           });
                       }
                   },
               });
           });
       });
   
       // Clear input errors on focus
       document.addEventListener(
           "focus",
           (e) => {
               if (e.target.classList?.contains("form-input") || e.target.classList?.contains("form-select")) {
                   e.target.classList.remove("error");
                   const errEl = document.querySelector(`[data-error-for="${e.target.name}"]`);
                   if (errEl) errEl.textContent = "";
               }
           },
           true
       );
   });
   
   /* ── Delete / Status Toggle helpers ──────────────────────── */
   window.deleteItem = function (url, { onSuccess, message = "This cannot be undone." } = {}) {
       confirm(
           message,
           () => {
               AjaxPost(
                   url,
                   {},
                   {
                       method: "DELETE",
                       loader: true,
                       onSuccess: (data) => {
                           showToast("success", data.message || "Deleted successfully.");
                           if (onSuccess) onSuccess(data);
                           else location.reload();
                       },
                   }
               );
           },
           { danger: true, title: "Delete this item?", confirmText: "Yes, delete" }
       );
   };
   
   window.toggleStatus = function (url, btn) {
       AjaxPost(
           url,
           {},
           {
               method: "POST",
               btn,
               onSuccess: (data) => {
                   showToast("success", data.message || "Status updated.");
                   if (data.reload) location.reload();
               },
           }
       );
   };
   
   /* ── Smooth Scroll ───────────────────────────────────────── */
   document.addEventListener("click", (e) => {
       const link = e.target.closest("[data-scroll-to]");
       if (!link) return;
       e.preventDefault();
       const target = document.querySelector(link.dataset.scrollTo);
       if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
   });
   
   /* ── Copy to Clipboard ───────────────────────────────────── */
   window.copyToClipboard = function (text, btn) {
       navigator.clipboard
           .writeText(text)
           .then(() => {
               showToast("success", "Copied to clipboard!");
               if (btn) {
                   const orig = btn.textContent;
                   btn.textContent = "Copied!";
                   setTimeout(() => (btn.textContent = orig), 2000);
               }
           })
           .catch(() => showToast("error", "Could not copy."));
   };
   