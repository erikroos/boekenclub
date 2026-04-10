<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';

try {
    $reviews = get_db()
        ->query('SELECT * FROM reviews ORDER BY sequence_number DESC')
        ->fetchAll();
} catch (Throwable $ex) {
    error_log('index.php fetch: ' . $ex->getMessage());
    $reviews = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/grids-responsive-min.css">
    <link rel="stylesheet" href="styles.css">
    <title>HBO-ICT Boekenclub</title>
    <link rel="icon" type="image/x-icon" href="/images/open-book-icon.jpg">
</head>
<body>
    <div id="layout" class="pure-g">
        <!-- Sidebar -->
        <div class="sidebar pure-u-1 pure-u-md-1-5">
            <div class="header">
                <h1 class="brand-title">HBO-ICT Boekenclub</h1>
                <h2 class="brand-tagline">Wij zijn een groep docenten bij de opleiding ICT van de Hanze met een voorliefde voor boeken die een raakvlak hebben met techniek. Deze site biedt een overzicht van onze bijeenkomsten en de boeken die we besproken hebben. Veel leesplezier!</h2>

                <nav class="nav">
                    <ul class="nav-list">
                        <li class="nav-item nav-item-cta">
                            <a href="leeslijst.php" class="pure-button">Draag een boek voor &rarr;</a>
                        </li>
                        <?php foreach ($reviews as $r): ?>
                            <li class="nav-item">
                                <a href="#book<?= (int) $r['sequence_number'] ?>" class="pure-button">#<?= (int) $r['sequence_number'] ?> <?= e($r['book_title']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content pure-u-1 pure-u-md-4-5">
            <div class="posts">

                <?php if (empty($reviews)): ?>
                    <section class="post">
                        <p>Er zijn nog geen recensies beschikbaar.</p>
                    </section>
                <?php endif; ?>

                <?php foreach ($reviews as $r): ?>
                    <section class="post" id="book<?= (int) $r['sequence_number'] ?>">
                        <header class="post-header">
                            <h3 class="post-title">#<?= (int) $r['sequence_number'] ?> '<?= e($r['book_title']) ?>' door <?= e($r['book_author']) ?></h3>
                            <p class="post-meta"><?= e(format_date_nl((string) $r['meeting_date'])) ?> bij <?= e($r['host_name']) ?> in <?= e($r['host_location']) ?></p>
                            <?php if (!empty($r['attendees'])): ?>
                                <p class="post-meta">Aanwezig: <?= e($r['attendees']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['verdict'])): ?>
                                <p class="post-meta">Oordeel over het boek: <b><?= e($r['verdict']) ?></b></p>
                            <?php endif; ?>
                        </header>

                        <?php if (!empty($r['preview']) || !empty($r['full_html'])): ?>
                            <div class="post-description">
                                <?php if (!empty($r['preview'])): ?>
                                    <div class="post-preview"><?= e($r['preview']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($r['full_html'])): ?>
                                    <div class="post-full" style="display: none;">
                                        <?php
                                        // full_html is ingevoerd door een vertrouwde admin
                                        // en wordt bewust NIET geescaped.
                                        echo $r['full_html'];
                                        ?>
                                    </div>
                                    <button class="toggle-btn" onclick="togglePost(this)">
                                        <span class="arrow">&#9660;</span>
                                        <span class="btn-text">Lees meer</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>

            </div>

            <footer></footer>
        </div>
    </div>

    <script>
        function togglePost(button) {
            const postDescription = button.parentElement;
            const preview = postDescription.querySelector('.post-preview');
            const full = postDescription.querySelector('.post-full');
            const arrow = button.querySelector('.arrow');
            const btnText = button.querySelector('.btn-text');

            if (full.style.display === 'none') {
                if (preview) preview.style.display = 'none';
                full.style.display = 'block';
                arrow.innerHTML = '&#9650;';
                btnText.textContent = 'Lees minder';
            } else {
                if (preview) preview.style.display = 'block';
                full.style.display = 'none';
                arrow.innerHTML = '&#9660;';
                btnText.textContent = 'Lees meer';
            }
        }
    </script>
</body>
</html>
