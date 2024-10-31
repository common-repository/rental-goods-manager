
/* 商品レンタル管理の共通クラス */
function RGMP_RentalGoodsManagerCommon(id, xWpNonce){
	this.xWpNonce = xWpNonce;
	this.rootHtmlId = id;
	this.rootHtmlElement = jQuery(this.rootHtmlId);
	
	//テンプレート（コンパイル結果を保存しておく場所）
	this.templates = {};
	//処理中の場合にIDを設定する（setTimeout()のID）
	this.processingFlagId = null;
	
	//ステータスの表示文字列の定義
	eval('this.DEF_STATUS = ' + this.getMsg('RESERVATION_STATUS.JS_ARRAY', false) + ';');
	//API情報の設定
	this.API_INFO = RGMP_RentalGoodsManagerCommon.API_INFO;
}

RGMP_RentalGoodsManagerCommon.inherit = function(sub) {//派生クラス, superクラス
	let sup = RGMP_RentalGoodsManagerCommon;
	sub.super_ = sup;
	Object.setPrototypeOf(sub.prototype, sup.prototype);
	return;
};


/** 
 * メッセージテキストの取得。
 * @param {String} name - メッセージ名。
 * @param {Bool} isEscape - HTMLエスケープするかどうか。
 */
RGMP_RentalGoodsManagerCommon.prototype.getMsg = function(name, isEscape){
	if(typeof isEscape === 'undefined') isEscape = true;
	let msg = RGMP_RentalGoodsManagerCommon.MESSAGES;
	if(typeof msg === 'undefined') throw 'MESSAGES is not defined. Maybe rental_goods_manager_common_def.js is not inclueded.';
	let str = msg[name];
	if(str == '') throw "Message ID is not found.";
	if(!isEscape) return str;
	return str.replace(/[&'`"<>]/g, function(match) {
		return {
		'&': '&amp;',
		"'": '&#x27;',
		'`': '&#x60;',
		'"': '&quot;',
		'<': '&lt;',
		'>': '&gt;',
		}[match]
	});
}

/**HTMLのテンプレート(handlebars)を設定する。idからテンプレートを取得しコンパイルしてthis.templatesに設定する。
 * @param {String} templateName  - テンプレート名。
 * @param {String} defaultTemplateId - デフォルトのテンプレートのID属性を指定。
 * @param {String} templateIds   - 指定するテンプレートのID属性を指定。
 */
RGMP_RentalGoodsManagerCommon.prototype.setTemplate = function(templateName, defaultTemplateId, templateIds){
	templateIds = !templateIds ? {} : templateIds;
	let tagId = !templateIds[templateName] ? defaultTemplateId : templateIds[templateName];
	let objTag = document.querySelector(tagId);
	if(objTag){
		this.templates[templateName] = Handlebars.compile(objTag.innerHTML);
	}
}

/**HTMLのテンプレート(handlebars)を適用してHTMLを作成する。
 * @param {String} templateName  - テンプレート名。
 * @param {Array} json - テンプレートに送るデータ。
 * @return {String} - テンプレートを適用した結果。HTML文字列。
 */
RGMP_RentalGoodsManagerCommon.prototype.createHtmlFromTemplate = function(templateName, json){
	if(this.templates[templateName]){
		json['DEF_STATUS'] = this.DEF_STATUS;
		return this.templates[templateName](json);
	}
	return '';
}

/**HTML内の要素の検索。
 * data-idを指定して要素を取得する。
 * @param {String} dataId        - data-idの名前
 * @param {String} [childDataId] - 省略可。子data-id。dataId配下のdata-idを検索し返す。
 * @return {Element} 結果要素(jQuery)
 */
RGMP_RentalGoodsManagerCommon.prototype.findElementByDataId = function(dataId, childDataId){
	let objTag =  this.rootHtmlElement.find('[data-id="' + dataId + '"]');
	if(!objTag || typeof childDataId === 'undefined') return objTag;
	return objTag.find('[data-id="' + childDataId + '"]');
}

/**HTML内の要素の検索。
 * @param {String} selector - cssセレクタ
 * @return {Element} 結果要素(jQuery)
 */
