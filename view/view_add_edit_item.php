<?php ob_start(); ?>

<div class="page">

    <script>window.APP_BASE = '<?= $web_root ?>';</script>

    <form id="item-form"
          method="post"
          action="item/add_edit_item<?= $item_id ? "/$item_id" : "" ?>"
          data-item-id="<?= $item_id ?? '' ?>">
        <input type="hidden" name="from" value="<?= $from ?? 'my_items' ?>">

        <section class="card">
            <h2>Basic Information</h2>

            <div class="field" id="field-title">
                <label>Item Title *</label>
                <input type="text" name="title" value="<?= $title ?>"
                       placeholder="Ex: iPhone 13 Pro Max 256GB">
                <?php if (!empty($errors["title"])): ?>
                    <div class="error"><?= $errors["title"] ?></div>
                <?php endif; ?>
            </div>

            <div class="field" id="field-description">
                <label>Description</label>
                <textarea name="description" rows="4"
                          placeholder="Describe your item in detail..."><?= $description ?></textarea>
                <?php if (!empty($errors["description"])): ?>
                    <div class="error"><?= $errors["description"] ?></div>
                <?php endif; ?>
            </div>

            <div class="field" id="field-duration">
                <label>Sale Duration (days) *</label>
                <input type="number" name="duration_days" value="<?= $duration_days ?>">
                <?php if (!empty($errors["duration_days"])): ?>
                    <div class="error"><?= $errors["duration_days"] ?></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <h2>Sale Type</h2>

            <div class="sale-box auction">
                <h3>⚖️ Option 1: Auction</h3>

                <div class="field" id="field-starting-bid">
                    <label>Starting Bid</label>
                    <input type="text" name="starting_bid" value="<?= $starting_bid ?>"
                           placeholder="e.g. 50.00">
                    <?php if (!empty($errors["starting_bid"])): ?>
                        <div class="error"><?= $errors["starting_bid"] ?></div>
                    <?php endif; ?>
                </div>

                <div class="field" id="field-buy-now">
                    <label>Instant Purchase Price (optional)</label>
                    <input type="text" name="buy_now_price" value="<?= $buy_now_price ?>"
                           placeholder="e.g. 200.00">
                    <?php if (!empty($errors["buy_now_price"])): ?>
                        <div class="error"><?= $errors["buy_now_price"] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sale-box direct">
                <h3>🛒 Option 2: Direct Sale</h3>

                <div class="field" id="field-sale-price">
                    <label>Sale Price</label>
                    <input type="text" name="sale_price" value="<?= $sale_price ?>"
                           placeholder="e.g. 150.00">
                    <?php if (!empty($errors["buy_now_price"])): ?>
                        <div class="error"><?= $errors["buy_now_price"] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </form>

    <div id="unsavedModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Unsaved changes</h3>
                <button type="button" class="close-modal" id="closeUnsavedCross">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>You have unsaved changes. Leave anyway?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelLeaveBtn">Cancel</button>
                <button type="button" class="btn-confirm" id="confirmLeaveBtn">Leave</button>
            </div>
        </div>
    </div>

</div>

<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>
