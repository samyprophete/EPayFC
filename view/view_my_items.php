<?php require_once "model/ItemPicture.php"; ?>

<?php
function euro(float $v): string
{
    return "€ " . number_format($v, 2, ",", " ");
}

function time_left(?string $endAt): string
{
    if (!$endAt)
        return "";
    $now = new DateTime(AppTime::get_current_datetime());
    $end = new DateTime($endAt);
    if ($end <= $now)
        return "Ended";

    $diff = $now->diff($end);
    if ($diff->days > 0)
        return "{$diff->days}d {$diff->h}h left";
    if ($diff->h > 0)
        return "{$diff->h}h {$diff->i}m left";
    return "{$diff->i}m left";
}
?>

<?php ob_start(); ?>

<div class="my-items-page">

    <div class="search-bar">
        <input type="text" id="search-input" placeholder="Search item" class="search-input"
            value="<?= $search_query ?>">
        <i class="bi bi-search search-icon"></i>
    </div>

    <!-- ACTIVE ITEMS -->
    <?php if (!empty($active_items)): ?>
        <section class="items-section">
            <h2>Active Items</h2>

            <div class="items-grid" id="active-items-container">
                <?php foreach ($active_items as $item): ?>
                    <?php
                    $pic = ItemPicture::get_main_picture($item->get_id());
                    $img = $pic ? $pic->picture_path : "assets/no-image.png";
                    ?>
                    <div class="item-card" data-id="<?= $item->get_id() ?>"
                        onclick="window.location='open_item/index/<?= $item->get_id() ?>/<?= $encoded_state ?>/0'"
                        data-title="<?= $item->get_title() ?? '' ?>"
                        data-seller="<?= $item->get_seller()->get_Pseudo() ?? '' ?>"
                        data-description="<?= $item->get_Description() ?? '' ?>">

                        <div class="image-wrapper">
                            <div class="item-labels">
                                <?php if ($item->get_Is_Auction()): ?>
                                    <span class="label label-auction">Auction</span>
                                <?php endif; ?>
                                
                                <?php if ($item->get_Has_buy_now_price()): ?>
                                    <span class="label label-buy-now">Buy Now</span>
                                <?php endif; ?>
                            </div>

                            <img src="<?= $img ?>" alt="">
                            <span class="images-count">
                                <i class="bi bi-images"></i>
                                <?php $count = count(ItemPicture::get_all_by_item($item->get_Id())); ?>
                                <?= $count ?> image<?= $count > 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <div class="item-body">
                            <h3 class="item-title"><?= $item->get_title() ?></h3>
                            
                            <div class="item-details">
                                <span class="seller">Listed by <?= $item->get_seller()->get_Pseudo() ?></span>
                            </div>

                            <div class="item-pricing">
                                <span class="price"><?= euro($item->get_buy_now_price() ?? $item->get_starting_bid()) ?></span>
                                <?php if ($item->get_max_bid_time()): ?>
                                    <span class="max-bid">Highest: <?= euro($item->get_max_bid_time()) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="item-time">
                                <i class="bi bi-clock"></i>
                                <?= time_left($item->get_end_at()) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- CLOSED UNSOLD -->
    <?php if (!empty($closed_unsold_items)): ?>
        <section class="items-section">
            <h2>Closed · Unsold Items</h2>

            <div class="items-grid" id="active-items-container">
                <?php foreach ($closed_unsold_items as $item): ?>
                    <?php
                    $pic = ItemPicture::get_main_picture($item->get_id());
                    $img = $pic ? $pic->picture_path : "assets/no-image.png";
                    ?>
                    <div class="item-card closed" data-id="<?= $item->get_id() ?>"
                        onclick="window.location='open_item/index/<?= $item->get_id() ?>/<?= $encoded_state ?>/0'">

                        <div class="image-wrapper">
                            <div class="item-labels">
                                <?php if ($item->get_Is_Auction()): ?>
                                    <span class="label label-auction">Auction</span>
                                <?php endif; ?>
                                
                                <?php if ($item->get_Has_buy_now_price()): ?>
                                    <span class="label label-buy-now">Buy Now</span>
                                <?php endif; ?>
                            </div>
                            <img src="<?= $img ?>" alt="">
                        </div>

                        <div class="item-body">
                            <h3 class="item-title"><?= $item->get_title() ?></h3>
                            
                            <div class="item-details">
                                <span class="seller">Listed by <?= $item->get_seller()->get_Pseudo() ?></span>
                            </div>

                            <div class="item-pricing">
                                <span class="price"><?= euro($item->get_buy_now_price() ?? $item->get_starting_bid()) ?></span>
                                <?php if ($item->get_max_bid_time()): ?>
                                    <span class="max-bid">Highest: <?= euro($item->get_max_bid_time()) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="item-time ended">
                                <i class="bi bi-x-circle"></i>
                                Not sold
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- SOLD ITEMS -->
    <?php if (!empty($sold_items)): ?>
        <section class="items-section">
            <h2>Sold Items</h2>

            <div class="items-grid" id="active-items-container">
                <?php foreach ($sold_items as $item): ?>
                    <?php
                    $pic = ItemPicture::get_main_picture($item->get_id());
                    $img = $pic ? $pic->picture_path : "assets/no-image.png";
                    ?>
                    <div class="item-card sold" data-id="<?= $item->get_id() ?>"
                        onclick="window.location='open_item/index/<?= $item->get_id() ?>/<?= $encoded_state ?>/0'">

                        <div class="image-wrapper">
                            <div class="item-labels">
                                <?php if ($item->get_Is_Auction()): ?>
                                    <span class="label label-auction">Auction</span>
                                <?php endif; ?>
                                
                                <?php if ($item->get_Has_buy_now_price()): ?>
                                    <span class="label label-buy-now">Buy Now</span>
                                <?php endif; ?>
                            </div>
                            <img src="<?= $img ?>" alt="">
                        </div>

                        <div class="item-body">
                            <h3 class="item-title"><?= $item->get_title() ?></h3>
                            
                            <div class="item-details">
                                <span class="seller">Listed by <?= $item->get_seller()->get_Pseudo() ?></span>
                            </div>

                            <div class="item-pricing">
                                <span class="price"><?= euro($item->get_max_bid_time()) ?></span>
                                <span class="max-bid">Final price</span>
                            </div>

                            <div class="item-time sold">
                                <i class="bi bi-check-circle"></i>
                                Sold
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>
