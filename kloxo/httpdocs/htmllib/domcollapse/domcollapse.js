/* 
 * DOMcollapse
 * Version 3.0
 * released 06.12.2005 
 * Not for commercial reselling or use, unless consent given by the author
 * Check for updates on http://onlinetools.org and http://wait-till-i.com
 *
*/
dc={
	triggerElements:'*', 	// elements to trigger the effect
	parentElementId:null,	// ID of the parent element (keep null if none)
	uniqueCollapse:false,	// is set to true only one element can be open at a time

	// CSS class names
	trigger:'trigger',
	triggeropen:'expanded',
	hideClass:'hide',
	showClass:'show',
	
	<?php 
	$skinget = $login->getSkinDir();
	?>
	// pictures and text alternatives
	closedPic:'<?php echo $skinget?>/plus.gif',
	closedAlt:'expand section',
	openPic:'<?php echo $skinget?>/minus.gif',
	openAlt:'collapse section',
	right:'right',
	/* Doesn't work with Safari
		hoverClass:'hover',
	*/

	init:function(e){
		var temp;
		if(!document.getElementById || !document.createTextNode){return;}
		if(!dc.parentElementId){
			temp=document.getElementsByTagName(dc.triggerElements);
		} else if(document.getElementById(dc.parentElementId)){
			temp=document.getElementById(dc.parentElementId).getElementsByTagName(dc.triggerElements);
		}else{
			return;
		}
		dc.tempLink=document.createElement('a');
		dc.tempLink.setAttribute('href','#');
		dc.tempLink.appendChild(document.createElement('img'));
		for(var i=0;i<temp.length;i++){
			if(dc.cssjs('check',temp[i],dc.trigger) || dc.cssjs('check',temp[i],dc.triggeropen)){
				dc.makeTrigger(temp[i],e);
			}
		}
	},
	makeTrigger:function(o,e){
		var tl=dc.tempLink.cloneNode(true);
		var tohide=o.nextSibling;
		while(tohide.nodeType!=1)
		{
			tohide=tohide.nextSibling;
		}
		o.tohide=tohide;
		if(!dc.cssjs('check',o,dc.triggeropen)){
			dc.cssjs('add',tohide,dc.hideClass);
			tl.getElementsByTagName('img')[0].setAttribute('align',dc.right);
			tl.getElementsByTagName('img')[0].setAttribute('src',dc.closedPic);
			tl.getElementsByTagName('img')[0].setAttribute('alt',dc.closedAlt);
			tl.getElementsByTagName('img')[0].setAttribute('title',dc.closedAlt);
			//o.setAttribute('title',dc.closedAlt);
		}else{
			dc.cssjs('add',tohide,dc.showClass);
			tl.getElementsByTagName('img')[0].setAttribute('align',dc.right);
			tl.getElementsByTagName('img')[0].setAttribute('src',dc.openPic);
			tl.getElementsByTagName('img')[0].setAttribute('alt',dc.openAlt);
			tl.getElementsByTagName('img')[0].setAttribute('title',dc.openAlt);
			//o.setAttribute('title',dc.openAlt);
			dc.currentOpen=o;
		}
	//	dc.addEvent(o,'click',dc.addCollapse,false);
		/* Doesn't work with Safari
		dc.addEvent(o,'mouseover',dc.hover,false);
		dc.addEvent(o,'mouseout',dc.hover,false);
		*/
		o.insertBefore(tl,o.firstChild);
		dc.addEvent(tl,'click',dc.addCollapse,false);
		// Safari hacks 
		tl.onclick=function(){return false;}
		o.onclick=function(){return false;}
	},
	/* Doesn't work with Safari
	hover:function(e){
		var o=dc.getTarget(e);
		var action=dc.cssjs('check',o,dc.hoverClass)?'remove':'add';
		dc.cssjs(action,o,dc.hoverClass)
	},
	*/
	addCollapse:function(e){
		var action,pic;
		// hack to fix safari's redraw bug 
		// as mentioned on http://en.wikipedia.org/wiki/Wikipedia:Browser_notes#Mac_OS_X
		if (self.screenTop && self.screenX){
			window.resizeTo(self.outerWidth + 1, self.outerHeight);    
			window.resizeTo(self.outerWidth - 1, self.outerHeight);   
		}
		if(dc.uniqueCollapse && dc.currentOpen){
			dc.currentOpen.getElementsByTagName('img')[0].setAttribute('align',dc.right);
			dc.currentOpen.getElementsByTagName('img')[0].setAttribute('src',dc.closedPic);
			dc.currentOpen.getElementsByTagName('img')[0].setAttribute('alt',dc.closedAlt);
			dc.currentOpen.setAttribute('img',dc.closedAlt);
			dc.cssjs('swap',dc.currentOpen.tohide,dc.showClass,dc.hideClass);
			dc.cssjs('remove',dc.currentOpen,dc.triggeropen);
			dc.cssjs('add',dc.currentOpen,dc.trigger);
		}
		var o=dc.getTarget(e);
		if(o.tohide){
			if(dc.cssjs('check',o.tohide,dc.hideClass)){
				o.getElementsByTagName('img')[0].setAttribute('align',dc.right);
				o.getElementsByTagName('img')[0].setAttribute('src',dc.openPic);
				o.getElementsByTagName('img')[0].setAttribute('alt',dc.openAlt);
				o.getElementsByTagName('img')[0].setAttribute('title',dc.openAlt);
				//o.setAttribute('title',dc.openAlt);
				dc.cssjs('swap',o.tohide,dc.hideClass,dc.showClass);
				dc.cssjs('add',o,dc.triggeropen);
				dc.cssjs('remove',o,dc.trigger);
			}else{
				o.getElementsByTagName('img')[0].setAttribute('align',dc.right);
				o.getElementsByTagName('img')[0].setAttribute('src',dc.closedPic);
				o.getElementsByTagName('img')[0].setAttribute('alt',dc.closedAlt);
				o.getElementsByTagName('img')[0].setAttribute('title',dc.closedAlt);
				//o.setAttribute('title',dc.closedAlt);
				dc.cssjs('swap',o.tohide,dc.showClass,dc.hideClass);
				dc.cssjs('remove',o,dc.triggeropen);
				dc.cssjs('add',o,dc.trigger);
			}
			dc.currentOpen=o;
			dc.cancelClick(e);
			//document.getElementById('debug').innerHTML=o.tohide.className;
		}
		else{
			dc.cancelClick(e);
		}
	},
	/* helper methods */
	getTarget:function(e){
		var target = window.event ? window.event.srcElement : e ? e.target : null;
		if (!target){return false;}
		while(!target.tohide && target.nodeName.toLowerCase()!='body')
		{
			target=target.parentNode;
		}
		// if (target.nodeName.toLowerCase() != 'a'){target = target.parentNode;} Safari fix not needed here
		return target;
	},
	cancelClick:function(e){
		if (window.event){
			window.event.cancelBubble = true;
			window.event.returnValue = false;
			return;
		}
		if (e){
			e.stopPropagation();
			e.preventDefault();
		}
	},
	addEvent: function(elm, evType, fn, useCapture){
		if (elm.addEventListener) 
		{
			elm.addEventListener(evType, fn, useCapture);
			return true;
		} else if (elm.attachEvent) {
			var r = elm.attachEvent('on' + evType, fn);
			return r;
		} else {
			elm['on' + evType] = fn;
		}
	},
	cssjs:function(a,o,c1,c2){
		switch (a){
			case 'swap':
				o.className=!dc.cssjs('check',o,c1)?o.className.replace(c2,c1):o.className.replace(c1,c2);
			break;
			case 'add':
				if(!dc.cssjs('check',o,c1)){o.className+=o.className?' '+c1:c1;}
			break;
			case 'remove':
				var rep=o.className.match(' '+c1)?' '+c1:c1;
				o.className=o.className.replace(rep,'');
			break;
			case 'check':
				return new RegExp("(^|\\s)" + c1 + "(\\s|$)").test(o.className)
			break;
		}
	}
}
dc.addEvent(window, 'load', dc.init, false);




