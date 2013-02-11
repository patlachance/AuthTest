var serviceURL = "https://secure.itisopen.net/AuthTest/services/do.php";

var debug = "false";

function logdebug(msg) {
	if ( debug == "true" ) {
		console.log(msg);
	}
}

function isOnMobileDevice() {
//	if (navigator.userAgent.match(/(iPhone|iPod|iPad|Android|BlackBerry)/)) {
	if (document.location.protocol == "file:") {
		return 1;
	}
	else {
		return 0;
	}
}

function myAlert(msg) {
	logdebug("-> alerting");
	if (isOnMobileDevice()) {
		navigator.notification.alert(msg, function() {});
	} else {
		$('#popupHolder p').remove();
		$('#popupHolder').append('<p>' + msg + '</p>');
		$('#popupHolder').popup( "open" );
	}
}


function init() {
	logdebug("init");
	if ( isOnMobileDevice() ) {
		logdebug("on mobile");
		document.addEventListener("deviceready", deviceReady, true);
   	} else {
		logdebug("on desktop");
		deviceReady();
   	}
	delete init;
}

function checkPreAuth() {
	logdebug("checkPreAuth");
	var form = $("#loginForm");

	// to remove after session handling validated
	// window.localStorage.clear();

	if(window.localStorage["username"] != undefined && window.localStorage["password"] != undefined) {
		$("#username", form).val(window.localStorage["username"]);
		$("#password", form).val(window.localStorage["password"]);
		handleLogin();
	}
}

function handleLogin() {
	logdebug("handleLogin");
	var form = $("#loginForm");
	//disable the button so we can't resubmit while we wait
	$("#submitButton",form).attr("disabled","disabled");
	var u = $("#username", form).val();
	var p = $("#password", form).val();
	if(u != '' && p!= '') {
		logdebug("-> call service");
		$.post(serviceURL + '?method=login&returnformat=json', {username:u,password:p}, function(res) {
			var result = res.items;
			logdebug("-> result " + result.status + "");
			if(result.status == 'success') {
				//store
				logdebug("-> storing");
				window.localStorage["username"] = u;
				window.localStorage["password"] = p;
				window.localStorage["sessionid"] = result.sessionid;
				//redirect 
				$.mobile.changePage("home.html");
			} else {
				logdebug("-> clearing local storage");
				window.localStorage.clear();
				myAlert(result.reason);
			}
		 $("#submitButton").removeAttr("disabled");
		},"json");
		logdebug("-> after service");
	} else {
		myAlert("You must enter a username and password");
		$("#submitButton").removeAttr("disabled");
	}
	return false;
}

function deviceReady() {
	logdebug("deviceReady");
	$("#loginPage").on("pageinit",function() {
		logdebug("pageinit run");
		$("#loginForm").on("submit",handleLogin);
		checkPreAuth();
	});
	$.mobile.changePage("#loginPage");
	logdebug("changePage #loginPage");
}
