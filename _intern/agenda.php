
<?php
// _intern/agenda.php
require_once 'includes/auth.php';
$current_page = 'agenda';
$page_title = 'Agenda setmanal';
require_once 'includes/page-header.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<link rel="stylesheet" href="css/agenda.css">

<section class="content-section active">
    <?php renderPageHeader($current_page); ?>
    <div class="agenda-container">
        <div class="agenda-header">
            <button class="agenda-btn" id="prev-week">&lt;</button>
            <div class="agenda-title">
                <span id="agenda-month">Mes</span>
                <span id="agenda-week-range">Setmana</span>
            </div>
            <button class="agenda-btn" id="next-week">&gt;</button>
            <button class="agenda-btn" id="today-btn">Avui</button>
            <button class="agenda-btn" id="month-view-btn">Vista mensual</button>
            <button class="agenda-btn" id="week-view-btn" style="display:none;">Vista setmanal</button>
            <button class="agenda-btn agenda-add-task-btn" id="add-task-btn" type="button">Afegir tasca</button>
        </div>
        <?php include __DIR__ . '/includes/agenda-add-task-modal.html'; ?>
        <!-- VISTA SETMANAL -->
        <div class="agenda-calendar week-view">
            <table class="agenda-table">
                <thead>
                    <tr>
                        <th class="agenda-time-header"></th>
                        <th class="agenda-day-header">Dilluns</th>
                        <th class="agenda-day-header">Dimarts</th>
                        <th class="agenda-day-header">Dimecres</th>
                        <th class="agenda-day-header">Dijous</th>
                        <th class="agenda-day-header">Divendres</th>
                        <th class="agenda-day-header">Dissabte</th>
                        <th class="agenda-day-header">Diumenge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($h = 6; $h <= 23; $h++): ?>
                        <tr>
                            <td class="agenda-time"><?php printf('%02d:00', $h); ?></td>
                            <?php for ($d = 0; $d < 7; $d++): ?>
                                <td class="agenda-slot" data-hour="<?php echo $h; ?>" data-day="<?php echo $d; ?>"></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <!-- VISTA DIÀRIA -->
        <div class="agenda-calendar day-view" style="display:none;">
            <div class="agenda-day-header-bar">
                <button class="agenda-btn" id="prev-day">&lt;</button>
                <span id="agenda-day-title">Dia</span>
                <button class="agenda-btn" id="next-day">&gt;</button>
                <button class="agenda-btn" id="today-day-btn">Avui</button>
                <button class="agenda-btn" id="week-view-btn-2">Vista setmanal</button>
            </div>
            <table class="agenda-day-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Hora</th>
                        <th>Tasques</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Files generades per JS -->
                </tbody>
            </table>
        </div>
        <!-- VISTA MENSUAL -->
        <div class="agenda-calendar month-view" style="display:none;">
            <table class="agenda-month-table">
                <thead>
                    <tr>
                        <th>Dilluns</th>
                        <th>Dimarts</th>
                        <th>Dimecres</th>
                        <th>Dijous</th>
                        <th>Divendres</th>
                        <th>Dissabte</th>
                        <th>Diumenge</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Files i cel·les generades dinàmicament amb JS -->
                </tbody>
            </table>
        </div>
    </div>
</section>
<script src="js/agenda.js"></script>
<?php require_once 'includes/footer.php'; ?>
