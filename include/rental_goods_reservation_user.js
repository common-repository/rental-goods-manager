
/**
 * ユーザ側の商品レンタル予約処理をするHTMLをコントロールするクラス。
 * データをAPIから取得して動的にHTMLを変更する。
 * @param {String} id - ルート親HTMLを指定する。sectionタグのid属性で指定。
 * @param {String} xWpNonce - Wordpressのnonceを設定。
 * @param {Array} [templateIds] - 連想配列でテンプレートのIDを指定する。{tab_main:'id1', tab_process:'id2'}
 */
function RGMP_GoodsResvUserCtrl(id, xWpNonce, templateIds){
	//継承のために
	RGMP_RentalGoodsManagerCommon.call(this, id, xWpNonce);
	//
	this.rootHtmlId = id;
	this.rootHtmlElement = jQuery(this.rootHtmlId);
	this.xWpNonce = xWpNonce;
	this.pagingUnit = 10;//検索結果で表示する件数
	this.data={
		"goodsNames": {}
	};
	//テンプレート設定関数
	this.setTemplate('tab_list', id + '_reservation_user_tab_list_table', templateIds);
	this.setTemplate('tab_process', id + '_reservation_user_tab_process', templateIds);
	
	//ボタンクリック
	this.prepareBtnOnclick();
	//
	this.getGoodsNames();
}

/**
 * HTML内のすべてのボタンのonclickの呼び出し先functionを設定する。
 */
RGMP_GoodsResvUserCtrl.prototype.prepareBtnOnclick = function(){
	let self = this;
let ttt = this.findElementByDataId('reservation_user_tab_list.btn_new');
	//予約一覧
	this.addEventByDataId('reservation_user_tab_list.btn_new',  'click', function(){ self.displayGoodsReservationProcess(); });
	this.addEventByDataId('reservation_user_tab_list.btn_find', 'click', function(){ self.displayGoodsReservationList(false); });
	this.addEventByDataId('reservation_user_tab_list.paging.btn_prev', 'click', function(event){ 
		let page = event.target.getAttribute('data-page');
		self.displayGoodsReservationList(false, page); 
	});
	this.addEventByDataId('reservation_user_tab_list.paging.btn_next', 'click', function(event){ 
		let page = event.target.getAttribute('data-page');
		self.displayGoodsReservationList(false, page); 
	});
	this.addEventByDataId('reservation_user_tab_list.paging.page', 'keypress', function(event){
		if( event.keyCode == 13 ){
			self.displayGoodsReservationList(false, event.target.value);
		}
	});
	
	//予約タブ
	this.addEventByDataId('reservation_user_tab_process.btn_back',   'click', function(event){ self.displayGoodsReservationList(true); });
	this.addEventByDataId('reservation_user_tab_process.btn_regist', 'click', function(event){ self.registUserOrder(event); });
	this.addEventByDataId('reservation_user_tab_process.btn_add_order_goods', 'click', function(event){ self.onClickAddDesiredGoods(event); });
	this.addEventByDataId('reservation_user_tab_process.btn_del_order_goods', 'click', function(event){ self.onClickDelDesiredGoods(event); });
	
}

/**
 * 商品名一覧を取得し、クラス内に保存する。
 */
