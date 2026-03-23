<div class="product-overview mb-3">
    <h4><?php echo __('Overview', 'cabling'); ?></h4>
    <div class="pdp-attribute mb-3">
        <div class="row">
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $fields): ?>
                    <div class="col-12 col-lg-6">
                        <div class="overview-item">
                            <h5><?php echo $fields['label'] ?></h5>
                            <p class="value"><?php echo $fields['value'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif ?>
        </div>
    </div>
</div>
