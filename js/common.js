
function copyToClipBoard(){
    var clipBoardContent="";
    clipBoardContent+=document.title;
    clipBoardContent+="";
    clipBoardContent+=window.location.href;
    window.clipboardData.setData("Text",clipBoardContent);
    alert("复制成功，请粘贴到你的QQ/MSN上推荐给你的好友");
}

function copyTo(val,tt){
	window.clipboardData.setData("Text",val);
	if(tt=="")
		tt="QQ/MSN";
    alert("复制成功，请粘贴到你的"+tt+"上推荐给你的好友");
}

function get_code() 
{
	var CodeFile = "do.php";
	if(document.getElementById("imgid"))document.getElementById("imgid").innerHTML = '<img src="'+CodeFile+'?act=chkcode&'+Math.random()+'" alt="点击刷新验证码" style="cursor:pointer;border:0;vertical-align:top;" onclick="this.src=\''+CodeFile+'?act=chkcode&\'+Math.random()"/>'
}

function getObject(objectId) 
{ 
	if(document.getElementById && document.getElementById(objectId)) { 
		return document.getElementById(objectId); 
	} 
	else if (document.all && document.all(objectId)) { 
		return document.all(objectId); 
	} 
	else if (document.layers && document.layers[objectId]) { 
		return document.layers[objectId]; 
	} 
	else { 
		return false; 
	} 
} 

function showHide(e,objname)
{     
    var obj = getObject(objname); 
    if(obj.style.display == "none"){ 
        obj.style.display = "block"; 
        e.className="xias"; 
    }else{ 
        obj.style.display = "none"; 
        e.className="rights"; 
    }
}

function is_complex_password(str) {
	var n = str.length;
	if ( n < 6 ) { return false; }
	var cc = 0, c_step = 0;
	for (var i=0; i<n; ++i) {
		if ( str.charCodeAt(i) == str.charCodeAt(0) ) {
			++ cc;
		}
		if ( i > 0 && str.charCodeAt(i) == str.charCodeAt(i-1)+1) {
			++ c_step;
		}
	}
	if ( cc == n || c_step == n-1) {
		return false;
	}
	return true;
}