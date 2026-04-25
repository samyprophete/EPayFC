<?php ob_start(); ?>

<div class="profile-picture-page">

    <!-- CURRENT -->
    <section class="card">
        <div class="card-header">Current Profile Picture</div>

        <div class="current-picture">
            <img src="<?= $currentUser->get_picture_path() ?? 'assets/avatar-default.png' ?>" alt="Profile">
            <div>
                <strong><?= $currentUser->get_FullName() ?></strong>
                <span>@<?= $currentUser->get_pseudo() ?></span>
            </div>
        </div>
    </section>

    <!-- UPLOAD -->
    <section class="card">
        <h2>Upload New Picture</h2>

        <form action="profile_picture/upload" method="post" enctype="multipart/form-data">
            <label>Select Image *</label>
            <input type="file" name="picture" required>

            <small>JPG, PNG, GIF, WebP</small>

            <button class="btn primary">
                Upload Picture
            </button>
        </form>
    </section>

    <!-- DELETE -->
    <?php if ($currentUser->get_picture_path()): ?>
        <section class="card danger">
            <h2>Remove Current Picture</h2>
            <p>This will permanently delete your current profile picture.</p>

            <a href="profile_picture/delete" class="btn danger">
                Delete Picture
            </a>
        </section>
    <?php endif; ?>

</div>

<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>
