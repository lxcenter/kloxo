
function hide_a_div_box(nameid){
crossobj= document.getElementById(nameid);
crossobj.style.visibility="hidden";
}

function getRandomNum(lbound, ubound) {
	return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
}
function getRandomChar() {
	var numberChars = "0123456789";
	var lowerChars = "abcdefghijklmnopqrstuvwxyz";
	var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var otherChars = "`~!@#%^*()-_=+[{]}\\|\",<.>/? ";
	var charSet = "";
	charSet += numberChars;
	charSet += lowerChars;
	charSet += upperChars;
	//charSet += otherChars;
	return charSet.charAt(getRandomNum(0, charSet.length));
}

function getRanPassword(length) {
	var rc = "";

	for (var idx = 1; idx < length; ++idx) {
		rc = rc + getRandomChar();
	}
	return rc;
}

function keyPressHandler()
{
	alert(window.event.keyCode);
	if (kC == 27) {
		alert('key');
		document.getElementById('showimage').visibility = 'hidden';
		alert(document.getElementById('showimage'));

	}
}

function generatePass(form, variable)
{
	var fm = document.getElementById(form);
	var passw = getRanPassword(12);

	//alert("Generated Password: " + passw);
	setFormVariableVlue(fm, variable, passw);
	setFormVariableVlue(fm, 'frm_confirm_password', passw);
	password_showbox(passw);
}


function searchpage(C)
{
	var i;
	var j;
	var K = C.value.toLowerCase();

	var nK = K.replace(/^\s*/, "").replace(/\s*$/, "");
	//alert("*" + nK + "*");
	nK = nK.replace(" ", "_");
	for (i = 0 ; i < global_action_box.length; i++) {
		var iconlist = global_action_box[i];
		var visiblecount = false;
		for(j = 1; j < iconlist.length; j++) {
		// Remove the searchdiv_
			nid = iconlist[j];
			nnid = nid.substr(10);
			if (nK.length > 0 && nnid.indexOf(nK) == -1) {
				document.getElementById(nid).style.visibility = "hidden";
				document.getElementById(nid).style.display = "none";
			} else {
				document.getElementById(nid).style.visibility = "visible";
				document.getElementById(nid).style.display = "block";
				visiblecount = true;
			}
		}
		bid = iconlist[0];
		if (!visiblecount) {
			document.getElementById("item_" + bid).style.visibility = "hidden";
			document.getElementById("item_" + bid).style.display = "none";
		} else {
			document.getElementById("item_" + bid).style.visibility = "visible";
			document.getElementById("item_" + bid).style.display = "block";
		}
	}

}
function updateStatusBar(str)
{
	document.getElementById('statusbar').innerHTML =  str;
}

function updateallWarning()
{
	if (confirm("UpdateAll will impress the parameters of the above form to EVERY object in the drop down list at the top. Do you want to continue? If unsure, press cancel, and use simple update")) {
		if (confirm("Are you really sure?\n\n\n\n\nEvery object in the drop down list at the top will be updated with the values in the above form. If unsure press cancel, and use simple update.")) {
			return true;
		}
	} 
	return false;
}


function toggleVisibility(id)
{
	var el = document.getElementById(id);
	if ( el.style.visibility == 'visible') {
		el.style.visibility = 'hidden';
		//el.style.position = 'absolute';
		el.style.display = 'none';
	} else {
		el.style.visibility = 'visible';
		//el.style.position = 'relative';
		el.style.display = 'block';
	}
}