RGMP_RentalGoodsManagerCommon.prototype.findElements = function(selector){
	return this.rootHtmlElement.find(selector);
}

/**HTML内の要素にイベントを付加する。既存イベントを削除してから付加。data-idで要素を指定する。要素が見つからない場合は何もしない。
 * @param {String} dataId      - data-idの名前
 * @param {String} eventName   - イベント名（例：click, keypress）。
 * @param {Function} func      - イベント発生時の処理。
 */
RGMP_RentalGoodsManagerCommon.prototype.addEventByDataId = function(dataId, eventName, func){
	let objTag =  this.findElementByDataId(dataId);
	if(objTag.length == 0) return;
	objTag.off(eventName);
	objTag.on(eventName, func);
}

/**
 * ページング処理。検索結果データを渡すとHTMLに情報を埋め込む。
 *    この関数を使ってページングのHTMLを処理する場合は以下のタグ属性(data-*)の構成にする。
 *    以下の「section_name1.paging」はデータIDのプレフィックスで、各自で自由にカスタマイズ可能。
 *    プレフィックスでページング処理する対象を決めている。後半の文字は固定文字列なので注意。
 *    &lt;a data-id="section_name1.paging.btn_prev" data-page="1"> prev &lt;/a>
 *    &lt;input type="text" data-id="section_name1.paging.page" name="paging.page" value="1" style="width:50px;">
 *     / &lt;span data-id="section_name1.paging.max_page">&lt;/span>
 *    &lt;a data-id="section_name1.paging.btn_next" data-page="1"> next &lt;/a>
 *
 * @param {string} pagingDataId - ページング対象のタグをdata-idで指定。（例:"section_name1.paging"）
 * @param {array} searchResultJson - 検索結果形式のJson。検索結果形式({amount:5, offset:0, limit:10, list:[...]})である必要あり。
 * @note HTMLは次の構成である必要がある。
 */
RGMP_RentalGoodsManagerCommon.prototype.writePaging = function(pagingDataId, searchResultJson){
	let amount = searchResultJson['amount'];
	let offset = searchResultJson['offset'];
	let limit = searchResultJson['limit'];
	//ページングを計算
	let page = Math.floor(offset / limit) + 1;
	let maxPage = Math.floor((amount-1) / limit) + 1;
	if(amount == 0){
		//検索結果なし（0件）
		page = 1;
	}else if(offset == amount){
		//検索結果はあるが、ページが件数を超えている
		page = maxPage + 1;
	}
	let prevPage = page < 2 ? 1 : page-1;
	
	//HTMLにページング情報を埋め込み
	this.findElementByDataId(pagingDataId + ".max_page").html(maxPage);
	this.findElementByDataId(pagingDataId + ".page").val(page);
	this.findElementByDataId(pagingDataId + ".btn_prev").attr("data-page", prevPage);
	this.findElementByDataId(pagingDataId + ".btn_next").attr("data-page", page+1);
}

/**
 * 処理中フラグを解除する。
 */
RGMP_RentalGoodsManagerCommon.prototype.cancelProcessingFlag = function(){
	 clearTimeout(this.processingFlagId);
	 this.processingFlagId = null;
}

/**
 * 処理中フラグを立てる。
 * @param {int} msec 処理中フラグを立てておく時間を指定
 */
RGMP_RentalGoodsManagerCommon.prototype.setProcessingFlag = function(msec){
	let self = this;
	this.processingFlagId = setTimeout(function(){self.cancelProcessingFlag();}, msec);
	return this.processingFlagId;
}

/**
 * 処理中かどうか。
 * @rerurn {bool} 処理中フラグが立っている場合true
 */
RGMP_RentalGoodsManagerCommon.prototype.isSetProcessingFlag = function(){
	return this.processingFlagId != null;
}

/**
 * タブを移動するアニメーションをする。
 * @param {bool} isPrev 戻る動きをする
 * @param {String} displayTab 対象のタブをcssセレクタで指定
 */
