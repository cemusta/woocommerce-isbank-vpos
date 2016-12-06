function validateCC() {
	var ccinput = jQuery('#innova-card-number');
	var e = ccinput.validateCreditCard({ accept: ['visa', 'mastercard'] });
  
	if(e.card_type == null){
		ccinput.removeClass();
		return;
	}
	else{
		ccinput.addClass(e.card_type.name);
	}

	if(e.valid){
		ccinput.addClass("valid");	
	}			
	else{
		ccinput.removeClass("valid");
	}
		
		
};
