<?php
/**
 * Braintree - Payment Gateway integration with 3D secure (Hosted Fields) example
 * ==============================================================================
 * 
 * @version v1.0: braintree_hosted_fields_with_3d_secure_demo.php 2016/03/25
 * @copyright Copyright (c) 2016, http://www.ilovephp.net
 * @author Sagar Deshmukh <sagarsdeshmukh91@gmail.com>
 * You are free to use, distribute, and modify this software
 * (Note: To use braintree with 3D secure, you need to enable the 3D secure option on your merchant account)
 * ==============================================================================
 *
 */

// Braintree library
require 'braintree/lib/Braintree.php';

$params = array(
	"testmode"   => "on",
	"merchantid" => "xxxxxxx",
	"publickey"  => "xxxxxxx",
	"privatekey" => "xxxxxxxxxxxxxxxxxxxxx",
);

if ($params['testmode'] == "on")
{
	Braintree_Configuration::environment('sandbox');
}
else
{
	Braintree_Configuration::environment('production');
}

Braintree_Configuration::merchantId($params["merchantid"]);
Braintree_Configuration::publicKey($params["publickey"]);
Braintree_Configuration::privateKey($params["privatekey"]);

if(isset($_POST['payment_method_nonce']))
{
	// Customer details
	$customer_firstname   = $_POST['c_firstname'];
	$customer_lastname    = $_POST['c_lastname'];
	$customer_email       = $_POST['c_email'];
	$customer_phonenumber = $_POST['c_phonenumber'];
	// EOF Customer details

	// Customer billing details
	$firstname = $_POST['firstname'];
	$lastname  = $_POST['lastname'];
	$email     = $_POST['email'];
	$address1  = $_POST['address1'];
	$address2  = $_POST['address2'];
	$city      = $_POST['city'];
	$state     = $_POST['state'];
	$postcode  = $_POST['postcode'];
	$country   = $_POST['country'];
	$phone     = $_POST['phonenumber'];
	// EOF Customer billing details

	$sale = array(
				'amount'   => $_POST['amount'],
				'orderId'  => $_POST['invoiceid'],
				'paymentMethodNonce' => $_POST['payment_method_nonce'],   // Autogenerated field from braintree
				'customer' => array(
								'firstName' => $customer_firstname,
								'lastName'  => $customer_lastname,
								'phone'     => $customer_phonenumber,
								'email'     => $customer_email
							  ),
				'billing' => array(
								'firstName'         => $firstname,
								'lastName'          => $lastname,
								'streetAddress'     => $address1,
								'extendedAddress'   => $address2,
								'locality'          => $city,
								'region'            => $state,
								'postalCode'        => $postcode,
								'countryCodeAlpha2' => $country
							 ),
				'options' => array(
								'submitForSettlement'   => true,
								'storeInVaultOnSuccess' => true,
								'three_d_secure' => array('required' => true)
							 )
			);
						
	$result = Braintree_Transaction::sale($sale);
	if ($result->success)
	{
		echo "Braintree_cust_id : ".$braintree_cust_id = $result->transaction->_attributes['customer']['id']; // After first successfull transaction, save this Braintree_cust_id in DB and use for future transactions
	}
	else
	{
		echo "Error : ".$result->_attributes['message'];
	}
	
	print_r($result); exit;
}
else
if (isset($_POST['braintree_cust_id']))
{
	$sale = array(
				'customerId' => $braintree_cust_id,
				'amount'     => $_POST['amount'],
				'orderId'    => $_POST['invoiceid'],  // This field is get back in responce to track this transaction
				'options'    => array(
									'submitForSettlement' => true
								)
			);
}
else
if (isset($_POST['action']) && $_POST['action'] == 'generateclienttoken')
{
	//$braintree_cust_id = "60033487";
	// Generate the nonce and send it back
	try
	{
		$clientToken = Braintree_ClientToken::generate(array(
			// use customerId to get a previous customer from the vault
			// 'customerId' => $braintree_cust_id    // $braintree_cust_id is Fetch from DB
		));
	}
	catch(Exception $e)
	{
		// cannot get the customer from the vault!!
		$clientToken = Braintree_ClientToken::generate();
	}
	
	echo $clientToken; exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Braintree Hosted Fields Demo</title>
<link href="style.css" type="text/css" rel="stylesheet" />
</head>

<body>
<h1 class="bt_title">Braintree Hosted Fields Demo</h1>
<form id="bt_custom_form" name="bt_custom_form" method="post" action="">
  <div class="dropin-page">
    <h4 class="bt_title">Customer Information</h4>
    <input type="hidden" name="invoiceid" value="123456">
    <fieldset class="one_off_firstname">
      <label class="input-label" for="firstname">
      <span class="field-name">First Name</span>
      <input id="c_firstname" name="c_firstname" class="input-field card-field" type="text" placeholder="First Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_lastname">
      <label class="input-label" for="lastname">
      <span class="field-name">Last Name</span>
      <input id="c_lastname" name="c_lastname" class="input-field card-field" type="text" placeholder="Last Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_lastname">
      <label class="input-label" for="email">
      <span class="field-name">Email</span>
      <input id="c_email" name="c_email" class="input-field card-field" type="text" placeholder="Email" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_phonenumber">
      <label class="input-label" for="phonenumber">
      <span class="field-name">Phone Number</span>
      <input id="c_phonenumber" name="c_phonenumber" class="input-field card-field" type="text"placeholder="Phone Number" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <h4 class="bt_title">Customer Billing Information</h4>
    <fieldset class="one_off_firstname">
      <label class="input-label" for="firstname">
      <span class="field-name">First Name</span>
      <input id="firstname" name="firstname" class="input-field card-field" type="text" placeholder="First Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_lastname">
      <label class="input-label" for="lastname">
      <span class="field-name">Last Name</span>
      <input id="lastname" name="lastname" class="input-field card-field" type="text" placeholder="Last Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_address1">
      <label class="input-label" for="address1">
      <span class="field-name">Address1</span>
      <input id="address1" name="address1" class="input-field card-field" type="text" placeholder="Address" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_address2">
      <label class="input-label" for="address2">
      <span class="field-name">Address2</span>
      <input id="address2" name="address2" class="input-field card-field" type="text" placeholder="Address" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_city">
      <label class="input-label" for="city">
      <span class="field-name">City/Town</span>
      <input id="city" name="city" class="input-field card-field" type="text" placeholder="City/Town" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_state">
      <label class="input-label" for="state">
      <span class="field-name">State/Region</span>
      <input id="state" name="state" class="input-field card-field" type="text" placeholder="State/Region" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_postcode">
      <label class="input-label" for="postcode">
      <span class="field-name">Post Code</span>
      <input id="postcode" name="postcode" class="input-field card-field" type="text" placeholder="Post Code" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_country">
      <label class="input-label" for="country">
      <span class="field-name">Country</span>
      <input id="country" name="country" class="input-field card-field" type="text" placeholder="Country" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <h4 class="bt_title">Credit Card Details</h4>
  </div>
  <div class="loader_container">
    <div class="loader_img loader"></div>
  </div>
  <div class="bt_form_wrap hide invisible">
    <fieldset>
      <p class="amountDeclare">Amount to be paid 10.00 ($)</p>
      <input type="hidden" name="amount" id="amount" value="10" />
    </fieldset>
    <fieldset>
      <label for="bt_card_number">Card number</label>
      <div id="bt_card_number" class="inputFields"></div>
    </fieldset>
    <fieldset>
      <label for="bt_cvv">CVV</label>
      <div id="bt_cvv" class="inputFields"></div>
    </fieldset>
    <fieldset>
      <label for="bt_exp_date">Expiration date (MM/YY)</label>
      <div id="bt_exp_date" class="inputFields"></div>
    </fieldset>
    <div id="bt_paypal_container"></div>
    <div class="btn_container">
      <button class="pay-btn">Make Payment</button>
      <span class="loader_img"></span> </div>
  </div>
</form>
<script type="text/javascript" src="https://js.braintreegateway.com/js/beta/braintree-hosted-fields-beta.18.min.js"></script> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script> 
<!-- TO DO : Place below JS code in js file and include that JS file --> 
<script type="text/javascript">
	(function() {
    
		var BTFn = {};
		var bt_client_token = '';
	
		BTFn.sendJSON = function($obj) {
	
			$.ajax({
				dataType: "text",
				type: "POST",
				data: { action: "generateclienttoken"},
				url: "braintree_hosted_fields_with_3d_secure_demo.php",
				success: function (req) {
					bt_client_token = req;
					BTFn.initBT(req, $obj);
				},
				error: function() {
				}
			});
		};
	
		BTFn.initBT = function(req, $obj) {
	
			// we're on the custom fields tab
			braintree.setup(req, 'custom', {
				id: 'bt_custom_form',
				hostedFields: {
					number: {
						selector: '#bt_card_number'
					},
					cvv: {
						selector: '#bt_cvv'
					},
					expirationDate: {
						selector: '#bt_exp_date'
					}
				},
				onPaymentMethodReceived:function(obj){
                    
                    var client = new braintree.api.Client({
                        clientToken: bt_client_token
                    });

                    client.verify3DS({
                        amount: $('#amount').val(),
                        creditCard: obj.nonce
                    }, function (error, response) {
                        if (!error) {
                            // 3D Secure finished. send the response.nonce to server for further processing
                            BTFn.appendTo(document.forms.bt_custom_form, 'input', {name: 'payment_method_nonce', type: 'hidden', value: response.nonce});
                            document.forms.bt_custom_form.submit();
                        } else {
                            // Handle errors
                            alert("Error : "+error.message);
                        }
                    });
                },
				onReady: function() {
					BTFn.showBtForm();
				},
				onError: function(error) {
					BTFn.showFormErrors(error);
					$obj.show().closest('.btn_container').find('.loader_img').hide();
				},
				paypal:{
					container:'bt_paypal_container',
				}
			});
		};
	
		BTFn.showBtForm = function() {
	
			var eAnimation = this.detectAnimationEvent();
			$('.loader_container').addClass('fadeOut');
	
			$('.loader_container').one(eAnimation, function(event) {
				$('.loader_container').remove();
				$('.bt_form_wrap').removeClass('hide');
				$('.bt_form_wrap').addClass('fadeIn').removeClass('invisible');
			});
		};
	
		BTFn.detectAnimationEvent = function() {
	
			var t, el = document.createElement("fakeelement");
	
			var animations = {
				"animation"      : "animationend",
				"OAnimation"     : "oAnimationEnd",
				"MozAnimation"   : "animationend",
				"WebkitAnimation": "webkitAnimationEnd"
			}
	
			for (t in animations){
				if (el.style[t] !== undefined){
					return animations[t];
				}
			}
		};
	
		BTFn.showFormErrors = function(error) {
	
			var inputMap = {
				'number' : '#bt_card_number',
				'cvv' : '#bt_cvv',
				'expirationDate' : '#bt_exp_date'
			};
			if(!error.details) {
				$('.inputFields').addClass('inputError');
			}else{
				var i, errorLength = error.details.invalidFieldKeys.length;
				$('.inputFields').removeClass('inputError');
				for(var i=0; i < errorLength; i++) {
					$(inputMap[error.details.invalidFieldKeys[i]]).addClass('inputError');
				}
			}
		};

		BTFn.appendTo = function($cont, childSelector, options) {

			var input = document.createElement(childSelector);
			input.type = options.type;
			input.name = options.name;
			input.value = options.value;
			$cont.appendChild(input);
		};
	
		$(document).ready(function() {
	
			var $pay_btn = $('.pay-btn');
			BTFn.sendJSON($pay_btn);
			$pay_btn.on('click', function() {
				$(this).hide().closest('.btn_container').find('.loader_img').css('display', 'inline-block');
			});
		});
	})();
	</script>
</body>
</html>