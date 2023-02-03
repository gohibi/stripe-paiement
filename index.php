<?php 
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-creditcardvalidator/1.0.0/jquery.creditCardValidator.js"></script>
<script type="text/javascript" src="script/payment.js"></script>
<link rel="stylesheet" href="build/css/intlTelInput.css">
<title>Stripe integration PHP </title>
</head>
<body class="">

	
	<div class="container" style="min-height:500px;">
	<div class=''>
	</div>

    <div class="container">	
	<div class="row">	
		<h2></h2>	

		<?php 
		if(isset($_SESSION["message"]) && $_SESSION["message"] && $_SESSION["message"] == 'failed') {
		?>			
			<div class="alert alert-danger">
			  <?php 
			  echo "Error : Payment failed!"; 
			  $_SESSION["message"] = '';
			  ?>
			</div>
		<?php 
		} elseif(isset($_SESSION["message"]) && $_SESSION["message"]) {
		?>
			<div class="alert alert-success">
			  <?php 
			  echo $_SESSION["message"]; 
			  $_SESSION["message"] = '';
			  ?>
			</div>
		<?php } ?>
		<div class="panel panel-default">			
			<div class="panel-heading">Processus de paiement</div>
			<div class="panel-body">
				<form action="process.php" method="POST" id="paymentForm">	
					<div class="row">
						<div class="col-md-8" style="border-right:1px solid #ddd;">
							<h4 align="center">Details Client</h4>
							<div class="form-group">
								<label><b>Nom Client<span class="text-danger">*</span></b></label>
								<input type="text" name="customerName" id="customerName" class="form-control" value="">
								<span id="errorCustomerName" class="text-danger"></span>
							</div>
							<div class="form-group">
								<label><b>Email <span class="text-danger">*</span></b></label>
								<input type="text" name="customerEmail" id="customerEmail" class="form-control" value="">
								<span id="errorEmailAddress" class="text-danger"></span>
							</div>
							<div class="form-group">
								<label><b>Telephone Mobile <span class="text-danger">*</span></b></label>
								<input type="tel" name="customerPhone" id="customerPhone" class="form-control" maxlength=10></input>
								<span id="errorCustomerPhone" class="text-danger"></span>
							</div>
							<hr>
							<h4 align="center">Details Paiement</h4>
							<div class="form-group">
								<label>Numer Carte <span class="text-danger">*</span></label>
								<input type="text" name="cardNumber" id="cardNumber" class="form-control" placeholder="1234 5678 9012 3456" maxlength="20" onkeypress="">
								<span id="errorCardNumber" class="text-danger"></span>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-4">
										<label>Mois </label>
										<input type="text" name="cardExpMonth" id="cardExpMonth" class="form-control" placeholder="MM" maxlength="2" onkeypress="return validateNumber(event);">
										<span id="errorCardExpMonth" class="text-danger"></span>
									</div>
									<div class="col-md-4">
										<label>Annee</label>
										<input type="text" name="cardExpYear" id="cardExpYear" class="form-control" placeholder="YYYY" maxlength="4" onkeypress="return validateNumber(event);">
										<span id="errorCardExpYear" class="text-danger"></span>
									</div>
									<div class="col-md-4">
										<label>CVC</label>
										<input type="text" name="cardCVC" id="cardCVC" class="form-control" placeholder="123" maxlength="4" onkeypress="return validateNumber(event);">
										<span id="errorCardCvc" class="text-danger"></span>
									</div>
								</div>
							</div>
							<br>
							<div align="center">
							<input type="hidden" name="price" value="5000">
							<input type="hidden" name="total_amount" value="5000">
							<input type="hidden" name="currency_code" value="XOF">
							<input type="hidden" name="item_details" value="Jeans">
							<input type="hidden" name="item_number" value="TS1234567">
							<input type="hidden" name="order_number" value="SKA987654321">
							<input type="button" name="payNow" id="payNow" class="btn btn-success btn-sm" onclick="stripePay(event)" value="Payer Maintenant" />
							</div>
							<br>
						</div>
						<div class="col-md-4">
							<h4 align="center">Details Commande</h4>
							<div class="table-responsive" id="order_table">
								<table class="table table-bordered table-striped">
									<tbody>
										<tr>  
											<th>Nom Produit</th>  
											<th>Quantite</th>  
											<th>Prix</th>  
											<th>Total</th>  
										</tr>
										<tr>
											<td><strong>Jeans</strong></td>
											<td>1</td>
											<td align="right">xof 5000.00</td>
											<td align="right">xof 5000.00</td>
										</tr>
										<tr>  
											<td colspan="3" align="right">Total</td>  
											<td align="right"><strong>xof 5000.00</strong></td>
										</tr>
									</tbody>
								</table>									
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>			
	</div>		
    </div>
    <div class="insert-post-ads1" style="margin-top:20px;">

    </div>
    </div>

<script src="build/js/intlTelInput.js"></script>
<script src="assets/js/validation.js"></script>

<script>
var input = document.querySelector("#customerPhone");
window.intlTelInput(input, {
  allowDropdown: false,
  // autoHideDialCode: false,
  //autoPlaceholder: "polite",
  // dropdownContainer: document.body,
  // excludeCountries: ["us"],
  // formatOnDisplay: false,
  // geoIpLookup: function(callback) {
  //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
  //     var countryCode = (resp && resp.country) ? resp.country : "";
  //     callback(countryCode);
  //   });
  // },
  // hiddenInput: "full_number",
    initialCountry: "ci",
  // localizedCountries: { 'de': 'Deutschland' },
  // nationalMode: false,
  onlyCountries: ['ci'],
  //placeholderNumberType: "MOBILE",
  // preferredCountries: ['cn', 'jp'],
  separateDialCode: true,
  customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
            return  selectedCountryPlaceholder;
    },
  utilsScript: "build/js/utils.js",
}); 
</script>
   
</body>
</html>