var tl_move = false;
function createTextAreaWithLines(id)
{
	var el = document.createElement('TEXTAREA');
	var ta = document.getElementById(id);

	var string = '';
	for(var no=1;no<300;no++){
		if(string.length>0)string += '\n';
		string += no;
	}

	el.className      = 'textAreaWithLines';
	//alert(ta.offsetHeight);
	if (ta.offsetHeight) {
		el.style.height   = (ta.offsetHeight-3) + "px";
	} else {
		el.style.height = "200px";
	}
	el.style.width    = "25px";
	el.style.position = "absolute";
	el.style.overflow = 'hidden';
	el.style.textAlign = 'right';
	el.style.paddingRight = '0.2em';
	el.innerHTML      = string;  //Firefox renders \n linebreak
	el.innerText      = string; //IE6 renders \n line break
	el.style.zIndex   = 0;
	ta.style.zIndex   = 1;
	ta.style.position = "relative";
	ta.parentNode.insertBefore(el, ta.nextSibling);
	setLine();
	//ta.focus();

	ta.onkeydown    = function() { setLine(); }
	ta.onmousedown  = function() { setLine(); tl_move=true; }
	ta.onmouseup    = function() { setLine(); tl_move=false; }
	ta.onmousemove  = function() { if(tl_move){setLine();} }


	function setLine(){
		el.scrollTop   = ta.scrollTop;
		el.style.top   = (ta.offsetTop) + "px";
		el.style.left  = (ta.offsetLeft - 27) + "px";
		el.style.right  = (ta.offsetLeft + 20) + "px";
	}
}


function check_for_needed_variables(frmname)
{
	var frm = document.getElementById(frmname);

	for (var i=0; i < frm.elements.length; i++) {
		var ele = frm.elements[i];
		if (typeof(global_need_list[ele.name]) != 'undefined') {
			if (ele.value == '') {
				alert(global_need_list[ele.name] + " Is a Needed variable");
				return false;
			}
		}
		if (typeof(global_match_list[ele.name]) != 'undefined') {
			var otherelem = global_match_list[ele.name];
			var v = getFormVariableVlue(frm, otherelem);
			if (ele.value !== v) {
				alert(global_desc_list[ele.name] + " does not match " + global_desc_list[otherelem]);
				return false;
			}
		}
	}
	return true;
}

function getFormVariableVlue(frm, varname)
{
	for (var i=0; i < frm.elements.length; i++) {
		var elem = frm.elements[i];
		if (elem.name == varname) {
			return elem.value;
		}
	}
}

function setFormVariableVlue(frm, varname, value)
{
	for (var i=0; i < frm.elements.length; i++) {
		var elem = frm.elements[i];
		if (elem.name == varname) {
			elem.value = value;
		}
	}
}



function checkBoxTextToggle(frmname, cname, tname,  cvalue, tvalue)
{

	frm = document.getElementById(frmname);

	if(eval("frm." + cname + ".checked === true")) { 
		eval('frm. ' + tname + '.disabled= true');
		eval("frm. " + tname + ".className= 'textdisable'");
		eval("frm. " + cname + ".value = cvalue"); 
	} else { 
		eval("frm." + tname + ".value = tvalue"); 
		eval("frm." + tname + ".className = 'textenable'");
		eval("frm." + tname + ".disabled = false");
		eval("frm." + cname + ".value = cvalue");
	}

	if(eval("frm." + tname + ".name.disabled===true")) { 
		eval("frm." + tname + ".value = '-' ; ");
	} else { 
		eval("frm." + tname + ".value = tvalue"); 
	} 
}


function my_alert(message)
{
	return;
	var elem = document.getElementById('j_message');
	elem.innerHTML = message;
}

expandMess = 'Click to Expand Navigation Bar';
retractMess = 'Click to Retract Navigation Bar';
function navigtoggleNavigation()
{
	var navigpoint = document.getElementById("navigpoint");
	var navig = document.getElementById("navigation"); 
	if (navig.style.visibility == 'visible') {
		navig.style.visibility = 'hidden';
		changeContent('help', expandMess);
		navigpoint.src = gl_imgleftpoint
	} else {
		navigpoint.src = gl_imgrightpoint
		navig.style.visibility = 'visible';
		changeContent('help', retractMess);
	}
}
function navigsetDefaultImage(img)
{
	var navigpoint = document.getElementById('navigpoint');
	var navig = document.getElementById('navigation'); 
	navigpoint.src = img;
}

function navigShowHelpMessage()
{
	var navig = document.getElementById('navigation'); 
	if (navig.style.visibility == 'visible') {
		changeContent('help', retractMess);

	} else {
		changeContent('help', expandMess);
	}

}

var selFolderObj
function selectFolder(obj, rootFolder, url){
	selFolderObj=obj;
	windowFolderSel=window.open(url,'FolderSel','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,titlebar=no,width=500,height=400,top='+ parseInt((window.screen.height-185) / 2) +',left='+ parseInt((window.screen.width-210) / 2));
    windowFolderSel.focus();
    
}

