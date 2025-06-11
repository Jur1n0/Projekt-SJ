<?php
// module/create_admin_account.php
require_once 'Database.php';

// ---------- ÚDAJE PRE VÁŠHO HLAVNÉHO ADMINA (ktoré sa vložia do tabuľky 'admin') ----------
$super_admin_email = "admin@admin.com"; // HLAVNÝ E-MAIL ADMINA
$super_admin_raw_password = "admin";    // HESLO ADMINA (TOTO SI ZAPAMÄTAJTE!)
// ------------------------------------------------------------------------------------------

// Heslo musíme VŽDY zahashovať pred uložením do databázy
$super_admin_hashed_password = password_hash($super_admin_raw_password, PASSWORD_DEFAULT);

try {
    $db = new Database();

    // Pokus o vytvorenie admina v tabuľke 'admin'
    // Metóda createAdmin už bola upravená na použitie 'Email'
    $adminCreated = $db->createAdmin($super_admin_email, $super_admin_hashed_password);

    if ($adminCreated) {
        echo "<h1>Super admin účet bol úspešne vytvorený v tabuľke 'admin'!</h1>";
        echo "<p>E-mail: <strong>" . htmlspecialchars($super_admin_email) . "</strong></p>";
        echo "<p>Heslo: <strong>" . htmlspecialchars($super_admin_raw_password) . "</strong></p>";
        echo "<p><strong>Dôležité: Po vytvorení tohto účtu OKAMŽITE vymažte alebo presuňte tento súbor (`create_admin_account.php`) z verejne prístupného adresára!</strong></p>";
    } else {
        echo "<h1>Chyba pri vytváraní super admin účtu.</h1>";
        echo "<p>Skontrolujte PHP error log pre viac detailov. Možno už admin s týmto e-mailom existuje, alebo tabuľka `admin` neexistuje.</p>";
    }

} catch (Exception $e) {
    echo "<h1>Nastala chyba!</h1>";
    echo "<p>Správa chyby: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Uistite sa, že vaše databázové pripojenie v `Database.php` je správne.</p>";
}
?>