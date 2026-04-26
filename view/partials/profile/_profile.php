<div class="user-card shadow-sm p-3 mb-4 rounded text-center">
    <?php if (!empty($current_user->picture_path)): ?>
        <img src="<?= $web_root . $current_user->picture_path ?>" alt="Profile"
            class="user-avatar img-thumbnail rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
    <?php else: ?>
        <div class="user-avatar-placeholder bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
            style="width: 80px; height: 80px;">
            <i class="bi bi-person-fill text-secondary fs-1"></i>
        </div>
    <?php endif; ?>
    <h2 class="user-name h5 mb-0"><?= $current_user->full_name ?></h2>
    <p class="user-pseudo mb-1 small">@<?= $current_user->pseudo ?></p>
    <p class="user-email text-primary small mb-0"><?= $current_user->get_email() ?></p>
</div>

<div class="section mb-4">
    <div class="section-header h6 text-uppercase fw-bold mb-3 border-bottom pb-2">My Activities</div>

    <a href="sales/index"
        class="menu-item list-group-item list-group-item-action d-flex align-items-center border rounded mb-2 py-3">
        <div class="menu-icon me-3 text-primary"><i class="bi bi-file-earmark-text fs-4"></i></div>
        <div class="menu-content">
            <div class="menu-title fw-bold">Sales</div>
            <div class="menu-desc small">Items you have sold</div>
        </div>
    </a>

    <a href="purchases/index"
        class="menu-item list-group-item list-group-item-action d-flex align-items-center border rounded mb-2 py-3">
        <div class="menu-icon me-3 text-success"><i class="bi bi-cart-fill fs-4"></i></div>
        <div class="menu-content">
            <div class="menu-title fw-bold">Purchases</div>
            <div class="menu-desc small">Items you have purchased</div>
        </div>
    </a>
</div>

<div class="section mb-4">
    <div class="section-header h6 text-uppercase fw-bold mb-3 border-bottom pb-2">Account Settings</div>

    <a href="edit_profile/index"
        class="menu-item list-group-item list-group-item-action d-flex align-items-center border rounded mb-2 py-3">
        <div class="menu-icon me-3 text-info"><i class="bi bi-pencil-fill fs-4"></i></div>
        <div class="menu-content">
            <div class="menu-title fw-bold">Edit Profile</div>
            <div class="menu-desc small">Update your personal information</div>
        </div>
    </a>

    <a href="user/change_password"
        class="menu-item list-group-item list-group-item-action d-flex align-items-center border rounded mb-2 py-3">
        <div class="menu-icon me-3 text-secondary"><i class="bi bi-key-fill fs-4"></i></div>
        <div class="menu-content">
            <div class="menu-title fw-bold">Change Password</div>
            <div class="menu-desc small">Update your account security</div>
        </div>
    </a>

    <a href="profile_picture/index"
        class="menu-item list-group-item list-group-item-action d-flex align-items-center border rounded mb-2 py-3">
        <div class="menu-icon me-3 text-warning"><i class="bi bi-camera-fill fs-4"></i></div>
        <div class="menu-content">
            <div class="menu-title fw-bold">Profile Picture</div>
            <div class="menu-desc small">Upload or change your profile picture</div>
        </div>
    </a>

    <a href="profile/logout"
        class="menu-item logout list-group-item list-group-item-action d-flex align-items-center border border-danger rounded py-3">
        <div class="menu-icon me-3 text-danger"><i class="bi bi-box-arrow-right fs-4"></i></div>
        <div class="menu-content">
            <div class="menu-title fw-bold text-danger">Logout</div>
            <div class="menu-desc small">Sign out of your account</div>
        </div>
    </a>
</div>