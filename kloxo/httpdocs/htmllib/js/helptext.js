function changeContent(id,message) {
  if (document.getElementById || document.all) {
  if (!top.leftframe) {
	  return;
  }
  var el = top.leftframe.document.getElementById(id)
  if (el && typeof el.innerHTML != "undefined") {
	  if(help_data[message]) el.innerHTML =help_data[message];
	  if(!help_data[message]) el.innerHTML =message;
 
 }
 }
}


help_data = new Object();

//help_data["helparea"]="Help Area";

help_data["search"]="Enter the search pattern";
help_data["showall"]="Show All";


var xpos,ypos;

function findPosition( oLink ) {
  if( oLink.offsetParent ) {
    for( var posX = 0, posY = 0; oLink.offsetParent; oLink = oLink.offsetParent ) {
      posX += oLink.offsetLeft;
      posY += oLink.offsetTop;
    }
    return  posY;
  } else {
    return  oLink.y;
  }
}


function showHelpInPos() 
{
	tpos = findPosition(document.getElementById('helppic')) + 30;
	//showMenuInFrame('help',82,tpos);
}
