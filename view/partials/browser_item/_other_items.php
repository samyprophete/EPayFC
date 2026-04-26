<div class="available">
    <h2>Other Available Items</h2> 
    <div class="item-list">
        <?php if (!empty($available_items)): ?>
            <?php 
                // Extraction de la règle métier depuis la config
                $precision = (int)($config['Rules']['decimal_precision'] ?? 2); 
            ?>
            <?php foreach ($available_items as $item): ?>
                <div class="item-card" data-id="<?= $item['id'] ?>"
                    data-title="<?= strtolower($item['title']) ?>"
                    data-seller="<?= strtolower($item['seller_pseudo']) ?>"
                    data-description="<?= strtolower($item['description']) ?>">
                    <a href="open_item/index/<?= $item['id'] ?>/<?= $encoded_state ?>/0">
                        <?php if ($item['pic_path']): ?>
                            <?php $thumb = str_replace('.jpg', '_thumbnail.jpg', $item['pic_path']); ?>
                            <img src="<?= $web_root . $thumb ?>" class="item-image">
                        <?php else: ?>
                            <div class="no-pic">No Pic</div>
                        <?php endif; ?>

                        <div class="item-info">
                            <h3 class="item-title"><?= $item['title'] ?></h3>
                            
                            <div class="item-labels">
                                <?php if ($item['is_auction']): ?>
                                    <span class="label label-auction">Auction</span>
                                <?php endif; ?>
                                
                                <?php if ($item['is_direct_sale']): ?>
                                    <span class="label label-direct-sale">Direct Sale</span>
                                <?php endif; ?>
                                
                                <?php if ($item['has_buy_now']): ?>
                                    <span class="label label-buy-now">Buy Now</span>
                                <?php endif; ?>
                            </div>

                            <div class="item-details">
                                <?php if ($item['picture_count'] > 0): ?>
                                    <?php $plural = $item['picture_count'] > 1 ? 's' : ''; ?>
                                    <span class="picture-count">
                                        <i class="bi bi-images"></i>
                                        <?= $item['picture_count'] ?> image<?= $plural ?>
                                    </span>
                                <?php endif; ?>
                                
                                <span class="seller">
                                    Listed by <?= $item['seller_pseudo'] ?>
                                </span>
                            </div>
                            
                            <div class="item-pricing">
                                <?php if ($item['has_buy_now'] && $item['buy_now_price']): ?>
                                    <?php $bn_price = number_format($item['buy_now_price'], $precision, '.', ''); ?>
                                    <span class="price">€<?= $bn_price ?></span>
                                <?php elseif ($item['starting_bid']): ?>
                                    <?php $st_price = number_format($item['starting_bid'], $precision, '.', ''); ?>
                                    <span class="price">€<?= $st_price ?></span>
                                <?php endif; ?>
                                
                                <?php if ($item['max_bid']): ?>
                                    <?php $max_bid = number_format($item['max_bid'], $precision, '.', ''); ?>
                                    <span class="max-bid">Highest: €<?= $max_bid ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-time">
                                <span class="time-remaining">
                                    <?= $item['time_remaining'] ?> left
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No other items available.</p>
        <?php endif; ?>
    </div>
</div>