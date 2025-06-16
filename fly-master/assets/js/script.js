'use strict';



/**
 * add event on element
 */

const addEventOnElem = function (elem, type, callback) {
  if (elem.length > 1) {
    for (let i = 0; i < elem.length; i++) {
      elem[i].addEventListener(type, callback);
    }
  } else {
    elem.addEventListener(type, callback);
  }
}



/**
 * navbar toggle
 */

const navbar = document.querySelector("[data-navbar]");
const navTogglers = document.querySelectorAll("[data-nav-toggler]");
const navbarLinks = document.querySelectorAll("[data-nav-link]");
const overlay = document.querySelector("[data-overlay]");

const toggleNavbar = function () {
  navbar.classList.toggle("active");
  overlay.classList.toggle("active");
  document.body.classList.toggle("active");
}

addEventOnElem(navTogglers, "click", toggleNavbar);

const closeNavbar = function () {
  navbar.classList.remove("active");
  overlay.classList.remove("active");
  document.body.classList.remove("active");
}

addEventOnElem(navbarLinks, "click", closeNavbar);



/**
 * active header & back top btn when window scroll down to 100px
 */

const header = document.querySelector("[data-header]");
const backTopBtn = document.querySelector("[data-back-top-btn]");

const activeElemOnScroll = function () {
  if (window.scrollY > 100) {
    header.classList.add("active");
    backTopBtn.classList.add("active");
  } else {
    header.classList.remove("active");
    backTopBtn.classList.remove("active");
  }
}

addEventOnElem(window, "scroll", activeElemOnScroll);

// news_modal.js (alebo pridajte tento kód do vášho existujúceho script.js)

document.addEventListener('DOMContentLoaded', function() {
  const newsModal = document.getElementById('newsModal');
  const modalTitle = document.getElementById('modalTitle');
  const newsForm = document.getElementById('newsForm');
  const newsId = document.getElementById('newsId');
  const newsNadpis = document.getElementById('newsNadpis');
  const newsText = document.getElementById('newsText');
  const newsObrazok = document.getElementById('newsObrazok');
  const submitNewsBtn = document.getElementById('submitNewsBtn');

  // Funkcia na otvorenie modálneho okna
  window.openNewsModal = function(id = null, nadpis = '', text = '', obrazok = '') {
    newsModal.style.display = 'flex'; // Zobrazí modálne okno ako flex, aby sa dalo centrovať
    if (id) {
      modalTitle.textContent = 'Upraviť';
      newsForm.action = 'process/process_update_news.php';
      newsId.value = id;
      newsNadpis.value = nadpis;
      newsText.value = text;
      newsObrazok.value = obrazok;
      submitNewsBtn.textContent = 'Uložiť zmeny';
    } else {
      modalTitle.textContent = 'Pridať';
      newsForm.action = 'process/process_add_news.php';
      newsId.value = '';
      newsNadpis.value = '';
      newsText.value = '';
      newsObrazok.value = '';
      submitNewsBtn.textContent = 'Pridať novinku';
    }
    // Skryje existujúce správy pri otvorení modálu
    const messages = newsModal.querySelectorAll('.message');
    messages.forEach(msg => msg.remove());
  }

  // Funkcia na zatvorenie modálneho okna
  window.closeNewsModal = function() {
    newsModal.style.display = 'none';
    // Vymaže správy pri zatvorení modálu, ak nejaké sú
    const messages = newsModal.querySelectorAll('.message');
    messages.forEach(msg => msg.remove());
  }

  // Zatvorenie modálu kliknutím mimo neho
  window.onclick = function(event) {
    if (event.target == newsModal) {
      closeNewsModal();
    }
  }

});