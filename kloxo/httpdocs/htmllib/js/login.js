function fieldcheck(form)
{
err=0	
m1="Enter the Username.";
m2="Enter the Password.";
m3="Enter the Username & Password.";
if(form.frm_clientname.value == "") { msg=m1; err=1; }
if(form.frm_password.value == "") { msg=m2; err=1; }
if(form.frm_clientname.value == "" && form.frm_password.value == "") { msg=m3; err=1; }

if (err==1) { 
	alert(msg); return false; }
else
	return true;
}

function forgotfield(form)
{
m1="Enter the Username.";
m2="Enter the Email Id.";
m3="Enter the Username & Email Id.";
m4="Invalid Email Id.";
if(form.frm_clientname.value == "") { msg=m1; err=1; }
if(form.frm_email.value == "") { 
msg=m2; err=1; }
else {
	emailchk = emailcheck(form.frm_email.value);
	/*
    if(emailchk == false) {
		msg=m4; 
		err=1;
	}
	*/
}

if(form.frm_clientname.value == "" && form.frm_email.value == "") { msg=m3; err=1; }

if (err==1) { 
	alert(msg); return false; }
else
	return true;
}
