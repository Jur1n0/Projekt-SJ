<?php

class FlightFilter
{
    private $search_query;
    private $miesto_odletu;
    private $miesto_priletu;
    private $min_capacity;
    private $max_capacity;
    private $min_price;
    private $max_price;
    private $date_from;
    private $date_to;
    private $sort_by;
    private $sort_order;

    public function __construct()
    {
        $this->search_query = trim((string)($_GET['search'] ?? ''));
        $this->miesto_odletu = trim((string)($_GET['miesto_odletu'] ?? ''));
        $this->miesto_priletu = trim((string)($_GET['miesto_priletu'] ?? ''));
        $this->min_capacity = filter_input(INPUT_GET, 'min_capacity', FILTER_VALIDATE_INT);
        $this->max_capacity = filter_input(INPUT_GET, 'max_capacity', FILTER_VALIDATE_INT);
        $this->min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
        $this->max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
        $this->date_from = trim((string)($_GET['date_from'] ?? ''));
        $this->date_to = trim((string)($_GET['date_to'] ?? ''));

        // Predvolené hodnoty pre triedenie
        $this->sort_by = trim((string)($_GET['sort_by'] ?? 'datum_cas_odletu'));
        $this->sort_order = trim((string)($_GET['sort_order'] ?? 'ASC'));

        // Validácia sort_by a sort_order
        $allowed_sort_by = ['datum_cas_odletu', 'cena', 'lietadlo', 'miesto_odletu', 'order_count'];
        if (!in_array($this->sort_by, $allowed_sort_by)) {
            $this->sort_by = 'datum_cas_odletu';
        }

        $allowed_sort_order = ['ASC', 'DESC'];
        if (!in_array(strtoupper($this->sort_order), $allowed_sort_order)) {
            $this->sort_order = 'ASC';
        }
    }

    // Gettery pre všetky vlastnosti
    public function getSearchQuery() { return $this->search_query; }
    public function getMiestoOdletu() { return $this->miesto_odletu; }
    public function getMiestoPriletu() { return $this->miesto_priletu; }
    public function getMinCapacity() { return $this->min_capacity; }
    public function getMaxCapacity() { return $this->max_capacity; }
    public function getMinPrice() { return $this->min_price; }
    public function getMaxPrice() { return $this->max_price; }
    public function getDateFrom() { return $this->date_from; }
    public function getDateTo() { return $this->date_to; }
    public function getSortBy() { return $this->sort_by; }
    public function getSortOrder() { return $this->sort_order; }

    public function getConditionsAndParams(&$conditions, &$params)
    {
        if (!empty($this->search_query)) {
            $conditions[] = "(f.lietadlo LIKE :search_query OR f.miesto_odletu LIKE :search_query OR f.miesto_priletu LIKE :search_query)";
            $params[':search_query'] = '%' . $this->search_query . '%';
        }
        if (!empty($this->miesto_odletu)) {
            $conditions[] = "f.miesto_odletu LIKE :miesto_odletu";
            $params[':miesto_odletu'] = '%' . $this->miesto_odletu . '%';
        }
        if (!empty($this->miesto_priletu)) {
            $conditions[] = "f.miesto_priletu LIKE :miesto_priletu";
            $params[':miesto_priletu'] = '%' . $this->miesto_priletu . '%';
        }
        if ($this->min_capacity !== null) {
            $conditions[] = "f.kapacita_lietadla >= :min_capacity";
            $params[':min_capacity'] = $this->min_capacity;
        }
        if ($this->max_capacity !== null) {
            $conditions[] = "f.kapacita_lietadla <= :max_capacity";
            $params[':max_capacity'] = $this->max_capacity;
        }
        if ($this->min_price !== null) {
            $conditions[] = "f.cena >= :min_price";
            $params[':min_price'] = $this->min_price;
        }
        if ($this->max_price !== null) {
            $conditions[] = "f.cena <= :max_price";
            $params[':max_price'] = $this->max_price;
        }
        if (!empty($this->date_from)) {
            $conditions[] = "DATE(f.datum_cas_odletu) >= :date_from";
            $params[':date_from'] = $this->date_from;
        }
        if (!empty($this->date_to)) {
            $conditions[] = "DATE(f.datum_cas_odletu) <= :date_to";
            $params[':date_to'] = $this->date_to;
        }
    }

    public function getOrderByClause()
    {
        $allowed_sort_by = ['datum_cas_odletu', 'cena', 'lietadlo', 'miesto_odletu', 'order_count'];
        if (!in_array($this->sort_by, $allowed_sort_by)) {
            $this->sort_by = 'datum_cas_odletu';
        }

        $sort_order = strtoupper($this->sort_order);
        if (!in_array($sort_order, ['ASC', 'DESC'])) {
            $sort_order = 'ASC';
        }

        return " ORDER BY " . $this->sort_by . " " . $sort_order;
    }
}