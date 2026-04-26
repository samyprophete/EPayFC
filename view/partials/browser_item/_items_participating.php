  <div class="participating">
        <h2>Items I'm Participating in</h2>
        <div class="item-list"> 
            <?php if (!empty($participating_items)): ?>
                <?php foreach ($participating_items as $item): ?>
                    <div class="item-card" data-id="<?= $item['id'] ?>"
                    data-title="<?= $item['title']?? '' ?>"
                    data-seller="<?= $item['seller_pseudo']?? '' ?>"
                    data-description="<?= $item['description']?? '' ?>"> 
                        <a href="open_item/index/<?= $item['id'] ?>/<?= $encoded_state ?>/0">
                            <?php if ($item['pic_path']): ?>
                                <img src="<?= $web_root . str_replace('.jpg', '_thumbnail.jpg', $item['pic_path']) ?>" class="item-image">
                            <?php else: ?>
                                <div class="no-pic">No Pic</div>
                            <?php endif; ?>
                            <div class="item-info">
                                <h3 class="item-title"><?= $item['title'] ?></h3>
                                
                                <div class="item-labels">
                                    <?php if ($item['is_highest_bidder']): ?>
                                        <span class="label label-highest-bidder">Highest Bidder</span>
                                    <?php elseif ($item['has_bid']): ?>
                                        <span class="label label-bidder">Bidder</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['is_auction']): ?>
                                        <span class="label label-auction">Auction</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['has_buy_now']): ?>
                                        <span class="label label-buy-now">Buy Now</span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-details">
                                    <?php if ($item['picture_count'] > 0): ?>
                                        <span class="picture-count">
                                            <i class="bi bi-images"></i>
                                            <?= $item['picture_count'] ?> image<?= $item['picture_count'] > 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                    
                                    <span class="seller">Listed by <?= $item['seller_pseudo'] ?></span>
                                </div>
                                
                                <div class="item-pricing">
                                    <?php if ($item['has_buy_now'] && $item['buy_now_price']): ?>
                                        <span class="price">€<?= number_format($item['buy_now_price'], 2, '.', '') ?></span>
                                    <?php elseif ($item['starting_bid']): ?>
                                        <span class="price">€<?= number_format($item['starting_bid'], 2, '.', '') ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['max_bid']): ?>
                                        <span class="max-bid">Highest: €<?= number_format($item['max_bid'], 2, '.', '') ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-time">
                                    <span class="time-remaining"><?= $item['time_remaining'] ?> left</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No active bids yet.</p>
            <?php endif; ?>
        </div>
    </div>