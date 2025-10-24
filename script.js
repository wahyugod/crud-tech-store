/**
 * ADMIN DASHBOARD - INTERACTIVE JAVASCRIPT
 * Professional Admin Panel Interactions
 */

document.addEventListener("DOMContentLoaded", function () {
  // Global flag to avoid formatting while submitting
  let isSubmitting = false;
  // ====================================
  // SIDEBAR TOGGLE FOR MOBILE
  // ====================================
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function (e) {
    if (window.innerWidth <= 768) {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove("active");
      }
    }
  });

  // ====================================
  // ACTIVE MENU HIGHLIGHTING
  // ====================================
  const currentPage = window.location.pathname.split("/").pop() || "index.php";
  const menuLinks = document.querySelectorAll(".sidebar-menu a");

  menuLinks.forEach((link) => {
    const href = link.getAttribute("href");
    if (href === currentPage) {
      link.classList.add("active");
    }
  });

  // ====================================
  // SEARCH BOX ENHANCEMENT
  // ====================================
  const searchInput = document.querySelector(".search-box input");

  if (searchInput) {
    // Clear search button
    const clearBtn = document.createElement("button");
    clearBtn.innerHTML = '<i class="fas fa-times"></i>';
    clearBtn.className = "search-clear";
    clearBtn.style.cssText =
      "position:absolute;right:15px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#7f8c8d;display:none;";

    searchInput.parentElement.appendChild(clearBtn);

    searchInput.addEventListener("input", function () {
      clearBtn.style.display = this.value ? "block" : "none";
    });

    clearBtn.addEventListener("click", function () {
      searchInput.value = "";
      clearBtn.style.display = "none";
      searchInput.focus();
    });
  }

  // ====================================
  // DELETE CONFIRMATION WITH ANIMATION
  // ====================================
  const deleteLinks = document.querySelectorAll('a[href*="delete.php"]');

  deleteLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      const confirmation = confirm(
        "Apakah Anda yakin ingin menghapus data ini?\n\nData yang dihapus tidak dapat dikembalikan."
      );

      if (confirmation) {
        // Add loading animation
        const row = this.closest("tr");
        if (row) {
          row.style.opacity = "0.5";
          row.style.transition = "opacity 0.3s ease";
        }

        window.location.href = this.href;
      }
    });
  });

  // ====================================
  // AUTO-HIDE ALERTS AFTER 5 SECONDS
  // ====================================
  const alerts = document.querySelectorAll(".alert");

  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
      alert.style.opacity = "0";
      alert.style.transform = "translateY(-20px)";

      setTimeout(() => {
        alert.remove();
      }, 500);
    }, 5000);
  });

  // ====================================
  // TABLE ROW HOVER EFFECT ENHANCEMENT
  // ====================================
  const tableRows = document.querySelectorAll(".data-table tbody tr");

  tableRows.forEach((row) => {
    row.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.01)";
      this.style.boxShadow = "0 4px 12px rgba(0,0,0,0.1)";
    });

    row.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)";
      this.style.boxShadow = "none";
    });
  });

  // ====================================
  // ANIMATE STATISTICS CARDS
  // ====================================
  const statCards = document.querySelectorAll(".stat-card");

  const observerOptions = {
    threshold: 0.5,
    rootMargin: "0px",
  };

  const observer = new IntersectionObserver(function (entries, observer) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "0";
        entry.target.style.transform = "translateY(30px)";

        setTimeout(() => {
          entry.target.style.transition =
            "opacity 0.6s ease, transform 0.6s ease";
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }, 100);

        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  statCards.forEach((card) => {
    observer.observe(card);
  });

  // ====================================
  // ANIMATE COUNTER NUMBERS
  // ====================================
  function animateCounter(element, target, duration = 1500) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        element.textContent = target.toLocaleString("id-ID");
        clearInterval(timer);
      } else {
        element.textContent = Math.floor(current).toLocaleString("id-ID");
      }
    }, 16);
  }

  // Trigger counter animation when stats are visible
  const statValues = document.querySelectorAll(".stat-value");

  const counterObserver = new IntersectionObserver(
    function (entries) {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const target = parseInt(entry.target.textContent.replace(/\./g, ""));
          if (!isNaN(target)) {
            animateCounter(entry.target, target);
          }
          counterObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.5 }
  );

  statValues.forEach((value) => {
    counterObserver.observe(value);
  });

  // ====================================
  // FORM VALIDATION
  // ====================================
  const forms = document.querySelectorAll('form[method="post"]');

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      // Mark submitting to disable blur formatting
      isSubmitting = true;

      // Normalize numeric fields (harga, stok) to digits only before validation
      const numericInputs = form.querySelectorAll(
        'input[name="harga"], input[name="stok"]'
      );
      numericInputs.forEach((input) => {
        input.value = (input.value || "").replace(/[^0-9]/g, "");
      });

      const requiredInputs = form.querySelectorAll("[required]");
      let isValid = true;

      requiredInputs.forEach((input) => {
        if (!input.value.trim()) {
          isValid = false;
          input.classList.add("error");

          // Add error message if not exists
          if (
            !input.nextElementSibling ||
            !input.nextElementSibling.classList.contains("error-message")
          ) {
            const errorMsg = document.createElement("span");
            errorMsg.className = "error-message";
            errorMsg.textContent = "Field ini wajib diisi";
            input.parentNode.insertBefore(errorMsg, input.nextSibling);
          }
        } else {
          input.classList.remove("error");
          const errorMsg = input.nextElementSibling;
          if (errorMsg && errorMsg.classList.contains("error-message")) {
            errorMsg.remove();
          }
        }
      });

      if (!isValid) {
        e.preventDefault();
        // Reset the flag if not submitting due to validation failure
        isSubmitting = false;
        alert("Mohon lengkapi semua field yang wajib diisi!");
      }
    });

    // Remove error on input
    const inputs = form.querySelectorAll("input, textarea, select");
    inputs.forEach((input) => {
      input.addEventListener("input", function () {
        this.classList.remove("error");
        const errorMsg = this.nextElementSibling;
        if (errorMsg && errorMsg.classList.contains("error-message")) {
          errorMsg.remove();
        }
      });
    });
  });

  // ====================================
  // PRICE FORMAT (IDR)
  // ====================================
  const priceInputs = document.querySelectorAll('input[name="harga"]');

  priceInputs.forEach((input) => {
    input.addEventListener("blur", function () {
      if (isSubmitting) return; // don't format while submitting
      let value = this.value.replace(/[^0-9]/g, "");
      if (value) {
        this.value = parseInt(value, 10).toLocaleString("id-ID");
      }
    });

    input.addEventListener("focus", function () {
      this.value = this.value.replace(/[^0-9]/g, "");
    });
  });

  // ====================================
  // TOOLTIP INITIALIZATION (if Bootstrap tooltips used)
  // ====================================
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  if (typeof bootstrap !== "undefined") {
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // ====================================
  // SMOOTH SCROLL
  // ====================================
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // ====================================
  // PRINT FUNCTIONALITY
  // ====================================
  const printButtons = document.querySelectorAll(".btn-print");

  printButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      window.print();
    });
  });

  // ====================================
  // REFRESH DATA BUTTON
  // ====================================
  const refreshBtn = document.getElementById("refreshData");

  if (refreshBtn) {
    refreshBtn.addEventListener("click", function () {
      this.classList.add("spinning");
      location.reload();
    });
  }

  // ====================================
  // DYNAMIC CLOCK (OPTIONAL)
  // ====================================
  const clockElement = document.getElementById("currentTime");

  if (clockElement) {
    function updateClock() {
      const now = new Date();
      const time = now.toLocaleTimeString("id-ID");
      const date = now.toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      });
      clockElement.innerHTML = `<i class="far fa-clock"></i> ${time} - ${date}`;
    }

    updateClock();
    setInterval(updateClock, 1000);
  }
});