function callSetSelectFolder(value)
{
	if (opener.parent.top.mainframe) {
		opener.parent.top.mainframe.setSelectFolderValue(value);
	} else {
		opener.parent.setSelectFolderValue(value);
	}
	window.close()
}

function setSelectFolderValue(value, form){
	if (form) {
		alert(form.frm_accountselect);
	}
	selFolderObj.value=value;
	
}

function onMouseOverLinkButton(obj, help)
{
	changeContent('help', help)
	return;
	obj.style.cursor='pointer';
	obj.style.textDecoration='underline';
}
function onMouseOutLinkButton(obj)
{
	changeContent('help','helparea')
	return;
	obj.style.cursor='pointer';
	obj.style.cursor='normal';
	obj.style.textDecoration='none';
}
function histToggleHistory()
{

	histMenu = document.getElementById('histMenu');
	if (histMenuOn) {
		histMenu.style.visbility = 'hidden';
		histMenu.style.display = 'none';
	} else {
		histMenu.style.visbility = 'visible';
		histMenu.style.display = 'block';
	}

	return;
	/// Old Code...

	if (window.activeMenus && window.activeMenus.length != 0) {
		//hideMenu(event);
		alert('hello');
	} else {
		showMenu(menu, x, y, child);
	}


}


function onMouseOverShowMenu(menu, x, y, child)
{
	if (window.activeMenus && window.activeMenus.length != 0) {
		//alert(window.ActiveMenu);
		showMenu(menu, x, y, child);
	}

}


function findOption (element, value) {
     if (typeof element == 'string') { element = document.getElementById(element); }

     if(!(value == undefined)) {
         for (var i = 0; i < element.options.length; i++) {
             if (element.options[i].value == value) {
                 return i;
             }
         }
     }

     return -1;
 }


function swapOptions (element, index1, index2) 
{
     if (typeof element == 'string') { element = document.getElementById(element); }


     // Make sure the indexes are valid
	 if (!(index1 != index2 && index1 >= 0 && index1 < element.options.length && index2 >= 0 && index2 < element.options.length)) {
		 return;
	 }


         // Save the selection state of all of the options because Opera
		 // seems to forget them when we click the button
	 var optionStates = new Array();
	 for(i = 0; i < element.options.length; i++) {
		 optionStates[i] = element.options[i].selected;
	 }

	 // Save the first option into a temporary variable
	 var option = element.options[index1];

	 // Copy the second option into the first option's place
	 element.options[index1] = new Option(element.options[index2].text, element.options[index2].value, element.options[index2].defaultSelected, element.options[index2].selected);

	 copyStyle(element.options[index2], element.options[index1]);

	 // Copy the first option into the second option's place
	 element.options[index2] = new Option(option.text, option.value, option.defaultSelected, option.selected);

	 // Reset the selection states for Opera's benefit
	 for(i = 0; i < element.options.length; i++) {
		 element.options[i].selected = optionStates[i];
	 }
	 copyStyle(option, element.options[index2]);

	 // Then select the ones we swapped, if they were selected before the swap
	 element.options[index1].selected = optionStates[index2];

	 element.options[index2].selected = optionStates[index1];
} // swapOptions()


function copyStyle(src, dst)
{
	if (src.style) {
		dst.style.background = src.style.background;
		dst.style.height = src.style.height;
		dst.style.padding = src.style.padding;
	}
}

 /**
  * Shifts an option (specified by index or value) in a select element up one position
  *
  * @author  Dan Delaney     http://fluidmind.org/
  * @param   element         The form select element with the options to me shifted
  * @param   option          The index or value of the option to be shifted up
  * @see     swapOptions
  */
function shiftOptionUp(form, variable, element)
{
	option = element.selectedIndex;
	if (typeof element == 'string') { element = document.getElementById(element); }

	//if (isNaN(option)) option = findOption(element, option);

	// Only move it up if it's not the first option and it's not
	// below another selected option

	if (!isNaN(option) && option > 0 && option < element.options.length) {
		swapOptions(element, option, option - 1);
	}

	createFormVariable(form, variable, element);


} // shiftOptionUp()


