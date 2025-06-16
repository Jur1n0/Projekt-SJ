<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/News.php';

$db = new Database();
$pdo_conn = $db->getConnection();
$news_obj = new News($pdo_conn);

$all_news = [];
$single_news = null;
$display_single_news = false;

// Kontrola, či bol odoslaný parameter id novinky
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $news_id = $_GET['id'];
    $news_obj->idNews = $news_id;
    if ($news_obj->readOne()) {
        $single_news = [
            'idNews' => $news_obj->idNews,
            'Nadpis' => $news_obj->Nadpis,
            'Text' => $news_obj->Text,
            'Obrazok' => $news_obj->Obrazok,
            'created_at' => $news_obj->created_at
        ];
        $display_single_news = true;
    } else {
        $_SESSION['message'] = "Novinka nebola nájdená.";
        $_SESSION['message_type'] = "error";
    }
}

// Načítanie všetkých noviniek pre archív
if (!$display_single_news) { // Načítať všetky novinky len ak nezobrazujeme jednu konkrétnu
    try {
        $stmt_all_news = $news_obj->readAll();
        if ($stmt_all_news) {
            while ($row = $stmt_all_news->fetch(PDO::FETCH_ASSOC)) {
                $all_news[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching all news on news.php: " . $e->getMessage());
        $_SESSION['message'] = "Momentálne nie je možné načítať novinky. Skúste to prosím neskôr.";
        $_SESSION['message_type'] = "error";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<?php include("head.php") ?>

<body id="top">

<?php include("header.php") ?>

<main>
    <article>

        <section class="section news-archive" aria-label="news archive">
            <div class="container">
                <?php
                if (isset($_SESSION['message'])) {
                    $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
                    echo "<p class='message {$class}'>" . htmlspecialchars($_SESSION['message']) . "</p>";
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                }
                ?>

                <?php if ($display_single_news && $single_news): ?>
                    <p class="section-subtitle">Detail novinky</p>
                    <h2 class="h2 section-title"><?php echo htmlspecialchars($single_news['Nadpis']); ?></h2>
                    <div class="single-news-item">
                        <p class="news-date">Zverejnené: <?php echo date('d.m.Y H:i', strtotime($single_news['created_at'])); ?></p>
                        <?php if (!empty($single_news['Obrazok'])): ?>
                            <img src="<?php echo htmlspecialchars($single_news['Obrazok']); ?>" alt="<?php echo htmlspecialchars($single_news['Nadpis']); ?>" class="single-news-image">
                        <?php endif; ?>
                        <p class="single-news-text"><?php echo nl2br(htmlspecialchars($single_news['Text'])); ?></p>
                    </div>
                    <div class="text-center" style="margin-top: 30px;">
                        <a href="news.php" class="btn btn-secondary">Návrat na archív noviniek</a>
                    </div>
                <?php else: ?>
                    <p class="section-subtitle">Naše archívum</p>
                    <h2 class="h2 section-title">Všetky novinky</h2>

                    <?php if (count($all_news) > 0): ?>
                        <div class="news-archive-list">
                            <?php foreach ($all_news as $news_item): ?>
                                <div class="news-archive-item">
                                    <h3><a href="news.php?id=<?php echo htmlspecialchars($news_item['idNews']); ?>"><?php echo htmlspecialchars($news_item['Nadpis']); ?></a></h3>
                                    <p class="news-date">Zverejnené: <?php echo date('d.m.Y H:i', strtotime($news_item['created_at'])); ?></p>
                                    <?php if (!empty($news_item['Obrazok'])): ?>
                                        <img src="<?php echo htmlspecialchars($news_item['Obrazok']); ?>" alt="Obrázok k novinke" class="news-archive-image">
                                    <?php endif; ?>
                                    <p class="news-archive-text"><?php echo htmlspecialchars(mb_strimwidth($news_item['Text'], 0, 200, "...")); ?></p>
                                    <a href="news.php?id=<?php echo htmlspecialchars($news_item['idNews']); ?>" class="btn btn-primary btn-read-more">Čítať viac</a>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Žiadne novinky na zobrazenie.</p>
                    <?php endif; ?>

                    <div class="text-center" style="margin-top: 30px;">
                        <a href="index.php" class="btn btn-secondary">Návrat na hlavnú stránku</a>
                    </div>
                <?php endif; ?>

            </div>
        </section>

    </article>
</main>

<?php include("footer.php") ?>

<script src="../assets/js/script.js" defer></script>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>