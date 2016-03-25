<?php
/**
 * Braintree - Payment Gateway integration with 3D secure example
 * ==============================================================================
 * 
 * @version v1.0: braintree_dropin_with_3d_secure_demo.php 2016/03/25
 * @copyright Copyright (c) 2016, http://www.ilovephp.net
 * @author Sagar Deshmukh <sagarsdeshmukh91@gmail.com>
 * You are free to use, distribute, and modify this software
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
	//$braintree_cust_id = "31904842";
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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
</head>
<body>
<link href="style.css" type="text/css" rel="stylesheet" />
<h1 class="bt_title">Braintree Drop-in</h1>
<div class="dropin-page">
  <form id="checkout" method="post" action="">
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
    <div id="dropin">
      <div class="loader_container">
        <div>Loading...</div>
      </div>
    </div>
    <fieldset class="one_off_amount">
      <?php
                if(isset($_GET['amt'])) {
                    $amt = number_format((float)$_GET['amt'], 2, '.', '');
            ?>
      <h3>Your bill is for an amount of $ <?php echo $amt; ?></h3>
      <input type="hidden" name="amount" step="any" id="amount" value="<?php echo $amt; ?>" />
      <?php
                }else{
            ?>
      <label class="input-label" for="amount">
      <span class="field-name">Amount</span>
      <input id="amount" name="amount" class="input-field card-field" type="number" inputmode="numeric" placeholder="Amount" autocomplete="off" step="any">
      <div class="invalid-bottom-bar"></div>
      </label>
      <?php
                }
            ?>
    </fieldset>
    <div class="btn_container">
      <input type="submit" value="Make Payment" class="pay-btn">
      <span class="loader_img"></span> </div>
  </form>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script> 
<script src="https://js.braintreegateway.com/v2/braintree.js"></script> 
<!-- TO DO : Place below JS code in js file and include that JS file --> 
<script type="text/javascript">
(function() {
    
    var BTFn = {};
	var bt_client_token = '';

    BTFn.sendJSON = function($pay_btn) {

        $.ajax({
            dataType: "text",
            type: "POST",
            data:  { action: "generateclienttoken"},
            url: "braintree_dropin_with_3d_secure_demo.php",
            success: function (req) {
				bt_client_token = req;
                BTFn.initBT(req, $pay_btn);
            },
            error: function() {
            }
        });
    };

    BTFn.initBT = function(req, $pay_btn) {

        braintree.setup(
            req,
            'dropin', {
                container: 'dropin',
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
						BTFn.appendTo(document.forms.checkout, 'input', {name: 'payment_method_nonce', type: 'hidden', value: response.nonce});
                    	document.forms.checkout.submit();
					  } else {
						// Handle errors
						alert("Error : "+error.message);
					  }
					});

                },
                onReady:function(){
                    $('.loader_container').remove();
                },
                onError: function(error) {
                    $pay_btn.show().closest('.btn_container').find('.loader_img').hide();
                }
        });
    };

    BTFn.formValidate = function($form, $submit, $amount, $pay_btn) {

        var THIS = this;

        $submit.on('click', function(e) {

            $('.input-label .invalid-bottom-bar').removeClass('invalid');
            $(this).hide().closest('.btn_container').find('.loader_img').css('display', 'inline-block');
        });
    };

    BTFn.updateForm = function($form, link) {
        
        $form.attr('action', link);
        $('.one_off_amount, .monthly_amount').toggleClass('hide');
    };

    BTFn.appendTo = function($cont, childSelector, options) {

        var input = document.createElement(childSelector);
        input.type = options.type;
        input.name = options.name;
        input.value = options.value;
        $cont.appendChild(input);
    };

    $(document).ready(function() {

        $('.loader_container').find("div").show();

		var $form = $('#checkout'), $submit = $('#checkout input[type="submit"]'), $amount = $('input[name="amount"]'), $pay_btn = $('.pay-btn');

        BTFn.sendJSON($pay_btn);
        BTFn.formValidate($form, $submit, $amount, $pay_btn);
    });
})();

</script>
</body>
</html>