/**
 * Returns the index of the option with the specified value, or -1 if not found
 *
 * @param   element         The HTML select element to be sorted
 * @param   value           The option value to search for
 */

 /**
  * Shifts an option (specified by index or value) in a select element down one position
  *
  * @author  Dan Delaney     http://fluidmind.org/
  * @param   element         The form select element with the options to me shifted
  * @param   option          The index or value of the option to be shifted up
  * @see     swapOptions
  */
function shiftOptionDown (form, variable, element)
{
	option = element.selectedIndex;

	if (typeof element == 'string') { element = document.getElementById(element); }


	 //if (isNaN(option)) option = findOption(element, option);

     // Only move it up if it's not the first option and it's not
     // Only move it up if it's not the first option and it's not
     // below another selected option
     if (!isNaN(option) && option >= 0 && option < (element.options.length - 1)) {
		 swapOptions(element, option, option + 1);
     }

	createFormVariable(form, variable, element);
 } // shiftOptionDown()


function multiSelectPopulate(form, variable, box1, box2) {
	var i;
	var n;
	frm=document.forms[form];
	field1= document.getElementById(box1);
	field2=document.getElementById(box2);
	n = field2.length;

	for(i=0; i < field1.length; i++) {
		if(field1.options[i].selected) {
			if (multiSelectisPresentIn(field1.options[i].text, field2)) {
				continue;
			}
			//alert(field1.options[i].text);
			newOpt=field1.options[i].text;
			field2.length= n + 1;
			field2.options[n]= new Option(newOpt, newOpt);
			field2.options[n].value = field1.options[i].value;
			copyStyle(field1.options[i], field2.options[n]);
			n++;
		}
	}
	createFormVariable(form, variable, box2);

}

function createFormVariable(form, variable, box)
{
	if (typeof box == 'string') {
		field = document.getElementById(box);
	} else {
		field = box;
	}

	frm=document.forms[form];
	var data = Array();
	for(i =0; i< field.length; i++) {
		data[i] = field.options[i].value;
	}



	eval("frm." + variable + ".value = data");
}

function multiSelectisPresentIn(text, field)
{
	var i;
	var n;
	n = field.length;
	for(i=0 ;  i < n; i++) {
		if (field.options[i].text == text) {
			return 1;
		}
	}
	return 0;
}

function multiSelectRemove(form, variable, box)
{
	var i, j;
	var n;
	frm=document.forms[form];
	field2 = document.getElementById(box);
	n = field2.length;
	for (j = 0; j < field2.length + 30; j++) { 
		for(i=0 ;  i < field2.length; i++) {
			if (field2.options[i].selected) {
				//alert(field2.options[i]);
				field2.remove(i);
				break;
			}
		}
	}
	createFormVariable(form, variable, box);
}


function jselectall(safrmelement,frmelecount, keyid ) 
{
	var c, d, n, i;
 c = 0; d = 0;
 n = frmelecount;
 for (i=0; i<n; i++) {
	  ckb = "ckbox"+ keyid + i;
       tid = "tr" + keyid + c++;

	   if (safrmelement.checked == true) {
	   if ( ! document.getElementById(ckb).disabled ){
           document.getElementById(ckb).checked = true;
           document.getElementById(tid).className = "hiliterow"; 
		   }
       } 
       else {
		    document.getElementById(ckb).checked = false;
			cnam = "tablerow" + d;
			document.getElementById(tid).className =cnam;
			if(d==0) d=1; else d=0;
       }
    }
}
function treeStoreValue() 
{

	var dataarr = new Array();
	var ii=0;

	for(i=0; i<__treecheckboxcount; i++) {
		if (document.getElementById('treecheckbox' + i)) {
			if(document.getElementById('treecheckbox' + i).checked) {
				dataarr[ii] = document.getElementById('treecheckbox' +i).value;
				ii++;
			}
		}
	}
	if (ii > 0) {
		document.__treeForm.frm_accountselect.value = dataarr;
		document.__treeForm.submit();
	} else {
		alert("Please select some Entries.");
	}
	
}