RGMP_RentalGoodsManagerCommon.prototype.changeSliderTab = function(isPrev, displayTabDataId){
	//現在のタブ
	let activeTab = this.findElements('.slider-tab--active');
	//クラスのクリア
	let reservationTab = this.findElements('.slider-tab');
	reservationTab.removeClass('slider-tab--active');
	reservationTab.removeClass('slider-tab--right-side');
	reservationTab.removeClass('slider-tab--slide-in-right');
	reservationTab.removeClass('slider-tab--slide-in-left');
	
	if(isPrev){
		//現在のタブのアニメーション設定
		activeTab.addClass('slider-tab--right-side');
		activeTab.addClass('slider-tab--slide-in-right');
		//次に移動するタブにアニメーション設定
		this.findElementByDataId(displayTabDataId).addClass('slider-tab--active');
		this.findElementByDataId(displayTabDataId).addClass('slider-tab--slide-in-right');
	}else{
		//現在のタブにアニメーション設定
		activeTab.addClass('slider-tab--slide-in-left');
		//次に移動するタブにアニメーション設定
		this.findElementByDataId(displayTabDataId).addClass('slider-tab--active');
		this.findElementByDataId(displayTabDataId).addClass('slider-tab--slide-in-left');
	}
}

/**
 * エラー処理をする。
 * @param {array} response エラーレスポンスJson
 */
RGMP_RentalGoodsManagerCommon.prototype.transactJsonErrByAlert = function(response){
	if(response.status >= 499){
		console.log(response);
		alert(this.getMsg('ERR.UNEXPECTED') + ': status=' + response.status);
		return;
	}
	
	let json = response.responseJSON;
	if(response.status == 400){
		let fields = json['errors']['fields'];
		let errorsStr = '';
		let i = 0;
		for(let field in fields){
			errorsStr += fields[field].join(',') + '\n';
			++i;
			if(i >= 5){
				errorsStr += '...and more';
				break;
			}
		}
		alert(this.getMsg('ERR.ERR_OCCURED') + '[' + json.code + ']\n ' + json.message
			+ '\n' + errorsStr);
		return;
	}
	
	let msg = '';
	if(response.status == 401){
		msg = this.getMsg('ERR.UNAUTHORIZED');
	}else if(response.status == 403){
		msg = this.getMsg('ERR.ACCESS_ERROR');
	}else{
		msg = this.getMsg('ERR.ERR_OCCURED');
	}
	//アラートを出す
	alert(msg + '[' + json.code + ']\n ' + json.message);
}

/**
 * エラー処理をする。フィールドエラーの場合はHTMLに書き込む。その他のエラーはalertで警告。
 *    HTMLに、「プレフィックス+'err.'+フィールド名」のdata-idを用意しておくこと。そこにエラーメッセージが書かれる。
 * @param {int} status HTTPステータス
 * @param {array} response エラーレスポンスJson
 * @param {string} dataIdPrefix エラーを書き込むHTMLのdata-idのプレフィックス。設定しない場合""として処理。
 */
RGMP_RentalGoodsManagerCommon.prototype.transactJsonErrByHTML = function(status, response, dataIdPrefix){
	let self = this;
	dataIdPrefix = (!dataIdPrefix ? "" : dataIdPrefix);
	let objErrs = this.findElements('[data-id^="' + dataIdPrefix + 'err."]');
	//エラーのタグをクリア
	objErrs.empty();
	
	//成功の場合
	if(status == 200){
		this.findElementByDataId(dataIdPrefix + 'message').html(this.getMsg('OK.SUCCESS', false));
		return;
	}
	
	//フィールドエラー以外の場合
	if(response.status != 400){
		this.transactJsonErrByAlert(response);
		return;
	}
	
	//エラー
	this.findElementByDataId(dataIdPrefix + 'message').html(this.getMsg('ERR.ERR_OCCURED', false) + response.responseJSON.message);
	
	//フィールドエラー表示
	let errors = response.responseJSON.errors.fields;
	for(field in errors){
		let obj = this.findElementByDataId(dataIdPrefix + 'err.' + field);
		if(obj) obj.html(errors[field].join(' / '));
	}
	
}




