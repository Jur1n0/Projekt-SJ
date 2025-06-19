<?php
header('Content-Type: application/json');
echo json_encode([
    'pickupDropoffPricePerPerson' => $pickup_dropoff_price_per_person,
    'servicePrices' => $service_prices
]);
?>