function storevalue(frmname,elementid,ckbname,ckcount,noselect, doconfirm) 
{

	if (frmname.action == "refresh") {
		window.location.reload();
		return;
	}



    if(noselect == 1) {
		if (doconfirm == 1) {
			if (confirm("Do you really want to proceed with the action?")) {
				frmname.submit();
			} else {
				return;
			}
		} else {
			frmname.submit();
			return;
		}
	} 


	var dataarr = new Array();
	var ii=0;

	for(i=0; i<ckcount; i++) {
		if(document.getElementById(ckbname+i).checked) {
			dataarr[ii] = document.getElementById(ckbname+i).value;
			ii++;
		}
	}

	if (ii > 0) {
		if (doconfirm == 1) {
			if (confirm("Do you really want to proceed with the action?")) {
				frmname.frm_accountselect.value = dataarr;
				frmname.submit();
			} else {
				return;
			}
		} else {
			frmname.frm_accountselect.value = dataarr;
			frmname.submit();
		}
	} else {
		alert("Please select an Entry.");
	}

}

function getfirstChecked(basefile, ckbname, ckcount)
{

	var dataarr;
	var ii=0;

	for(i=0; i<ckcount; i++) {
		if(document.getElementById(ckbname+i).checked) {
			dataarr = document.getElementById(ckbname+i).value;
			ii = 1;
		}
	}
     if (ii > 0) {
		//opener.parent.setSelectFolderValue(basefile + "/" + dataarr);
		opener.parent.top.mainframe.setSelectFolderValue(basefile + "/" + dataarr);
		window.close();
	 } else {
		 alert("Please select an Entry.");
	 }
	
}
function hiliteButton(tid,cname) {
	document.getElementById(tid).className = cname;
}

function hiliteRowColor(tid,cname,frmelement) {
  row = document.getElementById(tid);

  if(row.className != "hiliterow")
      row.className  = "hiliterow";
  else
	  row.className = cname;

 if(frmelement.checked == true)
	 frmelement.checked = false;

}

function restoreListOnMouseOver(tid, cname, inputid)
{
  row = document.getElementById(tid);

  frmelement = document.getElementById(inputid);

  if (frmelement && frmelement.checked == true && frmelement.disabled !== true) {
	  row.className = 'hiliterow';
  } else {
	  row.className = cname;
  }
}


function hiliteDetailRowColor(tid,cname) {
  row = document.getElementById(tid);
  if(row.className != "hiliterowdetail")
      row.className  = "hiliterowdetail";
  else
	  row.className = cname;
}

function rowPointer(tid) {
  col = document.getElementById(tid);
  if(col.className != "rowpoint")
      col.className  = "rowpoint";
  else
	  col.className = "rowpointhilite";
}

