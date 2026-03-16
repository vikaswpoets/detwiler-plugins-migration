<h2>Inventory, Lead Time and Pricing</h2>
<form id="webservice-api-form">
    <?php
//if customer has more than one sales organization
    //select from all sales orgs
    $userid=get_current_user_id();

    $AccountID = get_user_meta($userid, 'AccountID', true);
    $salesorgs = get_user_meta($userid, 'sales_org_lst', true); 
    if($AccountID && empty($salesorglst))
    {
        // retrieve list of sales org. for customer
        $crmxx = new CRMController();
        $salesorglst=$crmxx->getMultipleSalesOrganization($AccountID);
        //$salesorglst=$crmxx->getMultipleSalesOrganization(1004628);
        if($salesorglst){
            update_user_meta($userid, 'sales_org_lst', $salesorglst);
        }
    }   
    //print_r($salesorgs);
    $c=0;
    //check if user has access to both Parco and Double E
    foreach($salesorgs as $org)
    {
        if($org['id']==2141 || $org['id']==2142){
            $c++;
        }
    }

    if(!empty($salesorgs) && count($salesorgs)>1 && $c>1)
    {
        //echo "<p><strong>Select the factory</strong></p>";
        ?>
        <p><strong>Select Origin</strong></p>
        <div class="form-group">
            <select  class="form-control chosen-select" name="api[salesorg]" id="salesorg">
        <?php 
        foreach($salesorgs as $org)
        {
            if($org['id']==2141 || $org['id']==2142){
                echo "<option value=\"".$org['id']."\">".$org['name']??$org['id']."</option>";
            }
        }
    ?>            
        </select>
    </div>
    <?php
    }
    ?>
    <p class="form-error-text alert alert-danger mb-4 hidden"><i class="fa-solid fa-triangle-exclamation me-2"></i>Please fill out the filter</p>
    <p class="parcocompound-text alert alert-danger mb-4 hidden"><i class="fa-solid fa-triangle-exclamation me-2"></i>Please fill out the Part Number and Compound Number</p>
    <p><strong>For Datwyler’s Parco O-Rings, you can use either the SKU / material number or part and compound number</strong></p>
    <div class="form-group">
        <input type="text" class="form-control" name="api[SoldToParty]" id="SoldToParty"
               value="<?php echo get_user_meta(get_current_user_id(), 'sap_customer', true)?>"
               disabled
        >
        <label for="SoldToParty" class="form-label">Customer</label>
    </div>
    <p><strong>Search by Material Number</strong></p>
    <div class="form-group">
        <input type="text" class="form-control" name="api[Material]" id="parcomaterial">
        <label for="parcomaterial" class="form-label">Material / SKU</label>
    </div>
    <p><strong>Or search both Part and Compound Numbers. The Compound Number is normally 6 digits – please make sure to leave out any dashes. The Part Number is normally 7-8 digits, but If you’re searching for a Parco AS568 O-ring, you can just enter the 3- or 4-digit size number. Here too – please leave out any dashes.</strong></p>
    <div class="form-group">
        <input type="text" class="form-control" name="api[MaterialOldNumber]" id="sapMaterial">
        <label for="sapmaterial" class="form-label">Part Number</label>
    </div>
    <div class="form-group mb-1">
        <input type="text" class="form-control" name="api[BasicMaterial]" id="parcocompound">
        <label for="parcocompound" class="form-label">Compound Number</label>
    </div>
    <!--<div class="form-group hidden">
        <label for="date" class="form-label">Date Interval</label>
        <input type="date" class="form-control date-picker" name="api[due_date]" id="date">
    </div>-->
    <button type="submit" class="block-button mt-3">Submit</button>
    <input type="hidden" name="api_service" value="GET_DATA_PRICE">
    <input type="hidden" name="api_page" value="<?php echo CABLING_INVENTORY ?>">
</form>
<hr>
<div id="api-results"></div>