// ====================================
// EXPORT FUNCTIONS FOR GLOBAL USE
// ====================================

// Show notification
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `alert alert-${type}`;
  notification.style.cssText =
    "position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;animation:slideInRight 0.3s ease;";
  notification.innerHTML = `
        <i class="fas fa-${
          type === "success"
            ? "check-circle"
            : type === "danger"
            ? "exclamation-circle"
            : "info-circle"
        }"></i>
        <span>${message}</span>
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = "slideOutRight 0.3s ease";
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Loading overlay
function showLoading() {
  const overlay = document.createElement("div");
  overlay.id = "loadingOverlay";
  overlay.style.cssText =
    "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;";
  overlay.innerHTML =
    '<div style="background:white;padding:30px;border-radius:10px;"><i class="fas fa-spinner fa-spin fa-3x" style="color:#3498db;"></i><p style="margin-top:15px;color:#2c3e50;">Loading...</p></div>';
  document.body.appendChild(overlay);
}

function hideLoading() {
  const overlay = document.getElementById("loadingOverlay");
  if (overlay) overlay.remove();
}

// ====================================
// ADVANCED SEARCH & FILTER FEATURES
// ====================================

document.addEventListener("DOMContentLoaded", function () {
  // Real-time search with debounce
  const searchInput = document.querySelector('input[name="q"]');
  if (searchInput) {
    let searchTimeout;

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);

      // Show loading indicator
      const searchIcon = this.parentElement.querySelector("i");
      if (searchIcon) {
        searchIcon.className = "fas fa-spinner fa-spin";
      }

      searchTimeout = setTimeout(() => {
        // Reset icon
        if (searchIcon) {
          searchIcon.className = "fas fa-search";
        }
      }, 500);
    });

    // Submit on Enter key
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        document.getElementById("filterForm").submit();
      }
    });
  }

  // Sortable table headers enhancement
  const sortableHeaders = document.querySelectorAll(".data-table thead th a");

  sortableHeaders.forEach((header) => {
    header.addEventListener("click", function (e) {
      // Add loading effect
      showLoading();

      // Add a small delay for better UX
      setTimeout(() => {
        // The link will navigate naturally
      }, 300);
    });
  });

  // Filter dropdown change animation
  const filterSelects = document.querySelectorAll("#filterForm select");

  filterSelects.forEach((select) => {
    select.addEventListener("change", function () {
      // Show loading when filter changes
      showLoading();
    });
  });

  // Quick filter reset
  const resetButton = document.querySelector('a[href="index.php"]');
  if (resetButton && resetButton.textContent.includes("Reset")) {
    resetButton.addEventListener("click", function (e) {
      showLoading();
    });
  }

  // Highlight current sort column
  const urlParams = new URLSearchParams(window.location.search);
  const currentSort = urlParams.get("sort");

  if (currentSort) {
    const headers = document.querySelectorAll(".data-table thead th");
    headers.forEach((header) => {
      const link = header.querySelector("a");
      if (link && link.href.includes(`sort=${currentSort}`)) {
        header.style.backgroundColor = "rgba(52, 152, 219, 0.2)";
      }
    });
  }

  // Keyboard shortcuts
  document.addEventListener("keydown", function (e) {
    // Alt + S = Focus search
    if (e.altKey && e.key === "s") {
      e.preventDefault();
      const searchInput = document.querySelector('input[name="q"]');
      if (searchInput) {
        searchInput.focus();
        searchInput.select();
      }
    }

    // Alt + R = Reset filters
    if (e.altKey && e.key === "r") {
      e.preventDefault();
      window.location.href = "index.php";
    }

    // Alt + N = New product
    if (e.altKey && e.key === "n") {
      e.preventDefault();
      window.location.href = "create.php";
    }
  });

  // Show keyboard shortcuts hint
  console.log(
    "%c🎯 Keyboard Shortcuts:",
    "color: #3498db; font-weight: bold; font-size: 14px;"
  );
  console.log(
    "%cAlt + S = Focus Search\nAlt + R = Reset Filters\nAlt + N = New Product",
    "color: #7f8c8d; font-size: 12px;"
  );
});

// ====================================
// EXPORT TABLE TO CSV (OPTIONAL)
// ====================================
function exportTableToCSV(filename = "products.csv") {
  const table = document.querySelector(".data-table");
  if (!table) return;

  let csv = [];
  const rows = table.querySelectorAll("tr");

  rows.forEach((row) => {
    const cols = row.querySelectorAll("td, th");
    const rowData = [];

    cols.forEach((col) => {
      // Skip action column
      if (!col.textContent.includes("Aksi") && !col.querySelector(".btn")) {
        let data = col.textContent.trim();
        // Escape quotes
        data = data.replace(/"/g, '""');
        rowData.push(`"${data}"`);
      }
    });

    if (rowData.length > 0) {
      csv.push(rowData.join(","));
    }
  });

  // Download CSV
  const csvContent = csv.join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");

  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  showNotification("Data berhasil diexport ke CSV!", "success");
}

// ====================================
// PRINT TABLE
// ====================================
function printTable() {
  const printContent = document.querySelector(".content-card").innerHTML;
  const originalContent = document.body.innerHTML;

  document.body.innerHTML = `
        <html>
        <head>
            <title>Print - Daftar Produk</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #2c3e50; color: white; }
                .btn, .alert, form { display: none !important; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <h1>Daftar Produk</h1>
            <p>Tanggal: ${new Date().toLocaleDateString("id-ID")}</p>
            ${printContent}
        </body>
        </html>
    `;

  window.print();
  document.body.innerHTML = originalContent;
  location.reload();
}
