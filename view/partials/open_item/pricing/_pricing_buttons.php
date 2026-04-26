<?php
if (!$show_buttons) return;
?>
<div id="bid-feedback" class="bid-feedback" style="display:none;"></div>

<?php if ($is_auction): ?>
<form class="bid-form" method="post" action="bid/create">
<input type="hidden" name="encoded_state" value="<?= $encoded_state ?? '' ?>">
    <input type="hidden" name="item_id" value="<?= $item_id ?>">
    <div class="bid-input-group">
        <span>€</span>
        <input type="number" name="amount" step="0.01" 
               min="<?= $min_bid_amount ?>" value="<?= $min_bid_amount ?>" 

               required 
               <?= $buttons_disabled ? 'disabled' : '' ?>>
    </div>
    <button type="submit" class="btn-place-bid" <?= $buttons_disabled ? 'disabled' : '' ?>>
        Place Bid
    </button>
</form>
<?php endif; ?>


<?php if ($has_buy_now_price): ?>
<form method="post" action="bid/create">
<input type="hidden" name="encoded_state" value="<?= $encoded_state ?? '' ?>">
    <input type="hidden" name="item_id" value="<?= $item_id ?>">
    <input type="hidden" name="amount" value="<?= $buy_now_price?>">
    

    
    <button type="submit" class="<?= $buy_now_btn_class ?>" <?= $buttons_disabled ? 'disabled' : '' ?>>
        <?= $buy_now_btn_text  ?>
    </button>
</form>
<?php endif; ?>