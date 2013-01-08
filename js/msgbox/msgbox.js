/**
 * Yubox.js ��Ϣ��ʾ���
 */
//<meta http-equiv="X-UA-Compatible" content="IE=7" />  IE8͸���Ƚ������
//var location=window.location; ����iframe��ת�������
(function() {
	if (window.Yubox) return;
	var objType = function(type) {
		return new Function('o', "return Object.prototype.toString.call(o)=='[object " + type + "]'")
	}; //�ж�Ԫ������
	var isArray = objType('Array'),
	isObj = objType('Object'); //�ж�Ԫ���Ƿ����顢object
	window.Yubox = {
		version: '4.0',
		pubDate: '2009-03-02',
		apply: function(o, c, d) {
			if (d) Yubox.apply(o, d);
			if (o && c && isObj(c)) for (var p in c) o[p] = c[p];
			return o;
		},
		eventList: []
	};
	/*��ʼ��������ҳ�������ɵ��õĽӿڣ���ֹ�ⲿ����ʧ�ܡ�_initFn:�����ʼ���ô���Ĳ���*/
	var initFn = ['setDefaultCfg', 'show'], _initFn = {}, t;
	while (t = initFn.shift()) Yubox[t] = eval('0,function(){_initFn.' + t + '?_initFn.' + t + '.push(arguments):(_initFn.' + t + '=[arguments])}');
	/*����Ϊ���ú���������*/
	var isIE = !+'\v1'; //IE�����
	var isCompat = document.compatMode == 'CSS1Compat';	//�������ǰ����ģʽ
	var IE6 = isIE && /MSIE (\d)\./.test(navigator.userAgent) && parseInt(RegExp.$1) < 7; //IE6������Ҫ��iframe������
	var useFixed = !isIE || (!IE6 && isCompat); //����ʱ��IE7+����׼ģʽ�������������ʹ��Fixed��λ
	var $ = function(id) {return document.getElementById(id)}; //��ȡԪ��
	var $height = function(obj) {return parseInt(obj.style.height) || obj.offsetHeight}; //��ȡԪ�ظ߶�
	var addEvent = (function() {
		return new Function('env', 'fn', 'obj', 'obj=obj||document;' + (window.attachEvent ? "obj.attachEvent('on'+env,fn)": 'obj.addEventListener(env,fn,false)') + ';Yubox.eventList.push([env,fn,obj])')
	})(); //�¼���
	var detachEvent = (function() {
		return new Function('env', 'fn', 'obj', 'obj=obj||document;' + (window.attachEvent ? "obj.detachEvent('on'+env,fn)": 'obj.removeEventListener(env,fn,false)'))
	})(); //ȡ���¼���

	//ΪԪ�ص��ض���ʽ�����趨ֵ
	var setStyle = function(el, n, v) {
		if (!el) return;
		if (isObj(n)) {
			for (var i in n) setStyle(el, i, n[i]);
			return;
		}
		/*dom�����dom����*/
		if (isArray(el) || /htmlcollection|nodelist/i.test('' + el)) {
			for (var i = el.length - 1; i >= 0; i--) setStyle(el[i], n, v);
			return;
		}
		try {
			el.style[n] = v
		} catch(e) {}
	};
	/*----------------��ҵ���йصĹ��ú���-----------------*/
	var btnIndex = 0, btnCache, seed = 0; //��ǰ����İ�ť����������ǰ���ڵİ�ť��id����
	/*������ť*/
	var mkBtn = function(txt, sign, autoClose, id) {
		if (!txt) return;
		if (isArray(txt)) {
			/*��Ч��ťɾ��*/
			var item, t = [],
			dftBtn = {
				OK: [curCfg.okTxt, 'ok'],
				CANCEL: [curCfg.cancelTxt, 'cancel']
			};
			while (txt.length)(item = txt.shift()) && t[t.push(mkBtn.apply(null, dftBtn[item] || item)) - 1] || t.pop();
			return t;
		}
		id = id || 'Yubox_btn_' + seed++;
		autoClose = autoClose == undefined ? 'undefined': !!autoClose;
		return {
			id: id,
			html: "<input type='button' id='" + id + "' onclick='Yubox.doHandler(\"" + sign + "\"," + autoClose + ")' style='cursor:pointer' class='btnStyle handler' value='" + txt + "' />"
		};
	};
	/*���ɰ�ť��ϵ�html*/
	var joinBtn = function(btn) {
		if (!btn) return btnCache = '';
		if (!isArray(btn)) btn = [btn];
		if (!btn.length) return btnCache = '';
		btnCache = btn.concat();
		var html = [];
		while (btn.length) html.push(btn.shift().html);
		return html.join('&nbsp;&nbsp;');
	}
	/*Ĭ����ʾ���ü��û���ǰ����*/
	var dftCfg = {
		message: '����',		//��Ϣ������
		width: 300,				//��Ϣ����
		height: 185,			//��Ϣ��߶�
		title: '��ܰ��ʾ��',			//��Ϣ�����
		handler: function() {},	//�ص��¼���Ĭ�Ͽպ���
		maskAlphaColor: '#000',	//����͸��ɫ��Ĭ�Ϻ�ɫ
		maskAlpha: 0.2,			//����͸���ȣ�Ĭ��0.1
		iframe: false,			//iframeģʽ��Ĭ�ϲ���
		icoCls: '',				//��Ϣ�����ͼ�꣬Ĭ����
		btn: null,				//��Ϣ����ʾ�İ�ť��Ĭ����
		autoClose: true,		//����رա�ȷ���Ȱ�ť�Ƿ��Զ��رգ�Ĭ���Զ��ر�
		fixPosition: true,		//�Ƿ��������������Ĭ����
		dragOut: false,			//�Ƿ������ϳ����ڷ�Χ��Ĭ�ϲ�����
		titleBar: true,			//�Ƿ���ʾ��������Ĭ����ʾ
		showMask: true,			//�Ƿ���ʾ���֣�Ĭ����ʾ
		winPos: 'c',			//��Ϣ�򵯳���λ�ã�Ĭ����ҳ���м�
		winAlpha: 0.8,			//�϶�ʱ��Ϣ���͸���ȣ�Ĭ��0.8
		closeBtn: true,			//�Ƿ���ʾ�رհ�ť��Ĭ����ʾ
		showShadow: false,		//�Ƿ���ʾ��Ϣ�����Ӱ��Ĭ�ϲ���ʾ��IE֧�֣�
		useSlide: false,		//�Ƿ�������Ϣ��ĵ��뵭��Ч����Ĭ�ϲ�����
		slideCfg: {				//���뵭��Ч�����ã�useSlide=trueʱ��Ч
			increment: 0.1,		//ÿ�ν����ֵ��ֵ��Χ0-1
			interval: 50		//������ٶ�
		},
		closeTxt: '�ر�',		//�رհ�ť����ʾ�ı�
		okTxt: ' ȷ �� ',		//ȷ����ť����ʾ�ı�
		cancelTxt: ' ȡ �� ',	//ȡ����ť����ʾ�ı�
		msgCls: 'yu-content',	//��Ϣ�����ݵ�class���ƣ������Զ�����ʬ�٣�Ĭ��Ϊym-content,����iframe:falseʱ��Ч
		minBtn: false,			//�Ƿ���ʾ��С����ť��Ĭ�ϲ���ʾ
		minTxt: '��С��',		//��С����ť����ʾ�ı�
		maxBtn: false,			//�Ƿ���ʾ��󻯰�ť��Ĭ�ϲ���ʾ
		maxTxt: '���',		//��󻯰�ť����ʾ�ı�
		allowSelect:false,		//�Ƿ�����ѡ����Ϣ�����ݣ�Ĭ�ϲ�����
		allowRightMenu:false	//�Ƿ���������Ϣ��ʹ���Ҽ���Ĭ�ϲ�����
	},curCfg = {};

	/*��ʼ����*/
	(function() {
		var rootEl = document.body, callee = arguments.callee;
		if (!rootEl || typeof rootEl != 'object') return addEvent('load', callee, window); //�ȴ�ҳ��������
		/*��ֹ��IE����documentδ����������IE�޷���INTERNETվ��Ĵ��Ĵ�*/
		if (isIE && document.readyState != 'complete') return addEvent('readystatechange',function() {
			document.readyState == "complete"&&callee()
		});

		rootEl = isCompat ? document.documentElement: rootEl; //����html Doctype��ȡhtml���ڵ㣬�Լ��ݷ�xhtml��ҳ��
		var frameset = document.getElementsByTagName('frameset').length; //�Ƿ�framesetҳ��
		if (!isIE && frameset) return; //framesetҳ���Ҳ���IE��ֱ�ӷ��أ��������ִ���
		/*��ȡscrollLeft��scrollTop����fixed��λʱ����0��0*/
		var getScrollPos = function() {
			return curCfg.fixPosition && useFixed ? [0, 0] : [rootEl.scrollLeft, rootEl.scrollTop];
		}
		/*���洰�ڶ�λ��Ϣ�������������ҳ�����Ͻǵ�������Ϣ*/
		var saveWinInfo = function() {
			var pos = getScrollPos();
			Yubox.apply(dragVar, {
				_offX: parseInt(ym_win.style.left) - pos[0],
				_offY: parseInt(ym_win.style.top) - pos[1]
			});
		};
		/*-------------------------��������html-------------------*/
		var maskStyle = 'position:absolute;top:0;left:0;display:none;text-align:center';
		var div = document.createElement('div');
		div.innerHTML = [
		/*����*/
		"<div id='maskLevel' style=\'" + maskStyle + ';z-index:10000;\'></div>', IE6 ? ("<iframe id='maskIframe' scrolling='no' src='javascript:false' style='" + maskStyle + ";z-index:9999;filter:alpha(opacity=0);opacity:0'></iframe>") : '',
		/*����*/
		"<div id='yu-window' style='position:absolute;z-index:10001;display:none'>", IE6 ? "<iframe src='javascript:false' scrolling='no' style='width:100%;height:100%;position:absolute;top:0;left:0;z-index:-1'></iframe>": '', "<div class='yu-tl' id='yu-tl'><div class='yu-tr'><div class='yu-tc' style='cursor:move;'><div class='yu-header-text'></div><div class='yu-header-tools'>", "<div class='Yubox_min' title='��С��'><strong>0</strong></div>", "<div class='Yubox_max' title='���'><strong>1</strong></div>", "<div class='Yubox_close' title='�ر�'><strong>r</strong></div>", "</div></div></div></div>", "<div class='yu-ml' id='yu-ml'><div class='yu-mr'><div class='yu-mc'><div class='yu-body' style='position:relative'></div></div></div></div>", "<div class='yu-ml' id='yu-btnl'><div class='yu-mr'><div class='yu-btn'></div></div></div>", "<div class='yu-bl' id='yu-bl'><div class='yu-br'><div class='yu-bc'></div></div></div>", "</div>",
		/*��Ӱ*/
		isIE ? "<div id='yu-shadow' style='position:absolute;z-index:10000;background:#808080;filter:alpha(opacity=80) progid:DXImageTransform.Microsoft.Blur(pixelradius=2);display:none'></div>": ''].join('');
		document.body.appendChild(div);
		/*�����ϵĶ���*/
		/*mask��window*/
		var maskLevel = $('maskLevel');
		var ym_win = $('yu-window');
		var ym_shadow = $('yu-shadow');
		var ym_wins;
		/*header*/
		var ym_headbox = $('yu-tl');
		var ym_head = ym_headbox.firstChild.firstChild;
		var ym_hText = ym_head.firstChild;
		var ym_hTool = ym_hText.nextSibling;
		/*content*/
		var ym_body = $('yu-ml').firstChild.firstChild.firstChild;
		/*button*/
		var ym_btn = $('yu-btnl');
		var ym_btnContent = ym_btn.firstChild.firstChild;
		/*bottom*/
		var ym_bottom = $('yu-bl');
		var maskEl = [maskLevel]; //����Ԫ��
		IE6 && maskEl.push($('maskIframe'));
		var ym_ico = ym_hTool.childNodes; //���Ͻǵ�ͼ��
		var dragVar = {};
		/*���ڵ������С�����Ĺ���ʵ��*/
		var cur_state = 'normal',
		cur_cord = [0, 0]; //cur_cord��¼���ǰ���ڵ�����
		var cal_cord = function() {
			var pos = getScrollPos();
			cur_cord = [parseInt(ym_win.style.left) - pos[0], parseInt(ym_win.style.top) - pos[1]]
		}; //��������(���ҳ�����Ͻ�����)
		/*�ӳ�̬�����*/
		var doMax = function() {
			cal_cord(); //��¼���꣬���ڻ�ԭʱʹ��
			cur_state = 'max';
			ym_ico[1].firstChild.innerHTML = '2';
			ym_ico[1].className = 'Yubox_normal';
			setWinSize(rootEl.clientWidth, rootEl.clientHeight, [0, 0]);
		};
		/*����������С��*/
		var doMin = function() {
			cal_cord();
			cur_state = 'min';
			ym_ico[0].firstChild.innerHTML = '2';
			ym_ico[0].className = 'Yubox_normal';
			setWinSize(0, $height(ym_headbox), cur_cord); //��λ�ڵ�ǰ����
		};
		var doNormal = function(init) { //init=true,����ʱ���øú���
			! init && cur_state == 'min' && cal_cord(); //����С���������»�ȡ����
			cur_state = 'normal';
			ym_ico[0].firstChild.innerHTML = '0';
			ym_ico[1].firstChild.innerHTML = '1';
			ym_ico[0].className = 'Yubox_min';
			ym_ico[1].className = 'Yubox_max';
			setWinSize.apply(this, init ? [] : [0, 0, cur_cord]);
		};
		var max, min;
		addEvent('click', min = function() {
			cur_state != 'normal' ? doNormal() : doMin();
		},
		ym_ico[0]); //��С��
		addEvent('click', max = function() {
			cur_state != 'normal' ? doNormal() : doMax();
		},
		ym_ico[1]); //���
		addEvent('dblclick', function(e) {
			/*�������Ԫ���������С�رհ�ť�򲻽��д˴���*/
			curCfg.maxBtn && (e.srcElement || e.target).parentNode != ym_hTool && max()
		},
		ym_head);
		addEvent('click', function() {
			Yubox.doHandler('close');
		},
		ym_ico[2]); //�ر�
		/*���������С�����Ĳ��ֽ���*/
		/*getWinSizeȡ��ҳ��ʵ�ʴ�С*/
		var getWinSize = function() {
			return [Math.max(rootEl.scrollWidth, rootEl.clientWidth), Math.max(rootEl.scrollHeight, rootEl.clientHeight)]
		};
		var winSize = getWinSize(); //���浱ǰҳ���ʵ�ʴ�С
		/*�¼��󶨲���*/
		var bindEl = ym_head.setCapture && ym_head; //���Ϸ��¼��Ķ���ֻ��Ie��bindEl��Ч
		/*����͸���ȿ���*/
		var filterWin = function(v) {
			/*��갴��ʱȡ�������͸���ȣ�IE��׼ģʽ��͸����Ϊ1��ֱ�����͸�����ԣ���ֹiframe���ڲ����϶�������*/
			! frameset && setStyle(ym_win, v == 1 && isCompat ? {
				filter: '',
				opacity: ''
			}: {
				filter: 'Alpha(opacity=' + v * 100 + ')',
				opacity: v
			});
		};
		/*mousemove�¼�*/
		var mEvent = function(e) {
			var sLeft = dragVar.offX + e.clientX;
			var sTop = dragVar.offY + e.clientY;
			if (!curCfg.dragOut) { //ҳ��ɼ��������϶�
				var pos = getScrollPos(),
				sl = pos[0],
				st = pos[1];
				sLeft = Math.min(Math.max(sLeft, sl), rootEl.clientWidth - ym_win.offsetWidth + sl);
				sTop = Math.min(Math.max(sTop, st), rootEl.clientHeight - ym_win.offsetHeight + st);
			} else if (curCfg.showMask && '' + winSize != '' + getWinSize()) //��ʱ�������ִ�С
			resizeMask(true);
			setStyle(ym_wins, {
				left: sLeft + 'px',
				top: sTop + 'px'
			});
		};
		/*mouseup�¼�*/
		var uEvent = function() {
			filterWin(1);
			detachEvent("mousemove", mEvent, bindEl);
			detachEvent("mouseup", uEvent, bindEl);
			saveWinInfo(); //���浱ǰ���ڵ�λ��
			curCfg.iframe && setStyle(getPage().nextSibling, 'display', 'none');
			/*IE�´����ⲿ�϶�*/
			bindEl && (detachEvent("losecapture", uEvent, bindEl), bindEl.releaseCapture());
		};
		addEvent('mousedown',function(e) {
			if ((e.srcElement || e.target).parentNode == ym_hTool) return false; //���������ť�����������϶�����
			filterWin(curCfg.winAlpha); //��갴��ʱ�����͸����
			/*����뵯��������Ͻǵ�λ�Ʋ�*/
			Yubox.apply(dragVar, {
				offX: parseInt(ym_win.style.left) - e.clientX,
				offY: parseInt(ym_win.style.top) - e.clientY
			});
			addEvent("mousemove", mEvent, bindEl);
			addEvent("mouseup", uEvent, bindEl);
			if (curCfg.iframe) {
				var cfg = {display: ''}, pg = getPage();
				isCompat && IE6 && Yubox.apply(cfg, {
					width: pg.offsetWidth,
					height: pg.offsetHeight
				}); //IE6�������ø߶�
				setStyle(pg.nextSibling, cfg)
			}
			/*IE�´����ⲿ�϶�*/
			bindEl && (addEvent("losecapture", uEvent, bindEl), bindEl.setCapture());
		},
		ym_head);
		/*ҳ������������ڹ���*/
		var scrollEvent = function() {
			setStyle(ym_win, {
				left: dragVar._offX + rootEl.scrollLeft + 'px',
				top: dragVar._offY + rootEl.scrollTop + 'px'
			});
		};
		/*���̼���*/
		var keydownEvent = function(e) {
			var keyCode = e.keyCode;
			if (keyCode == 27) destroy(); //esc��
			if (btnCache) {
				var l = btnCache.length, nofocus;
				/*tab��/���ҷ�����л�����*/
				document.activeElement && document.activeElement.id != btnCache[btnIndex].id && (nofocus = true);
				if (keyCode == 9 || keyCode == 39) nofocus && (btnIndex = -1),
				$(btnCache[++btnIndex == l ? (--btnIndex) : btnIndex].id).focus();
				if (keyCode == 37) nofocus && (btnIndex = l),
				$(btnCache[--btnIndex < 0 ? (++btnIndex) : btnIndex].id).focus();
				if (keyCode == 13) return true;
			}
			/*��ֹF1-F12/ tab �س�*/
			return keyEvent(e, (keyCode > 110 && keyCode < 123) || keyCode == 9 || keyCode == 13);
		};
		/*���������¼�*/
		var keyEvent = function(e, d) {
			e = e || event;
			/*����Ա�����в���*/
			if (!d && /input|select|textarea/i.test((e.srcElement || e.target).tagName)) return true;
			try {
				e.returnValue = false;
				e.keyCode = 0;
			} catch(ex) {
				e.preventDefault && e.preventDefault();
			}
			return false;
		};
		maskLevel.oncontextmenu = keyEvent; //��ֹ�Ҽ���ѡ��
		/*���¼������ֵĴ�С*/
		var resizeMask = function(noDelay) {
			setStyle(maskEl, 'display', 'none'); //������
			var size = getWinSize();
			var resize = function() {
				setStyle(maskEl, {
					width: size[0] + 'px',
					height: size[1] + 'px',
					display: ''
				});
			};
			isIE ? noDelay === true ? resize() : setTimeout(resize, 0) : resize();
			cur_state == 'min' ? doMin() : cur_state == 'max' ? doMax() : setWinSize(); //�����С��״̬��ԭ
		};
		/*�ɰ����ʾ����,state:true��ʾ,false���أ�Ĭ��Ϊtrue*/
		var maskVisible = function(visible) {
			if (!curCfg.showMask) return; //������
			(visible === false ? detachEvent: addEvent)("resize", resizeMask, window); //ҳ���С�ı估ʱ�������ִ�С
			if (visible === false) return setStyle(maskEl, 'display', 'none'); //��������
			setStyle(maskLevel, {
				background: curCfg.maskAlphaColor,
				filter: 'Alpha(opacity=' + curCfg.maskAlpha * 100 + ')',
				opacity: curCfg.maskAlpha
			});
			resizeMask(true);
		};
		/*����ָ��λ�õ����꣬��������*/
		var getPos = function(f) {
			/*������Ч�����飬������û����꣨��Ҫ���򵥴�����������ݴ����ַ�����map��ƥ�䣬���ƥ�䲻����Ĭ�ϲ���c����*/
			f = isArray(f) && f.length == 2 ? (f[0] + '+{2},{3}+' + f[1]) : (posMap[f] || posMap['c']);
			var pos = [rootEl.clientWidth - ym_win.offsetWidth, rootEl.clientHeight - ym_win.offsetHeight].concat(getScrollPos());
			var arr = f.replace(/\{(\d)\}/g,function(s, s1) {return pos[s1]}).split(',');
			return [eval(arr[0]), eval(arr[1])];
		}; //9������λ�ó���
		var posMap = {
			c: '{0}/2+{2},{1}/2+{3}',
			l: '{2},{1}/2+{3}',
			r: '{0}+{2},{1}/2+{3}',
			t: '{0}/2+{2},{3}',
			b: '{0}/2,{1}+{3}',
			lt: '{2},{3}',
			lb: '{2},{1}+{3}',
			rb: '{0}+{2},{1}+{3}',
			rt: '{0}+{2},{3}'
		};
		/*�趨���ڴ�С����λ*/
		var setWinSize = function(w, h, pos) {
			if (ym_win.style.display == 'none') return; //��ǰ���ɼ��򲻴���
			/*Ĭ��ʹ�����õĿ��*/
			h = parseInt(h) || curCfg.height;
			w = parseInt(w) || curCfg.width;
			setStyle(ym_wins, {
				width: w + 'px',
				height: h + 'px',
				left: 0,
				top: 0
			});
			pos = getPos(pos || curCfg.winPos); //֧���Զ������꣬����Ĭ������
			setStyle(ym_wins, {
				top: pos[1] + 'px',
				left: pos[0] + 'px'
			});
			saveWinInfo(); //���浱ǰ����λ����Ϣ
			setStyle(ym_body, 'height', h - $height(ym_headbox) - $height(ym_btn) - $height(ym_bottom) + 'px'); //�趨�������ĸ߶�
			isCompat && IE6 && curCfg.iframe && setStyle(getPage(), {height: ym_body.clientHeight}); //IE6��׼ģʽ��Ҫ����iframe�߶�
		};
		var _obj = []; //IE�пɼ���objԪ��
		var cacheWin = []; //�����еĴ���
		var winVisible = function(visible) {
			var fn = visible === false ? detachEvent: addEvent;
			fn('scroll', curCfg.fixPosition && !useFixed ? scrollEvent: saveWinInfo, window);
			setStyle(ym_wins, 'position', curCfg.fixPosition && useFixed ? 'fixed': 'absolute');
			fn('keydown', keydownEvent);
			if (visible === false) { //�ر�
				setStyle(ym_shadow, 'display', 'none');
				/*�رմ���ִ�еĲ���*/
				var closeFn = function() {
					setStyle(ym_win, 'display', 'none');
					setStyle(_obj, 'visibility', 'visible');
					_obj = []; //�ѵ�ǰ�����Ƴ�
					cacheWin.shift(); //��ȡ������δִ�еĵ���
					if (cacheWin.length) Yubox.show.apply(null, cacheWin[0].concat(true))
				};
				/*���䷽ʽ�ر�*/
				var alphaClose = function() {
					var alpha = 1;
					var hideFn = function() {
						alpha = Math.max(alpha - curCfg.slideCfg.increment, 0);
						filterWin(alpha);
						if (alpha == 0) {
							maskVisible(false);
							closeFn();
							clearInterval(it);
						}
					};
					hideFn();
					var it = setInterval(hideFn, curCfg.slideCfg.interval);
				};
				curCfg.useSlide ? alphaClose() : closeFn();
				return;
			}
			for (var o = document.getElementsByTagName('object'), i = o.length - 1; i > -1; i--) o[i].style.visibility != 'hidden' && _obj.push(o[i]) && (o[i].style.visibility = 'hidden');
			setStyle([ym_hText, ym_hTool], 'display', (curCfg.titleBar ? '': 'none'));
			ym_head.className = 'yu-tc' + (curCfg.titleBar ? '': ' yu-ttc'); //�ޱ�����
			ym_hText.innerHTML = curCfg.title; //����
			for (var i = 0, c = ['min', 'max', 'close']; i < 3; i++) {
				ym_ico[i].style.display = curCfg[c[i] + 'Btn'] ? '': 'none';
				ym_ico[i].title = curCfg[c[i] + 'Txt'];
			}
			/*iframe���������opacity=100����ym-win����������iframe��divҲ͸��ʱ��iframeҲ��͸����*/
			var ifmStyle = 'position:absolute;width:100%;height:100%;top:0;left:0;opacity:1;filter:alpha(opacity=80)';
			ym_body.innerHTML = !curCfg.iframe ? ('<div class="' + curCfg.msgCls + '">' + curCfg.message + '</div>') : "<iframe scrolling='no' style='" + ifmStyle + "' border='0' frameborder='0' src='" + curCfg.message + "'></iframe><div style='" + ifmStyle + ";background:#000;opacity:0.1;filter:alpha(opacity=10);display:none'></div>"; //����
			(function(el, obj) {
				for (var i in obj) try {
					el[i] = obj[i]
				} catch(e) {}
			})(ym_body.firstChild, curCfg.iframe); //Ϊiframe����Զ�������
			ym_body.className = "yu-body " + curCfg.icoCls; //ͼ������
			setStyle(ym_btn, 'display', ((ym_btnContent.innerHTML = joinBtn(mkBtn(curCfg.btn))) ? '': 'none')); //û�а�ť������
			! curCfg.useSlide && curCfg.showShadow && setStyle(ym_shadow, 'display', '');
			setStyle(ym_win, 'display', '');
			doNormal(true);
			filterWin(curCfg.useSlide ? 0 : 1); //�˴�ʹ��filterͬʱ���Խ��IE�Ǳ�׼ģʽ����ʱ�±߻����1px�հף�ʹ�������²����νӵ�����
			/*���䷽ʽ��ʾ*/
			curCfg.useSlide && (function() {
				var alpha = 0;
				var showFn = function() {
					alpha = Math.min(alpha + curCfg.slideCfg.increment, 1);
					filterWin(alpha);
					if (alpha == 1) {
						clearInterval(it);
						curCfg.showShadow && setStyle(ym_shadow, 'display', '')
					}
				}
				showFn();
				var it = setInterval(showFn, curCfg.slideCfg.interval);
			})();
			btnCache && $(btnCache[btnIndex = 0].id).focus(); //��һ����ť��ȡ����
			/*�Ƿ��ֹѡ�񡢽�ֹ�Ҽ�*/
			ym_win.onselectstart = curCfg.allowSelect?null:keyEvent;
			ym_win.oncontextmenu = curCfg.allowRightMenu?null:keyEvent;
		}; //��ʼ��
		var init = function() {
			ym_wins = [ym_win].concat(curCfg.showShadow ? ym_shadow: ''); //�Ƿ�ʹ����Ӱ
			maskVisible();
			winVisible();
		}; //����
		var destroy = function() { ! curCfg.useSlide && maskVisible(false);
			winVisible(false);
		}; //ȡ��iframe
		var getPage = function() {
			return curCfg.iframe ? ym_body.firstChild: null
		}
		Yubox.apply(Yubox, {
			close: destroy,
			max: max,
			min: min,
			normal: doNormal,
			getPage: getPage,
			/*��ʾ��Ϣ��,fargs:�������ã��Ḳ��args�е�����*/
			/*show ǿ����ʾ*/
			show: function(args, fargs, show) { //����д���δ�ر��򽫱��δ������Ϣ�ŵ�������
				//09-8-28 thinksns ����δ��¼���ж�

//				if(MID==0 && NEED_LOGIN==1){
//					alert("����½����ܽ��д˲�����");
//				}
				if (!show && cacheWin.push([args, fargs]) && cacheWin.length > 1) return;
				/*֧�����ֲ������뷽ʽ:(1)JSON��ʽ (2)�����������*/
				var a = [].slice.call(args, 0), o = {}, j = -1;
				if (!isObj(a[0])) {
					for (var i in dftCfg) if (a[++j]) o[i] = a[j];
				} else {
					o = a[0];
				}
				Yubox.apply(curCfg, Yubox.apply({}, o, fargs), Yubox.setDefaultCfg()); //�Ȼ�ԭĬ������
				/*����curCfg�е���Чֵ(null/undefined)��ΪĬ��ֵ*/
				for (var i in curCfg) curCfg[i] = curCfg[i] != null ? curCfg[i] : Yubox.cfg[i];
				init();
			},
			doHandler: function(sign, autoClose, closeFirst) {
				if (autoClose == undefined ? curCfg.autoClose: autoClose) destroy();
				try { (curCfg.handler)(sign)
				} catch(e) {
					alert(e.message)
				};
			},
			resizeWin: setWinSize,
			/*�趨Ĭ������*/
			setDefaultCfg: function(cfg) {
				return Yubox.cfg = Yubox.apply({}, cfg, Yubox.apply({}, Yubox.cfg, dftCfg));
			},
			getButtons: function() {
				var btns = btnCache || [], btn, rBtn = [];
				while (btn = btns.shift()) rBtn.push($(btn.id));
				return rBtn;
			}
		});
		Yubox.setDefaultCfg(); //��ʼ��Ĭ������
		/*ִ���û���ʼ��ʱ�ĵ���*/
		var t;
		for (var i in _initFn) while (t = _initFn[i].shift()) Yubox[i].apply(null, t);
		/*ȡ���¼���*/
		addEvent('unload', function() {
			while (Yubox.eventList.length) detachEvent.apply(null, Yubox.eventList.shift());
		}, window);
	})();
})(); //����Ϣ�����ͬ����
(function(){
var fbind = function(el,type,handle){
	el.addEventListener
		? el.addEventListener(type, handle, false)
		: ( el.attachEvent ? el.attachEvent('on' + type, handle) : '' );
};
var toFloat = function(obj){
	obj = parseFloat(obj);
	return isNaN(obj) ? 0 : obj;
};
var adapt = function(iframe){
	var win,doc,dom,H,W,scrollMaxY = 0, scrollMaxX = 0;
	try {
		win = iframe.contentWindow;
		doc = win ? win.document : iframe.contentDocument;
		dom = doc.documentElement;
		H = Math.max(toFloat(doc.offsetHeight), toFloat(doc.scrollHeight));
		W = Math.max(toFloat(doc.offsetWidth), toFloat(doc.scrollWidth));
		if (dom) {
			H = Math.max(H, toFloat(dom.clientHeight), toFloat(dom.scrollHeight));
			W = Math.max(W, toFloat(dom.clientWidth), toFloat(dom.scrollWidth));
		}
		if (win) {
			H = Math.max(H, toFloat(win.innerHeight), toFloat(win.scrollHeight));
			W = Math.max(W, toFloat(win.innerWidth), toFloat(win.scrollWidth));
			scrollMaxY = toFloat(win.scrollMaxY);
			scrollMaxX = toFloat(win.scrollMaxX);
		}
	}
	catch (e) {
		return;
	}
	if(!(scrollMaxY || scrollMaxX)){
		var i = 1, o = dom || iframe;
		do{
			o.scrollTop += 20;
			if(o.scrollTop < 20*i){
				break;
			}
		}while(i++);
		scrollMaxY = o.scrollTop;
		o.scrollTop  = 0;
		i=1;
		do{
			o.scrollLeft += 20;
			if(o.scrollLeft < 20*i){
				break;
			}
		}while(i++);
		scrollMaxX = o.scrollLeft;
		o.scrollLeft  = 0;
	}
	H += scrollMaxY;
	W += scrollMaxX;
	Yubox.resizeWin(W,H);
};
Yubox.apply(Yubox, {
	alert: function() {
		Yubox.show(arguments, {
			icoCls: 'Yubox_alert',
			btn: ['OK']
		});
	},
	succeedInfo: function() {
		Yubox.show(arguments, {
			icoCls: 'Yubox_succeed',
			btn: ['OK']
		});
	},
	errorInfo: function() {
		Yubox.show(arguments, {
			icoCls: 'Yubox_error',
			btn: ['OK']
		});
	},
	confirmInfo: function() {
		Yubox.show(arguments, {
			icoCls: 'Yubox_confirm',
			btn: ['OK', 'CANCEL']
		});
	},
	win: function() {
		Yubox.show(arguments);
	},
	pop:function() {
		Yubox.show(arguments);
		var iframe = Yubox.getPage();
		if(iframe){
			fbind(iframe,'load',function(){
				adapt(iframe);
			});
		}
	}
});
})();