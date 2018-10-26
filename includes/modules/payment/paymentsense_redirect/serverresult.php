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
	chdir('../../../../');
	require_once('includes/application_top.php');
	require_once('includes/classes/order.php');
		
		function createhash($PreSharedKey,$Password,$EncodingMethod) { 
			$str="PreSharedKey=" . $PreSharedKey;
			$str=$str . '&MerchantID=' . $_POST["MerchantID"];
			$str=$str . '&Password=' . $Password;
			$str=$str . '&StatusCode=' . $_POST["StatusCode"];
			$str=$str . '&Message=' . $_POST["Message"];
			$str=$str . '&PreviousStatusCode=' . $_POST["PreviousStatusCode"];
			$str=$str . '&PreviousMessage=' . $_POST["PreviousMessage"];
			$str=$str . '&CrossReference=' . $_POST["CrossReference"];
			$str=$str . '&Amount=' . $_POST["Amount"];
			$str=$str . '&CurrencyCode=' . $_POST["CurrencyCode"];
			$str=$str . '&OrderID=' . $_POST["OrderID"];
			$str=$str . '&TransactionType=' . $_POST["TransactionType"];
			$str=$str . '&TransactionDateTime=' . $_POST["TransactionDateTime"];
			$str=$str . '&OrderDescription=' . $_POST["OrderDescription"];
			$str=$str . '&CustomerName=' . $_POST["CustomerName"];
			$str=$str . '&Address1=' . $_POST["Address1"];
			$str=$str . '&Address2=' . $_POST["Address2"];
			$str=$str . '&Address3=' . $_POST["Address3"];
			$str=$str . '&Address4=' . $_POST["Address4"];
			$str=$str . '&City=' . $_POST["City"];
			$str=$str . '&State=' . $_POST["State"];
			$str=$str . '&PostCode=' . $_POST["PostCode"];
			$str=$str . '&CountryCode=' . $_POST["CountryCode"];
			
			switch ($EncodingMethod) {
				case "SHA1":
					return sha1($str);
					break;	
				case "MD5":
					return md5($str);
					break;
			}
			
		}
		
		// String together other strings using a "," as a seperator.
		function addStringToStringList($szExistingStringList, $szStringToAdd)
		{
			$szReturnString = "";
			$szCommaString = "";

			if (strlen($szStringToAdd) == 0)
			{
				$szReturnString = $szExistingStringList;
			}
			else
			{
				if (strlen($szExistingStringList) != 0)
				{
					$szCommaString = ", ";
				}
				$szReturnString = $szExistingStringList.$szCommaString.$szStringToAdd;
			}

			return ($szReturnString);
		}
		
		$szHashDigest = "";
		$szOutputMessage = "";
		$boErrorOccurred = false;
		$nStatusCode = 30;
		$szMessage = "";
		$nPreviousStatusCode = 0;
		$szPreviousMessage = "";
		$szCrossReference = "";
		$nAmount = 0;
		$nCurrencyCode = 0;
		$szOrderID = "";
		$szTransactionType= "";
		$szTransactionDateTime = "";
		$szOrderDescription = "";
		$szCustomerName = "";
		$szAddress1 = "";
		$szAddress2 = "";
		$szAddress3 = "";
		$szAddress4 = "";
		$szCity = "";
		$szState = "";
		$szPostCode = "";
		$nCountryCode = "";

		try
			{
				// hash digest
				if (isset($_POST["HashDigest"]))
				{
					$szHashDigest = $_POST["HashDigest"];
				}

				// transaction status code
				if (!isset($_POST["StatusCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [StatusCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["StatusCode"] == "")
					{
						$nStatusCode = null;
					}
					else
					{
						$nStatusCode = intval($_POST["StatusCode"]);
					}
				}
				// transaction message
				if (!isset($_POST["Message"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Message] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szMessage = $_POST["Message"];
				}
				// status code of original transaction if this transaction was deemed a duplicate
				if (!isset($_POST["PreviousStatusCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PreviousStatusCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["PreviousStatusCode"] == "")
					{
						$nPreviousStatusCode = null;
					}
					else
					{
						$nPreviousStatusCode = intval($_POST["PreviousStatusCode"]);
					}
				}
				// status code of original transaction if this transaction was deemed a duplicate
				if (!isset($_POST["PreviousMessage"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PreviousMessage] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szPreviousMessage = $_POST["PreviousMessage"];
				}
				// cross reference of transaction
				if (!isset($_POST["CrossReference"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CrossReference] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCrossReference = $_POST["CrossReference"];
				}
				// amount (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["Amount"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Amount] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["Amount"] == null)
					{
						$nAmount = null;
					}
					else
					{
						$nAmount = intval($_POST["Amount"]);
					}
				}
				// currency code (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["CurrencyCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CurrencyCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["CurrencyCode"] == null)
					{
						$nCurrencyCode = null;
					}
					else
					{
						$nCurrencyCode = intval($_POST["CurrencyCode"]);
					}
				}
				// order ID (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["OrderID"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [OrderID] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szOrderID = $_POST["OrderID"];
				}
				// transaction type (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["TransactionType"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [TransactionType] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szTransactionType = $_POST["TransactionType"];
				}
				// transaction date/time (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["TransactionDateTime"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [TransactionDateTime] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szTransactionDateTime = $_POST["TransactionDateTime"];
				}
				// order description (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["OrderDescription"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [OrderDescription] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szOrderDescription = $_POST["OrderDescription"];
				}
				// customer name (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["CustomerName"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CustomerName] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCustomerName = $_POST["CustomerName"];
				}
				// address1 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address1"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address1] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress1 = $_POST["Address1"];
				}
				// address2 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address2"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address2] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress2 = $_POST["Address2"];
				}
				// address3 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address3"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address3] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress3 = $_POST["Address3"];
				}
				// address4 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address4"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address4] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress4 = $_POST["Address4"];
				}
				// city (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["City"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [City] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCity = $_POST["City"];
				}
				// state (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["State"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [State] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szState = $_POST["State"];
				}
				// post code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["PostCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PostCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szPostCode = $_POST["PostCode"];
				}
				// country code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["CountryCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CountryCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["CountryCode"] == "")
					{
						$nCountryCode = null;
					}
					else
					{
						$nCountryCode = intval($_POST["CountryCode"]);
					}
				}
			}
		catch (Exception $e)
		{
			$boErrorOccurred = true;
			$szOutputMessage = "Error";
			if (!isset($_POST["Message"]))
			{
				$szOutputMessage = $_POST["Message"];
			}
		}
		
	// Check the passed HashDigest against our own to check the values passed are legitimate.
	$str1 = $_POST["HashDigest"];
	$hashcode = createhash(MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_PRESHARED_KEY,MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_PASSWORD,MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ENCODING_METHOD);
	if ($hashcode != $str1) {
		$nOutputProcessedOK = 30; 
		$szOutputMessage = "Hashes did not match";
	} 
	
	// The nOutputProcessedOK should return 0 except if there has been an error talking to the gateway or updating the website order system.
	// Any other process status shown to the gateway will prompt the gateway to send an email to the merchant stating the error.
	// The customer will also be shown a message on the hosted payment form detailing the error and will not return to the merchants website.
	$nOutputProcessedOK = 0;
	$transstatus = "failed";
	
	if (is_null($nStatusCode))
	{
		$nOutputProcessedOK = 30;		
	}
	
	if ($boErrorOccurred == true)
	{
		$nOutputProcessedOK = 30;
	}

	// *********************************************************************************************************
	// You should put your code that does any post transaction tasks
	// (e.g. updates the order object, sends the customer an email etc) in this section
	// *********************************************************************************************************
	if ($nOutputProcessedOK != 30)
		{	
			$nOutputProcessedOK = 0;
			// Alter this line once you've implemented the code.
			//$szOutputMessage = $szMessage."--"."Environment specific function needs to be implemented by merchant developer";
			try
			{
				switch ($nStatusCode)
				{
					// transaction authorised
					case 0:						
						$transstatus = "passed";
						break;
					// card referred (treat as decline)
					case 4:						
						$transstatus = "failed";
						break;
					// transaction declined
					case 5:
						$transstatus = "failed";
						break;				
					// duplicate transaction
					case 20:
						// need to look at the previous status code to see if the
						// transaction was successful
						if ($nPreviousStatusCode == 0)
						{
							$transstatus = "passed";	
							break;
						} else {
							$transstatus = "failed";
							break;
						}
						break;
					// error occurred
					case 30:
						$transstatus = "failed";	
						break;
					default:
						$transstatus = "failed";
						break;
				}

				$comments_query = tep_db_query("SELECT orders_status_history_id, comments FROM orders_status_history WHERE orders_id = '" . (int)$szOrderID . "' AND customer_notified = 0");
				$comments_data  = tep_db_fetch_array($comments_query);
				if (is_array($comments_data) && count($comments_data) == 2) {
					$orders_status_history_id = $comments_data['orders_status_history_id'];
					$comments                 = $comments_data['comments'];
				} else {
					throw new Exception("Order History Not Found!");
				}

				$comments_text = "Comments: " . $comments;

				$AVSCV2ThreeDResults = "Address Numeric Check: " . tep_db_input($_POST["AddressNumericCheckResult"]) .
					" \n Postcode Check: " . tep_db_input($_POST["PostCodeCheckResult"]) .
					" \n CV2 Check: " . tep_db_input($_POST["CV2CheckResult"]) .
					" \n 3DS Check: " . tep_db_input($_POST["ThreeDSecureCheckResult"]) .
					" \n Card Type: " . tep_db_input($_POST["CardClass"] . " " . $_POST["CardType"]);

				$comments = tep_db_input($szMessage) .
					" \n Payment Cross Reference: " . tep_db_input($szCrossReference) .
					" \n " . $AVSCV2ThreeDResults .
					" \n " . tep_db_input($comments_text);

				if ($transstatus == "failed") {
					$order_status = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID_FAILED > 0 ? MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID_FAILED : DEFAULT_ORDERS_STATUS_ID;

					tep_db_query("INSERT into paymentsense_redirect (cross_reference, osc_order_id, auth_code, message, amount_received, transaction_result) VALUES ('". $szCrossReference ."', '". $szOrderID ."', 0, '". $szMessage ."', 0, ".$nStatusCode.")");

					tep_db_query("UPDATE orders_status_history SET orders_status_id = " . (int)$order_status . " , date_added = now(), customer_notified = 1, comments ='" . tep_db_input($comments) . "' WHERE orders_status_history_id = " . $orders_status_history_id);

					tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = '" . (int)$order_status . "', last_modified = now() WHERE orders_id = '" . (int)$szOrderID . "'");
				} else {
					$order_status = MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID > 0 ? MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ORDER_STATUS_ID : DEFAULT_ORDERS_STATUS_ID;

					tep_db_query("INSERT into paymentsense_redirect (cross_reference, osc_order_id, auth_code, message, amount_received, transaction_result) VALUES ('". $szCrossReference ."', '". $szOrderID ."', '". str_replace("AuthCode: ","",$szMessage) ."', '". $szMessage ."', '". $nAmount ."', 0)");

					tep_db_query("UPDATE orders_status_history SET orders_status_id = " . (int)$order_status . " , date_added = now(), customer_notified = 1, comments ='" . tep_db_input($comments) . "' WHERE orders_status_history_id = " . $orders_status_history_id);

					tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = '" . (int)$order_status . "', last_modified = now() WHERE orders_id = '" . (int)$szOrderID . "'");

					// initialized for the email confirmation
					$products_ordered = '';
					$subtotal = 0;
					$total_tax = 0;
					
					require_once("includes/languages/english/checkout_process.php");
					
					$order = new order($szOrderID);
					
					$customer_id = $order->customer['id'];

					for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
					// Stock Update
					if (STOCK_LIMITED == 'true') {
					  if (DOWNLOAD_ENABLED == 'true') {
						$stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
											FROM " . TABLE_PRODUCTS . " p
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
											ON p.products_id=pa.products_id
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
											ON pa.products_attributes_id=pad.products_attributes_id
											WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
					// Will work with only one option for downloadable products
					// otherwise, we have to build the query dynamically with a loop
						$products_attributes = $order->products[$i]['attributes'];
						if (is_array($products_attributes)) {
						  $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
						}
						$stock_query = tep_db_query($stock_query_raw);
					  } else {
						$stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
					  }
					  if (tep_db_num_rows($stock_query) > 0) {
						$stock_values = tep_db_fetch_array($stock_query);
					// do not decrement quantities if products_attributes_filename exists
						if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
						  $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
						} else {
						  $stock_left = $stock_values['products_quantity'];
						}
						tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
						if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
						  tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
						}
					  }
					}

					// Update products_ordered (for bestsellers list)
					tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

					//------insert customer choosen option to order--------
					$attributes_exist = '0';
					$products_ordered_attributes = '';
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

						$products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
					  }
					}
					//------insert customer choosen option eof ----
					$total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
					$total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
					$total_cost += $total_products_price;
					
					$products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
					}
					
					$shipping_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$szOrderID . "' and class = 'ot_shipping'");
					$shipping_array = tep_db_fetch_array($shipping_query);
					
					$subtotal_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$szOrderID . "' and class = 'ot_subtotal'");
					$subtotal_array = tep_db_fetch_array($subtotal_query);
					
					$total_query = tep_db_query("select* from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$szOrderID . "' and class = 'ot_total'");
					$total_array = tep_db_fetch_array($total_query);
					
					$products_ordered .= EMAIL_SEPARATOR . "\n";
					$products_ordered .= $subtotal_array['title'] . " " . $subtotal_array['text'] . "\r";
					$products_ordered .= "Shipping - " . $shipping_array['title'] . " " . $shipping_array['text'] . "\r";
					$products_ordered .= $total_array['title'] . " " . $total_array['text'] . "\r";

					// lets start with the email confirmation
					$email_order = STORE_NAME . "\n" .
								 EMAIL_SEPARATOR . "\n" .
								 EMAIL_TEXT_ORDER_NUMBER . ' ' . $szOrderID . "\n" .
								 EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $szOrderID, 'SSL', false) . "\n" .
								 EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";

					$email_order .= $comments_text . "\n\n";

					$email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
								  EMAIL_SEPARATOR . "\n" .
								  $products_ordered .
								  EMAIL_SEPARATOR . "\n";

					for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
					$email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
					}

					if ($order->content_type != 'virtual') {
						$email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" . EMAIL_SEPARATOR . "\n";									
						if ($order->delivery['name'] != "") { $email_order .= $order->delivery['name'] . "\n"; }
						if ($order->delivery['company'] != "") { $email_order .= $order->delivery['company'] . "\n"; }
						if ($order->delivery['street_address'] != "") { $email_order .= $order->delivery['street_address'] . "\n"; }
						if ($order->delivery['suburb'] != "") { $email_order .= $order->delivery['suburb'] . "\n"; }
						if ($order->delivery['city'] != "") { $email_order .= $order->delivery['city'] . "\n"; }
						if ($order->delivery['postcode'] != "") { $email_order .= $order->delivery['postcode'] . "\n"; }
						if ($order->delivery['state'] != "") { $email_order .= $order->delivery['state'] . "\n"; }
						if ($order->delivery['country']['title'] != "") { $email_order .= $order->delivery['country']['title'] . "\n"; }
					}

					$email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" . EMAIL_SEPARATOR . "\n";
					if ($order->billing['name'] != "") { $email_order .= $order->billing['name'] . "\n"; }
					if ($order->billing['company'] != "") { $email_order .= $order->billing['company'] . "\n"; }
					if ($order->billing['street_address'] != "") { $email_order .= $order->billing['street_address'] . "\n"; }
					if ($order->billing['suburb'] != "") { $email_order .= $order->billing['suburb'] . "\n"; }
					if ($order->billing['city'] != "") { $email_order .= $order->billing['city'] . "\n"; }
					if ($order->billing['postcode'] != "") { $email_order .= $order->billing['postcode'] . "\n"; }
					if ($order->billing['state'] != "") { $email_order .= $order->billing['state'] . "\n"; }
					if ($order->billing['country']['title'] != "") { $email_order .= $order->billing['country']['title'] . "\n"; }					
					
					$email_order .= EMAIL_SEPARATOR . "\n";
					
					tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], "Your order from " . STORE_NAME , $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
					tep_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, "Order Placed - ". EMAIL_TEXT_ORDER_NUMBER . " " . $szOrderID, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
				}
			}
			catch (Exception $e)
			{
				$nOutputProcessedOK = 30;
				$szOutputMessage = "Error updating website system, please ask the developer to check code";
			}
		}

	if ($nOutputProcessedOK != 0 &&
		$szOutputMessage == "")
	{
		$szOutputMessage = "Unknown error";
	}	

	// output the status code and message letting the payment form
	// know whether the transaction result was processed successfully
	echo("StatusCode=".$nOutputProcessedOK."&Message=".$szOutputMessage);
	
	require_once('includes/application_bottom.php');
?>