function statechange(element,ename,frmname,status)
 {
var en=0;
var k;
//alert(element.length);
for(k=0;k<ename.length;k++) {
    if(ename[k].checked == true) {
     ename[k].disabled = true;
	} else {
     ename[k].disabled = false;
	}
//   alert(ename[k].value);
}

//return 0;



  if(status == 1) classtype = 'textdisable';
  else classtype = 'textenable';

  classtype1 = 'textdisable';
  classtype2 = 'textenable';

  if(element.checked==true)
 
 
  for(i=0;i<frmname.length;i++) {
    if(frmname.elements[i].name != element.name) {
     if(en==1) {
	  if(frmname.elements[i].type != 'submit' && frmname.elements[i].type != 'reset') {
		   
	//	   alert (frmname.elements[i].type + "  " + frmname.elements[i].className);
if (status == 0) {
	if(frmname.elements[i].type == 'select-one') {
	   	 frmname.elements[i].selectedIndex = 0;
		 frmname.elements[i].disabled = status;          
		 frmname.elements[i].className = classtype;  
	 } else if(frmname.elements[i].className == 'dckbox1' && frmname.elements[i].type=='checkbox' ) {

//       alert (frmname.elements[i].type + "  " + frmname.elements[i].className +  " " + frmname.elements[i].disabled);

		     if(frmname.elements[i].checked==true){
				 frmname.elements[i-1].disabled = true;
   				 frmname.elements[i-1].value = "-";
				 frmname.elements[i-1].className= classtype1;
             } else {
				 frmname.elements[i-1].disabled = false;
                 frmname.elements[i-1].className= classtype2; 
			 }

			 frmname.elements[i].className = 'ckbox1';
			 frmname.elements[i].disabled=false;

   	 } else if(frmname.elements[i].className == 'dckbox2' && frmname.elements[i].type=='checkbox' ) {
		
		     if(frmname.elements[i].checked==true){
				 frmname.elements[i-1].disabled = false;
                 frmname.elements[i-1].className= classtype2; 
    		 } else {
				 frmname.elements[i-1].disabled = true;
				 frmname.elements[i-1].value = "-";
                 frmname.elements[i-1].className= classtype1;

			 }
                 frmname.elements[i].className='ckbox2';
				 frmname.elements[i].disabled=false;
   	 } else {
             frmname.elements[i].disabled = status;          
			 frmname.elements[i].value = '';
		   	 frmname.elements[i].className = classtype;    
			}
} else {

	if(frmname.elements[i].type == 'select-one') {
	   	 frmname.elements[i].selectedIndex = 0;
		 frmname.elements[i].disabled = status;          
		 frmname.elements[i].className = classtype;  
    } else {
			frmname.elements[i].disabled = status;

			if(frmname.elements[i].type == 'text') {
               frmname.elements[i].value = '-';
			} else  {
               frmname.elements[i].value = '';
			}

		   	if(frmname.elements[i].className == 'ckbox1') 
				frmname.elements[i].className = 'dckbox1';    
		   	else if(frmname.elements[i].className == 'ckbox2')
				frmname.elements[i].className = 'dckbox2';    
			else frmname.elements[i].className = classtype;    
			}
}

		  } 
	   }
	 }
	 else {
		   en=1;
	  }
    } 
  }

function check_password(formpasswd)	
{ 
	if(formpasswd.password.value == '' || formpasswd.repassword.value == ''){
		alert('Please enter passwords'); 
		return false;
	}

	if(formpasswd.password.value == formpasswd.repassword.value) {
		formpasswd.password.value = escape(formpasswd.password.value);
		return true;
	} else {
		alert('Passwords do not Match');
		return false;
	}
}

function change_notify(element)
{
	if(element.checked == true)		element.value = 'yes';
	else							element.value = 'no';
}

function encode_url(form)
{
	return;
	for (var i=0; i < form.elements.length; i++) {
		if (form.elements[i].type != 'text' && form.elements[i].type != 'password') {
			continue;
		}
		form.elements[i].value = encodeURIComponent(form.elements[i].value);
		//alert(form.elements[i].value);
	}
}

function uplevel(num)
{
 parent.body.navigation.go(num);
}
	

function fun1(arg)
{
		arg.frm_action.value="update";
		arg.submit();
	    return false;
}

function checksearch(form,calltype)
{
	if(form.frm_searchstring.value != "" && form.frm_searchstring.value != "Search...."){
		if(calltype == 1) return true;
		if(calltype == 2) form.submit(); 
	}
    else{
		if(calltype == 1) return false;
	}
}
function clientcheck(form1)
{
	if(document.form1.clcheck.checked) {
        document.form1.cltext.disabled=true;
        document.form1.cltext.style.backgroundColor = "#eff7ff";
		document.form1.clname.disabled=false;
	
	}
	else {
		document.form1.cltext.disabled=false;
        document.form1.clname.disabled=true;
		document.form1.clname.style.backgroundColor= "#eff7ff";
		document.form1.cltext.style.backgroundColor= "#ffffff";

	}
}

function send_notification(newfrm, oldfrm , count)
{
 var adm = new Array();
 var clt = new Array();
 var dur = new Array();
  
  
 for(i=0; i<=count; i++) {

   a = 'admin_' +  i; c = 'client_' + i; d = 'duser_' +  i;
   adm[i] =  document.getElementById(a).value;
   clt[i] =  document.getElementById(c).value;
   dur[i] =  document.getElementById(d).value;
  
  }

document.frm_notification.frm_toadmin.value = adm;
document.frm_notification.frm_toclient.value = clt;
document.frm_notification.frm_toduser.value = dur;

document.frm_notification.submit();


}


