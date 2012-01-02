

function preloadImages() 
{ 
	var d=document; 
  
	if(d.images) { 
		if(!d.p) d.p=new Array();
		var i,j=d.p.length;
		a=preloadImages.arguments;
	
		for(i=0; i<a.length; i++) {

			if (a[i].indexOf("#")!=0) { 
				d.p[j]=new Image; d.p[j++].src=a[i];
			}
		}
	}
}

function loadImage(im, source)
{
	im = new Image;
	im.src = source;
}

function findObj(n, d) { 
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}
function swapImage() { 
  var i,j=0,x,a=swapImage.arguments; document.sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=findObj(a[i]))!=null){document.sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}


function swapImgRestore() { 
  var i,x,a=document.sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function changeImageSize(name, size)
{
	img = document.getElementById(name);
	img.width = size;
	img.height = size;
}



function showHideLayers() {
  var i,p,v,obj,args=showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style	; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
    obj.visibility=v; }
}


function emailcheck(str) {

                var at="@"
                var dot="."
                var lat=str.indexOf(at)
                var lstr=str.length
                var ldot=str.indexOf(dot)
                if (str.indexOf(at)==-1){

                   return false
                }

                if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
                   return false
                }

                if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
                    return false
                }

                 if (str.indexOf(at,(lat+1))!=-1){
                    return false
                 }

                 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
                    return false
                 }

                 if (str.indexOf(dot,(lat+2))==-1){
                    return false
                 }

                 if (str.indexOf(" ")!=-1){
                    return false
                 }

                  return true
}
