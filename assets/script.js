/* ---------------------------------------------------------
   WMSTAY CAMPUS DORM MANAGEMENT SYSTEM
   FULL JAVASCRIPT FILE
   - Sidebar Toggle (Mobile)
   - Modal System
   - Smooth UI behavior
--------------------------------------------------------- */

// ----------------------------
// SIDEBAR TOGGLE (MOBILE)
// ----------------------------
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.querySelector("#menuToggle");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
        });
    }
});


// ----------------------------
// UNIVERSAL MODAL FUNCTIONS
// ----------------------------

// Open a modal by ID
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "flex";
    }
}

// Close a modal by ID
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "none";
    }
}


// ----------------------------
// CLICK OUTSIDE TO CLOSE MODAL
// ----------------------------
document.addEventListener("click", function(event) {
    if (event.target.classList.contains("modal-bg")) {
        event.target.style.display = "none";
    }
});


// ----------------------------
// ESC KEY CLOSES ANY OPEN MODAL
// ----------------------------
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal-bg").forEach(modal => {
            modal.style.display = "none";
        });
    }
});
