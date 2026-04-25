<?php ob_start() ?>

<div class="edit-profile-page">
    <main>
        <?php require "partials/edit_profile/_edit_profile.php"; ?>
    </main>
</div>

<?php $content = ob_get_clean() ?>
<?php require 'view_layout.php' ?>