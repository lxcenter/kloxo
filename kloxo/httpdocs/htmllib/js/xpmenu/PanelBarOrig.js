
// link target constants
TARGET_OTHER  = 0;
TARGET_BLANK  = 1;
TARGET_SELF   = 2;
TARGET_PARENT = 3;
TARGET_TOP    = 4;

// borwser constants
BROWSER_IE    = 1;
BROWSER_GECKO = 2;
BROWSER_OTHER = -1;

var _currMenu     = "";
var _arrMenus     = new Array;	//Holds top level menus
var _mnuIndex     = 0;			//Total number of menus.
var _themeFolder  = "/htmllib/css/xpmenu/";
var _themeFile    = "XPBlue.css";
var _imageFolder  = "/img/xpmenu/";
var _browser      = -1;
var _strCtxt      = "";

function MenuBand(pstrDesc, pstrTip, open)
{
	// Properties
	this.id        = "";		//generated at runtime.
	this.label     = pstrDesc;	//Text to be displayed in header
	this.microHelp = pstrTip;	//Tooltip text
	this.isHeader  = true;
	this.open      = open;		//future use
	this.submenu   = new Array;	//array containing link items.
	this.smcount   = 0;			//count of sub items.

	//Methods
	this.addSubMenu = addSubMenu;	//function for item addition
	this.render     = drawMenu;		//function for rendering header
}

function SubMenu(pstrDesc, pstrLink, pstrLinkData, pstrTip, pstrImage, pstrTarget)
{
	// Properties
	this.parentId   = "";			//populated at runtime
	this.label      = pstrDesc;		//Item text to be displayed
	this.hlink      = pstrLink;		//Link url
	this.linkTarget = pstrTarget;	//Target window and or frame identifier
	this.linkData   = pstrLinkData;	//Exrta data to be passed through querystring.
	this.microHelp  = pstrTip;		//Tooltip text.
	this.isHeader   = false;
	this.isSelected = false;		//future use
	this.iconSrc    = pstrImage;	//Name of the image file.

	//Methods
	this.render     = drawSubMenu;	//function for rendering sub item.
}

function addSubMenu(pstrDesc, pstrLink, pstrLinkData, pstrTip, pstrImage, pstrTarget)
{
	var objTmp;

	objTmp = new SubMenu(pstrDesc, pstrLink, pstrLinkData, pstrTip, pstrImage, pstrTarget);
	objTmp.parentId = this.id;
	this.submenu[this.smcount] =  objTmp;
	this.smcount++;
}

function drawMenu()
{   
	var iCntr = 0;
	var objMenu;
	var strId, strLbl;
	if (this.open) {
		visib = 'visibile';
		disp = 'block';
		menuclass = "menuHeaderExpanded";
		image = '/minus.gif';
	} else {
		visib = 'hidden';
		disp = 'none';
		menuclass = "menuHeaderCollapsed";
		image = '/plus.gif';
	}
	
	document.write("<table  border=\"0\" cellspacing=\"0\"" +
					" cellpadding=\"0\" style=\"padding:0 0 0 0;\" width=\"100%\">");
	document.write("<tr style=\"background:url('/background1.gif')\" onMouseover=\"this.style.background='url(/onexpand.gif)'\" onMouseout=\"this.style.background='url(/background1.gif)'\"><td style=\"width:180px;vertical-align: center; \"><font style='font-weight:bold'>&nbsp;" + this.label +
					"</font></td><td class=" + menuclass + " id=\"" + this.id + "\"" + 
					"onclick=\"toggle(this)\">");
	document.write("&nbsp;<img id="+ this.id +"_image src="+ image +"></td></tr>");
	document.write("</table>");
	document.write("<div style=\"display: " + disp + "; visibility: " + visib + ";\"" +
			" class=\"menuItems\" id=\"" + this.id + "_child" + "\">");
	document.write("<table border=0 style='background:white' border=0 cellspacing=1 cellpadding=0 width=100%>");
	for (iCntr = 0; iCntr < this.smcount; iCntr++)
	{
		this.submenu[iCntr].render();
	}
	document.write("</table></div>");
}

function drawSubMenu()
{
	var strImg = "";
	var leftmargin = "";


	if (this.hlink) {
		document.write("<a href=" + getLink(this.linkTarget, (_strCtxt + this.hlink), this.linkData) + ">");
		document.write(this.label);
		document.write("</a>");
	} else {
		document.write(this.label);
	}
}

function getLink(pTarget, pstrLink, pstrLinkData) 
{
	var strRet = "";
	var strTmp;

	strTmp = pstrLink;
	if (pstrLinkData != null)
		strTmp = pstrLink;

	if (pTarget == TARGET_BLANK) 
		strRet = "\"" + strTmp + "\" TARGET=\"_blank\"";
	else if (pTarget == TARGET_SELF)
		strRet = "\"" + strTmp + "\" TARGET=\"_self\"";
	else if (pTarget == TARGET_PARENT)
		strRet = "\"" + strTmp + "\" TARGET=\"_parent\"";
	else if (pTarget == TARGET_TOP)
		strRet = "\"" + strTmp + "\" TARGET=\"_top\"";
	else if (pTarget != null)
		strRet = "\"" + strTmp + "\" TARGET=\"" + pTarget + "\"";
	else
		strRet = "\"" + strTmp + "\"";
	return strRet;
}

