<?php
session_start();
$paymentMessage = '';
if(!empty($_POST['stripeToken'])){
    
	// obtenir token
    
    $stripeToken  = $_POST['stripeToken'];
    
    // obtenir details client

    $customerName = $_POST['customerName'];
    $customerEmail = $_POST['customerEmail'];
	$customerPhone = $_POST['customerPhone'];
;
	
    $cardNumber = $_POST['cardNumber'];
    $cardCVC = $_POST['cardCVC'];
    $cardExpMonth = $_POST['cardExpMonth'];
    $cardExpYear = $_POST['cardExpYear'];    
    
	//inclure libraire Stripe PHP 
    require_once('stripe-php/init.php'); 
	
    //cle public et cle prive
    $stripe = array(
      "secret_key"      => "sk_test_51MTNZYJmTX1c6uonI6cLaTLQI3SjM4pwIrVC3NW5WEU65jo7TQ7aF1LLCC6KMHq26wVLfdM0onZUA4bexlKV5FQG00bIrnHAhD", 
      "publishable_key" => "pk_test_51MTNZYJmTX1c6uonS0ki0d6hPAdVfH8ChNFm4xNBlQa8GODx7BvosqBouycduxqRKt6jZ0WFkL8yQDAwtxnIYxBr00mWhssXfK"
    );    
	
    \Stripe\Stripe::setApiKey($stripe['secret_key']);    
    
	//ajouter un client a stripe
    $customer = \Stripe\Customer::create(array(
		'name' => $customerName,
		'description' => 'test description',
        'email' => $customerEmail,
        'source'  => $stripeToken,
		"address" => null,
        "phone" => $customerPhone
    ));  
	
    // details du produit a payer
	$itemName = $_POST['item_details'];
	$itemNumber = $_POST['item_number'];
	$itemPrice = $_POST['price'];
	$totalAmount = $_POST['total_amount'];
	$currency = $_POST['currency_code'];
	$orderNumber = $_POST['order_number'];    
    
    // details de paiement
    $payDetails = \Stripe\Charge::create(array(
       'customer' => $customer->id,
        'amount'   => $totalAmount,
        'currency' => $currency,
        'description' => $itemName,
        'metadata' => array(
            'order_id' => $orderNumber
        )
    ));   
	
    // fichier json pour les details de paiment
    $paymentResponse = $payDetails->jsonSerialize();
	
    // verifier si le paiement a ete un succes
    if($paymentResponse['amount_refunded'] == 0 && empty($paymentResponse['failure_code']) && $paymentResponse['paid'] == 1 && $paymentResponse['captured'] == 1){
        
		// details de la transaction
        $amountPaid = $paymentResponse['amount'];
        $balanceTransaction = $paymentResponse['balance_transaction'];
        $paidCurrency = $paymentResponse['currency'];
        $paymentStatus = $paymentResponse['status'];
        $paymentDate = date("Y-m-d H:i:s");        
       
	   //inserer la transaction dans la base de donnees
		include_once("database/dbconnect.php");
       
	   $insertTransactionSQL = "INSERT INTO transaction( cust_name, cust_email, cust_phone, card_number, card_cvc, card_exp_month, card_exp_year, item_name, item_number, item_price, item_price_currency, paid_amount, paid_amount_currency, txn_id, payment_status, created, modified)
        VALUES ('".$customerName. "','".$customerEmail."','".$customerPhone."','".$cardNumber."','".$cardCVC."','".$cardExpMonth."','".$cardExpYear."','".$itemName."','".$itemNumber."','".$itemPrice."','".$paidCurrency."','".$amountPaid."','".$paidCurrency."','".$balanceTransaction."','".$paymentStatus."','".$paymentDate."','".$paymentDate."')";
		
		mysqli_query($conn, $insertTransactionSQL) or die("database error: ". mysqli_error($conn));
       
	   $lastInsertId = mysqli_insert_id($conn); 
       
	   //si la commande a ete insere avec succes
       if($lastInsertId && $paymentStatus == 'succeeded'){
        $paymentMessage = "The payment was successful. Order ID: {$orderNumber}";
       } else{
          $paymentMessage = "failed";
       }
	   
    } else{
        $paymentMessage = "failed";
    }
} else{
    $paymentMessage = "failed";
}
$_SESSION["message"] = $paymentMessage;
header('location:index.php');
?>