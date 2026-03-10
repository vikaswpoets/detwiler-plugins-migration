<?php
$sellable = 0;
if (isset($_REQUEST['data-history'])) {
    $data_history = base64_decode($_REQUEST['data-history']);
    if($data_history){
        $data_history = json_decode($data_history,true);
        if( isset($data_history['attributes']) ){
            $attributes = $data_history['attributes'];
            if( isset($attributes['sellable']) && isset($attributes['sellable'][0])){
                $sellable = $attributes['sellable'][0];
            }
        }
    }
}
?>
<div class="filter-heading-blog mb-3">
    <div class="heading d-flex align-items-center justify-content-between mb-3">
    <div class="breadcrumbs-filter sellable-productlisting">
        <label class="sellable-text"><b>Welcome to Datwyler’s O-Ring selector and purchase platform.</b><br>
                A few tips to help get you started:<br>
        1. <b>Searching for O-Rings is easy</b> – just use the filters to the left.<br>
        2. Looking to <b>buy O-Rings online?</b> When filtering for <b>Buy Online</b>, you’ll find a comprehensive range of <b>standard O-Rings</b> available for <b>online purchasing</b>, delivered right to your door in the U.S. (Generic compounds are quoted)<br>
        3. Looking for O-Rings with a <b>specific Parco compound number</b>, or needing <b>delivery outside the U.S?</b>  Simply <div data-action="0" class="product-request-button show-product-quote" style="display:inline"><a class="" href="#"><?php _e('Request a quote', 'cabling'); ?></a></div> for a prompt reply.
        </div>
        <div class="total hidden">
            <i class="fa-light fa-sliders"></i>
            <?php printf(__('<span></span> RESULTS', 'cabling')) ?>
        </div>
    </div>
    <div id="imp-notice">
        <marquee direction="left" style="color:red; font-size:20px;"> Our online store is temporarily down — we're on it and will be back shortly. Thanks for your patience!</marquee>
    </div>
    <div id="filter-heading-product" class="filter-params d-flex align-items-center">
        <div class="item item-group-type clear-all me-2">Clear All<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>
    </div>
</div>
