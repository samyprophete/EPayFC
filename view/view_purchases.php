<!---
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Purchases</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/purchases.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
-->
<?php ob_start(); ?>





<div class="purchases-page">

        <div class="page-top-row">
            <div class="page-left">
                <h1 class="page-title">My Purchases</h1>
                <p class="page-subtitle">
                    Track the gear you've successfully secured.
                </p>
            </div>

                    <div class="page-right">
            <span class="badge-count">
                <?= $stats["count"] ?> purchases
            </span>
        </div>

        </div>



    <?php
    function euro(float $v): string {
        return "€ " . number_format($v, 2, ",", " ");
    }
    ?>

    <section class="stats">
        <div class="stat-card">
            <span class="label">TOTAL SPENT</span>
            <span class="value"><?= euro($stats["total"]) ?></span>
            <span class="sub">Across <?= $stats["count"] ?> purchases</span>
        </div>

        <div class="stat-card">
            <span class="label">AVERAGE TICKET</span>
            <span class="value"><?= euro($stats["average"]) ?></span>
            <span class="sub">Helps you plan future bids</span>
        </div>

        <div class="stat-card">
            <span class="label">TOP SELLER</span>
            <span class="value"><?= $stats["top_seller"] ?? "—" ?></span>
            <span class="sub">Who you've bought from the most</span>
        </div>
    </section>

    <?php if (!empty($items)): ?>
        <section class="items-section">
            <div class="items-grid">

                <?php foreach ($items as $item): ?>
                    <?php
                    $pic = ItemPicture::get_main_picture($item->get_id());
                    $img = $pic ? $pic->picture_path : "assets/no-image.png";
                    ?>

                    <div class="item-card closed">
                        <a href="open_item/index/<?= $item->get_Id() ?>/0/purchases" style="display:block;text-decoration:none;color:inherit;">

                        <div class="image-wrapper">
                            <img src="<?= $img ?>" alt="">

                            <span class="badge success">
                                <i class="bi bi-trophy"></i> Highest Bidder
                            </span>

                            <span class="images-count">
                                <i class="bi bi-images"></i>
                                <?= count(ItemPicture::get_all_by_item($item->get_Id())) ?> images
                            </span>
                        </div>

                        <div class="item-body">
                            <h3><?= $item->get_title() ?></h3>
                            <p class="seller">by <?= $item->get_seller()->get_pseudo() ?></p>

                            <div class="price">
                                <span class="main"><?= euro($item->get_max_bid_time()) ?></span>
                                <span class="sub">Current bid</span>
                            </div>

                            <div class="item-footer">
                                <div>
                                    <i class="bi bi-tag"></i>
                                    Paid <?= euro($item->get_max_bid_time()) ?>
                                </div>
                                <div>
                                    <i class="bi bi-clock"></i>
                                    Closed on <?= date("d/m/Y H:i:s", strtotime($item->get_end_at())) ?>
                                </div>
                            </div>
                        </div>

                        </a>
                    </div>
                <?php endforeach; ?>

            </div>
        </section>
    <?php endif; ?>

</div>


    

<?php $content = ob_get_clean(); ?>
<?php require "view_layout.php"; ?>

