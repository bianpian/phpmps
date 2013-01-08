
function checkemail(str)
{
	var sReg = /[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/;   
	if(!sReg.test(str))
	{
		return false;
	}  
	return true;   
} 

ifcheck = true;
function CheckAll(form)
{
	for (var i=0;i<form.elements.length-1;i++)
	{
		var e = form.elements[i];
		e.checked = ifcheck;
	}
	ifcheck = ifcheck == true ? false : true;
}

var tID=0;
function ShowTabs(ID)
{
	var tTabTitle=document.getElementById("TabTitle"+tID);
	var tTabs=document.getElementById("Tabs"+tID);
	var TabTitle=document.getElementById("TabTitle"+ID);
	var Tabs=document.getElementById("Tabs"+ID);
	if(ID!=tID)
	{
		tTabTitle.className='title1';
		TabTitle.className='title2';
		tTabs.style.display='none';
		Tabs.style.display='';
		tID=ID;
	}
}