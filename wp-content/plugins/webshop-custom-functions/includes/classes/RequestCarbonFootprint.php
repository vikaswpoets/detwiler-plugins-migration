<?php

class RequestCarbonFootprint
{
    public function __construct()
    {
    }

	public
    static function cabling_add_carbon_footprint_analysis_popup()
    {
        wc_get_template('template-parts/request-a-carbon-footprint-analysis-popup.php', [], '', WBC_PLUGIN_DIR);
    }

	public static function gi_submit_carbon_footprint_callback() {
		try {
			$data = $_POST;

			if (
				!isset($data['_wp_carbon_footprint_analysis_nonce']) ||
				!wp_verify_nonce($data['_wp_carbon_footprint_analysis_nonce'], 'save_request_a_carbon_footprint_analysis')
			) {
				wp_send_json_error(['message' => 'Invalid submission. Please reload the page and try again.']);
			}

			$fields = [
				'cfp_first_name' => 'sanitize_text_field',
				'cfp_last_name' => 'sanitize_text_field',
				'cfp_company' => 'sanitize_text_field',
				'cfp_email' => 'sanitize_email',
				'cfp_industry' => 'sanitize_text_field',
				'cfp_analysis_request' => 'sanitize_textarea_field'
			];
			$form = [];
			foreach ($fields as $f => $sanitize) {
				$form[$f] = call_user_func($sanitize, $data[$f] ?? '');
			}
			if (!is_email($form['cfp_email'])) {
				wp_send_json_error(['message' => 'Please enter a valid email address.']);
			}

			$attachment = '';
			if (!empty($_FILES['cfp_file']['tmp_name'])) {
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				$uploaded = wp_handle_upload($_FILES['cfp_file'], ['test_form' => false]);
				if (!empty($uploaded['file'])) {
					$attachment = $uploaded['file'];
				}
			}

			$content = "";
			foreach ($form as $k => $v) {
				$label = ucwords(str_replace(['cfp_', '_'], ['', ' '], $k));
				$content .= "<p>$label: $v</p>";
			}

			$admin_email = get_option('admin_email');
			$subject = __('New Carbon Footprint Analysis Request', 'cabling');

			self::send_email($admin_email, $subject, $content, $attachment ? [$attachment] : []);

			// mail to Client
			$client_subject = __('Your Carbon Footprint Analysis Request Received', 'cabling');
			self::send_email($form['cfp_email'], $client_subject, $content, $attachment ? [$attachment] : []);

			$message = '<div class="alert alert-success d-flex align-items-center" role="alert"><i class="fa-solid fa-circle-check me-2"></i>
                <div>'. __('Thank you. Your request has been submitted successfully!', 'cabling') .'</div>
            </div>';

			wp_send_json_success(['message' => $message]);
		} catch (\Exception $e) {
			error_log($e->getMessage());
			wp_send_json_error(['message' => __('There was an error processing your request. Please try again.', 'cabling')]);
		}
	}

    private static function send_email($to, $subject, $content, $attachment = ''): void
    {
        $mailer = WC()->mailer();
        $mailer->recipient = $to;
        $type = 'template-parts/carbon-footprint-analysis-email.php';
        $body = self::get_email_html($subject, $mailer, $type, $content);
        $headers = "Content-Type: text/html\r\n";

        $mailer->send($to, $subject, $body, $headers, $attachment);
    }

    private static function get_email_html($heading, $mailer, $type, $content = ''): string
    {
        return wc_get_template_html($type, array(
            'email_heading' => $heading,
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $mailer,
            'content' => $content,
        ),
        '', WBC_PLUGIN_DIR);

    }

    public
    static function cabling_add_product_quote_popup()
    {
        wc_get_template('template-parts/request-a-carbon-footprint-analysis-popup.php', [], '', WBC_PLUGIN_DIR);
    }
}

new RequestCarbonFootprint();
