<?php $errors = $errors ?? ['full_name' => '', 'pseudo' => '', 'email' => '', 'iban' => '']; ?>


<form id="edit-form" action="edit_profile/save" method="post">
    <div class="section">
        <div class="section-header">Personal Information</div>
        <div class="section-content">
            <div class="form-group">
                <label class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" class="form-input<?= !empty($errors['full_name']) ? ' input-error' : '' ?>"
                       value="<?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                       <?php if (!empty($errors['full_name'])): ?>
                            <div class="error"><?= $errors['full_name'] ?></div>
                         <?php endif; ?>

                <p class="form-help">Your complete name as it should appear on your profile</p>
            </div>

            <div class="form-group">
                <label class="form-label">Username <span class="required">*</span></label>
                <input type="text" name="pseudo" class="form-input<?= !empty($errors['pseudo']) ? ' input-error' : '' ?>"
                       value="<?= htmlspecialchars($user['pseudo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                       <?php if (!empty($errors['pseudo'])): ?>
                            <div class="error"><?= $errors['pseudo'] ?></div>
                        <?php endif; ?>
                <p class="form-help">This will be your public display name on the platform</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Contact & Payment</div>
        <div class="section-content">
            <div class="form-group">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="text" name="email" class="form-input<?= !empty($errors['email']) ? ' input-error' : '' ?>"
                       value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                       <?php if (!empty($errors['email'])): ?>
    <div class="error"><?= $errors['email'] ?></div>
<?php endif; ?>
                <p class="form-help">
                    We'll use this email to send you notifications about your sales and purchases
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">IBAN <span class="optional">(Optional)</span></label>
                <?php $user_iban = $user['iban'] ?? ''; ?>
                <input type="text" name="iban" class="form-input<?= !empty($errors['iban']) ? ' input-error' : '' ?>"
                       value="<?= htmlspecialchars($user_iban, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="BE99 9999 9999 9999 9999">
                <?php if (!empty($errors['iban'])): ?>
                    <div class="error"><?= $errors['iban'] ?></div>
                <?php endif; ?>
                <p class="form-help">
                    For receiving payments from your sales. Leave empty if you don't want to
                    receive payments directly.
                </p>
            </div>
        </div>
    </div>
</form>