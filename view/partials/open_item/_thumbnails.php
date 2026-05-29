<?php if (count($pictures) > 1): ?>
<section class="additional-images-section">
    <h3 class="section-title">Additional Images</h3>

    <div class="thumbnail-gallery">
        <?php foreach ($pictures as $index => $picture): ?>
            <?php
            if (!empty($search_state)) {
                $thumb_href = ($index === 0)
                    ? 'open_item/index/' . (int) $item_id . '/' . urlencode($search_state)
                    : 'open_item/index/' . (int) $item_id . '/' . urlencode($search_state) . '/' . (int) $index;
            } else {
                $thumb_href = ($index === 0)
                    ? 'open_item/index/' . (int) $item_id
                    : 'open_item/index/' . (int) $item_id . '/0/' . (int) $index;
            }
            ?>
            <a href="<?= $thumb_href ?>">
                <img class="thumbnail <?= $selected_img === $index ? 'active' : '' ?>" 
                
                     src="<?= str_replace('.jpg', '_thumbnail.jpg', $picture->picture_path) ?>" 
                     alt="Thumbnail <?= $index + 1 ?>">
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>