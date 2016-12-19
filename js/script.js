function nextPage(){
	document.getElementById("generalForm").submit();
}

function displayTable(elem){
	if(elem.value != "0"){
		$('#historyTable').show('fast');
	}else{
		$('#historyTable').hide('fast');
	}
}

function page(action){
	document.getElementById("action").value = action == "P" ? "previous" : "next";
	document.getElementById("generalForm").submit();
}

function addActivity(){
	document.getElementById("action").value = "addActivity";
	document.getElementById("generalForm").submit();
}

function displayActivityOn(elem){
	if(elem.value == "0" || elem.value == "1"){
		$('#activityOn').hide('fast');
	}else{
		$('#activityOn').show('fast');
	}
}

$(function(){
    var empty = $('.empty-for-fixed');
    var tableHeaderElem = $('tr.table-header');
    if(empty.length > 0 && tableHeaderElem.length > 0){
        empty.height(tableHeaderElem.height());
        var eTop = tableHeaderElem.offset().top;
        var haveClassFix = false;
        $(window).scroll(function(){
            if(! haveClassFix && eTop - $(window).scrollTop() < 0){
                haveClassFix = true;
                tableHeaderElem.addClass("table-header-fixed");
                empty.show();
            }else if(haveClassFix && eTop - $(window).scrollTop() > 0){
                haveClassFix = false;
                tableHeaderElem.removeClass("table-header-fixed");
                empty.hide();
            }
        });
    }
});