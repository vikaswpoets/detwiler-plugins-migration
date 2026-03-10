<div class="modal fade" id="carbonFootprintAnalysisModal" tabindex="-1" aria-labelledby="carbonFootprintAnalysisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content p-3" style="border-radius: 0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="carbonFootprintAnalysisModalLabel">
                    Request a Carbon Footprint Analysis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-help">
                    <p>
                    At Datwyler, we are committed to transparency, sustainability, and measurable impact.
                    Our Carbon Footprint Analysis helps you assess the environmental footprint of your elastomer components—so you can make informed decisions, reduce emissions, and meet your sustainability goals.
                    Whether you’re in mobility, energy, medical, or industrial sectors, we offer material-specific insights based on real production data from our certified, renewable-powered sites.
                    </p>
                    <p>Contact Us for Your Custom Analysis</p>
                    <p>Please fill out the form below. A Datwyler expert will respond within 2 business days.</p>
                </div>
                <form id="carbonFootprintForm" method="post" enctype="multipart/form-data" autocomplete="on">
                    <div class="mb-3">
                        <label for="cfp_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cfp_first_name" name="cfp_first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="cfp_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cfp_last_name" name="cfp_last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="cfp_company" class="form-label">Company <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cfp_company" name="cfp_company" required>
                    </div>
                    <div class="mb-3">
                        <label for="cfp_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="cfp_email" name="cfp_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="cfp_industry" class="form-label">Industry / Application Area <span class="text-danger">*</span></label>
                        <select class="form-select" id="cfp_industry" name="cfp_industry" required>
                            <option value="" disabled selected>Please select</option>
                            <option>Medical</option>
                            <option>Mobility</option>
                            <option>Energy</option>
                            <option>Aerospace</option>
                            <option>Industrial</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cfp_analysis_request" class="form-label">What would you like analyzed? <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cfp_analysis_request" name="cfp_analysis_request" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="cfp_file" class="form-label">Upload supporting documents (optional)</label>
                        <input class="form-control" type="file" id="cfp_file" name="cfp_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </div>
                    <?php wp_nonce_field('save_request_a_carbon_footprint_analysis', '_wp_carbon_footprint_analysis_nonce') ?>
                    <button type="submit" class="btn btn-primary w-100">SUBMIT REQUEST</button>
                </form>
                <div class="mt-3 small text-muted">
                    Need help now? <a href="mailto:info@datwyler.com">Contact us.</a>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
