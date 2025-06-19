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

document.addEventListener('DOMContentLoaded', function () {
  const newsModal = document.getElementById('newsModal');
  const modalTitle = document.getElementById('modalTitle');
  const newsForm = document.getElementById('newsForm');
  const newsId = document.getElementById('newsId');
  const newsNadpis = document.getElementById('newsNadpis');
  const newsText = document.getElementById('newsText');
  const newsObrazok = document.getElementById('newsObrazok');
  const submitNewsBtn = document.getElementById('submitNewsBtn');

  // Funkcia na otvorenie modálneho okna
  window.openNewsModal = function (id = null, nadpis = '', text = '', obrazok = '') {
    newsModal.style.display = 'flex';
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
  window.closeNewsModal = function () {
    newsModal.style.display = 'none';
    // Vymaže správy pri zatvorení modálu, ak nejaké sú
    const messages = newsModal.querySelectorAll('.message');
    messages.forEach(msg => msg.remove());
  }

  // Zatvorenie modálu kliknutím mimo neho
  window.onclick = function (event) {
    if (event.target == newsModal) {
      closeNewsModal();
    }
  }

});

function openEditModal(id, firstName, lastName, email, role) {
  document.getElementById('editUserId').value = id;
  document.getElementById('editFirstName').value = firstName;
  document.getElementById('editLastName').value = lastName;
  document.getElementById('editEmail').value = email;
  document.getElementById('editRole').value = role;
  document.getElementById('editUserModal').classList.add('show');
}

function closeEditModal() {
  document.getElementById('editUserModal').classList.remove('show');
}

// Funkcie pre správu noviniek
function openEditNewsModal(id, nadpis, text, obrazok) {
  document.getElementById('editNewsId').value = id;
  document.getElementById('editNewsNadpis').value = nadpis;
  document.getElementById('editNewsText').value = text.replace(/\\n/g, '\n'); // Nahradí \\n za skutočné nové riadky
  document.getElementById('editNewsObrazok').value = obrazok;
  document.getElementById('editNewsModal').classList.add('show');
}

function closeEditNewsModal() {
  document.getElementById('editNewsModal').classList.remove('show');
}

function openAddFlightModal() {
  document.getElementById('addFlightModal').classList.add('show');
}

function closeAddFlightModal() {
  document.getElementById('addFlightModal').classList.remove('show');
}

function openEditFlightModal(id, lietadlo, miesto_odletu, miesto_priletu, datum_cas_odletu, datum_cas_priletu, cena, kapacita_lietadla, dlzka_hodiny, dlzka_minuty, obrazok) {
  document.getElementById('editFlightId').value = id;
  document.getElementById('editFlightLietadlo').value = lietadlo;
  document.getElementById('editFlightMiestoOdletu').value = miesto_odletu;
  document.getElementById('editFlightMiestoPriletu').value = miesto_priletu;
  document.getElementById('editFlightDatumCasOdletu').value = datum_cas_odletu;
  document.getElementById('editFlightDatumCasPriletu').value = datum_cas_priletu;
  document.getElementById('editFlightCena').value = cena;
  document.getElementById('editFlightKapacita').value = kapacita_lietadla;
  document.getElementById('editFlightDlzkaHodiny').value = dlzka_hodiny;
  document.getElementById('editFlightDlzkaMinuty').value = dlzka_minuty;
  document.getElementById('editFlightObrazok').value = obrazok;
  document.getElementById('editFlightModal').classList.add('show');
}

function closeEditFlightModal() {
  document.getElementById('editFlightModal').classList.remove('show');
}

// NOVÉ: Funkcie pre správu objednávok
function openEditSaleModal(id, paymentStatus, orderStatus) {
  document.getElementById('editSaleId').value = id;
  document.getElementById('editSalePaymentStatus').value = paymentStatus;
  document.getElementById('editSaleOrderStatus').value = orderStatus;
  document.getElementById('editSaleModal').classList.add('show');
}

function closeEditSaleModal() {
  document.getElementById('editSaleModal').classList.remove('show');
}


// Zatvorenie modálneho okna kliknutím mimo neho
window.onclick = function (event) {
  const userModal = document.getElementById('editUserModal');
  const newsEditModal = document.getElementById('editNewsModal');
  const flightAddModal = document.getElementById('addFlightModal'); // NOVÉ
  const flightEditModal = document.getElementById('editFlightModal'); // NOVÉ
  const saleEditModal = document.getElementById('editSaleModal');   // NOVÉ

  if (event.target === userModal) {
    userModal.classList.remove('show');
  }
  if (event.target === newsEditModal) {
    newsEditModal.classList.remove('show');
  }
  if (event.target === flightAddModal) { // NOVÉ
    flightAddModal.classList.remove('show');
  }
  if (event.target === flightEditModal) { // NOVÉ
    flightEditModal.classList.remove('show');
  }
  if (event.target === saleEditModal) {   // NOVÉ
    saleEditModal.classList.remove('show');
  }
}

  document.getElementById('payment_method').addEventListener('change', function() {
  const cardDetails = document.getElementById('card_details');
  if (this.value === 'card') {
  cardDetails.style.display = 'block';
  cardDetails.querySelectorAll('input').forEach(input => input.setAttribute('required', 'required'));
} else {
  cardDetails.style.display = 'none';
  cardDetails.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
}
});

  // Simulácia platby - odstránime require z kariet pre ostatné platobné metódy
  document.addEventListener('DOMContentLoaded', function() {
  const paymentMethodSelect = document.getElementById('payment_method');
  const cardDetailsInputs = document.querySelectorAll('#card_details input');

  function toggleCardRequired() {
  if (paymentMethodSelect.value === 'card') {
  cardDetailsInputs.forEach(input => input.setAttribute('required', 'required'));
  document.getElementById('card_details').style.display = 'block';
} else {
  cardDetailsInputs.forEach(input => input.removeAttribute('required'));
  document.getElementById('card_details').style.display = 'none';
}
}

  paymentMethodSelect.addEventListener('change', toggleCardRequired);

  // Initial call in case the default value is 'card'
  toggleCardRequired();
});


fetch('../components/get_data.php')
    .then(response => response.json())
    .then(data => {
      const pickupDropoffPricePerPerson = data.pickupDropoffPricePerPerson;
      const servicePrices = data.servicePrices;
      console.log(pickupDropoffPricePerPerson);
      console.log(servicePrices);
    });

  function updateItemPrice(element) {
  const row = element.closest('tr');
  if (!row) return; // Ak element nie je v riadku tabuľky, nič nerobíme

  const cartItemId = row.dataset.cartItemId;
  const basePrice = parseFloat(row.dataset.basePrice);
  const capacity = parseInt(row.dataset.capacity);

  const servicePackageSelect = row.querySelector('.service-package-select');
  const pickupServiceCheckbox = row.querySelector('.pickup-service-checkbox');
  const dropoffServiceCheckbox = row.querySelector('.dropoff-service-checkbox');
  const itemTotalPriceElement = row.querySelector('.item-total-price');
  const hiddenCalculatedPriceInput = document.getElementById(`new_calculated_price_${cartItemId}`);

  let currentItemPrice = basePrice;

  // Cena za servisný balík
  const selectedPackage = servicePackageSelect.value;
  const packagePrice = servicePrices[selectedPackage] || 0;
  currentItemPrice += packagePrice * capacity;

  // Cena za odvoz na letisko
  if (pickupServiceCheckbox.checked) {
  currentItemPrice += pickupDropoffPricePerPerson * capacity;
}

  // Cena za odvoz z letiska
  if (dropoffServiceCheckbox.checked) {
  currentItemPrice += pickupDropoffPricePerPerson * capacity;
}

  itemTotalPriceElement.textContent = '€' + currentItemPrice.toLocaleString('sk-SK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  hiddenCalculatedPriceInput.value = currentItemPrice.toFixed(2); // Uložíme presnú cenu
  updateTotalCartPrice();
}

  function updateTotalCartPrice() {
  let totalCartPrice = 0;
  document.querySelectorAll('.item-total-price').forEach(itemPriceElement => {
  // Extrahujeme číslo z textu, odstránime € a medzery
  const priceText = itemPriceElement.textContent.replace('€', '').replace(/\s/g, '').replace(',', '.');
  totalCartPrice += parseFloat(priceText);
});
  document.getElementById('total-cart-price').textContent = '€' + totalCartPrice.toLocaleString('sk-SK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

  // Pridáme event listenery pre všetky relevantné prvky
  document.querySelectorAll('.service-package-select').forEach(select => {
  select.addEventListener('change', () => updateItemPrice(select));
});

  document.querySelectorAll('.pickup-service-checkbox').forEach(checkbox => {
  checkbox.addEventListener('change', () => updateItemPrice(checkbox));
});

  document.querySelectorAll('.dropoff-service-checkbox').forEach(checkbox => {
  checkbox.addEventListener('change', () => updateItemPrice(checkbox));
});

  // Funkcia na potvrdenie zmazania položky
  function confirmDeleteItem(itemId) {
  if (confirm("Naozaj chcete odstrániť tento let z košíka?")) {
  window.location.href = 'cart.php?action=delete&item_id=' + itemId;
}
}

  // Inicializácia cien pri načítaní stránky
  document.addEventListener('DOMContentLoaded', () => {
  // Prejdeme všetky riadky a zavoláme updateItemPrice pre každý, aby sa ceny inicializovali
  document.querySelectorAll('tr[data-cart-item-id]').forEach(row => {
    updateItemPrice(row); // Zavoláme updateItemPrice na samotnom riadku
  });
  updateTotalCartPrice(); // Prepočíta celkovú sumu po načítaní
});

  // Pridáme event listener pre textarea, aby sa aktualizovali hidden inputy aj pri zmene poznámok
  // Toto je dôležité, aby sa poznámky uložili aj pri aktualizácii košíka
  document.querySelectorAll('textarea[name^="cart_items"]').forEach(textarea => {
  textarea.addEventListener('input', () => {
    const row = textarea.closest('tr');
    const cartItemId = row.dataset.cartItemId;
    // Poznámky sa uložia priamo cez name atribút vo formulári, nemusíme ich dávať do hidden inputu zvlášť
  });
});

function openFilterModal() {
  document.getElementById('filterModal').classList.add('show');
}

function closeFilterModal() {
  document.getElementById('filterModal').classList.remove('show');
}

// Zatvorenie modálneho okna kliknutím mimo neho
window.onclick = function(event) {
  const filterModal = document.getElementById('filterModal');
  if (event.target === filterModal) {
    filterModal.classList.remove('show');
  }
}
