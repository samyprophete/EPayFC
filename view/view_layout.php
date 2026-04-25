

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $header_title ?? 'Page' ?></title>

    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">

    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php foreach ($page_css ?? [] as $css): ?>
    <link rel="stylesheet" href="css/<?= $css ?>">
    <?php endforeach; ?>
    <?php foreach ($page_js ?? [] as $js): ?>
    <script src="js/<?= $js ?>"></script>
    <?php endforeach; ?>
</head>
<body>
<?php if (!($no_header_footer ?? false)): ?>
<?php require "partials/_header.php"; ?>
<?php endif; ?>

<?= $content ?>

<?php if (!($no_header_footer ?? false)): ?>
<?php require "partials/_navbar.php"; ?>
<?php require "partials/_timebar.php"; ?>
<?php endif; ?>
</body>
</html>