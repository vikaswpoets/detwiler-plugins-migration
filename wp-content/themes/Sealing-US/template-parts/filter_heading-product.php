<div class="filter-heading-blog mb-3">
    <div class="heading d-flex align-items-center justify-content-between mb-3">
	    <?php if (empty($args['hideWelcome'])): ?>
            <div class="breadcrumbs-filter sellable-productlisting sellable-for-oring"
                 style="'display:none'">
                <b>Welcome to Datwyler’s O-Ring selector and purchase platform.</b><br>
                A few tips to help get you started:<br>
                1. <b>Searching for O-Rings is easy</b> – just use the filters to the left.<br>
                2. Looking to <b>buy O-Rings online?</b> When filtering for <b>Buy Online</b>, you’ll find a
                comprehensive range of <b>standard O-Rings</b> available for <b>online purchasing</b>, delivered right
                to your door in the U.S. (Generic compounds are quoted)<br>
                3. Looking for O-Rings with a <b>specific Parco compound number</b>, or needing <b>delivery outside the
                    U.S?</b> Simply
                <div data-action="0" class="product-request-button show-product-quote" style="display:inline"><a
                            class="" href="#"><?php _e( 'Request a quote', 'cabling' ); ?></a></div>
                for a prompt reply.
            </div>
            <div class="breadcrumbs-filter sellable-productlisting sellable-for-doublee"
                 style="display:none">
                <b>Welcome to Datwyler’s Double E Production Equipment selector and purchase platform</b><br>
                A few tips to help get you started:<br>
                1. <b>Searching for a specific product or component?</b><br>
                From BOPs and Rod Guides, to Cap Kings and Stuffing Boxes, is easy – just use the filters to the
                left.<br>
                2. <b>In the US, and looking to buy?</b><br>
                When filtering for Buy Online, you’ll find a comprehensive range of BOPs, Rod Guides, SRSs and Cap King
                assemblies and components available for online purchasing, delivered right to your door in the U.S.</b>
                <br>
                3. <b>Not seeing what you need, or looking buy outside the US?</b><br>
                Simply
                <div data-action="0" class="product-request-button show-product-quote" style="display:inline"><a
                            class="" href="#"><?php _e( 'Request a quote', 'cabling' ); ?></a></div>
                for a prompt reply.
            </div>
	    <?php endif ?>
        <div class="total hidden">
            <i class="fa-light fa-sliders"></i>
            <?php printf(__('<span></span> RESULTS', 'cabling')) ?>
        </div>
    </div>
    <div id="filter-heading-product" class="filter-params d-flex align-items-center">
        <div class="item item-group-type clear-all me-2">Clear All<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>
    </div>
</div>
