<?php ob_start(); ?>

<main class="delete-confirm-container">
    <div class="delete-confirm-box">

        <div class="icon-delete-container">
            <i class="bi bi-trash"></i>
        </div>

        <h2>Are you sure?</h2>

        <hr class="divider-line">

        <p>Do you really want to delete item
            <strong>"<?= $item_title ?>"</strong>
            by <strong><?= $seller ? $seller_full_name : 'unknown' ?></strong>
            and all of its dependencies?
        </p>
        <p>This process cannot be undone.</p>

        <div class="action-buttons">
        <a href="<?= $back_url ?? ('open_item/index/' . $item_id) ?>" class="btn btn-cancel">Cancel</a>
            <form method="post" action="delete_confirm/confirm" style="display: inline;">
                <input type="hidden" name="encoded_state" value="<?=$encoded_state ?? '' ?>">
                <input type="hidden" name="item_id" value="<?= $item->get_Id() ?>">
                <button type="submit" class="btn btn-delete">Delete</button>
            </form>
        </div>
    </div>
</main>
<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>