function setusername(form1,frm_domain)
{
	dnamef = "";
	dnamef = document.form1.frm_domain.value;

	if(document.form1.frm_domain.value != ""){

		if(dnamef.indexOf(".") == -1 ){
			document.form1.username.value = "";
			alert(""+dnamef+" is Not A Valid Domain name");
			document.form1.frm_domain.focus();
		}

		else {
			var re = new RegExp ('\\.', 'gi');
			dname = dnamef.replace(re, '-');
			document.form1.username.value = dname;
			document.form1.cltext.value=dname;

		}
	}

}

function setpermission(frmname,mode,val)
{
if(mode=='user')   { e = frmname.eu; w = frmname.wu; r = frmname.ru; } 
if(mode=='group')  { e = frmname.eg; w = frmname.wg; r = frmname.rg; } 
if(mode=='other')  { e = frmname.eo; w = frmname.wo; r = frmname.ro; } 

switch (val)
{
 case 0:       e.checked=false; w.checked=false;  r.checked=false; 	   break;
 case 1:       e.checked=true;  break;	      
 case 2:       w.checked=true;  break;
 case 3:       e.checked=true;  w.checked=true;   break;
 case 4:       r.checked=true;  break;
 case 5:       e.checked=true;  r.checked=true;   break;
 case 6:       w.checked=true;  r.checked=true;   break;
 case 7:       e.checked=true;  w.checked=true;   r.checked=true; 	   
}

}

function allrights(frmname,element,mode)
{
if(mode=='user')   { e = frmname.eu; w = frmname.wu; r = frmname.ru; txt=frmname.user;  } 
if(mode=='group')  { e = frmname.eg; w = frmname.wg; r = frmname.rg; txt=frmname.group; } 
if(mode=='other')  { e = frmname.eo; w = frmname.wo; r = frmname.ro; txt=frmname.other; } 

if(element.checked == true) {
 e.checked=true;  w.checked=true;   r.checked=true; 	   
 txt.value=7;

} else {
 e.checked=false;  w.checked=false;   r.checked=false; 	   
 txt.value=0;
}
}

function changerights(frmname,element,mode,val)
{
if(mode=='user')  {  txt=frmname.user;  ckall=frmname.userall;  } 
if(mode=='group') {  txt=frmname.group; ckall=frmname.groupall; } 
if(mode=='other') {  txt=frmname.other; ckall=frmname.otherall; } 

if(element.checked == true) {
 txt.value = parseInt(txt.value) + val;
} else {
 txt.value = parseInt(txt.value) - val;
 
 if(ckall.checked == true)
	 ckall.checked = false;

}

}

function testSelect(form) {                                                                 
var Result;
var conf;
Item = form.frm_iplist.selectedIndex;
Result = form.frm_iplist.options[Item].text; 
conf = confirm("Do you want to change IP Address?");
if(conf==true)
{
	return form.submit();
}            
else
{
	return false;
}
}


function getAbsolutePos(el){
	for (var lx=0,ly=0;el!=null;
			lx+=el.offsetLeft,ly+=el.offsetTop,el=el.offsetParent);
	return {x:lx,y:ly}
}



var clockID = 0;

function UpdateClock() {
	if(clockID) {
		clearTimeout(clockID);
		clockID  = 0;
	}

	var tDate = new Date();

	var hours = tDate.getHours() + clockTimeZoneHours;
	var minutes = tDate.getMinutes() + clockTimeZoneMinutes;

	document.theClock.theTime.value = ""
		+ hours + ":"
		+ minutes + ":"
		+ tDate.getSeconds();

   clockID = setTimeout("UpdateClock()", 30000);
}

function startClock() {
	clockID = setTimeout("UpdateClock()", 500);
}

function KillClock() {
	if(clockID) {
		clearTimeout(clockID);
		clockID  = 0;
	}
}

function logOut()
{
	if (window.coverScreen) {
		coverScreen(1);
	}

	if (confirm("Do You Really Want To Logout?")) {
		top.mainframe.location = '/htmllib/phplib/logout.php';
	} else {
		if (window.coverScreen) {
			coverScreen(0);
		}

	}
}





