<%
' What do you want to do?
If Request("sec") = "" Or  (Request("sec") <> "" And Request("sec") <> "save" And Request("sec") <> "charge") Then
	If Request("ClientID") = "" Then
		Response.Redirect("http://www.statusrequest.com")
	End If
%>
	<html>
	<head>
	<title>Secure Checkout</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style>
			.Head {font-family:Tahoma; Helvetica; font-size:14px; color:Black; text-decoration:none;}
			.Body {font-family:Tahoma; Helvetica; font-size:10px; color:White; text-decoration:none;}
			A.Body {font-family:Helvetica; font-size:12px; color:white; text-decoration:none;}
			A.Body:hover {color:yellow; text-decoration:underline;}
			A.Body:active {color:yellow;}
			A.Body:visited {color:white;}
			A.Body:visited:hover {color:yellow; text-decoration:underline;}
			.smallBody {font-family:Arial; font-size:10px; color:White; text-decoration:none;}
			A.smallBody {font-family:Arial; font-size:10px; color:White; text-decoration:none;}
			A.smallBody:hover {color:yellow; text-decoration:underline;}
			A.smallBody:active {color:yellow;}
			A.smallBody:visited {color:white;}
			A.smallBody:visited:hover {color:yellow; text-decoration:underline;}
		</style>

	<script>
		// Swap layer visibility
		function show(id) {
			document.getElementById(id).style.visibility = "visible";
			document.getElementById(id).style.display = "block";
		}
		function hide(id) {
			document.getElementById(id).style.visibility = "hidden";
			document.getElementById(id).style.display = "none";
		}

		// Only accept digits (numbers)
		function onlyNumbers(e){
			var keynum
			var keychar
			var ltrcheck
			var crcheck
			if(window.event){ // IE
				keynum = e.keyCode
			}else if(e.which){ // Netscape/Firefox/Opera
				keynum = e.which
			}
			keychar = String.fromCharCode(keynum)
			ltrcheck = /\D/ //Regular expression for NON-digit (letter)
			crcheck = /\cM/ //Regular expression ctrl-M (enter)
		//	return !ltrcheck.test(keychar) //Return true if not a letter
			return (!ltrcheck.test(keychar)||crcheck.test(keychar)) //Return true if not a letter OR enter
		}
	</script>
	
	<!-- Time/Date Math Function Libraries -->
	<script language="JavaScript" src="/shared/includes/timedate.js"></script>	

	<!-- Form Validation Script -->
	<script>
	// Verify that terms were accepted
	function Validate(theForm){
		// In "TEST" mode? If so, don't validate.
		if (theForm.test){
			if (theForm.test.value == "Yes"){
				return true;
			}
		}
	// Username
		if (theForm.Username){
			if (theForm.Username.value == ""){
				theForm.Username.style.background="#FF0000";
				alert("Please Enter The Username You Want For Your Membership");
				theForm.Username.style.background="#FFFFFF";
				theForm.Username.focus();
				return false;
			}
		}
	// Password
		if (theForm.Password){
			if (theForm.Password.value == ""){
				theForm.Password.style.background="#FF0000";
				alert("Please Enter The Password You Want For Your Membership");
				theForm.Password.style.background="#FFFFFF";
				theForm.Password.focus();
				return false;
			}
			if (theForm.Password.value != theForm.Password2.value){
				theForm.Password.style.background="#FF0000";
				theForm.Password2.style.background="#FF0000";
				alert("The Passwords You Entered Do Not Match - Please Correct Them");
				theForm.Password.style.background="#FFFFFF";
				theForm.Password2.style.background="#FFFFFF";
				theForm.Password.focus();
				return false;
			}
		}
	// First Name
		if (theForm.FirstName){
			if (theForm.FirstName.value == ""){
				theForm.FirstName.style.background="#FF0000";
				alert("Please Enter Your First Name");
				theForm.FirstName.style.background="#FFFFFF";
				theForm.FirstName.focus();
				return false;
			}
		}
	// Last Name
		if (theForm.LastName){
			if (theForm.LastName.value == ""){
				theForm.LastName.style.background="#FF0000";
				alert("Please Enter Your Last Name");
				theForm.LastName.style.background="#FFFFFF";
				theForm.LastName.focus();
				return false;
			}
		}
	// Address1
		if (theForm.Address1){
			if (theForm.Address1.value == ""){
				theForm.Address1.style.background="#FF0000";
				alert("Please Enter Your Street Address");
				theForm.Address1.style.background="#FFFFFF";
				theForm.Address1.focus();
				return false;
			}
		}
	// City
		if (theForm.City){
			if (theForm.City.value == ""){
				theForm.City.style.background="#FF0000";
				alert("Please Enter Your City");
				theForm.City.style.background="#FFFFFF";
				theForm.City.focus();
				return false;
			}
		}
	// State
		if (theForm.State){
			if (theForm.State.value == ""){
				theForm.State.style.background="#FF0000";
				alert("Please Enter Your State");
				theForm.State.style.background="#FFFFFF";
				theForm.State.focus();
				return false;
			}
		}
	// Zip Code
		if (theForm.Zip){
			if (theForm.Zip.value == ""){
				theForm.Zip.style.background="#FF0000";
				alert("Please Enter Your Zip Code");
				theForm.Zip.style.background="#FFFFFF";
				theForm.Zip.focus();
				return false;
			}
			var usa_regex = /(^\d{5}$)|(^\d{5}-\d{4}$)/;  // xxxxx or xxxxx-xxxx
//			var cdn_regex = /(^\D{1}\d{1}\D{1}\s\d{1}\D{1}\d{1}$)|(^\D{1}\d{1}\D{1}\-?\d{1}\D{1}\d{1}$)/;  // ANA NAN or ANA-NAN
// 			if (usa_regex.test(theForm.Zip.value) == false && cdn_regex.test(theForm.Zip.value) == false) { 
 			if (usa_regex.test(theForm.Zip.value) == false) { 
				theForm.Zip.style.background="#FF0000";
				alert('Please Enter a Valid Zip Code as "NNNNN" or "NNNNN-NNNN"');
				theForm.Zip.style.background="#FFFFFF";
				theForm.Zip.focus();
				return false;
			}
		}
	// Phone Number
		if (theForm.PhoneNumber){
			if (theForm.PhoneNumber.value == ""){
				theForm.PhoneNumber.style.background="#FF0000";
				alert("Please Enter Your Phone Number");
				theForm.PhoneNumber.style.background="#FFFFFF";
				theForm.PhoneNumber.focus();
				return false;
			}
// Don't test for proper formatting
//			var phone1_regex = /^\([1-9]\d{2}\)\s?\d{3}\-\d{4}$/;  // (xxx)xxx-xxxx
//			var phone2_regex = /^[1-9]\d{2}\-\d{3}\-\d{4}$/;  // xxx-xxx-xxxx
//			if (phone1_regex.test(theForm.PhoneNumber.value) == false && phone2_regex.test(theForm.PhoneNumber.value) == false) { 
//				theForm.PhoneNumber.style.background="#FF0000";
//				alert('Please Enter a Valid Phone Number as (NNN)NNN-NNNN or NNN-NNN-NNNN');
//				theForm.PhoneNumber.style.background="#FFFFFF";
//				theForm.PhoneNumber.focus();
//				return false;
//			}
// Use this instead to count the digits
			var AllNumbers = theForm.PhoneNumber.value.replace(/[^\d]/g,'');
//alert(AllNumbers);
			if (AllNumbers.length != 10){
				theForm.PhoneNumber.style.background="#FF0000";
				alert("Please Enter a Valid Phone Number with Area Code and No Dashes");
				theForm.PhoneNumber.style.background="#FFFFFF";
				theForm.PhoneNumber.focus();
				return false;
			}
			document.prepayForm.ScrubbedPhoneNumber.value = AllNumbers;
		}
	// Email Address
		if (theForm.EmailAddress){
			if (theForm.EmailAddress.value == ""){
				theForm.EmailAddress.style.background="#FF0000";
				alert("Please Enter Your Email Address");
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for "@"
	 		if (theForm.EmailAddress.value.indexOf("@") == -1) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Contain an "@"');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for "@" as first char.
	 		if (theForm.EmailAddress.value.indexOf("@") == 0) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Cannot Start With a "@"');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for anything after "@"
	 		if (theForm.EmailAddress.value.length == (theForm.EmailAddress.value.indexOf("@")+1)) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Contain a Domain Name After the "@"');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for multiple "@"
	 		if (theForm.EmailAddress.value.substring((theForm.EmailAddress.value.indexOf("@")+1),theForm.EmailAddress.value.length).indexOf("@") != -1) {
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Contain Only One "@"');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for "."
	 		if (theForm.EmailAddress.value.indexOf(".") == -1) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Contain a "."');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for "." as first char.
	 		if (theForm.EmailAddress.value.indexOf(".") == 0) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Cannot Start With a "."');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for ".."
	 		if (theForm.EmailAddress.value.indexOf("..") != -1) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Not Contain Adjacent Dots ("..")');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for "." adjacent to "@"
	 		if (theForm.EmailAddress.value.indexOf(".@") != -1 || theForm.EmailAddress.value.indexOf("@.") != -1) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Not Contain Adjacent Dots and Ats (".@" or "@.") ');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for TLD
	 		if (theForm.EmailAddress.value.length == (theForm.EmailAddress.value.indexOf(".")+1)) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Contain a Top Level Domain (i.e. ".com") After the "."');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for TLD at least 2 char.
			var domain = theForm.EmailAddress.value.substring((theForm.EmailAddress.value.indexOf("@")+1),theForm.EmailAddress.value.length);
			if (domain.length - (domain.indexOf(".")+1) < 2) {
				theForm.EmailAddress.style.background="#FF0000";
				alert('The Top Level Domain (i.e. ".com") Must Contain At Least 2 Characters');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for TLD over 3 char.
//			if (domain.length - (domain.indexOf(".")+1) > 3) {
//				theForm.EmailAddress.style.background="#FF0000";
//				alert('The Top Level Domain (i.e. ".com") Cannot Contain More Than 3 Characters');
//				theForm.EmailAddress.style.background="#FFFFFF";
//				theForm.EmailAddress.focus();
//				return false;
//			}
			// Check for "_" in TLD
			if (theForm.EmailAddress.value.indexOf("_") > theForm.EmailAddress.value.indexOf("@")) {
				theForm.EmailAddress.style.background="#FF0000";
				alert('The Top Level Domain (i.e. ".com") Cannot Contain Any Underscores ("_")');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for spaces
	 		if (theForm.EmailAddress.value.indexOf(" ") != -1) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Not Contain Any Spaces (" ")');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
			// Check for illegal char.
			var email_regex = /^\w(?:\w|-|\.(?!\.|@))*@\w(?:\w|-|\.(?!\.))*\.\w{2,3}/;
	 		if (email_regex.test(theForm.EmailAddress.value) == false) { 
				theForm.EmailAddress.style.background="#FF0000";
				alert('Your Email Must Not Contain Any Of These Characters: ( )<>,;:\"`\'{ }/~#$%^&*+=[ ]|? ');
				theForm.EmailAddress.style.background="#FFFFFF";
				theForm.EmailAddress.focus();
				return false;
			}
		}
	// Pay By Credit Card
		if (document.getElementById("PayByCC").style.visibility == "visible"){
		// Credit Card Number
			if (theForm.CardNumber){
				if (theForm.CardNumber.value == ""){
					theForm.CardNumber.style.background="#FF0000";
					alert("Please Enter The Credit Card Number (No Spaces)");
					theForm.CardNumber.style.background="#FFFFFF";
					theForm.CardNumber.focus();
					return false;
				}
				// Verify Card Number Form
				switch(theForm.CardType.value){
					// Visa
					case 'Visa':
					// 13 or 16 Digits Starting With "4"
						var prefix = parseInt(theForm.CardNumber.value.substring(0,1));
						if ((theForm.CardNumber.value.length != 13 && theForm.CardNumber.value.length != 16) || prefix != 4){
							theForm.CardNumber.style.background="#FF0000";
							alert("Please Enter A Valid Visa Card Number (No Spaces)");
							theForm.CardNumber.style.background="#FFFFFF";
							theForm.CardNumber.focus();
							return false;
						}
	 					break;
					// Mastercard
					case 'MasterCard':
					// 16 Digits Starting With Ranging From "51" to "55"
						var prefix = parseInt(theForm.CardNumber.value.substring(0,2));
						if (theForm.CardNumber.value.length != 16 || (prefix < 51 || prefix > 55)){
							theForm.CardNumber.style.background="#FF0000";
							alert("Please Enter A Valid MasterCard Number (No Spaces)");
							theForm.CardNumber.style.background="#FFFFFF";
							theForm.CardNumber.focus();
							return false;
						}
	 					break;
					// Amex
					case 'American Express':
					// 15 Digits Starting With "34" or "37"
						var prefix = parseInt(theForm.CardNumber.value.substring(0,2));
						if (theForm.CardNumber.value.length != 15 || (prefix != 34 && prefix != 37)){
							theForm.CardNumber.style.background="#FF0000";
							alert("Please Enter A Valid American Express Card Number (No Spaces)");
							theForm.CardNumber.style.background="#FFFFFF";
							theForm.CardNumber.focus();
							return false;
						}
						break;
					// Discover
					case 'Discover':
					// 16 Digits Starting With "6011"
						var prefix = parseInt(theForm.CardNumber.value.substring(0,4));
						if (theForm.CardNumber.value.length != 16 || prefix != 6011){
							theForm.CardNumber.style.background="#FF0000";
							alert("Please Enter A Valid Discover Card Number (No Spaces)");
							theForm.CardNumber.style.background="#FFFFFF";
							theForm.CardNumber.focus();
							return false;
						}
	 					break;
				}
				// Verify Card Number Against Check Digit Using MOD10
				var ar = new Array(theForm.CardNumber.value.length);
				var i = 0,sum = 0;
				for(i = 0; i < theForm.CardNumber.value.length; ++i){
					ar[i] = parseInt(theForm.CardNumber.value.charAt(i));
				}
				for(i = ar.length -2; i >= 0; i-=2){	// you have to start from the right, and work back.
					ar[i] *= 2;							// every second digit starting with the right most (check digit)
					if(ar[i] > 9) ar[i]-=9;				// will be doubled, and summed with the skipped digits.
				}										// if the double digit is > 9, ADD those individual digits together 
				for(i = 0; i < ar.length; ++i){
					sum += ar[i];
				}
				if ((sum%10)!=0){						// if the sum is not evenly divisible by 10 it fails
					theForm.CardNumber.style.background="#FF0000";
					alert("Please Enter A Valid Credit Card Number (No Spaces)");
					theForm.CardNumber.style.background="#FFFFFF";
					theForm.CardNumber.focus();
					return false;
				}
			}
		// Credit Card Expiration Date Passed?
			if (theForm.CardExp){
				if (theForm.CardExp.value == ""){
					theForm.CardExp.style.background="#FF0000";
					alert("Please Enter Your Credit Card Expiration Date (mmyy)");
					theForm.CardExp.style.background="#FFFFFF";
					theForm.CardExp.focus();
					return false;
				}
				var expires = new Date('20'+theForm.CardExp.value.substring(2,4),theForm.CardExp.value.substring(0,2),0,0,0);
				if (compareDates(formatDate(expires,'MMM d, y'),"MMM d, y",formatDate(new Date(),'MMM d, y'),"MMM d, y")== 0){
					theForm.CardExp.style.background="#FF0000";
					alert("The Expiration Date You Entered Indicates That The Credit Card Is Expired");
					theForm.CardExp.style.background="#FFFFFF";
					theForm.CardExp.focus();
					return false;
				}
				document.prepayForm.CardMonth.value = theForm.CardExp.value.substring(0,2);
				document.prepayForm.CardYear.value = theForm.CardExp.value.substring(2,4);
			}
		// Credit Card CVV
			if (theForm.CVV){
				if (theForm.CVV.value == ""){
					theForm.CVV.style.background="#FF0000";
					alert("Please Enter Your Credit Card CVV Security Code");
					theForm.CVV.style.background="#FFFFFF";
					theForm.CVV.focus();
					return false;
				}
			}
		// Credit Card Name
			if (theForm.CardName){
				if (theForm.CardName.value == ""){
					theForm.CardName.style.background="#FF0000";
					alert("Please Enter The Name Exactly As It Appears On Your Credit Card");
					theForm.CardName.style.background="#FFFFFF";
					theForm.CardName.focus();
					return false;
				}
			}
		}
	// Pay By E-Check
		if (document.getElementById("PayByEcheck").style.visibility == "visible"){
		// Bank Routing Number
			if (theForm.RoutingNumber){
				if (theForm.RoutingNumber.value == ""){
					theForm.RoutingNumber.style.background="#FF0000";
					alert("Please Enter The Bank Routing Number From Your Check");
					theForm.RoutingNumber.style.background="#FFFFFF";
					theForm.RoutingNumber.focus();
					return false;
				}
				if (theForm.RoutingNumber.value.length != 9){
					theForm.RoutingNumber.style.background="#FF0000";
					alert("Please Enter a Valid 9 Digit Bank Routing Number");
					theForm.RoutingNumber.style.background="#FFFFFF";
					theForm.RoutingNumber.focus();
					return false;
				}
			}
		// Bank Account Number
			if (theForm.AccountNumber){
				if (theForm.AccountNumber.value == ""){
					theForm.AccountNumber.style.background="#FF0000";
					alert("Please Enter Your Checking Account Number From Your Check");
					theForm.AccountNumber.style.background="#FFFFFF";
					theForm.AccountNumber.focus();
					return false;
				}
				if (theForm.AccountNumber.value.length < 5){
					theForm.AccountNumber.style.background="#FF0000";
					alert("Please Enter a Valid Bank Account Number");
					theForm.AccountNumber.style.background="#FFFFFF";
					theForm.AccountNumber.focus();
					return false;
				}
			}
		// Bank Name
			if (theForm.BankName){
				if (theForm.BankName.value == ""){
					theForm.BankName.style.background="#FF0000";
					alert("Please Enter The Name of Your Bank");
					theForm.BankName.style.background="#FFFFFF";
					theForm.BankName.focus();
					return false;
				}
			}
		// Name on Account
			if (theForm.AcctName){
				if (theForm.AcctName.value == ""){
					theForm.AcctName.style.background="#FF0000";
					alert("Please Enter The Name on the Account (Your Name, Company Name, etc.)");
					theForm.AcctName.style.background="#FFFFFF";
					theForm.AcctName.focus();
					return false;
				}
			}
		}
	// E-Bill Choice
		if (theForm.Ebill.checked){
			document.prepayForm.SendEbill.value = "Yes";
		}else{
			document.prepayForm.SendEbill.value = "No";
		}
	// Agree to T&C's
		if (!theForm.Terms.checked){
			theForm.Terms.style.background="#FF0000";
			alert("You must agree with the Terms & Conditions to complete your order.");
			theForm.Terms.style.background="#FFFFFF";
			theForm.Terms.focus();
			return false;
		}
	// PASSED!
		return true;
	}
	</script>

	</head>
	
	
<body bgcolor="#FFFFFF" text="#000000" onload="window.resizeTo(830,775)">
<!-- <form action="http://www.wbsfiles.com/shared/pages/mailform.asp" method="get" name="Dealer Signup" id="Dealer Signup">-->
<form action="" method="POST" name="prepayForm" id="prepayForm" onsubmit="return Validate(this)">
	<table border="0" bgcolor="#999999" width="780">
	  <tr><td>
	        
        <table width="775" border="0" align="center" bgcolor="#FFFFFF" height="454">
          <tr> 
	            <td colspan="3"> 
	              
              <div align="center"><font face="Tahoma, Helvetica, sans-serif" size="4" font color="#000000"><b><font color="#2F3359">Axiom 
                Mobility Signup Form</font></b></font></div>
	            </td>
	          </tr>
	          <tr> 
	            <td width="390"> 
	              <table width="100%" border="0">
	                <tr> 
	                  
                  <td class=Head><b>Member Signup</b></td>
	                </tr>
	              </table>
	              
              <table width="390" border="0" bgcolor="#2F3359" cellpadding="3">
                <tr class=body> 
	                  <td width="33%">Username<br>
	                    <input type="text" name="Username" size="15" value="<%=Request("Username")%>">
	                  </td>
	                  <td width="34%">Password<br>
	                    <input type="password" name="Password" size="15">
	                  </td>
	                  <td width="33%">Confirm Password<br>
	                    <input type="password" name="Password2" size="15">
	                  </td>
	                </tr>
	                <tr class=body> 
	                  <td colspan="3">Username and Password must be at least 6 characters 
	                    long. You will use this to log into your account and add minutes.</td>
	                </tr>
	              </table>
	            </td>
	            <td rowspan="2" valign="top" width="375"> 
	              <table width="100%" border="0">
	                <tr> 
	                  
                  <td class=Head><b>Billing Information</b></td>
	                </tr>
	              </table>
	              
              <table width="370" border="0" bgcolor="#2F3359" cellpadding="3">
                <tr class=Body> 
	                  <td width="50%">First Name<br>
	                    <input type="text" name="FirstName" size="15" value="<%=Request("FirstName")%>">
	                  </td>
	                  <td width="50%">Last Name<br>
	                    <input type="text" name="LastName" size="20" value="<%=Request("LastName")%>">
	                  </td>
	                </tr>
	                <tr class=Body> 
	                  <td width="50%">Address 1<br>
	                    <input type="text" name="Address1" size="25" value="<%=Request("Address1")%>">
	                  </td>
	                  <td width="50%">Address 2<br>
	                    <input type="text" name="Address2" size="15" value="<%=Request("Address2")%>">
	                  </td>
	                </tr>
	                <tr class=Body> 
	                  <td width="50%" height="45">City<br>
	                    <input type="text" name="City" size="15" value="<%=Request("City")%>">
	                  </td>
	                  <td width="50%" height="45">State<br>
	                    <select name="State">
                      <option>&gt; Select</option>
                      <option value="AK"<% If Request("State") = "AK" Then Response.Write(" selected") End If%>>Alaska</option>
                      <option value="AL"<% If Request("State") = "AL" Then Response.Write(" selected") End If%>>Alabama</option>
                      <option value="AR"<% If Request("State") = "AR" Then Response.Write(" selected") End If%>>Arkansas</option>
                      <option value="AS"<% If Request("State") = "AS" Then Response.Write(" selected") End If%>>American Samoa</option>
                      <option value="AZ"<% If Request("State") = "AZ" Then Response.Write(" selected") End If%>>Arizona</option>
                      <option value="CA"<% If Request("State") = "CA" Then Response.Write(" selected") End If%>>California</option>
                      <option value="CO"<% If Request("State") = "CO" Then Response.Write(" selected") End If%>>Colorado</option>
                      <option value="CT"<% If Request("State") = "CT" Then Response.Write(" selected") End If%>>Connecticut</option>
                      <option value="DC"<% If Request("State") = "DC" Then Response.Write(" selected") End If%>>District of Columbia</option>
                      <option value="DE"<% If Request("State") = "DE" Then Response.Write(" selected") End If%>>Delaware</option>
                      <option value="FL"<% If Request("State") = "FL" Then Response.Write(" selected") End If%>>Florida</option>
                      <option value="GA"<% If Request("State") = "GA" Then Response.Write(" selected") End If%>>Georgia</option>
                      <option value="GU"<% If Request("State") = "GU" Then Response.Write(" selected") End If%>>Guam</option>
                      <option value="HI"<% If Request("State") = "HI" Then Response.Write(" selected") End If%>>Hawaii</option>
                      <option value="IA"<% If Request("State") = "IA" Then Response.Write(" selected") End If%>>Iowa</option>
                      <option value="ID"<% If Request("State") = "ID" Then Response.Write(" selected") End If%>>Idaho</option>
                      <option value="IL"<% If Request("State") = "IL" Then Response.Write(" selected") End If%>>Illinois</option>
                      <option value="IN"<% If Request("State") = "IN" Then Response.Write(" selected") End If%>>Indiana</option>
                      <option value="KS"<% If Request("State") = "KS" Then Response.Write(" selected") End If%>>Kansas</option>
                      <option value="KY"<% If Request("State") = "KY" Then Response.Write(" selected") End If%>>Kentucky</option>
                      <option value="LA"<% If Request("State") = "LA" Then Response.Write(" selected") End If%>>Louisiana</option>
                      <option value="MA"<% If Request("State") = "MA" Then Response.Write(" selected") End If%>>Massachusetts</option>
                      <option value="MD"<% If Request("State") = "MD" Then Response.Write(" selected") End If%>>Maryland</option>
                      <option value="ME"<% If Request("State") = "ME" Then Response.Write(" selected") End If%>>Maine</option>
                      <option value="MI"<% If Request("State") = "MI" Then Response.Write(" selected") End If%>>Michigan</option>
                      <option value="MN"<% If Request("State") = "MN" Then Response.Write(" selected") End If%>>Minnesota</option>
                      <option value="MO"<% If Request("State") = "MO" Then Response.Write(" selected") End If%>>Missouri</option>
                      <option value="MS"<% If Request("State") = "MS" Then Response.Write(" selected") End If%>>Mississippi</option>
                      <option value="MT"<% If Request("State") = "MT" Then Response.Write(" selected") End If%>>Montana</option>
                      <option value="NC"<% If Request("State") = "NC" Then Response.Write(" selected") End If%>>North Carolina</option>
                      <option value="ND"<% If Request("State") = "ND" Then Response.Write(" selected") End If%>>North Dakota</option>
                      <option value="NE"<% If Request("State") = "NE" Then Response.Write(" selected") End If%>>Nebraska</option>
                      <option value="NH"<% If Request("State") = "NH" Then Response.Write(" selected") End If%>>New Hampshire</option>
                      <option value="NJ"<% If Request("State") = "NJ" Then Response.Write(" selected") End If%>>New Jersey</option>
                      <option value="NM"<% If Request("State") = "NM" Then Response.Write(" selected") End If%>>New Mexico</option>
                      <option value="NV"<% If Request("State") = "NV" Then Response.Write(" selected") End If%>>Nevada</option>
                      <option value="NY"<% If Request("State") = "NY" Then Response.Write(" selected") End If%>>New York</option>
                      <option value="OH"<% If Request("State") = "OH" Then Response.Write(" selected") End If%>>Ohio</option>
                      <option value="OK"<% If Request("State") = "OK" Then Response.Write(" selected") End If%>>Oklahoma</option>
                      <option value="OR"<% If Request("State") = "OR" Then Response.Write(" selected") End If%>>Oregon</option>
                      <option value="PA"<% If Request("State") = "PA" Then Response.Write(" selected") End If%>>Pennsylvania</option>
                      <option value="PR"<% If Request("State") = "PR" Then Response.Write(" selected") End If%>>Puerto Rico</option>
                      <option value="PW"<% If Request("State") = "PW" Then Response.Write(" selected") End If%>>Palau</option>
                      <option value="RI"<% If Request("State") = "RI" Then Response.Write(" selected") End If%>>Rhode Island</option>
                      <option value="SC"<% If Request("State") = "SC" Then Response.Write(" selected") End If%>>South Carolina</option>
                      <option value="SD"<% If Request("State") = "SD" Then Response.Write(" selected") End If%>>South Dakota</option>
                      <option value="TN"<% If Request("State") = "TN" Then Response.Write(" selected") End If%>>Tennessee</option>
                      <option value="TX"<% If Request("State") = "TX" Then Response.Write(" selected") End If%>>Texas</option>
                      <option value="UT"<% If Request("State") = "UT" Then Response.Write(" selected") End If%>>Utah</option>
                      <option value="VA"<% If Request("State") = "VA" Then Response.Write(" selected") End If%>>Virginia</option>
                      <option value="VI"<% If Request("State") = "VI" Then Response.Write(" selected") End If%>>Virgin Islands</option>
                      <option value="VT"<% If Request("State") = "VT" Then Response.Write(" selected") End If%>>Vermont</option>
                      <option value="WA"<% If Request("State") = "WA" Then Response.Write(" selected") End If%>>Washington</option>
                      <option value="WI"<% If Request("State") = "WI" Then Response.Write(" selected") End If%>>Wisconsin</option>
                      <option value="WV"<% If Request("State") = "WV" Then Response.Write(" selected") End If%>>West Virginia</option>
                      <option value="WY"<% If Request("State") = "WY" Then Response.Write(" selected") End If%>>Wyoming</option>
                    </select>
	                  </td>
	                </tr>
	                <tr class=Body> 
	                  <td width="50%">Zip<br>
	                    <input type="text" name="Zip" size="10" onKeyPress="return onlyNumbers(event);" value="<%=Request("Zip")%>">
	                  </td>
	                  <td width="50%">Daytime Phone<br>
	                    <input type="text" name="PhoneNumber" size="15" onKeyPress="return onlyNumbers(event);" value="<%=Request("PhoneNumber")%>">
	                  </td>
	                </tr>
	                <tr class=Body> 
	                  <td width="50%">Email Address<br>
	                    <input type="text" name="EmailAddress" size="25" value="<%=Request("EmailAddress")%>">
	                  </td>
	                  <td width="50%">Email will only be used for billing and customer 
	                    service.</td>
	                </tr>
	              </table>
	            </td>
	          </tr>
	          <tr> 
	            <td height="75" width="390"> 
	              <table width="100%" border="0">
	                <tr> 
	                  
                  <td class=Head height="2"><b>Method of Payment&nbsp;&nbsp;
<!--                    <input type="radio" name="PayMethod" value="CC" onClick="hide('PayByEcheck'); show('PayByCC');"<% If Request("PayMethod") = "" Or Request("PayMethod") = "CC" Then Response.Write(" checked") End If%>>
                    <font color="#FF0000">Credit Card</font>&nbsp;&nbsp; 
                    <input type="radio" name="PayMethod" value="Echeck" onClick="hide('PayByCC'); show('PayByEcheck');"<% If Request("PayMethod") = "Echeck" Then Response.Write(" checked") End If%>>
                    <font color="#FF0000">Pay by Check</font></b>--></td>
	                </tr>
	              </table>

				<div id="PayByCC" style="position:static; visibility:visible; display:block;">
                <table width="100%" border="0" bgcolor="#2F3359" cellpadding="3">
                  <tr class=Body> 
	                  <td width="33%">Type of Card<br>
	                    <select name="CardType">
	                      <option value="Visa"<% If Request("CardType") = "" Or Request("CardType") = "Visa" Then Response.Write(" selected") End If%>>Visa</option>
	                      <option value="MasterCard"<% If Request("CardType") = "MasterCard" Then Response.Write(" selected") End If%>>MasterCard</option>
	                      <option value="Discover"<% If Request("CardType") = "Discover" Then Response.Write(" selected") End If%>>Discover</option>
	                      <option value="American Express"<% If Request("CardType") = "American Express" Then Response.Write(" selected") End If%>>American Express</option>
	                    </select>
	                  </td>
	                  <td width="29%">Card Number<br>
	                    <input type="text" name="CardNumber" size="15" onKeyPress="return onlyNumbers(event);" value="<%=Request("CardNumber")%>">
	                  </td>
	                  <td width="38%">Expiration Date (mmyy)<br>
	                    <input type="text" name="CardExp" id="CardExp" size="10" maxlength="4" onKeyPress="return onlyNumbers(event);" value="<%=Request("CardExp")%>">
	                  </td>
	                </tr>
	                <tr class=Body> 
	                  <td colspan="3">&nbsp;</td>
	                </tr>
	                <tr class=Body> 
	                  <td width="33%">3 or 4 Digit CVV Code<br>
	                    <input type="text" name="CVV" size="5" maxlength="4" onKeyPress="return onlyNumbers(event);" value="<%=Request("CVV")%>">
	                  </td>
	                  <td colspan="2">Name on Card<br>
	                    <input type="text" name="CardName" size="25" value="<%=Request("CardName")%>">
	                  </td>
	                </tr>
	                <tr class=Body> 
	                  <td colspan="3"> 
	                    <div align="center"> <br>
	                      <input type="checkbox" name="Ebill" value="Yes"<% If Request("Ebill") = "" Or Request("Ebill") = "Yes" Then Response.Write(" checked") End If%>>
	                      <font size="1">&nbsp;Send e-Bill - (Receive electronic bills 
	                      in your email box) </font></div>
	                  </td>
	                </tr>
	              </table>
				</div>
				<div id="PayByEcheck" style="position:static; visibility:hidden; display:none;">
<!--					
                <table width="390" border="0" bgcolor="#2F3359" cellpadding="3">
                  <tr class="Body">
						<td width="50%">
							Bank Routing Number<br>
							<input type="text" name="RoutingNumber" size="15" onKeyPress="return onlyNumbers(event);" value="<%=Request("RoutingNumber")%>">
						</td>
						<td width="50%">
							Bank Account Number<br>
							<input type="text" name="AccountNumber" size="15" onKeyPress="return onlyNumbers(event);" value="<%=Request("AccountNumber")%>">
						</td>
					</tr>
					<tr class="Body">
						<td colspan="2">
							<img src="/shared/images/Echeck.gif" alt="" width="370" height="65" border="0"><br>
							<img src="/shared/images/spacer.gif" alt="" width="1" height="3" border="0"><br>
						</td>
					</tr>
                  <tr class="Body">
						<td width="50%">
							Bank Name<br>
							<input type="text" name="BankName" size="25" value="<%=Request("BankName")%>">
						</td>
						<td width="50%">
							Account Type<br>
		                    <select name="AcctType">
	 	                      <option value="CHECKING"<% If Request("AcctType") = "" Or Request("AcctType") = "CHECKING" Then Response.Write(" selected") End If%>>Consumer Checking</option>
    		                  <option value="SAVINGS"<% If Request("AcctType") = "SAVINGS" Then Response.Write(" selected") End If%>>Consumer Savings</option>
            		          <option value="BUSINESSCHECKING"<% If Request("AcctType") = "BUSINESSCHECKING" Then Response.Write(" selected") End If%>>Corporate Checking</option>
                    		</select>
						</td>
					</tr>
                  <tr class="Body">
						<td colspan="2">
							Name on Account<br>
							<input type="text" name="AcctName" size="53" value="<%=Request("AcctName")%>">
						</td>
					</tr>
					<tr class="Body">
						<td colspan="2" align="center"> 
							<input type="checkbox" name="Ebill" value="Yes"<% If Request("Ebill") = "" Or Request("Ebill") = "Yes" Then Response.Write(" checked") End If%>>
							<font size="1">&nbsp;Send e-Bill - (Receive electronic bills in your email box)</font>
	                  </td>
					</tr>
					</table>
-->
				</div>
				<%
'				If Request("PayMethod") = "Echeck" Then
'					Response.Write("<script>hide('PayByCC'); show('PayByEcheck');</script>")
'				End If
				%>
	          <tr valign="bottom"> 
	            
            <td height="4" colspan="2" class=Head><br> <b>Shopping Cart includes:</b></td>
	          </tr>
	          <tr valign="top"> 
	            <td height="14" colspan="2"> 
	              <table width="100%" border="0">
	                <tr> 
	                  <td class=Body height="33"> 
	                    
                    <table width="100%" border="0" align="left">
                      <tr> 
	                        <td width="56%" height="13"> 
	                          
                          <table width="99%" border="0" bgcolor="#2F3359" class=Body>
                            <tr> 
	                              <td> 
	                                <div align="right">One-time Setup Fee: </div>
	                              </td>
	                            </tr>
	                            <tr> 
	                              <td> 
	                                <div align="right">Audiovox 8910 Camera Flip phone 
	                                  (equivalent or better): </div>
	                              </td>
	                            </tr>
	                            <tr> 
	                              <td> 
	                                <div align="right">First month of service ($44.99 
	                                  - 400 Anytime w/ 3,000 nights/weekends at 7:00 
	                                  PM): </div>
	                              </td>
	                            </tr>
	                            <tr> 
	                              <td> 
	                                <div align="right">Activation Fee: </div>
	                              </td>
	                            </tr>
	                            <tr> 
	                              <td> 
	                                <div align="right">Shipping charge: </div>
	                              </td>
	                            </tr>
	                            <tr> 
	                              <td> 
	                                
                                <div align="right"><a href="http://www.axiommobility.com/Axiom%20Mobility%20$50%20rebate.pdf" target="_blank"><font color="#ffff00">$50 
                                  MAIL-IN REBATE</font></a> for FREE Fouth month of service: 
                                </div>
	                              </td>
	                            </tr>
	                            <tr> 
	                              <td> 
	                                <div align="right"><b>GRAND TOTAL DUE TODAY: </b></div>
	                              </td>
	                            </tr>
	                          </table>
	                        </td>
	                        <td width="44%" height="13"> 
	                          
                          <table width="35%" border="0" class=Body bgcolor="#D7D7D7">
                            <tr> 
	                              <td><b><font color="#000000">$99.99</font></b></td>
	                            </tr>
	                            <tr> 
	                              <td><b><font color="#000000">FREE</font></b></td>
	                            </tr>
	                            <tr> 
	                              <td><b><font color="#000000">FREE</font></b></td>
	                            </tr>
	                            <tr> 
	                              <td><b><font color="#000000">FREE</font></b></td>
	                            </tr>
	                            <tr> 
	                              <td><b><font color="#000000">$9.99</font></b></td>
	                            </tr>
	                            <tr> 
	                              <td><b><font color="#000000">FREE</font></b></td>
	                            </tr>
	                            <tr> 
	                              <td><b><font color="#000000">$109.98</font></b></td>
	                            </tr></table></td></tr></table></td></tr></table></td></tr><tr> 
	            
            <td colspan="3" valign="top" class=Head> <b>AutoPay - $10 for 100 
              extra minutes (unused minutes rollover)</b></td>
	          </tr>
	          <tr valign="top"> 
	            <td colspan="4" height="2"> 
	              <table width="100%" border="0">
	                <tr> 
	                  <td width="54%" height="64"> 
	                    
                    <table width="100%" border="0" bgcolor="#2F3359">
                      <tr> 
	                        <td width="18%" class=Body> 
	                          <input type="radio" name="Autopay" value="Yes"<% If Request("Autopay") = "" Or Request("Autopay") = "Yes" Then Response.Write(" checked") End If%>>
	                          ON </td>
	                        <td width="82%">&nbsp;</td>
	                      </tr>
	                      <tr> 
	                        <td width="18%" class=body> 
	                          <input type="radio" name="Autopay" value="No"<% If Request("Autopay") = "No" Then Response.Write(" checked") End If%>>
	                          OFF </td>
	                        <td width="82%">&nbsp;</td>
	                      </tr>
	                      <tr> 
	                        <td colspan="2" class=Body> 
	                          
                          <div align="left">AutoPay will automatically renew your 
                            monthly fee as well as charge your credit card for 
                            more airtime when you are close to running out. </div>
	                        </td>
	                      </tr>
	                    </table>
	                  </td>
	                  <td width="46%" bgcolor="#FFFFFF" height="64"> 
	                    <div align="center"> 
	                      <input type="checkbox" name="Terms" value="AGREED">
	                      &nbsp;<font face="Arial, Helvetica, sans-serif" size="1"><b>I 
	                      Agree to the <a href="http://www.axiommobility.com/terms.htm" target="_blank">Terms and Conditions</a> 
	                      of this offer.</b></font><br>
	                      <input type="reset" name="Submit2" value="Reset">
	                      &nbsp;&nbsp; <font face="Arial, Helvetica, sans-serif" size="1"><b>&nbsp;</b></font>&nbsp;&nbsp;&nbsp; 
	                      <input type="submit" name="Submit" value="Submit">
	                    </div>
	                    <input type="hidden" name="ClientID" id="ClientID" value="<%=Request("ClientID")%>">
	                    <input type="hidden" name="CompanyName" id="CompanyName" value="Axiom Mobility">
	                    <input type="hidden" name="ScrubbedPhoneNumber" id="ScrubbedPhoneNumber" value=""> <!-- scrub this in validation -->
	                    <input type="hidden" name="CardMonth" id="CardMonth" value=""> <!-- create this in validation -->
	                    <input type="hidden" name="CardYear" id="CardYear" value=""> <!-- create this in validation -->
	                    <input type="hidden" name="SendEbill" id="SendEbill" value=""> <!-- assign this in validation -->
	                    <input type="hidden" name="Phone_Choice1" id="Phone_Choice1" value="Audiovox-PDM8910.gif">
	                    <input type="hidden" name="Phone1_PhoneID" id="Phone1_PhoneID" value="520">
	                    <input type="hidden" name="Plan_Choice" id="Plan_Choice" value="$44.99 - 400 Anytime w/ 3,000 nights/weekends at 7:00 PM">
	                    <input type="hidden" name="Plan_Choice_PlanID" id="Plan_Choice_PlanID" value="387">
	                    <input type="hidden" name="MSRPPhone1" id="MSRPPhone1" value="199.99">
	                    <input type="hidden" name="FinalPricePhone1" id="FinalPricePhone1" value="99.99">
	                    <input type="hidden" name="PriceAfterRebatePhone1" id="PriceAfterRebatePhone1" value="49.99">
	                    <input type="hidden" name="ShippingCharge" id="ShippingCharge" value="9.99">
	                    <input type="hidden" name="SalesTax" id="SalesTax" value="0">
	                    <input type="hidden" name="SMTM1" id="SMTM1" value="<%=Request("smtm1")%>">
	                    <input type="hidden" name="SMTM2" id="SMTM2" value="<%=Request("smtm2")%>">
	                    <input type="hidden" name="SMTM3" id="SMTM3" value="<%=Request("smtm3")%>">
	                    <input type="hidden" name="SMTM4" id="SMTM4" value="<%=Request("smtm4")%>">
	                    <input type="hidden" name="SMTM5" id="SMTM5" value="<%=Request("smtm5")%>">
<!-- Uncomment to disable form validation for testing -->
<input type="hidden" name="test" id="test" value="Yes">
	                    <input type="hidden" name="sec" id="sec" value="charge">
	                  </td></tr></table></td></tr></table></td></tr></table></form>
	</body>
	</html>
<%
Else If Request("sec") = "charge" Then
%>

<%
	'Charge Credit Card or run eCheck

	'Constants
	Const x_login = "3Y6hN7abNH" 'Authorize.net login ID
	Const x_tran_key = "8Ka6v4Qn9A4rr7mJ" 'Transaction Key

	'Variables to be passed to Authorize.net
	Dim x_version
	Dim x_type
	Dim x_method
	Dim x_recurring_billing
	Dim x_amount
	Dim x_card_num
	Dim x_exp_date
	Dim x_card_code
	Dim x_trans_id
	Dim x_auth_code
	Dim x_test_request
	Dim x_duplicate_window
	Dim x_invoice_num
	Dim x_description
	Dim x_line_item
	Dim x_first_name
	Dim x_last_name
	Dim x_company
	Dim x_address
	Dim x_city
	Dim x_state
	Dim x_zip
	Dim x_country
	Dim x_phone
	Dim x_fax
	Dim x_email
	Dim x_cust_id
	Dim x_customer_ip
	Dim x_ship_to_first_name
	Dim x_ship_to_last_name
	Dim x_ship_to_company
	Dim x_ship_to_address
	Dim x_ship_to_city
	Dim x_ship_to_state
	Dim x_ship_to_zip
	Dim x_ship_to_country
	Dim x_tax
	Dim x_freight
	Dim x_duty
	Dim x_tax_exempt
	Dim x_po_num
	Dim x_authentication_indicator
	Dim x_cardholder_authentication_value
	Dim x_bank_aba_code
	Dim x_bank_acct_num
	Dim x_bank_acct_type
	Dim x_bank_name
	Dim x_bank_acct_name
	Dim x_echeck_type
	Dim x_bank_check_number
	Dim x_delim_data
	Dim x_delim_char
	Dim x_encap_char
	Dim x_email_customer
	Dim x_header_email_receipt
	Dim x_footer_email_receipt
	Dim x_relay_response

	'For future
	'Dim x_customer_tax_id
	'Dim x_merchant_email
	'Dim x_currency_code
	'Dim x_customer_organization_type
	'Dim x_drivers_license_num
	'Dim x_drivers_license_state
	'Dim x_drivers_license_dob

	'Assign values.  Refer to Authorize.net "AIM" documentation for explanation of each.
	x_version = "3.1"
	x_type = "AUTH_CAPTURE"
	If Request("CardNumber") <> "" Then
		x_method = "CC"
	Else
		x_method = "ECHECK"
	End If
	x_recurring_billing = "False"
	x_amount = cDbl(Request("FinalPricePhone1")) + cDbl(Request("ShippingCharge")) + cDbl(Request("SalesTax"))  'Total Charge
	x_card_num = Request("CardNumber")
	x_exp_date = Request("CardExp")
	x_card_code = Request("CVV")
	x_trans_id = ""
	x_auth_code = ""
x_test_request = "true"  'Comment out to set to "false" if live
	x_duplicate_window = ""
	x_invoice_num = ""  'NEED TO FIGURE OUT A WAY TO GET THIS BEFOREHAND
	x_description = "PrePay Phone Order"
	x_line_item = "Phone1<|>" & Request("Phone_Choice1") & "<|>" & Request("Plan_Choice") & "<|>1<|>" & Request("FinalPricePhone1") & "<|>YES"
	x_first_name = Request("FirstName")
	x_last_name = Request("LastName")
	x_company = ""
	x_address = Request("Address1") & " " & Request("Address2")
	x_city = Request("City")
	x_state = Request("State")
	x_zip = Request("Zip")
	x_country = "US"
	x_phone = Request("PhoneNumber")
	x_fax = ""
	x_email = Request("EmailAddress")
	x_cust_id = ""  'NEED TO FIGURE OUT A WAY TO GET THIS BEFOREHAND
	x_customer_ip = request.servervariables("remote_addr")
	x_ship_to_first_name = Request("FirstName")
	x_ship_to_last_name = Request("LastName")
	x_ship_to_company = ""
	x_ship_to_address = Request("Address1") & " " & Request("Address2")
	x_ship_to_city = Request("City")
	x_ship_to_state = Request("State")
	x_ship_to_zip = Request("Zip")
	x_ship_to_country = "US"
	If Request("State") = "TX" Then
		x_tax = "Tax<|>Texas Sales Tax<|>8.25%"
	Else
		x_tax = ""
	End If
	x_freight = "Shipping<|>Standard Ground<|>" & Request("ShippingCharge")
	x_duty = ""
	x_tax_exempt = "false"
	x_po_num = ""
	x_authentication_indicator = ""
	x_cardholder_authentication_value = ""
	x_bank_aba_code = Request("RoutingNumber")
	x_bank_acct_num = Request("AccountNumber")
	x_bank_acct_type = Request("AcctType")
	x_bank_name = Request("BankName")
	x_bank_acct_name = Request("AcctName")
	If Request("AcctType") = "BUSINESSCHECKING" Then
		x_echeck_type = "CCD" 'Corporate Checking
	Else
		x_echeck_type = "WEB" 'Consumer Checking or Savings
	End If
	x_bank_check_number = ""

	'Transaction Response values
	x_delim_data = "true"
	x_delim_char = "|"
	x_encap_char = "'"
	x_email_customer = "true" 'Authorize.net generated email receipt
	x_header_email_receipt = ""
	x_footer_email_receipt = ""
	x_relay_response = "false"

	'For future.
	'x_customer_tax_id = "111111111"
	'x_merchant_email = "youremailaddress@yourdomain.com"
	'x_currency_code = "USD"
	'x_customer_organization_type = "B"
	'x_drivers_license_num = "000000000"
	'x_drivers_license_state = "UT"
	'x_drivers_license_dob = "1960/10/26"

	'Optional merchant-defined values
	Dim my_own_variable_name
	Dim another_field_name

	'my_own_variable_name = "Inkjet Cartridge 22 DPI"
	'another_field_name = "Color: Black"

	'Now build the REQUEST string to submit via POST
	Dim vPostData
	vPostData = "x_login=" & x_login &_
				"&x_tran_key=" & x_tran_key &_
				"&x_version=" & x_version &_
				"&x_type=" & x_type &_
				"&x_method=" & x_method &_
				"&x_recurring_billing=" & x_recurring_billing &_
				"&x_amount=" & x_amount &_
				"&x_card_num=" & x_card_num &_
				"&x_exp_date=" & x_exp_date &_
				"&x_card_code=" & x_card_code &_
				"&x_trans_id=" & x_trans_id &_
				"&x_auth_code=" & x_auth_code &_
				"&x_test_request=" & x_test_request &_
				"&x_duplicate_window=" & x_duplicate_window &_
				"&x_invoice_num=" & x_invoice_num &_
				"&x_description=" & x_description &_
				"&x_line_item=" & x_line_item &_
				"&x_first_name=" & x_first_name &_
				"&x_last_name=" & x_last_name &_
				"&x_company=" & x_company &_
				"&x_address=" & x_address &_
				"&x_city=" & x_city &_
				"&x_state=" & x_state &_
				"&x_zip=" & x_zip &_
				"&x_country=" & x_country &_
				"&x_phone=" & x_phone &_
				"&x_fax=" & x_fax &_
				"&x_email=" & x_email &_
				"&x_cust_id=" & x_cust_id &_
				"&x_customer_ip=" & x_customer_ip &_
				"&x_ship_to_first_name=" & x_ship_to_first_name &_
				"&x_ship_to_last_name=" & x_ship_to_last_name &_
				"&x_ship_to_company=" & x_ship_to_company &_
				"&x_ship_to_address=" & x_ship_to_address &_
				"&x_ship_to_city=" & x_ship_to_city &_
				"&x_ship_to_state=" & x_ship_to_state &_
				"&x_ship_to_zip=" & x_ship_to_zip &_
				"&x_ship_to_country=" & x_ship_to_country &_
				"&x_tax=" & x_tax &_
				"&x_freight=" & x_freight &_
				"&x_duty=" & x_duty &_
				"&x_tax_exempt=" & x_tax_exempt &_
				"&x_po_num=" & x_po_num &_
				"&x_authentication_indicator=" & x_authentication_indicator &_
				"&x_cardholder_authentication_value=" & x_cardholder_authentication_value &_
				"&x_bank_aba_code=" & x_bank_aba_code &_
				"&x_bank_acct_num=" & x_bank_acct_num &_
				"&x_bank_acct_type=" & x_bank_acct_type &_
				"&x_bank_name=" & x_bank_name &_
				"&x_bank_acct_name=" & x_bank_acct_name &_
				"&x_echeck_type=" & x_echeck_type &_
				"&x_bank_check_number=" & x_bank_check_number &_
				"&x_delim_data=" & x_delim_data &_
				"&x_delim_char=" & x_delim_char &_
				"&x_encap_char=" & x_encap_char &_
				"&x_email_customer=" & x_email_customer &_
				"&x_header_email_receipt=" & x_header_email_receipt &_
				"&x_footer_email_receipt=" & x_footer_email_receipt &_
				"&x_relay_response=" & x_relay_response

				'For Future
'				"&x_customer_tax_id=" & x_customer_tax_id &_
'				"&x_merchant_email=" & x_merchant_email &_
'				"&x_currency_code=" & x_currency_code &_
'				"&x_customer_organization_type=" & x_customer_organization_type &_
'				"&x_drivers_license_num=" & x_drivers_license_num &_
'				"&x_drivers_license_state=" & x_drivers_license_state &_
'				"&x_drivers_license_dob=" & x_drivers_license_dob &_
'				"&my_own_variable_name=" & my_own_variable_name &_
'				"&another_field_name=" & another_field_name &_

'Response.Write "Post String: "&vPostData
'Response.Write "<br></br>"

	'Use XMLHTTP to perform POST
	Dim xml
	Dim strStatus
	Dim strRetval
	Set xml = Server.CreateObject("Microsoft.XMLHTTP")
	'xml.open "POST", "https://test.authorize.net/gateway/transact.dll", false
	'Uncomment the line ABOVE for test accounts OR the line BELOW for LIVE accounts
	xml.open "POST", "https://secure.authorize.net/gateway/transact.dll", false
	xml.send vPostData
	strStatus = xml.Status
	strRetval = xml.responseText
	Set xml = nothing

Response.Write "Return String: "&strRetval
Response.Write "<br></br>"

	'Interrogate the response
	Dim strArrayVal
	strArrayVal = split(strRetVal, "|", -1)

Response.Write "Response Code: "&strArrayVal(0)
Response.Write "<br></br>"

	'Test if transaction was approved, if not tell them why
	If strArrayVal(0) <> "'1'" Then
'	If strArrayVal(4) = "" Then  'No Auth Code
		Response.Write("<script>alert(""" & strArrayVal(3) & """);</script>")
		'Send them back
		Response.Write("<script>history.go(-1);</script>")
	End If

'TEMP CODE TO DISPLAY RESULT - REMOVE ME FROM HERE....
%>
<!--
<html>
<head>
<title>ASP Example&nbsp;&nbsp;::&nbsp;&nbsp;Authorize.Net AIM</title>

<style type="text/css" media="all">

BODY {
	background-color: #ffffff;
	font-family: Arial, Verdana, Helvetica, Geneva, sans-serif;
	font-size: 8pt;
}

TD {
	font-family: Arial, Verdana, Helvetica, Geneva, sans-serif;
	font-size: 12px;
}

.small {
	font-family: Arial, Verdana, Helvetica, Geneva, sans-serif;
	font-size: 10px;
}

.copy {
	font-family: Arial, Verdana, Helvetica, Geneva, sans-serif;
	font-size: 12px;
}


</style>

<head>

<body marginheight="10" marginwidth="10" topmargin="10" leftmargin="10" rightmargin="10" link="#73757B" vlink="#73757B" alink="#73757B" bgcolor="#ffffff">
<%
'' Begin code to get the last modified date of the current document
'' Get the location of the root directory for the server
'file_info = request.servervariables("appl_physical_path")
'' Get the location of the current file on the server
'file_info = file_info + right(request.servervariables("script_name"),(len(request.servervariables("script_name"))-1))
'' create a file scripting object on the server
'set fso = createobject("scripting.filesystemobject")
'' Assign a variable to contain the document date last modified
'document_date=fso.getfile(file_info).datelastmodified
'response.write "This code was last updated: "
'response.write datevalue(document_date)
'' End code to get the last modified date of the current document
'Response.Write "<br><br>"
'Response.Write "Login ID: "&x_login
'Response.Write "</br>"
'Response.Write "Trans Key: "&x_tran_key
'Response.Write "<br></br>"
'Response.Write "Length of the return string: "&len(strRetVal)
'Response.Write "</br>"
'Response.Write "Return String: "&strRetVal
'Response.Write "<br></br>"

'Response.Write "<hr>"

'Dim arrData, iq
'arrData = strArrayVal

' The following few variables are simply to facilitate the display of the information
'Dim tr1, tr2
'tr1 = "<tr class='small' bgcolor='#EEEEEE'>"
'tr2 = "</tr>"
'td1 = "<td>"
'td2 = "</td>"

'Response.Write "<table width='100%' border='0' cellspacing='0' cellpadding='1'>"

'       for i=0 to ubound(arrData)
'            ' the next line trims out leading and trailing spaces if the data is not null or zero-length
'            If arrData(i)<>"" and not IsNull(arrData(i)) then arrData(i) = trim(arrData(i))

'			' alternating table row colors
'			If i MOD 2 = 0 Then
'				tr1 = "<tr class='small' bgcolor='#eeeeee'>"
'			Else
'				tr1 = "<tr class='small' bgcolor='#ffffff'>"
'			End If

'			iq = i + 1
'			Select Case iq
'				Case 1,2,3,4,5,6,7
'					Response.Write tr1
'					Response.Write td1
'					Response.Write iq & " - Authorize.Net Response: " & arrData(i)
'					'br()
'					Response.Write "<br>"
'					Response.Write td2
'					Response.Write tr2
'				Case 8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37
'					Response.Write tr1
'					Response.Write td1
'					Response.Write iq & " - Returned Values: " & arrData(i)
'					'br()
'					Response.Write "<br>"
'					Response.Write td2
'					Response.Write tr2
'				Case 38
'					Response.Write tr1
'					Response.Write td1
'					Response.Write iq & " - MD5 Hash: " & arrData(i)
'					'br()
'					Response.Write "<br>"
'					Response.Write td2
'					Response.Write tr2
'				Case Else
'					If iq >= 69 then
'					Response.Write tr1
'					Response.Write td1
'						Response.Write iq & " - Merchant-defined: " & arrData(i)
'					'br()
'					Response.Write "<br>"
'					Response.Write td2
'					Response.Write tr2
'					Else
'					Response.Write tr1
'					Response.Write td1
'						Response.Write iq & " - Other: " & arrData(i)
'					'br()
'					Response.Write "<br>"
'					Response.Write td2
'					Response.Write tr2
'					End If
'			End Select'

'        next

'Response.Write "</table>"

'Response.Write "<hr>"

'sub br()
'	Response.Write("<br>")
'end sub

'...HERE!
%>

<%
'Else 'Request("sec") = "save"
'	If strArrayVal(0) = "'1'" Then 'Transaction approved, write the order to the DB
	If 1 = 2 Then
%>
		<!--#include file="dbconn.asp"-->
		<!--#include file="../SqlCheck.asp"-->
<%
		' Write order to Goliath
		Query = "INSERT INTO Customers (" &_
				"ClientID,"&_
				"SMTM1,"&_
				"SMTM2,"&_
				"SMTM3,"&_
				"SMTM4,"&_
				"SMTM5,"&_
				"CompanyName,"&_
				"Customer_Name,"&_
				"FirstName,"&_
				"LastName,"&_
				"Home_Phone,"&_
				"Home_Address,"&_
				"Apt,"&_
				"City,"&_
				"State,"&_
				"Zip_Code,"&_
				"Email,"&_
				"Credit_Card_Type,"&_
				"Credit_Card_Number,"&_
				"Credit_Card_CID,"&_
				"Exp_Month,"&_
				"Exp_Year,"&_
				"NameOnCC,"&_
				"EcheckRoutingNumber,"&_
				"EcheckAccountNumber,"&_
				"EcheckBankName,"&_
				"EcheckAcctName,"&_
				"EcheckAcctType,"&_
				"MerchantTransID,"&_
				"MerchantAuthCode,"&_
				"PrePayAutoPay,"&_
				"PrePayUsername,"&_
				"PrePayPassword,"&_
				"PrePaySendEbill,"&_
				"phone_choice1,"&_
				"Phone1_PhoneID,"&_
				"Individual_Plan,"&_
				"Individual_Plan_PlanID,"&_
				"MSRPPhone1,"&_
				"FinalPricePhone1,"&_
				"PriceAfterRebatePhone1,"&_
				"SubmissionIP,"&_
				"SubmissionBrowser,"&_
				"CheckedOut,"&_
				"DateAdded)"&_
				"VALUES ("&_
				"'"& Request("ClientID")&"',"&_
				"'"& Replace(Request("SMTM1"),"'","''")&"',"&_
				"'"& Replace(Request("SMTM2"),"'","''")&"',"&_
				"'"& Replace(Request("SMTM3"),"'","''")&"',"&_
				"'"& Replace(Request("SMTM4"),"'","''")&"',"&_
				"'"& Replace(Request("SMTM5"),"'","''")&"',"&_
				"'"& Replace(Request("CompanyName"),"'","''")&"',"&_
				"'"& Replace(Request("FirstName")&" "&Request("LastName"),"'","''")&"',"&_
				"'"& Replace(Request("FirstName"),"'","''")&"',"&_
				"'"& Replace(Request("LastName"),"'","''")&"',"&_
				"'"& Request("ScrubbedPhoneNumber")&"',"&_
				"'"& Replace(Request("Address1"),"'","''")&"',"&_
				"'"& Replace(Request("Address2"),"'","''")&"',"&_
				"'"& Replace(Request("City"),"'","''")&"',"&_
				"'"& Request("State")&"',"&_
				"'"& Request("Zip")&"',"&_
				"'"& Request("EmailAddress")&"',"&_
				"'"& Request("CardType")&"',"&_
				"'"& Request("CardNumber")&"',"&_
				"'"& Request("CVV")&"',"&_
				"'"& Request("CardMonth")&"',"&_
				"'"& Request("CardYear")&"',"&_
				"'"& Replace(Request("CardName"),"'","''")&"',"&_
				"'"& Replace(Request("RoutingNumber"),"'","''")&"',"&_
				"'"& Replace(Request("AccountNumber"),"'","''")&"',"&_
				"'"& Replace(Request("BankName"),"'","''")&"',"&_
				"'"& Replace(Request("AcctName"),"'","''")&"',"&_
				"'"& Replace(Request("AcctType"),"'","''")&"',"&_
				strArrayVal(6) &","&_
				strArrayVal(4) &","&_
				"'"& Replace(Request("Autopay"),"'","''")&"',"&_
				"'"& Replace(Request("Username"),"'","''")&"',"&_
				"'"& Replace(Request("Password"),"'","''")&"',"&_
				"'"& Replace(Request("SendEbill"),"'","''")&"',"&_
				"'"& Replace(Request("Phone_Choice1"),"'","''")&"',"&_
				"'"& Replace(Request("Phone1_PhoneID"),"'","''")&"',"&_
				"'"& Replace(Request("Plan_Choice"),"'","''")&"',"&_
				"'"& Replace(Request("Plan_Choice_PlanID"),"'","''")&"',"&_
				""& Request("MSRPPhone1")&","&_
				""& Request("FinalPricePhone1")&","&_
				""& Request("PriceAfterRebatePhone1")&","&_
				"'"& Request.ServerVariables("REMOTE_ADDR")&"',"&_
				"'"& Request.ServerVariables("HTTP_USER_AGENT")&"',"&_
				"'No',"&_
				"'"& now()&"')"
'Response.Write(Query&"<br></br>")
		Set rsInsert = ObjConn.Execute(Query)
'		Response.Redirect("https://www.wbsrecords.com/data/thankyou.htm")
	End If
End If
%>