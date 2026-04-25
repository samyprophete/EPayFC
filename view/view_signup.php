<?php ob_start(); ?>

<div class="top-title">
    <i class="bi bi-cart4"></i> EPayFC
</div>

<script>window.APP_BASE = '<?= $web_root ?>';</script>

<?php include __DIR__ . "/partials/sign_up/_sign_up.php"; ?>

<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>
