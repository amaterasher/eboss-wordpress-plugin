<?php
require_once 'api/eBossApiClient.php';

//shortcodes for listings
add_shortcode('eboss_v3_list_jobs', 'eboss_v3_list_jobs_func');
add_shortcode('eboss_v3_list_news', 'eboss_v3_list_news_func');

//shortcodes for forms
add_shortcode('eboss_v3_candidate_reg_form', 'eboss_v3_candidate_reg_form_func');
add_shortcode('eboss_v3_client_registration_form', 'eboss_v3_client_reg_form_func');


add_shortcode('eboss_v3_job_detail', 'eboss_v3_job_detail_func');
add_shortcode('eboss_v3_job_search', 'eboss_v3_job_search_func');

add_shortcode('eboss_v3_account_login', 'eboss_v3_account_login_func');
add_shortcode('eboss_v3_account_logout', 'eboss_v3_account_logout_func');
add_shortcode('eboss_v3_candidate_profile', 'eboss_v3_candidate_profile_func');
add_shortcode('eboss_v3_account_settings', 'eboss_v3_account_settings_func');


function eboss_v3_list_jobs_func()
{
		ob_start();
		include 'templates/job-listing.php';
		$content = ob_get_clean();
		return $content;
}

function eboss_v3_list_news_func()
{
		ob_start();
		include 'templates/news-listing.php';
		$content = ob_get_clean();
		return $content;

}

function eboss_v3_candidate_reg_form_func()
{
		ob_start();
		include 'templates/candidate-register-form.php';
		$content = ob_get_clean();
		return $content;
}

function eboss_v3_client_reg_form_func()
{
		ob_start();
		include 'templates/client-register-form.php';
		$content = ob_get_clean();
		return $content;
}

function eboss_v3_job_detail_func()
{
		ob_start();
		include 'templates/job-details.php';
		$content = ob_get_clean();
		return $content;
}

function eboss_v3_job_search_func()
{
		ob_start();
		include 'templates/job-search-form.php';
		$content = ob_get_clean();
		return $content;
}

function eboss_v3_account_login_func()
{
		ob_start();
		include 'templates/account-login-form.php';
		$content = ob_get_clean();
		return $content;
}

function eboss_v3_account_logout_func()
{
		if (isset($_SESSION['eboss']['user']) && $_SESSION['eboss']['user'] == 'candidate') {
				unset($_SESSION['eboss']);
				unset($_SESSION['eboss_key_pass']);

				session_destroy();
				wp_redirect(site_url());
		} else {
				wp_redirect(site_url());
		}

}

function eboss_v3_candidate_profile_func()
{
		if (isset($_SESSION['eboss']['user']) && $_SESSION['eboss']['user'] == 'candidate')
		{
				ob_start();
				include 'templates/candidate-profile.php';
				$content = ob_get_clean();
				return $content;
		}
		else
		{
				wp_redirect(site_url());
		}

}

function eboss_v3_account_settings_func()
{
		if (isset($_SESSION['eboss']['user']) && $_SESSION['eboss']['user'] == 'candidate')
		{
				ob_start();
				include 'templates/candidate-account.php';
				$content = ob_get_clean();
				return $content;
		}
		else
		{
				wp_redirect(site_url());
		}
}

