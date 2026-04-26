<div class="sale-card">
    <a href="open_item/index/<?= (int)$card['item_id'] ?>/sales/0" class="sale-card-link">
        <div class="sale-card-image">
            <?php if (!empty($card['thumb_url'])): ?>
                <img src="<?= $card['thumb_url'] ?>" alt="">
            <?php else: ?>
                <div class="no-pic">No Image</div>
            <?php endif; ?>
            <div class="sale-card-labels">
                <?php if (!empty($card['is_auction'])): ?>
                    <span class="label label-auction"><i class="bi bi-hammer"></i> Auction</span>
                <?php endif; ?>
                <?php if (!empty($card['has_buy_now'])): ?>
                    <span class="label label-buy-now"><i class="bi bi-bag"></i> Buy Now</span>
                <?php endif; ?>
            </div>
            <?php if (($card['picture_count'] ?? 0) > 0): ?>
                <span class="picture-badge"><i class="bi bi-images"></i> <?= (int)$card['picture_count'] ?></span>
            <?php endif; ?>
        </div>
        <div class="sale-card-info">
            <h3><?= $card['title'] ?></h3>
            <p class="seller">by <?= $card['seller_pseudo'] ?></p>
            <div class="pricing">
                <span class="price"><?= format_euro($card['display_price']) ?></span>
                <?php if (!empty($card['max_bid'])): ?>
                    <span class="current-bid">
                        Current bid<br>
                        <strong><?= format_euro($card['max_bid']) ?></strong>
                    </span>
                <?php endif; ?>
            </div>
            <div class="status"><i class="bi bi-clock"></i> Closed</div>
        </div>
    </a>
    <div class="sale-details">
        <div class="detail-row">
            <i class="bi bi-currency-dollar"></i>
            <span>Final price <?= format_euro($card['max_bid'] ?? $card['display_price']) ?></span>
        </div>
        <div class="detail-row">
            <i class="bi bi-trophy"></i>
            <span><?= $card['winner_pseudo'] ?? 'Unknown' ?></span>
        </div>
        <div class="detail-row">
            <i class="bi bi-clock-history"></i>
            <span>Closed on <?= $card['closed_at_formatted'] ?></span>
        </div>
    </div>
</div>