function mousehover(pobjSrc)
{
	return;
	var strCls = pobjSrc.className;
	if (strCls == "menuHeaderExpanded")
		pobjSrc.className = "menuHeaderExpandedOver";
	else
		pobjSrc.className = "menuHeaderCollapsedOver";
}

function mouseout(pobjSrc)
{
	return;
	var strCls = pobjSrc.className;
	if (strCls == "menuHeaderExpandedOver")
		pobjSrc.className = "menuHeaderExpanded";
	else
		pobjSrc.className = "menuHeaderCollapsed";
}

function toggle(pobjSrc)
{
	var strCls = pobjSrc.className;
	var strId = pobjSrc.id;
	var objTmp, child;

	if (pobjSrc.id != _currMenu)
		objTmp = document.getElementById(_currMenu);

	/*
	if (objTmp) {
		objTmp.className = "menuHeaderCollapsed";

		child = document.getElementById(_currMenu + "_child");
		child.style.visibility = "hidden";
		child.style.display = "none";
	}
	*/

	child = document.getElementById(strId + "_child");
	ichild = document.getElementById(strId + "_image");
	if (child.style.visibility == "hidden")
	{
		pobjSrc.className = "menuHeaderExpanded";
		child.style.visibility = "visible";
		child.style.display = "block";
		ichild.src = "<?php echo $skinget?>/minus.gif";
	} else {
		pobjSrc.className = "menuHeaderCollapsed";
		child.style.visibility = "hidden";
		child.style.display = "none";
		ichild.src = "<?php echo $skinget?>/plus.gif";
	}
	_currMenu = pobjSrc.id;
}

function detectBrowser()
{
	switch(navigator.family)
	{
		case 'ie4':
			_browser = BROWSER_IE;
			break;
		case 'gecko':
			_browser = BROWSER_GECKO;
			break;
		default:
			_browser = BROWSER_OTHER;
			break;
	}
}

function detectContext()
{
	var strProto, strHost, strPath;
	var strPort, strUrl, strBase;
	var strRemain;
	var intLen, intPos;

	// determine the context
	strProto  = window.location.protocol;
	if (strProto.indexOf("http") != -1)
	{
		strHost   = window.location.hostname;
		strPath   = window.location.pathname;
		strPort   = window.location.port;
		strUrl    = window.location.href;
		strBase   = strProto + "/" + "/" + strHost + ":" + strPort;
		intLen    = strBase.length;
		strRemain = strUrl.substr(intLen + 1);
		intPos    = strRemain.indexOf("/");
		_strCtxt  = strRemain.substr(0, intPos);
		if (_strCtxt.length > 0)
			_strCtxt = "/" + _strCtxt + "/";
	}
}

function initialize(pintWidth)
{
	var iCntr = 0;

	document.write("<link href=\"" + _themeFolder + "/" + _themeFile + "\"" + 
					" rel=\"stylesheet\" type=\"text/css\">");

	document.write("<center><font style=\"color: " + document.bgColor + 
					";font-size: 4pt;\">" + "</font>");
	document.write("<div id=\"panelBar\" style=\"width: " + pintWidth + "\">");
	for (iCntr = 0; iCntr < _mnuIndex; iCntr++)
	{
		_arrMenus[iCntr].render();
		document.write("<span style=\"display: block;\">&nbsp;</span>");
	}
	document.write("</div></center>");
}

/*------------------------------------------------------------------------------
 * Public functions
 *----------------------------------------------------------------------------*/
function createMenu(pstrDesc, pstrTip, isOpen)
{
	var mnuRet;

	mnuRet = new MenuBand(pstrDesc, pstrTip, isOpen);
	mnuRet.id = "mnu_" + _mnuIndex;
	_arrMenus[_mnuIndex] = mnuRet;
	_mnuIndex++;
	return mnuRet;
}
 
function createSubMenu(pMenu, pstrDesc, pstrLink, pstrLinkData, pstrTip, 
						pstrImage, pstrTarget)
{
	if (pMenu)
		if (pMenu.isHeader)
			pMenu.addSubMenu(pstrDesc, pstrLink, pstrLinkData, pstrTip, pstrImage, 
								pstrTarget);
}

function setTheme(pstrTheme, pstrThemeFolder, pstrImgFolder)
{
	if (pstrTheme != null)
		_themeFile =  pstrTheme;
	if (pstrThemeFolder != null)
		_themeFolder = pstrThemeFolder;
	if (pstrImgFolder != null)
		_imageFolder = pstrImgFolder;
}

