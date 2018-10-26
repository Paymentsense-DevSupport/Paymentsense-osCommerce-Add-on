<?php
/*
$Id$

OS-Commerce PaymentSense Re-Direct Payment Module
Copyright (C) 2012 PaymentSense.
Support: ecomsupport@paymentsense.com

------------------------

Last Updated: 03/05/2012

------------------------

Released under the GNU General Public License
*/
	chdir('../../../../');
	require('includes/application_top.php');
		
	global $cart;
	
	if (MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_DEBUG_MODE == "Yes") {
		$debugmode = true;
	}
	
	$HashDigest = $_GET["HashDigest"];
	$MerchantID = $_GET["MerchantID"];
	$CrossReference = $_GET["CrossReference"];
	$OrderID = $_GET["OrderID"];	
	
	if ($HashDigest != "" && $MerchantID != "" && $CrossReference != "" && $OrderID != "") {	
		$HashString="PreSharedKey=" . MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_PRESHARED_KEY;
		$HashString=$HashString . '&MerchantID=' . $MerchantID;
		$HashString=$HashString . '&Password=' . MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_MERCHANT_PASSWORD;
		$HashString=$HashString . '&CrossReference=' . $CrossReference;
		$HashString=$HashString . '&OrderID=' . $OrderID;		
		
		switch (MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ENCODING_METHOD) {
			case "SHA1":
				$GeneratedHash = sha1($HashString);
				break;	
			case "MD5":
				$GeneratedHash = md5($HashString);
				break;
		}
		
		if ($GeneratedHash == $HashDigest) { 			
			$query = "SELECT * FROM paymentsense_redirect WHERE osc_order_id = ". $OrderID ." AND cross_reference = '". $CrossReference ."' ORDER BY id desc"; 
			$result = tep_db_query($query);
			$row = tep_db_fetch_array($result);
			
			$TransactionResult = $row["transaction_result"];
			$Message = $row["message"];
					
			switch ($TransactionResult) {
				case 0:
					//$resultURL = "/checkout_success.php?HashDigest=". $HashDigest ."&MerchantID=". $MerchantID ."&CrossReference=". $CrossReference ."&OrderID=". $OrderID;
					$resultURL = DIR_WS_HTTP_CATALOG . FILENAME_CHECKOUT_SUCCESS . "?HashDigest=". $HashDigest ."&MerchantID=". $MerchantID ."&CrossReference=". $CrossReference ."&OrderID=". $OrderID;
								
					$cart->reset(true);
					// unregister session variables used during checkout
					tep_session_unregister('cartID');
					tep_session_unregister('billto');
					tep_session_unregister('shipping');
					tep_session_unregister('payment');
					tep_session_unregister('comments');				
					break;
					
				default:
					//$resultURL = "/checkout_payment.php?payment_error=paymentsense_redirect&error=n&customerror=".urlencode($Message);
					$resultURL = DIR_WS_HTTP_CATALOG . FILENAME_CHECKOUT_PAYMENT . "?payment_error=paymentsense_redirect&error=n&customerror=".urlencode($Message);					
					break;
			}
			
			if (!$debugmode) {
				echo "<script type=\"text/javascript\">";
				echo "window.location = \"". $resultURL ."\"";
				echo "</script>";
			}
		
		} else {
			echo "Hash check failed";
		}
	} else {
		echo "HashDigest, MerchantID, CrossReference or OrderID missing";
	}
	
	if ($debugmode) {
		echo "<br><br>Debug Output";
		echo "<br>MerchantID: " . $MerchantID;
		echo "<br>CrossReference: " . $CrossReference;
		echo "<br>OrderID: " . $OrderID;
		echo "<br>Retuned Hash: " . $HashDigest;
		echo "<br>Generated Hash: " . $GeneratedHash;
		echo "<br>Generated HashString: " . $HashString;				
		echo "<br>Encoding Method: " . MODULE_PAYMENT_PAYMENTSENSE_REDIRECT_ENCODING_METHOD;
		echo "<br>Result URL: <a href=\"". $resultURL ."\">". $resultURL ."</a>";
	}
	require('includes/application_bottom.php');
?>