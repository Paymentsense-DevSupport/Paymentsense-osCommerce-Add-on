<?php
/*
$Id$

OS-Commerce PaymentSense Re-Direct Payment Module
Copyright (C) 2018 PaymentSense.
Support: ecomsupport@paymentsense.com

------------------------

Last Updated: 26/10/2018

------------------------

Released under the GNU General Public License
*/
require_once('paymentsense_redirect/common.php');

class paymentsense_redirect {
	var $code, $title, $description, $enabled;

    function paymentsense_redirect() {
		
		global $order;		
		
		$code = "paymentsense_redirect";
		$title = "PaymentSense Redirect";
		$version = "2.5";
		$versiondate = "26 October 2018";
		
		$this->signature = 'paymentsense|'.$code.'|'.$version.'|2.5';
		$this->code = $code;
		$this->title = $title;
		$this->version = $version;
		$this->versiondate = $versiondate;
		$this->public_title = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_PUBLIC_TITLE;
		$this->description = "<b>PaymentSense Redirect Payments Module</b><br>For integration support Email the integrationsupport@paymentsense.com or visit the <a href=\"http://developers.paymentsense.co.uk/\">PaymentSense Developers Website</a>";
		$this->sort_order = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_STATUS == 'True') ? true : false);
		$this->form_action_url = 'https://mms.paymentsensegateway.com/Pages/PublicPages/PaymentForm.aspx';
		if ((int)MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID;
		}
		
		if (is_object($order)) $this->update_status();	
		request_status($code,$version);
		
		$table_exists_query = tep_db_query('SHOW TABLES LIKE "paymentsense_redirect"');
		$table_exists_result = tep_db_num_rows($table_exists_query);
		
		if ($table_exists_result == 0 && $this->enabled == true) {
			$critical_config_problem = true;
			$paymentsense_redirect_config_messages .= '<p><span style="color: red"><b>Warning:</b><br />The PaymentSense Redirect Database Table Does Not Exist!<br /><b>Please re-install this module, or create the database table as described in the installation instructions before proceeding.</b></span></p>';
		}		
		
		$this->description = "	<p><b> Redirect Payment Module</b><br>
								<b>Version:</b> ". $this->version . $_SESSION['updates'] . "<br>
								<b>Release Date:</b> ". $this->versiondate ."</p>
								
								<p><a href=\"https://mms.paymentsensegateway.com\">PaymentSense Merchant Management System (MMS)</a><br>
								<a href=\"mailto:ecomsupport@paymentsense.com\">PaymentSense Integration Support</a><br>
								<a href=\"http://www.paymentsense.co.uk\">PaymentSense Website</a></p>
								
								<p>For integration support call the PaymentSense EcomSupport Team on 0208 962 5424</p>" . $paymentsense_redirect_config_messages;
								
	}	

    function javascript_validation() {
		return false;
    }

	function selection() {
		global $cart_PaymentSense_ID;
		
		if (tep_session_is_registered('cart_PaymentSense_ID')) {
			$order_id = substr($cart_PaymentSense_ID, strpos($cart_PaymentSense_ID, '-')+1);		
			$check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');
		
			if (tep_db_num_rows($check_query) < 1) {
				tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
				tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
				tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
				tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
				tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
				tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');	
				tep_session_unregister('cart_PaymentSense_ID');
			}
		}
		
		return array('id' => $this->code,
		'module' => $this->public_title);
	}

	function pre_confirmation_check() {
		global $cartID, $cart;
	
		if (empty($cart->cartID)) {
			$cartID = $cart->cartID = $cart->generate_cart_id();
		}
		
		if (!tep_session_is_registered('cartID')) {
			tep_session_register('cartID');
		}
	}

	function confirmation() {
		global $cartID, $customer_id, $languages_id, $order, $order_total_modules, $cart_PaymentSense_ID;
		
		if (tep_session_is_registered('cartID')) {
			$insert_order = false;
		
			if (tep_session_is_registered('cart_PaymentSense_ID')) {
				$order_id = substr($cart_PaymentSense_ID, strpos($cart_PaymentSense_ID, '-')+1);
				$curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
				$curr = tep_db_fetch_array($curr_check);
		
				if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_PaymentSense_ID, 0, strlen($cartID))) ) {
					$check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');
		
					if (tep_db_num_rows($check_query) < 1) {
						tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
						tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
						tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
						tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
						tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
						tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');
					}
		
					$insert_order = true;
				}
			} else {
				$insert_order = true;
			}
		
			if ($insert_order == true) {
				$order_totals = array();
					if (is_array($order_total_modules->modules)) {
						reset($order_total_modules->modules);
						while (list(, $value) = each($order_total_modules->modules)) {
							$class = substr($value, 0, strrpos($value, '.'));
							if ($GLOBALS[$class]->enabled) {
								for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
									if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
										$order_totals[] = array('code' => $GLOBALS[$class]->code,
																'title' => $GLOBALS[$class]->output[$i]['title'],
																'text' => $GLOBALS[$class]->output[$i]['text'],
																'value' => $GLOBALS[$class]->output[$i]['value'],
																'sort_order' => $GLOBALS[$class]->sort_order);
									}
								}
							}
						}
					}
		
					$sql_data_array = array('customers_id' => $customer_id,
											'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
											'customers_company' => $order->customer['company'],
											'customers_street_address' => $order->customer['street_address'],
											'customers_suburb' => $order->customer['suburb'],
											'customers_city' => $order->customer['city'],
											'customers_postcode' => $order->customer['postcode'],
											'customers_state' => $order->customer['state'],
											'customers_country' => $order->customer['country']['title'],
											'customers_telephone' => $order->customer['telephone'],
											'customers_email_address' => $order->customer['email_address'],
											'customers_address_format_id' => $order->customer['format_id'],
											'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
											'delivery_company' => $order->delivery['company'],
											'delivery_street_address' => $order->delivery['street_address'],
											'delivery_suburb' => $order->delivery['suburb'],
											'delivery_city' => $order->delivery['city'],
											'delivery_postcode' => $order->delivery['postcode'],
											'delivery_state' => $order->delivery['state'],
											'delivery_country' => $order->delivery['country']['title'],
											'delivery_address_format_id' => $order->delivery['format_id'],
											'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
											'billing_company' => $order->billing['company'],
											'billing_street_address' => $order->billing['street_address'],
											'billing_suburb' => $order->billing['suburb'],
											'billing_city' => $order->billing['city'],
											'billing_postcode' => $order->billing['postcode'],
											'billing_state' => $order->billing['state'],
											'billing_country' => $order->billing['country']['title'],
											'billing_address_format_id' => $order->billing['format_id'],
											'payment_method' => $order->info['payment_method'],
											'cc_type' => $order->info['cc_type'],
											'cc_owner' => $order->info['cc_owner'],
											'cc_number' => $order->info['cc_number'],
											'cc_expires' => $order->info['cc_expires'],
											'date_purchased' => 'now()',
											'orders_status' => "1",
											'currency' => $order->info['currency'],
											'currency_value' => $order->info['currency_value']);
		
					tep_db_perform(TABLE_ORDERS, $sql_data_array);
		
					$insert_id = tep_db_insert_id();
		
					for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
						$sql_data_array = array('orders_id' => $insert_id,
												'title' => $order_totals[$i]['title'],
												'text' => $order_totals[$i]['text'],
												'value' => $order_totals[$i]['value'],
												'class' => $order_totals[$i]['code'],
												'sort_order' => $order_totals[$i]['sort_order']);						
						tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
					}
		
					for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
						$sql_data_array = array('orders_id' => $insert_id,
												'products_id' => tep_get_prid($order->products[$i]['id']),
												'products_model' => $order->products[$i]['model'],
												'products_name' => $order->products[$i]['name'],
												'products_price' => $order->products[$i]['price'],
												'final_price' => $order->products[$i]['final_price'],
												'products_tax' => $order->products[$i]['tax'],
												'products_quantity' => $order->products[$i]['qty']);
		
						tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);		
						$order_products_id = tep_db_insert_id();		
						$attributes_exist = '0';
		
						if (isset($order->products[$i]['attributes'])) {
							$attributes_exist = '1';
							for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
								if (DOWNLOAD_ENABLED == 'true') {
									$attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
														from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
														left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
														on pa.products_attributes_id=pad.products_attributes_id
														where pa.products_id = '" . $order->products[$i]['id'] . "'
														and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
														and pa.options_id = popt.products_options_id
														and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
														and pa.options_values_id = poval.products_options_values_id
														and popt.language_id = '" . $languages_id . "'
														and poval.language_id = '" . $languages_id . "'";
									$attributes = tep_db_query($attributes_query);
								} else {
									$attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
								}
								$attributes_values = tep_db_fetch_array($attributes);
		
								$sql_data_array = array('orders_id' => $insert_id,
														'orders_products_id' => $order_products_id,
														'products_options' => $attributes_values['products_options_name'],
														'products_options_values' => $attributes_values['products_options_values_name'],
														'options_values_price' => $attributes_values['options_values_price'],
														'price_prefix' => $attributes_values['price_prefix']);
		
								tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
		
								if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
								$sql_data_array = array('orders_id' => $insert_id,
														'orders_products_id' => $order_products_id,
														'orders_products_filename' => $attributes_values['products_attributes_filename'],
														'download_maxdays' => $attributes_values['products_attributes_maxdays'],
														'download_count' => $attributes_values['products_attributes_maxcount']);
		
								tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
							}
						}
					}
				}
			
				$cart_PaymentSense_ID = $cartID . '-' . $insert_id;
				tep_session_register('cart_PaymentSense_ID');
			}
		}
		return false;
    }

	function process_button() {
		global $order, $currencies, $currency, $cartID, $cart_PaymentSense_ID;

		switch (MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_CURRENCY) {
			case 'GBP':
				$ps_currency = 826;
				break;
			case 'EUR':
				$ps_currency = 978;
				break;
			case 'USD':
				$ps_currency = 840;
				break;
		}

		//Get country ISO Code
		require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paymentsense_redirect/ISOCountries.php');

		$country_code = 0;

		$tep_country_code = $order->billing['country']['iso_code_3'];

		for ($country_i = 0; $country_i < $iclISOCountryList->getCount() - 1; $country_i++) {
			if ($iclISOCountryList->getAt($country_i)->getCountryNameShort() == $tep_country_code) {
				$country_code = $iclISOCountryList->getAt($country_i)->getISOCode();
				break;
			}
		}

		if (is_null($country_code)) {
			$country_code = 0;
		}

		$order_id          = substr($cart_PaymentSense_ID, strpos($cart_PaymentSense_ID, '-')+1);
		$callback_url      = tep_href_link('includes/modules/payment/paymentsense_redirect/callback.php', '', 'NONSSL', false);
		$server_result_url = tep_href_link('includes/modules/payment/paymentsense_redirect/serverresult.php', '', 'NONSSL', false);
		$fields            = array(
			'Amount'                                   => $order->info['total'] * 100,
			'CurrencyCode'                             => $country_code,
			'EchoAVSCheckResult'                       => 'true',
			'EchoCV2CheckResult'                       => 'true',
			'EchoThreeDSecureAuthenticationCheckResult'=> 'true',
			'EchoCardType'                             => 'true',
			'OrderID'                                  => $order_id,
			'TransactionType'                          => 'SALE',
			'TransactionDateTime'                      => date('Y-m-d H:i:s P'),
			'CallbackURL'                              => $callback_url,
			'OrderDescription'                         => STORE_NAME . " " . date('Ymdhis'),
			'CustomerName'                             => stripGWInvalidChars($order->billing['firstname'] . ' ' . $order->billing['lastname']),
			'Address1'                                 => stripGWInvalidChars($order->billing['street_address']),
			'Address2'                                 => stripGWInvalidChars($order->billing['suburb']),
			'Address3'                                 => '',
			'Address4'                                 => '',
			'City'                                     => stripGWInvalidChars($order->billing['city']),
			'State'                                    => stripGWInvalidChars($order->billing['state']),
			'PostCode'                                 => stripGWInvalidChars($order->billing['postcode']),
			'CountryCode'                              => $country_code,
			'EmailAddress'                             => stripGWInvalidChars($order->customer['email_address']),
			'PhoneNumber'                              => stripGWInvalidChars($order->customer['telephone']),
			'EmailAddressEditable'                     => 'false',
			'PhoneNumberEditable'                      => 'false',
			'CV2Mandatory'                             => 'true',
			'Address1Mandatory'                        => 'true',
			'CityMandatory'                            => 'true',
			'PostCodeMandatory'                        => 'true',
			'StateMandatory'                           => 'true',
			'CountryMandatory'                         => 'true',
			'ResultDeliveryMethod'                     => 'SERVER',
			'ServerResultURL'                          => $server_result_url,
			'PaymentFormDisplaysResult'                => 'false'
		);

		$fields = array_map(
			function ($value) {
				return $value === null ? '' : $value;
			},
			$fields
		);

		$data  = 'MerchantID=' . MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_ID;
		$data .= '&Password=' . MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_PASSWORD;

		foreach ($fields as $key => $value) {
			$data .= '&' . $key . '=' . $value;
		};

		$additional_fields = array(
			'HashDigest' => $this->calculate_hash_digest(
				$data,
				MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ENCODING_METHOD,
				MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_PRESHARED_KEY
			),
			'MerchantID' => MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_ID,
		);

		$fields = array_merge($additional_fields, $fields);

		$result = '';

		foreach ($fields as $key => $value) {
			$result .= tep_draw_hidden_field($key, $value);
		}

		$this->add_comments($order_id);

		return $result;
	}

    function before_process() {
		return false;
    }

    function after_process() {
		return false;
    }
	
	function update_status() {
		global $order;		
    }

    function get_error() {

		if ($_GET['error'] == 'n') {
			$error_message = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_ERROR_MESSAGE_N;
		} elseif ($_GET['error'] == 'c') {
			$error_message = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_ERROR_MESSAGE_C;
		} else {
			$error_message = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_ERROR_MESSAGE;
		}
  
		$custom_message = stripslashes(urldecode($_GET['customerror']));
		$custom_message = str_replace('|lt;|', '<', $custom_message);
		$custom_message = str_replace('|gt;|', '>', $custom_message);
		
		if  ($custom_message != "") {
			$error_message .= " (". $custom_message ."). Please try another card.";
		} else {
			$error_message .= ".";
		}
		
		$error = array(
			'title' => MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_ERROR,
			'error' => $error_message
			);
		return $error;
    }

    function check() {
		if (!isset($this->_check)) {
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
    }

    function install() {
		if (defined('MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_STATUS')) {
			$messageStack->add_session('paymentsense Redirect module already installed.', 'error');
			zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paymentsense_redirect', 'NONSSL'));
			return 'failed';
		}
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Text Title', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_PUBLIC_TITLE', 'Credit/Debit Card', 'Title to use on payment form', '6', '1', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PaymentSense Redirect Module', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_STATUS', 'False', 'Do you want to accept payments through the PaymentSense gateway?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_ID', 'MerchantID', 'Merchant ID to use', '6', '2', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Password', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_PASSWORD', 'MerchantPassword', 'The gateway account password.', '6', '3', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Pre-Shared Key', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_PRESHARED_KEY', 'yourPSK', 'The Pre-Shared Key from the PaymentSense MMS for this gateway account.', '6', '4', now())");      
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Hash Encoding Method', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ENCODING_METHOD', 'SHA1', 'Select SHA1 or MD5 - ensure this matches the setting in the PaymentSense MMS', '6', '5', 'tep_cfg_select_option(array(\'SHA1\', \'MD5\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_CURRENCY', 'GBP', 'The currency to use for credit card transactions', '6', '6', 'tep_cfg_select_option(array(\'GBP\', \'EUR\', \'USD\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Successful Order Status', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID', '0', 'Update the Order Status to this value when a payment is successful.', '6', '8', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Failed Order Status', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID_FAILED', '0', 'Update the Order Status to this value when a payment fails.', '6', '9', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Display Order', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '10', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Debug Mode', 'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_DEBUG_MODE', 'No', 'If enabled, this will:<ul><li>Display the unencoded contents of the HashDigest.</li><li>Display the contents of the callback on screen.</li></ul>DO NOT ENABLE ON A LIVE SITE.', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");

				
		$query = "SELECT * FROM ". TABLE_ORDERS_STATUS ." ORDER BY orders_status_id desc LIMIT 1";
		$row = tep_db_query($query);
		$row_values = tep_db_fetch_array($row);

		$LastInsertID = intval($row_values["orders_status_id"]);
		$LastInsertID1 = $LastInsertID + 1;
		$LastInsertID2 = $LastInsertID + 2;
		
		tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values (". $LastInsertID1 .", 1, 'Payment Failed')");
		tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values (". $LastInsertID2 .", 1, 'Payment Successful')");	
		
		tep_db_query("CREATE TABLE `paymentsense_redirect` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`cross_reference` varchar(25) NOT NULL,
					`osc_order_id` int(11) NOT NULL,
					`auth_code` varchar(15) NOT NULL,
					`message` varchar(255) default NULL,
					`amount_received` varchar(15) default NULL,
					`transaction_result` int(11) default NULL,
					PRIMARY KEY (`id`))");
    }

    function remove() {
		tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");	  
		tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Payment Failed'");
		tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Payment Successful'");
		
		$table_exists_query = tep_db_query('SHOW TABLES LIKE "paymentsense_redirect"');
		$table_exists_result = tep_db_num_rows($table_exists_query);
		
		if ($table_exists_result > 0) {
			tep_db_query("drop table paymentsense_redirect");
		}
    }

	function keys() {
		return array(
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_STATUS',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_TEXT_PUBLIC_TITLE',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_ID',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_PASSWORD',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_PRESHARED_KEY',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ENCODING_METHOD',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_CURRENCY',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID_FAILED',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_SORT_ORDER',
			'MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_DEBUG_MODE'
		);
	}

	/**
	 * Calculates the hash digest.
	 * Supported hash methods: MD5, SHA1, HMACMD5, HMACSHA1
	 *
	 * @param string $data Data to be hashed.
	 * @param string $hash_method Hash method.
	 * @param string $key Secret key to use for generating the hash.
	 * @return string
	 */
	function calculate_hash_digest($data, $hash_method, $key)
	{
		$result      = '';
		$include_key = in_array($hash_method, ['MD5', 'SHA1'], true);
		if ($include_key) {
			$data = 'PreSharedKey=' . $key . '&' . $data;
		}
		switch ($hash_method) {
			case 'MD5':
				// @codingStandardsIgnoreLine
				$result = md5($data);
				break;
			case 'SHA1':
				$result = sha1($data);
				break;
			case 'HMACMD5':
				$result = hash_hmac('md5', $data, $key);
				break;
			case 'HMACSHA1':
				$result = hash_hmac('sha1', $data, $key);
				break;
		}
		return $result;
	}

	/**
	 * Adds comments to the orders status history
	 *
	 * @param string $order_id Order ID
	 */
	public function add_comments($order_id) {
		$comments       = isset($_POST['comments']) ? $_POST['comments'] : '';
		$comments_query = tep_db_query("SELECT orders_status_history_id FROM orders_status_history WHERE orders_id = '" . $order_id. "' AND customer_notified = 0");
		$comments_data  = tep_db_fetch_array($comments_query);
		if ($comments_data) {
			$orders_status_history_id = array_shift($comments_data);
			tep_db_query("UPDATE orders_status_history SET orders_status_id = " . (int)DEFAULT_ORDERS_STATUS_ID . " , date_added = now(), comments ='" . tep_db_input($comments) . "' WHERE orders_status_history_id = " . $orders_status_history_id);
		} else {
			tep_db_query("INSERT INTO orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) VALUES ('". $order_id ."', ". (int)DEFAULT_ORDERS_STATUS_ID .", now(), 0, '". tep_db_input($comments) ."')");
		}
	}
}
?>
