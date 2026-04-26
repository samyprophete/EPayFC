<?php ob_start(); ?>

<div class="content-wrapper">
    <main class="main-content">

        <section class="page-header">
            <div class="page-header-text">
                <h2 class="page-title">Completed Sales</h2>
                <p class="page-subtitle">A snapshot of the deals you've wrapped up.</p>
            </div>
            <span class="badge badge-green">
                <?= $stats["count"] ?> sale<?= $stats["count"] !== 1 ? 's' : '' ?>
            </span>
        </section>

        <section class="stats-row">
            <div class="stat-box">
                <span class="stat-label">TOTAL REVENUE</span>
                <span class="stat-value"><?= format_euro($stats["total"]) ?></span>
                <span class="stat-desc">Across <?= $stats["count"] ?> sale<?= $stats["count"] !== 1 ? 's' : '' ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">AVERAGE TICKET</span>
                <span class="stat-value"><?= format_euro($stats["average"]) ?></span>
                <span class="stat-desc">Median buyer appetite indicator</span>
            </div>
            <div class="stat-box">
                <span class="stat-label"><strong>LOYAL BIDDER</strong></span>
                <span class="stat-value stat-value-text"><?= $stats["loyal_bidder"] ?? 'N/A' ?></span>
                <span class="stat-desc">Most recurring winning bidder</span>
            </div>
        </section>

        <?php if (!empty($items)): ?>
            <section class="sales-grid">
                <?php foreach ($items as $item): ?>
                    <?php
                    $pictures  = ItemPicture::get_all_by_item($item->get_Id());
                    $main_pic  = !empty($pictures) ? $pictures[0] : null;
                    $pic_path  = $main_pic ? $main_pic->picture_path : null;
                    $thumb_url = $pic_path ? str_replace('.jpg', '_thumbnail.jpg', $pic_path) : '';
                    $closed_at = $item->get_sold_at();
                    ?>
                    <div class="sale-card">
                        <a href="open_item/index/<?= $item->get_Id() ?>/sales/0" class="sale-card-link">
                            <div class="sale-card-image">
                                <?php if (!empty($thumb_url)): ?>
                                    <img src="<?= $thumb_url ?>" alt="">
                                <?php else: ?>
                                    <div class="no-pic">No Image</div>
                                <?php endif; ?>
                                <div class="sale-card-labels">
                                    <?php if ($item->get_Is_Auction()): ?>
                                        <span class="label label-auction"><i class="bi bi-hammer"></i> Auction</span>
                                    <?php endif; ?>
                                    <?php if ($item->get_Has_buy_now_price()): ?>
                                        <span class="label label-buy-now"><i class="bi bi-bag"></i> Buy Now</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (count($pictures) > 0): ?>
                                    <span class="picture-badge"><i class="bi bi-images"></i> <?= count($pictures) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="sale-card-info">
                                <h3><?= $item->get_title() ?></h3>
                                <p class="seller">by <?= $item->get_seller()->get_Pseudo() ?></p>
                                <div class="pricing">
                                    <span class="price"><?= format_euro($item->get_buy_now_price() ?? $item->get_starting_bid()) ?></span>
                                    <?php if ($item->get_max_bid_time()): ?>
                                        <span class="current-bid">
                                            Current bid<br>
                                            <strong><?= format_euro($item->get_max_bid_time()) ?></strong>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="status"><i class="bi bi-clock"></i> Closed</div>
                            </div>
                        </a>
                        <div class="sale-details">
                            <div class="detail-row">
                                <i class="bi bi-currency-dollar"></i>
                                <span>Final price <?= format_euro($item->get_max_bid_time() ?? $item->get_buy_now_price()) ?></span>
                            </div>
                            <div class="detail-row">
                                <i class="bi bi-trophy"></i>
                                <span><?= $item->get_highest_bidder_pseudo() ?? 'Unknown' ?></span>
                            </div>
                            <div class="detail-row">
                                <i class="bi bi-clock-history"></i>
                                <span>Closed on <?= $closed_at ? date('d/m/Y H:i:s', strtotime($closed_at)) : '' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <?php require "partials/sales/_no_sales.php"; ?>
        <?php endif; ?>

    </main>
</div>

<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>