RGMP_GoodsResvUserCtrl.prototype.getGoodsNames = function(){
	let self = this;
	//商品名一覧の取得
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/goods_masters/names',
		method    : 'GET', 
		data      : {
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (response) {
		let goodsNames = response['list'];
		self.data.goodsNames = goodsNames;
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}
/**希望の商品の追加ボタンを押したときの挙動。
 */
RGMP_GoodsResvUserCtrl.prototype.onClickAddDesiredGoods = function(event){
	let self = this;
	event.preventDefault();
	
	//注文情報（商品）
	this.addDesiredGoods("", 1);
}
//
RGMP_GoodsResvUserCtrl.prototype.addDesiredGoods = function(goodsName, orderNum){
	let div = this.findElementByDataId('reservation_user_tab_process.order_goods');
	let index = div.find('p').length;
	let goodsNames = this.data.goodsNames;
	
	//商品名select作成
	let objSelect = document.createElement("select");
	objSelect.style.width = '150px';
	for(let name in goodsNames){
		let objOption = document.createElement("option");
		objOption.setAttribute("value", name);
		if(name == goodsName) objOption.setAttribute("selected", "selected");
		objOption.append(document.createTextNode(name));
		objSelect.appendChild(objOption);
	}
	
	//注文情報（商品）
	let objP = document.createElement("p");
	let objLabel = document.createElement("label");
	objLabel.append(document.createTextNode(this.getMsg('OBJ.GOODS_NAME') + ":"));
	objP.appendChild(objLabel);
	//
	objP.appendChild(objSelect);
	//
	objP.append(this.getMsg('OBJ.RESERVATION_ORDER_NUM') + ':');
	objInput = document.createElement("input");
	objP.appendChild(objInput);
	objInput.outerHTML = "<input type='text' name='reservation_user_tab_process.order_nums[" + index + "]' value='" + orderNum + "' style='width:50px;'>"
		+ "<span data-id='reservation_user_tab_process.err.order_nums[" + index + "]' class='reservation-user-err'></span>";
	
	//1行追加
	div.append(objP);
}

/**希望の商品の削除ボタンを押したときの挙動。
 */
RGMP_GoodsResvUserCtrl.prototype.onClickDelDesiredGoods = function(event){
	let self = this;
	event.preventDefault();
	
	let tbody = this.findElementByDataId('reservation_user_tab_process.order_goods');
	let index = tbody.find('p').length;
	
	//最後の1行の場合は何もしない
	if(index <= 1) return;
	//一番下の行を削除する
	tbody.find('p:last-child').remove();
}

RGMP_GoodsResvUserCtrl.prototype.writeReservationResult = function(status, response, dataIdPrefix){
	let self = this;
	dataIdPrefix = (!dataIdPrefix ? "" : dataIdPrefix);
	let objErrs = this.findElements('[data-id^="' + dataIdPrefix + 'err."]');
	//エラーのタグをクリア
	objErrs.empty();
	
	//成功の場合
	if(status == 200){
		//submitボタンを使えなくする
		let tm =this.findElementByDataId('reservation_user_tab_process.btn_regist');
		tm.prop("disabled", true);
		//メッセージ
		this.findElementByDataId(dataIdPrefix + 'message').html(this.getMsg('OK.SUCCESS', false));
		return;
	}
}


RGMP_GoodsResvUserCtrl.prototype.writeReservationList = function(json){
	let self = this;
	//
	let html = this.createHtmlFromTemplate('tab_list', json);
	this.findElementByDataId('reservation_user_tab_list.tbody').html(html);
	//ボタンのクリックイベントの設定
	this.findElementByDataId('reservation_user_tab_list.tbody').find('.rental-goods-manager-btn-cancel').click(function(event){ 
		//注文IDを自オブジェクトのdata-orderId属性から取得
		let order_id = event.target.getAttribute('data-orderId');
		self.displayGoodsReservationCancel(order_id); 
	});
	this.prepareBtnOnclick();
	return;
	
	//データ部の幅をタイトル部と一致させるために、タイトル部の幅を取得
	let thWidths = this.findElementByDataId('reservation_user_tab_list.table').find('thead th')
		.map(function(i, el){
			return jQuery(this).outerWidth();
		}).get();
	let tbody = this.findElementByDataId('reservation_user_tab_list.tbody');
	//タグの中をクリア
	tbody.empty();
	//
	let offset = json['offset'];
	let limit = json['limit'];
	let list = json['list'];
	for(let data of list){
		let order_goods = data['order_goods'];
		let order_id = data['order_id'];
		let status = data['status'];
		let cancelButtonDisabled = "disabled";
		if(status == 're') cancelButtonDisabled = "";
		let tr = "<tr><td style='width:" + thWidths[0] + "px'>" + order_id + "</td><td style='width:" + thWidths[1] + "px'>" + data['user_name'] 
			+ "</td><td style='width:" + thWidths[2] + "px'>" + data['rental_from'] + "</td><td style='width:" + thWidths[3] + "px'>" + data['rental_to'] 
			+ "</td><td style='width:" + thWidths[4] + "px'>" + data['application_date'] + "</td><td style='width:" + thWidths[5] + "px'>" + this.DEF_STATUS[status]
			+ "</td><td style='width:" + thWidths[6] + "px'>"
			+ "<button type='button' class='btn-square-so-pop' data-orderId='" + order_id + "' " + cancelButtonDisabled + ">" + this.getMsg('BTN.CANCEL') + "</button>"
			+ "</td></tr>";
		tbody.append(tr);
		
		//ボタンのクリックイベント
		tr = jQuery('tr:last-child');
		//処理ボタン
		tr.find('button:nth-of-type(1)').click(function(event){ 
			//注文IDを自オブジェクトのdata-orderId属性から取得
			let order_id = event.target.getAttribute('data-orderId');
			self.displayGoodsReservationCancel(order_id); 
		});
	}
}


RGMP_GoodsResvUserCtrl.prototype.writeReservationProcess = function(){
	let self = this;
	
	//メッセージをクリア
	this.findElementByDataId('reservation_user_tab_process.message').empty();
	
	//エラーのタグをクリア
	let objErrs = this.findElements('[data-id^="reservation_user_tab_process.err."]');
	objErrs.empty();
	
	//ボタンを使えるようにする
	this.findElementByDataId('reservation_user_tab_process.btn_regist').prop("disabled", false);
	
	//値を設定
	this.findElements('input[data-id^="reservation_user_tab_process."]').val('');
	this.findElements('textarea[data-id^="reservation_user_tab_process."]').val('');
	this.findElementByDataId('reservation_user_tab_process.is_canel').val('false');
	this.findElementByDataId('reservation_user_tab_process.order_id').val('');
	this.findElementByDataId('reservation_user_tab_process.btn_regist').val(this.getMsg('BTN.ORDER_REGISTER'));
}

RGMP_GoodsResvUserCtrl.prototype.writeReservationCancel = function(json){
	let self = this;
	let order = json;
	
	//メッセージをクリア
	this.findElementByDataId('reservation_user_tab_process.message').empty();
	//エラーのタグをクリア
	let objErrs = this.findElements('[data-id^="reservation_user_tab_process.err."]');
	objErrs.empty();
	
	//ボタンを使えるようにする
	this.findElementByDataId('reservation_user_tab_process.btn_regist').prop("disabled", false);
	//値を設定する
	this.findElementByDataId('reservation_user_tab_process.is_canel').val('true');
	this.findElementByDataId('reservation_user_tab_process.order_id').val(order['order_id']);
	this.findElementByDataId('reservation_user_tab_process.rental_from').val(order['rental_from']);
	this.findElementByDataId('reservation_user_tab_process.rental_to').val(order['rental_to']);
	this.findElementByDataId('reservation_user_tab_process.department').val(order['user_department']);
	this.findElementByDataId('reservation_user_tab_process.remarks').val(order['remarks']);
	this.findElementByDataId('reservation_user_tab_process.user_zip').val(order['user_zip']);
	this.findElementByDataId('reservation_user_tab_process.user_address').val(order['user_address']);
	this.findElementByDataId('reservation_user_tab_process.btn_regist').val(this.getMsg('BTN.CANCEL'));
	
	//予約商品内訳
	let order_goods = order['order_goods'];
	for(let goods of order_goods){
		this.addDesiredGoods(goods['goods_name'], goods['order_num']);
	}
	
}

/**
 * HTML内のすべてのボタンのonclickの呼び出し先functionを設定する。
 */
RGMP_GoodsResvUserCtrl.prototype.registUserOrder = function(event){
	let self = this;
	event.preventDefault();
	//貸出先情報の取得
	let isCancel = this.findElementByDataId('reservation_user_tab_process.is_canel').val();
	let order_id = this.findElementByDataId('reservation_user_tab_process.order_id').val();
	let rental_from = this.findElementByDataId('reservation_user_tab_process.rental_from').val();
	let rental_to   = this.findElementByDataId('reservation_user_tab_process.rental_to').val();
	let department  = this.findElementByDataId('reservation_user_tab_process.user_department').val();
	let zip         = this.findElementByDataId('reservation_user_tab_process.user_zip').val();
	let address     = this.findElementByDataId('reservation_user_tab_process.user_address').val();
	let remarks     = this.findElementByDataId('reservation_user_tab_process.remarks').val();
	//注文内訳の取得
	let goods_names = this.findElementByDataId('reservation_user_tab_process.order_goods').find('select')
		.map(function(i, el){
			return jQuery(this).val();
		}).get();
	let order_nums  = this.findElementByDataId('reservation_user_tab_process.order_goods').find('input')
		.map(function(i, el){
			return jQuery(this).val();
		}).get();
	//予約キャンセルの場合
	let status = 're';
	let urlSuffix = '';
	if(isCancel == 'true'){
		status = 'ca';
		urlSuffix = '/' + order_id;
	}
	//
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders' + urlSuffix,
		method    : 'POST', 
		data      : {
						'status': status,
						'rental_from': rental_from,
						'rental_to': rental_to,
						'user_department': department,
						'user_zip': zip,
						'user_address': address,
						'remarks': remarks,
						'goods_names[]': goods_names,
						'order_nums[]': order_nums
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (response) {
		//成功を表示
		self.writeReservationResult(200, response, "reservation_user_tab_process.");
		
	}).fail( (data) => {
		self.transactJsonErrByHTML(data.status, data, 'reservation_user_tab_process.');
	});
}

RGMP_GoodsResvUserCtrl.prototype.displayGoodsReservationList = function(isPrev, page){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	if(typeof(page) === 'undefined') page = this.findElementByDataId('reservation_user_tab_list.paging.page').val();
	page = parseInt(page);
	if(Number.isNaN(page) || page < 1) page = 1;
	
	//ページング情報取得
	let offset = (page-1) * this.pagingUnit;
	let limit = this.pagingUnit;
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders',
		method    : 'GET', 
		data      : {
						'order_by_asc': 0,
						'offset': offset,
						'limit': limit,
						'order_by_asc': 1
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (response) {
		self.writeReservationList(response);
		self.writePaging('reservation_user_tab_list.paging', response);
		//
		if(isPrev){
			//タブの移動
			self.changeSliderTab(true, 'reservation_user_tab_list');
		}
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}

RGMP_GoodsResvUserCtrl.prototype.displayGoodsReservationProcess = function(){
	let self = this;
	
	//予約画面の書き込み
	this.writeReservationProcess();
	
	//タブの移動
	this.changeSliderTab(false, 'reservation_user_tab_process');
}

RGMP_GoodsResvUserCtrl.prototype.displayGoodsReservationCancel = function(order_id){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders/' + order_id,
		method    : 'GET', 
		data      : {
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (response) {
		self.writeReservationCancel(response);
		//self.writePaging('reservation_user_tab_list.paging', response);
		
		//タブの移動
		self.changeSliderTab(false, 'reservation_user_tab_process');
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}

RGMP_RentalGoodsManagerCommon.inherit(RGMP_GoodsResvUserCtrl);


