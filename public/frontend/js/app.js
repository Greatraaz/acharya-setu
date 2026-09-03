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
               document.querySelectorAll(".user-dropdown").forEach((el) => {
                   if (el !== dd) el.classList.remove("open");
               });
               if (dd) dd.classList.toggle("open");
           });
       });

       // Insights dropdown (desktop click support; hover also works via CSS)
       document.querySelectorAll(".nav-item-has-dropdown").forEach((item) => {
           const trigger = item.querySelector(".nav-dropdown-trigger");
           if (!trigger) return;
           trigger.addEventListener("click", (e) => {
               e.preventDefault();
               e.stopPropagation();
               const open = !item.classList.contains("is-open");
               document.querySelectorAll(".nav-item-has-dropdown").forEach((el) => el.classList.remove("is-open"));
               item.classList.toggle("is-open", open);
               trigger.setAttribute("aria-expanded", open ? "true" : "false");
           });
       });

       document.addEventListener("click", () => {
           document.querySelectorAll(".nav-item-has-dropdown").forEach((el) => {
               el.classList.remove("is-open");
               el.querySelector(".nav-dropdown-trigger")?.setAttribute("aria-expanded", "false");
           });
           document.querySelectorAll(".user-dropdown").forEach((dd) => dd.classList.remove("open"));
       });

       document.querySelectorAll("[data-mobile-insights-toggle]").forEach((btn) => {
           btn.addEventListener("click", () => {
               const panel = document.getElementById("mobileInsightsPanel");
               if (!panel) return;
               panel.hidden = !panel.hidden;
           });
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
           selectedSlotMax = null,
           ratePerMin = 0,
           mentorId = null,
           mentorHasSchedule = null,
           dayAvailability = {},
           weeklySummary = [],
           lastSlotPayload = null,
           viewYear = null,
           viewMonth = null; // 0-indexed

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

       function formatDisplayTime(hhmm) {
           if (!hhmm) return "—";
           const [h, m] = hhmm.split(":").map(Number);
           const ampm = h >= 12 ? "PM" : "AM";
           const hr = ((h + 11) % 12) + 1;
           return `${hr}:${String(m).padStart(2, "0")} ${ampm}`;
       }

       function formatPrettyDate(iso) {
           if (!iso) return "—";
           const d = new Date(iso + "T12:00:00");
           return d.toLocaleDateString("en-IN", {
               weekday: "short",
               day: "numeric",
               month: "short",
           });
       }

       function renderSlotsEmpty(kind) {
           const grid = document.getElementById("timeGrid");
           if (!grid) return;

           const messages = {
               no_schedule:
                   "This mentor hasn't added availability yet. Please check back later or choose another mentor.",
               no_slots:
                   "No open slots on this date. Try another day from the calendar.",
               no_duration:
                   "No slots fit the selected session duration. Try 15 or 30 minutes, or pick another date.",
               pick_date: "Select an available date to see time slots.",
               loading: "Loading available times…",
           };

           grid.innerHTML = `<div class="booking-empty-state" data-empty="${kind || "no_slots"}">
               <span class="booking-empty-state__icon">${kind === "no_schedule" ? "📅" : "⏰"}</span>
               <p class="booking-empty-state__text">${messages[kind] || messages.no_slots}</p>
           </div>`;
       }

       function slotFitsDuration(opt, duration) {
           if (!opt || !opt.duration) return true;
           return Number(duration) <= Number(opt.duration);
       }

       function renderSlots(slots, options = []) {
           const grid = document.getElementById("timeGrid");
           if (!grid) return;
           grid.innerHTML = "";
           const byStart = {};
           (options || []).forEach((opt) => {
               if (opt && opt.start_time) byStart[opt.start_time] = opt;
           });
           if (mentorHasSchedule === false) {
               renderSlotsEmpty("no_schedule");
               return;
           }
           if (!slots || !slots.length) {
               renderSlotsEmpty(selectedDate ? "no_slots" : "pick_date");
               return;
           }

           const fitting = slots.filter((slot) => {
               const start = typeof slot === "string" ? slot : slot.start_time;
               const opt = byStart[start] || (typeof slot === "object" ? slot : null);
               return slotFitsDuration(opt, selectedDuration);
           });

           if (!fitting.length) {
               const maxDur = Math.max(
                   ...slots.map((slot) => {
                       const start = typeof slot === "string" ? slot : slot.start_time;
                       const opt = byStart[start] || (typeof slot === "object" ? slot : null);
                       return Number(opt?.duration) || 0;
                   })
               );
               renderSlotsEmpty("no_duration");
               if (maxDur > 0) {
                   const hint = grid.querySelector(".booking-empty-state__text");
                   if (hint) {
                       hint.textContent = `Slots on this day are up to ${maxDur} minutes. Choose ${maxDur}m duration to book.`;
                   }
               }
               return;
           }

           fitting.forEach((slot) => {
               const start = typeof slot === "string" ? slot : slot.start_time;
               const opt = byStart[start] || (typeof slot === "object" ? slot : null);
               const div = document.createElement("button");
               div.type = "button";
               div.className = "time-slot";
               div.dataset.time = start;
               if (opt && opt.duration) div.dataset.maxDuration = String(opt.duration);
               div.textContent = formatDisplayTime(start);
               div.addEventListener("click", function (e) {
                   e.preventDefault();
                   e.stopPropagation();
                   document.querySelectorAll("#timeGrid .time-slot").forEach((s) => s.classList.remove("selected"));
                   div.classList.add("selected");
                   selectedTime = start;
                   selectedSlotMax = opt && opt.duration ? Number(opt.duration) : null;
                   constrainDurationButtons();
                   if (selectedSlotMax && selectedDuration > selectedSlotMax) {
                       const allowed = [15, 30, 60, 90].filter((m) => m <= selectedSlotMax);
                       selectedDuration = allowed.length ? allowed[allowed.length - 1] : 15;
                       document.querySelectorAll(".duration-btn").forEach((b) => b.classList.remove("selected"));
                       document.querySelector(`.duration-btn[data-min="${selectedDuration}"]`)?.classList.add("selected");
                   }
                   updateSummary();
               });
               grid.appendChild(div);
           });
       }

       function constrainDurationButtons() {
           document.querySelectorAll(".duration-btn").forEach((btn) => {
               const min = Number(btn.dataset.min);
               const tooLong = selectedSlotMax != null && min > selectedSlotMax;
               btn.classList.toggle("is-disabled", tooLong);
               btn.style.opacity = tooLong ? "0.35" : "";
               btn.style.pointerEvents = tooLong ? "none" : "";
           });
       }

       function updateSummary() {
           const el = (id) => document.getElementById(id);
           if (el("bk-date")) el("bk-date").textContent = selectedDate ? formatPrettyDate(selectedDate) : "—";
           if (el("bk-time")) el("bk-time").textContent = formatDisplayTime(selectedTime);
           if (el("bk-duration")) el("bk-duration").textContent = selectedDuration + " min";
           const total = selectedDuration * ratePerMin;
           if (el("bk-total")) el("bk-total").textContent = "₹" + total.toLocaleString("en-IN");
           const heading = document.getElementById("slots-heading");
           if (heading) {
               heading.textContent = selectedDate
                   ? `Available Slots on ${formatPrettyDate(selectedDate)}`
                   : "Available Slots";
           }
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

       function renderWeeklySummary(summary, hasSchedule) {
           weeklySummary = Array.isArray(summary) ? summary : [];
           const wrap = document.getElementById("availabilitySummary");
           if (!wrap) return;

           if (hasSchedule === false) {
               wrap.innerHTML = `<div class="booking-empty-state booking-empty-state--compact">
                   <span class="booking-empty-state__icon">📅</span>
                   <p class="booking-empty-state__text">Availability not set up yet. This mentor still needs to add their weekly schedule.</p>
               </div>`;
               return;
           }

           if (!weeklySummary.length) {
               wrap.innerHTML = `<p class="avail-hint">Weekly schedule loading…</p>`;
               return;
           }
           const letters = ["M", "T", "W", "T", "F", "S", "S"];
           const circles = weeklySummary
               .map((d, i) => {
                   const title = d.enabled
                       ? `${d.label}: ${d.windows || (d.from && d.to ? `${d.from}–${d.to}` : "Open")}`
                       : `${d.label}: Off`;
                   return `<div class="avail-day-circle ${d.enabled ? "is-on" : "is-off"}" title="${title}">
                        <span>${letters[i] || d.label?.[0] || "?"}</span>
                        ${d.enabled ? '<i class="avail-day-dot"></i>' : ""}
                    </div>`;
               })
               .join("");
           wrap.innerHTML = `<div class="avail-day-row">${circles}</div>`;
       }

       function markDateButtons() {
           document.querySelectorAll("#dateGrid .cal-day[data-date]").forEach((btn) => {
               const info = dayAvailability[btn.dataset.date];
               const isToday = btn.classList.contains("today");
               btn.classList.remove("disabled", "unavailable", "has-slots");
               btn.removeAttribute("disabled");
               btn.querySelector(".cal-dot")?.remove();
               if (!info) {
                   if (btn.dataset.inMonth === "1") {
                       btn.classList.add("disabled", "unavailable");
                       btn.disabled = true;
                   }
                   return;
               }
               if (!info.available) {
                   btn.classList.add("disabled", "unavailable");
                   btn.disabled = true;
                   btn.title = isToday ? "No remaining slots today" : "Mentor not available";
               } else {
                   btn.classList.add("has-slots");
                   const dot = document.createElement("i");
                   dot.className = "cal-dot";
                   btn.appendChild(dot);
                   btn.title = `${info.slot_count || ""} slots available`.trim();
               }
               if (selectedDate && btn.dataset.date === selectedDate) {
                   btn.classList.add("selected");
               }
           });
       }

       function selectDate(btn) {
           if (!btn || btn.classList.contains("disabled") || btn.disabled || !btn.dataset.date) return;
           document.querySelectorAll("#dateGrid .cal-day").forEach((c) => c.classList.remove("selected"));
           btn.classList.add("selected");
           selectedDate = btn.dataset.date;
           selectedTime = null;
           selectedSlotMax = null;
           constrainDurationButtons();
           updateSummary();

           const grid = document.getElementById("timeGrid");
           if (grid) {
               renderSlotsEmpty("loading");
           }
           loadSlots(selectedDate);
       }

       function loadSlots(date) {
           const id = resolveMentorId();
           if (!id || !date) return;

           const qs = new URLSearchParams({
               date,
           });

           fetch(`/api/mentors/${id}/availability?${qs.toString()}`, {
               headers: {
                   "X-Requested-With": "XMLHttpRequest",
                   Accept: "application/json",
               },
           })
               .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
               .then(({ ok, data }) => {
                   if (!ok) {
                       renderSlotsEmpty("no_slots");
                       return;
                   }
                   if (typeof data.has_schedule === "boolean") {
                       mentorHasSchedule = data.has_schedule;
                   }
                   lastSlotPayload = {
                       date,
                       slots: Array.isArray(data.slots) ? data.slots : [],
                       slot_options: Array.isArray(data.slot_options) ? data.slot_options : [],
                   };
                   const openCount = lastSlotPayload.slots.length;
                   dayAvailability[date] = {
                       ...(dayAvailability[date] || {}),
                       date,
                       available: openCount > 0,
                       slot_count: openCount,
                       label: data.label || dayAvailability[date]?.label,
                   };
                   markDateButtons();
                   selectedTime = null;
                   selectedSlotMax = null;
                   constrainDurationButtons();
                   if (mentorHasSchedule === false) {
                       renderSlotsEmpty("no_schedule");
                   } else {
                       renderSlots(lastSlotPayload.slots, lastSlotPayload.slot_options);
                   }
                   updateSummary();
               })
               .catch(() => renderSlotsEmpty("no_slots"));
       }

       function renderMonthCalendar() {
           const grid = document.getElementById("dateGrid");
           const label = document.getElementById("cal-month-label");
           if (!grid) return;

           const now = new Date();
           now.setHours(12, 0, 0, 0);
           if (viewYear == null || viewMonth == null) {
               viewYear = now.getFullYear();
               viewMonth = now.getMonth();
           }

           const first = new Date(viewYear, viewMonth, 1, 12);
           const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
           const startWeekday = first.getDay(); // 0 Sun

           if (label) {
               label.textContent = first.toLocaleDateString("en-IN", { month: "long", year: "numeric" });
           }

           grid.innerHTML = "";
           ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"].forEach((d) => {
               const h = document.createElement("div");
               h.className = "cal-weekday";
               h.textContent = d;
               grid.appendChild(h);
           });

           for (let i = 0; i < startWeekday; i++) {
               const empty = document.createElement("div");
               empty.className = "cal-day is-empty";
               grid.appendChild(empty);
           }

           const todayStr = localDateString(now);
           for (let day = 1; day <= daysInMonth; day++) {
               const d = new Date(viewYear, viewMonth, day, 12);
               const iso = localDateString(d);
               const btn = document.createElement("button");
               btn.type = "button";
               btn.className = "cal-day";
               btn.dataset.date = iso;
               btn.dataset.inMonth = "1";
               if (iso === todayStr) btn.classList.add("today");
               if (iso < todayStr) {
                   btn.classList.add("disabled", "unavailable");
                   btn.disabled = true;
               }
               btn.innerHTML = `<span class="cal-day-num">${day}</span>`;
               btn.addEventListener("click", function (e) {
                   e.preventDefault();
                   e.stopPropagation();
                   selectDate(btn);
               });
               grid.appendChild(btn);
           }

           markDateButtons();
       }

       function loadMonthOverview() {
           const id = resolveMentorId();
           if (!id) return Promise.resolve();

           const start = new Date(viewYear, viewMonth, 1, 12);
           const today = new Date();
           today.setHours(12, 0, 0, 0);
           const fetchStart = start < today ? today : start;
           const end = new Date(viewYear, viewMonth + 1, 0, 12);
           const days = Math.max(1, Math.ceil((end - fetchStart) / 86400000) + 1);

           const qs = new URLSearchParams({
               week: "1",
               days: String(Math.min(42, days)),
               start: localDateString(fetchStart),
           });

           return fetch(`/api/mentors/${id}/availability?${qs.toString()}`, {
               headers: {
                   "X-Requested-With": "XMLHttpRequest",
                   Accept: "application/json",
               },
           })
               .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
               .then(({ ok, data }) => {
                   if (!ok || !data) return;
                   if (typeof data.has_schedule === "boolean") {
                       mentorHasSchedule = data.has_schedule;
                   }
                   (data.days || []).forEach((d) => {
                       dayAvailability[d.date] = d;
                   });
                   renderWeeklySummary(data.weekly_summary, mentorHasSchedule);
                   markDateButtons();

                   if (mentorHasSchedule === false) {
                       selectedDate = null;
                       selectedTime = null;
                       renderSlotsEmpty("no_schedule");
                       updateSummary();
                       return;
                   }

                   if (!selectedDate) {
                       const firstOpen = document.querySelector("#dateGrid .cal-day.has-slots:not(.disabled)");
                       if (firstOpen) selectDate(firstOpen);
                   }
               })
               .catch(() => {});
       }

       function bindMonthNav() {
           const prev = document.getElementById("cal-prev-month");
           const next = document.getElementById("cal-next-month");
           if (prev && !prev.dataset.bound) {
               prev.dataset.bound = "1";
               prev.addEventListener("click", () => {
                   viewMonth -= 1;
                   if (viewMonth < 0) {
                       viewMonth = 11;
                       viewYear -= 1;
                   }
                   selectedDate = null;
                   selectedTime = null;
                   renderMonthCalendar();
                   loadMonthOverview();
                   updateSummary();
               });
           }
           if (next && !next.dataset.bound) {
               next.dataset.bound = "1";
               next.addEventListener("click", () => {
                   viewMonth += 1;
                   if (viewMonth > 11) {
                       viewMonth = 0;
                       viewYear += 1;
                   }
                   selectedDate = null;
                   selectedTime = null;
                   renderMonthCalendar();
                   loadMonthOverview();
                   updateSummary();
               });
           }
       }

       return {
           init(rate, id = null) {
               ratePerMin = Number(rate) || 0;
               mentorId = id != null && id !== "" ? String(id) : null;
               if (!mentorId) mentorId = resolveMentorId();
               selectedDate = null;
               selectedTime = null;
               selectedDuration = 30;
               selectedSlotMax = null;
               mentorHasSchedule = null;
               lastSlotPayload = null;
               dayAvailability = {};
               const now = new Date();
               viewYear = now.getFullYear();
               viewMonth = now.getMonth();

               document.querySelectorAll(".duration-btn").forEach((b) => b.classList.remove("selected", "is-disabled"));
               document.querySelector('.duration-btn[data-min="30"]')?.classList.add("selected");
               constrainDurationButtons();

               const timeGrid = document.getElementById("timeGrid");
               if (timeGrid) {
                   renderSlotsEmpty("pick_date");
               }
               const summary = document.getElementById("availabilitySummary");
               if (summary) summary.innerHTML = '<p class="avail-hint">Loading…</p>';

               bindMonthNav();
               renderMonthCalendar();
               updateSummary();
               loadMonthOverview();
           },

           selectDate,
           loadSlots,
           setDuration(min) {
               const next = Number(min) || 30;
               if (selectedSlotMax != null && next > selectedSlotMax) {
                   showToast("error", `This slot is only ${selectedSlotMax} minutes. Choose a shorter duration.`);
                   return;
               }
               selectedDuration = next;
               selectedTime = null;
               document.querySelectorAll(".duration-btn").forEach((b) => b.classList.remove("selected"));
               document.querySelector(`.duration-btn[data-min="${selectedDuration}"]`)?.classList.add("selected");
               updateSummary();
               if (selectedDate && lastSlotPayload && lastSlotPayload.date === selectedDate) {
                   renderSlots(lastSlotPayload.slots, lastSlotPayload.slot_options);
               } else if (selectedDate) {
                   loadSlots(selectedDate);
               }
           },

           getBookingData() {
               if (mentorHasSchedule === false) {
                   showToast("error", "This mentor has not set availability yet.");
                   return null;
               }
               if (!selectedDate) {
                   showToast("error", "Please select an available date.");
                   return null;
               }
               if (!selectedTime) {
                   showToast("error", "Please select a time slot.");
                   return null;
               }
               if (selectedSlotMax != null && selectedDuration > selectedSlotMax) {
                   showToast("error", `This slot is only ${selectedSlotMax} minutes long.`);
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
   
       function updateCount(data) {
           const wrap = document.getElementById("mentor-count-wrap");
           const totalEl = document.getElementById("mentor-count");
           const rangeEl = document.getElementById("mentor-count-range");
           const total = data.total ?? 0;

           if (!wrap) return;

           if (total > 0 && data.from != null && data.to != null) {
               if (rangeEl) {
                   rangeEl.textContent = `${data.from}–${data.to}`;
               } else {
                   wrap.innerHTML = `Showing <strong id="mentor-count-range" style="color:var(--text);">${data.from}–${data.to}</strong> of <strong id="mentor-count" style="color:var(--text);">${total}</strong> mentors`;
               }
               if (totalEl) totalEl.textContent = total;
           } else {
               wrap.innerHTML = `Showing <strong id="mentor-count" style="color:var(--text);">0</strong> mentors`;
           }
       }

       function renderMentors(data) {
           const grid = document.getElementById("mentors-grid");
           if (!grid) return;
           updateCount(data);

           const rows = data.data || data.mentors || [];
           if (!rows.length) {
               grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
           <div class="empty-state-icon">🔍</div>
           <h3>No mentors found</h3>
           <p>Try adjusting your filters or search term.</p>
         </div>`;
               renderPagination({ ...data, last_page: 0 });
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
           if (!pg) return;

           const lp = Number(data.last_page || 0);
           if (lp <= 1) {
               pg.innerHTML = "";
               return;
           }

           let html = "";
           const cp = Number(data.current_page || 1);
           html += `<a class="page-btn ${cp === 1 ? "disabled" : ""}" data-pg="${cp - 1}" aria-label="Previous page">‹</a>`;
           for (let i = 1; i <= lp; i++) {
               if (i === 1 || i === lp || Math.abs(i - cp) <= 2)
                   html += `<a class="page-btn ${i === cp ? "active" : ""}" data-pg="${i}" aria-label="Page ${i}">${i}</a>`;
               else if (Math.abs(i - cp) === 3) html += `<span class="page-btn disabled" aria-hidden="true">…</span>`;
           }
           html += `<a class="page-btn ${cp === lp ? "disabled" : ""}" data-pg="${cp + 1}" aria-label="Next page">›</a>`;
           pg.innerHTML = html;
           pg.dataset.page = String(cp);
           pg.querySelectorAll("[data-pg]:not(.disabled):not(.active)").forEach((a) => {
               a.addEventListener("click", (e) => {
                   e.preventDefault();
                   doSearch(a.dataset.pg);
                   const grid = document.getElementById("mentors-grid");
                   (grid || pg).scrollIntoView({ behavior: "smooth", block: "start" });
               });
           });
       }

       function initialPaginationMeta() {
           const pg = document.getElementById("pagination-wrap");
           if (!pg) return null;

           return {
               current_page: Number(pg.dataset.currentPage || pg.dataset.page || 1),
               last_page: Number(pg.dataset.lastPage || 1),
               total: Number(pg.dataset.total || 0),
               from: Number(pg.dataset.from || 0) || null,
               to: Number(pg.dataset.to || 0) || null,
           };
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
               const initial = initialPaginationMeta();
               if (initial) renderPagination(initial);

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
   