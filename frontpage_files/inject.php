	var isEnabledInternalAppointments = 0;
	//-------------------------------------------------------------
var isInited2 = false; var isOesEnabled = false;
window.addEventListener('message', function (event) {
	if (typeof event.data !== 'object') return;
	if (!('msg' in event.data)) return;
	if (!('type' in event.data.msg)) return;
	if (event.data.msg.type != 'yes-exam-going') return;
	console.log('yes-exam-going');
	initOes();
});
console.log("OES: ", window.location.hash);
if (window.location.hash == '#isExam'){
	initOes();
}

function initOes(){
	//-----------------------------------------------------
	if (isInited2) return;
	isInited2 = true;
	isOesEnabled = true;
	//--------------------------------------------------
	function stopExam(){
		window.postMessage("start-exit-moodle");
	}
	//--------------------------------------------------
	// Отключаем правую кнопку
	document.addEventListener("contextmenu", function(evt){
	  evt.preventDefault();
	}, false);
	// Отключаем копирование
	document.addEventListener("copy", function(evt){
	  	evt.clipboardData.setData("text/plain", "Копирование на этой странице запрещено");
	  	evt.preventDefault();
	}, false);
	// Отключаем вырезку
	document.addEventListener("cut", function(evt){
	  	evt.preventDefault();
	}, false);
	// Отключаем выделение
	var sheet = document.createElement('style')
	sheet.innerHTML = "* {user-select: none !important;}*::selection {background: none;}*::-moz-selection {background: none;}";
	document.body.appendChild(sheet);
	// При попытке вставить делаем запись
	document.addEventListener('paste', (e) => {
	    var clipboardData, pastedData;
	    e.stopPropagation();
	    e.preventDefault();
	    clipboardData = e.clipboardData || window.clipboardData;
	    pastedData = clipboardData.getData('Text');
	    event = {'type': 'on-paste', 'content': pastedData};
	    window.parent.postMessage(JSON.stringify(event), '*');
	    console.log('send event', JSON.stringify(event));
	});
	//--------------------------------------------------
	if (window.location.pathname.endsWith("/mod/quiz/review.php") || window.location.pathname.endsWith("/moodle/course/view.php")){
		console.log('send start-exit');
		window.postMessage("start-exit");
	} 
	//--------------------------------------------------
	console.log('Moodle AITU');
	//--------------------------------------------------
	var jqueryLoaded = (function(){
		//--------------------------------------------------
		if (window.location.pathname.endsWith("/mod/quiz/attempt.php") || window.location.pathname.endsWith("/moodle/course/attempt.php")){
			var examName = $('#page h1').text().trim();
			if (examName != ''){
				var msg = {};
				msg['type'] = 'on-exam-name';
				msg['name'] = examName;
				var data = {};
				data['type'] = 'send-data-message';
				data['msg'] = msg;
				window.postMessage(JSON.stringify(data), '*');
			}
		}
		//--------------------------------------------------
		if ($('.quizstartbuttondiv form button[type=submit]').length >= 1){
			$('.quizstartbuttondiv form button[type=submit]').click();
		}
		$('#jump-to-activity').css('display', 'none');
		$('#next-activity-link').css('display', 'none');
		$('#prev-activity-link').css('display', 'none');

		/*
		if ($('.mod_quiz-next-nav').attr('value') == 'Закончить попытку...') $('.mod_quiz-next-nav').attr('value', 'Завершить экзамен');
		$('.othernav .endtestlink').text('Завершить экзамен');
		*/

		if ($('.mod_quiz-next-nav').attr('value') == 'Закончить попытку...') $('.mod_quiz-next-nav').attr('value', 'Завершить');
		$('.othernav .endtestlink').text('Завершить');
		//--------------------------------------------------
		if (window.location.pathname.endsWith("/mod/quiz/startattempt.php")){
			$('#id_submitbutton').click();
		}
		if (window.location.pathname.endsWith("/mod/quiz/summary.php")){

			var parent = $('.quizsummaryofattempt').parent();
			var first = parent.find('.btn-secondary').eq(0);
			var second = parent.find('.btn-secondary').eq(1);

			second.click();

			$(document).on('click', '.moodle-dialogue-confirm input[type=button][value="Отмена"]', function(){
				console.log('click canser');
				first.click();
			});
		}
		//--------------------------------------------------
	});	
	//--------------------------------------------------
	(function() {
	  	const script = document.createElement("script");
	  	script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js';
	  	script.type = 'text/javascript';
	  	script.addEventListener('load', () => {
	    console.log(`jQuery ${$.fn.jquery} has been loaded successfully!`);
	   		jqueryLoaded();
	  	});
	  	document.head.appendChild(script);
	})();
	//--------------------------------------------------
}; 
//------------------------------------------------------
document.addEventListener('mouseover', function(e) {
	if (e.target == document) return;
    if (!event.target.matches('.quizstartbuttondiv form button[type=submit], .quizstartbuttondiv form button[type=submit] *')) return;
    //--------------------
    if (isOesEnabled) return;
    if ("isEnabledInternalAppointments" in window && window.isEnabledInternalAppointments == 0) return;
    //--------------------
    var el = e.target;
    if (el.matches(".oesInited")) return;
    elClone = el.cloneNode(true);
    elClone.classList.add('oesInited');
    el.parentNode.replaceChild(elClone, el);
    //--------------------
}); 
async function getCookieData() {
	try {
		const response = await fetch('/get-cookie.php', {
			method: 'GET',
			credentials: 'include' // если нужно отправлять куки
		});

		if (!response.ok) {
			throw new Error(`Ошибка HTTP: ${response.status}`);
		}

		// если ответ — JSON
		const data = await response.json();
		return data;

		// если ответ — обычный текст, используй:
		// const text = await response.text();
		// return text;

	} catch (error) {
		console.error('Ошибка при получении cookie:', error);
		return null;
	}
}
document.addEventListener('click', async function(e) {
	//--------------------
	function getCookie(cookieName) {
	  	var cookie = {};
	  	document.cookie.split(';').forEach(function(el) {
	    	var [key,value] = el.split('=');
	    	cookie[key.trim()] = value;
	  	});
	  	return cookie[cookieName];
	}
	//--------------------
  	if (!event.target.matches('.quizstartbuttondiv form button[type=submit], .quizstartbuttondiv form button[type=submit] *')) return;
  	if (isOesEnabled) return; 
  	if (window.location.hash == '#OES') return;
	//--------------------
  	if ("isEnabledInternalAppointments" in window && window.isEnabledInternalAppointments == 0) return;
  	console.log("Click submit");
  	e.preventDefault();
	//--------------------
  	var examName = document.querySelector(".page-header-headings h1").innerText;
  	if (examName.trim() == '') {
  		alert("OES: Не могу получить название экзамена");
  		return;
  	}
	//--------------------
	var cookieData = await getCookieData();
	// cookieData['MoodleSession'] = getCookie('MoodleSession'); 
	//--------------------
  	window.location.href = "https://aitu.oes.kz/internal_assignment?system=DEFAULT_MOODLE&examName=" + encodeURIComponent(examName) + "&url=" + encodeURIComponent(window.location.href + "#OES") + "&backurl=" + encodeURIComponent(window.location.href) + "&cookies=" + encodeURIComponent(JSON.stringify(cookieData));
  	return false;
}); 
//------------------------------------------------------
(function(){
	console.log("OES START CHECK");
	var isLogged = false;
	var elements = document.querySelectorAll('.menu-action-text');
	for (var i = 0; i < elements.length; i++) {
		var el = elements[i];
		if (el.innerText.trim() == 'Личный кабинет') isLogged = true;
		if (el.innerText.trim() == 'О пользователе') isLogged = true;
		if (el.innerText.trim() == 'Выход') isLogged = true;
	}
	elements = document.querySelectorAll('.dropdown-item');
	for (var i = 0; i < elements.length; i++) {
		var el = elements[i];
		if (el.innerText.trim() == 'Личные файлы') isLogged = true;
		if (el.innerText.trim() == 'Настройки') isLogged = true;
		if (el.innerText.trim() == 'Выход') isLogged = true;
	}
	console.log("OES START CHECK", isLogged, elements);
	if (isLogged){
		var data = {};
		data['type'] = 'im-logged';
		//
		var args = {};
		args['type'] = 'send-data-message';
		args['msg'] = data;
		args['packetId'] = -1;
		window.postMessage(JSON.stringify(args), '*');
		//
		console.log("OES: send is logged");
	} 

	if (!isLogged){
		setTimeout(function(){
			var isLogged = false;
			var elements = document.querySelectorAll('.menu-action-text');
			for (var i = 0; i < elements.length; i++) {
				var el = elements[i];
				if (el.innerText.trim() == 'Личный кабинет') isLogged = true;
				if (el.innerText.trim() == 'О пользователе') isLogged = true;
				if (el.innerText.trim() == 'Выход') isLogged = true;
			}
			elements = document.querySelectorAll('.dropdown-item');
			for (var i = 0; i < elements.length; i++) {
				var el = elements[i];
				if (el.innerText.trim() == 'Личные файлы') isLogged = true;
				if (el.innerText.trim() == 'Настройки') isLogged = true;
				if (el.innerText.trim() == 'Выход') isLogged = true;
			}
			console.log("OES START CHECK", isLogged, elements);
			if (isLogged){
				var data = {};
				data['type'] = 'im-logged';
				//
				var args = {};
				args['type'] = 'send-data-message';
				args['msg'] = data;
				args['packetId'] = -1;
				window.postMessage(JSON.stringify(args), '*');
				//
				console.log("OES: send is logged");
			} 
		}, 1000);
	}
	//--------------------------------------------------
	if (window.location.pathname.endsWith("/mod/quiz/review.php") || window.location.pathname.endsWith("/moodle/course/view.php") || window.location.pathname.endsWith("/moodle/quiz/view.php")){
		window.postMessage("start-exit");
		try{
			parent.postMessage("do-exit", "*");
			console.log("do-exit");
		} catch (e){
			console.log("do-exit", e);
		}
	}
	setTimeout(async function(){
		function convertAttemptUrlToView(url) {
		    try {
		        const u = new URL(url);

		        // Берём cmid из параметров
		        const cmid = u.searchParams.get("cmid");
		        if (!cmid) return null; // нет cmid — нечего конвертировать

		        // Меняем путь
		        u.pathname = u.pathname.replace("attempt.php", "view.php");

		        // Чистим параметры и ставим новый
		        u.search = "";              // очистить ?...
		        u.searchParams.set("id", cmid);

		        return u.toString();
		    } catch (e) {
		        return null;
		    }
		}

		if (window.location.pathname.endsWith("/mod/quiz/attempt.php") || window.location.pathname.endsWith("/moodle/course/attempt.php")){
			var h1 = document.querySelector('#page h1');
		    var examName = h1 ? h1.textContent.trim() : '';

		    if (examName !== '') {
		        var msg = {
		            type: 'on-exam-name',
		            name: examName
		        };

		        var data = {
		            type: 'oes-data-message',
		            msg: msg
		        };

		        try {
		            parent.postMessage(JSON.stringify(data), '*');
		        } catch (e) {
		            // ignore
		        }
		    }
		    var cookieData = await getCookieData();
			if ("isEnabledInternalAppointments" in window && window.isEnabledInternalAppointments == 0) return;
			if (isOesEnabled) return;
			if (window.self !== window.top) {

			} else {
				var url = convertAttemptUrlToView(window.location.href);
				// console.log("ОПА", 'examName', examName, url);
				window.location.href = "https://aitu.oes.kz/internal_assignment?system=DEFAULT_MOODLE&examName=" + encodeURIComponent(examName) + "&url=" + encodeURIComponent(window.location.href + "#OES") + "&backurl=" + encodeURIComponent(url) + "&cookies=" + encodeURIComponent(JSON.stringify(cookieData));
			}
		}	
	}, 1000);
	//--------------------------------------------------
})();
//------------------------------------------------------
/*
setTimeout(async function(){
	console.log("requestFullscreen 1");
	if (window.self === window.top) return;
	console.log("requestFullscreen 2");
	['click', 'keydown', 'mousemove', 'touchstart'].forEach(event => {
	    document.addEventListener(event, async () => {
	        if (!document.fullscreenElement) {
	            try {
	                await document.documentElement.requestFullscreen();
	            } catch (_) {}
	        }
	    }, { passive: true });
	}); 

	const box = document.getElementById('region-main-box');

	function applyBlur() {
	    if (box) box.style.filter = 'blur(14px)';
	}

	function removeBlur() {
	    if (box) box.style.filter = 'none';
	}

	// вкладка ушла из видимости (переключение, Win+Tab и т.п.)
	document.addEventListener('visibilitychange', () => {
	    if (document.visibilityState === 'hidden') {
	        applyBlur();
	    } else {
	        removeBlur();
	    }
	});

	// мышь вышла за пределы окна (часто перед любыми действиями)
	document.documentElement.addEventListener('mouseleave', () => {
	    applyBlur();
	});

	document.documentElement.addEventListener('mouseenter', () => {
	    removeBlur();
	});


	await document.documentElement.requestFullscreen();
}, 3000);
*/

setTimeout(async function(){ 
	if (window.self === window.top) return; 
	const box = document.getElementById('region-main-box');

	function applyBlur() {
	    if (box) box.style.filter = 'blur(14px)';
	}

	function removeBlur() {
	    if (box) box.style.filter = 'none';
	}

	// вкладка ушла из видимости (переключение, Win+Tab и т.п.)
	document.addEventListener('visibilitychange', () => {
	    if (document.visibilityState === 'hidden') {
	        applyBlur();
	    } else {
	        removeBlur();
	    }
	});

	// мышь вышла за пределы окна (часто перед любыми действиями)
	document.documentElement.addEventListener('mouseleave', () => {
	    applyBlur();
	});

	document.documentElement.addEventListener('mouseenter', () => {
	    removeBlur();
	}); 
}, 3000);
//------------------------------------------------------
var msg = {};
msg['type'] = 'is-exam-going';
var data = {};
data['type'] = 'send-data-message';
data['msg'] = msg;
window.postMessage(JSON.stringify(data), '*');
//------------------------------------------------------