
/**
 * オペレータ側の商品レンタル予約処理をするHTMLをコントロールするクラス。
 * データをAPIから取得して動的にHTMLを変更する。
 * @param {String} id - ルート親HTMLを指定する。sectionタグのid属性で指定。
 * @param {String} xWpNonce - Wordpressのnonceを設定。
 * @param {Object} [templateIds] - 連想配列でテンプレートのIDを指定する。{tab_main:'テンプレートscriptタグ(CSSセレクタで指定)'}
 */
function RGMP_RentalGoodsReservationEditCtrl(id, xWpNonce, templateIds){
	//継承のために
	RGMP_RentalGoodsManagerCommon.call(this, id, xWpNonce);
	//
	this.xWpNonce = xWpNonce;
	this.pagingUnit = 10;//検索結果で表示する件数
	this.data={
		"reservationProcesssTab": { 
			//商品名一覧を保存
			"goodsNames"  : {},
			//選択された注文
			"order" : {},
			//商品マスタの検索結果（現在のページングの情報を保存）
			"goodsMasters": {}
		}
	};
	//テンプレート設定
	this.setTemplate('tab_main', id + '_reservation_tab_main_tbody', templateIds);
	this.setTemplate('tab_process_goods_num_list', id + '_reservation_tab_process_goods_num_list', templateIds);
	this.setTemplate('tab_process_goods_num_popup', id + '_reservation_tab_process_goods_num_popup', templateIds);
	this.setTemplate('tab_process_schedule', id + '_reservation_tab_process_schedule', templateIds);
	this.setTemplate('tab_print_area', id + '_reservation_tab_print_area', templateIds);
	this.setTemplate('tab_history_schedule', id + '_reservation_tab_history_schedule', templateIds);
	this.setTemplate('tab_history_order', id + '_reservation_tab_history_order', templateIds);
	
	this.prepareBtnOnclick();
}

/**
 * HTML内のすべてのボタンのonclickの呼び出し先functionを設定する。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.prepareBtnOnclick = function(){
	let self = this;
	//メインタブ内のボタン
	this.addEventByDataId('reservation_tab_main.btn_history', 'click', function(){ self.displayGoodsReservationHistory(true); });
	this.addEventByDataId('reservation_tab_main.btn_find', 'click', function(){ self.displayGoodsReservationMain(false); });
	this.addEventByDataId('reservation_tab_main.paging.btn_prev', 'click', function(event){ 
		let page = event.target.getAttribute('data-page');
		self.displayGoodsReservationMain(false, page); 
	});
	this.addEventByDataId('reservation_tab_main.paging.btn_next', 'click', function(event){ 
		let page = event.target.getAttribute('data-page');
		self.displayGoodsReservationMain(false, page); 
	});
	this.addEventByDataId('reservation_tab_main.paging.page', 'keypress', function(event){
		if( event.keyCode == 13 ){
			self.displayGoodsReservationMain(false, event.target.value);
		}
	});
	this.addEventByDataId('reservation_tab_main.user_mail', 'keypress', function(event){
		if( event.keyCode == 13 ){
			self.displayGoodsReservationMain(false);
		}
	});
	this.addEventByDataId('reservation_tab_main.order_id', 'keypress', function(event){
		if( event.keyCode == 13 ){
			self.displayGoodsReservationMain(false);
		}
	});
	
	//予約処理タブ内のボタン
	this.addEventByDataId('reservation_tab_process.btn_back',   'click', function(){ self.displayGoodsReservationMain(true); });
	this.addEventByDataId('reservation_tab_process.btn_regist', 'click', function(){ self.registOrder(); });
	this.addEventByDataId('reservation_tab_process.btn_add',    'click', function(e){ self.onClickAddDesiredGoods(e); });
	this.addEventByDataId('reservation_tab_process.btn_del',    'click', function(e){ self.onClickDelDesiredGoods(e); });
	this.addEventByDataId('reservation_tab_process.btn_goods_num_list', 'click', function(event){
		self.writeReservationProcess_goodsNumList();
	});
	this.addEventByDataId("reservation_tab_process.bottom.btn_find", 'click', function(event){ 
		self.displayGoodsReservationProcess_schedule();
	});
	this.addEventByDataId('reservation_tab_process.paging.btn_next', 'click', function(event){ 
		let page = event.target.getAttribute('data-page'); 
		self.displayGoodsReservationProcess_schedule(page);
	});
	this.addEventByDataId('reservation_tab_process.paging.btn_prev', 'click', function(event){ 
		let page = event.target.getAttribute('data-page'); 
		self.displayGoodsReservationProcess_schedule(page);
	});
	this.addEventByDataId('reservation_tab_process.bottom.goods_name', 'keypress', function(event){ 
		if( event.keyCode == 13 ){
			self.displayGoodsReservationProcess_schedule(1);
		}
	});
	this.addEventByDataId('reservation_tab_process.bottom.goods_serialno', 'keypress', function(event){ 
		if( event.keyCode == 13 ){
			self.displayGoodsReservationProcess_schedule(1);
		}
	});
	this.addEventByDataId('reservation_tab_process.bottom.goods_no', 'keypress', function(event){ 
		if( event.keyCode == 13 ){
			self.displayGoodsReservationProcess_schedule(1);
		}
	});
	
	//印刷タブ内のボタン
	this.addEventByDataId('reservation_tab_print.btn_back', 'click', function(){ self.displayGoodsReservationMain(true); });
	
	this.addEventByDataId('reservation_tab_print.btn_print', 'click', function(event){ 
		//jQueryからHTMLElementを取得する
		let printArea = self.findElementByDataId("reservation_tab_print.printarea")[0];
		printIframeArea(printArea);
	});
	
	//履歴タブ内のボタン
	this.addEventByDataId('reservation_tab_history.btn_back', 'click', function(){ self.displayGoodsReservationMain(true); });
	this.findElementByDataId("reservation_tab_history.btn_find").click(function(event){ 
		self.displayGoodsReservationHistory(false);
	});
	this.addEventByDataId('reservation_tab_history.paging.btn_prev', 'click', function(event){ 
		let page = event.target.getAttribute('data-page'); 
		self.displayGoodsReservationHistory(false, page);
	});
	this.addEventByDataId('reservation_tab_history.paging.btn_next', 'click', function(event){ 
		let page = event.target.getAttribute('data-page'); 
		self.displayGoodsReservationHistory(false, page);
	});
	this.addEventByDataId('reservation_tab_history.paging.page', 'keypress', function(event){
		if( event.keyCode == 13 ){
			self.displayGoodsReservationHistory(false, event.target.value);
		}
	});
	this.addEventByDataId('reservation_tab_history.btn_prev_month', 'click', function(event){ 
		let targetYearMonth = event.target.getAttribute('data-target_year_month'); 
		self.displayGoodsReservationHistory(false, undefined, targetYearMonth);
	});
	this.addEventByDataId('reservation_tab_history.btn_next_month', 'click', function(event){ 
		let targetYearMonth = event.target.getAttribute('data-target_year_month'); 
		self.displayGoodsReservationHistory(false, undefined, targetYearMonth);
	});
	this.addEventByDataId('reservation_tab_history.goods_name', 'keypress', function(event){ 
		if( event.keyCode == 13 ){
		self.displayGoodsReservationHistory(false, 1);
		}
	});
	this.addEventByDataId('reservation_tab_history.goods_serialno', 'keypress', function(event){ 
		if( event.keyCode == 13 ){
		self.displayGoodsReservationHistory(false, 1);
		}
	});
	this.addEventByDataId('reservation_tab_history.goods_no', 'keypress', function(event){ 
		if( event.keyCode == 13 ){
		self.displayGoodsReservationHistory(false, 1);
		}
	});
	this.addEventByDataId('reservation_tab_history.target_year_month', 'change', function(event){ 
		let targetYearMonth = event.target.value; 
		self.displayGoodsReservationHistory(false, 1, targetYearMonth);
	});
}

/**
 * 予約処理タブから予約商品ID（rental_goods_ids）を取得する。
 * @return {array} 予約商品ID([string])
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.getRentalGoodsIdsFromProcessTab = function(){
	ary_goods_ids = this.findElementByDataId('reservation_tab_process.rental_goods_info')
		.map(function(i, el){
			let objSpan = jQuery(el);
			return objSpan.data('goods_id');
		}).get();
	return ary_goods_ids;
}

/**
 * 予約処理タブから予約商品の情報を取得するする。
 * @return {Array} 予約商品情報（[{goods_id,goods_name}]）
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.getRentalGoodsListFromProcessTab = function(){
	let ary_goods_ids = this.findElementByDataId('reservation_tab_process.rental_goods_info')
		.map(function(i, el){
			let objSpan = jQuery(el);
			return {'goods_id': objSpan.data('goods_id'), 'goods_name': objSpan.data('goods_name')};
		}).get();
	if(!Array.isArray(ary_goods_ids)) ary_goods_ids = [ary_goods_ids];
	return ary_goods_ids;
}

/**
 * 予約処理タブから希望する商品のすべての情報を取得する。
 * @return {Array} 希望の商品情報を配列で取得（{selected_goods_ids[(string)],selected_goods_names[(string)],goods_names[(string)],goods_nums[(string)]}）
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.getDesiredAndSelectedGoodsArraysFromProcessTab = function(){
	let aryGoodsNames = this.findElementByDataId('reservation_tab_process.middle_tbody')
		.find('tr select').map(function(i, el){
			return jQuery(this).val();
		}).get();
	let aryOrderNums = this.findElementByDataId('reservation_tab_process.middle_tbody')
		.find('tr input').map(function(i, el){
			return jQuery(this).val();
		}).get();
	let arySelectedGoodsIds = this.findElementByDataId('reservation_tab_process.middle_tbody_rental', 'reservation_tab_process.rental_goods_info')
		.map(function(i, el){
			return el.getAttribute('data-goods_id');
		}).get();
	let arySelectedGoodsNames = this.findElementByDataId('reservation_tab_process.middle_tbody_rental', 'reservation_tab_process.rental_goods_info')
		.map(function(i, el){
			return el.getAttribute('data-goods_name');
		}).get();
	//
	return {selected_goods_ids: arySelectedGoodsIds, selected_goods_names: arySelectedGoodsNames, 
		goods_names: aryGoodsNames, goods_nums: aryOrderNums};
}

/**
 * 予約処理タブから希望する商品と選択した予約商品をまとめたリスト情報を取得する。
 * @return {Object} 希望の商品情報をListで取得（連想配列:{goods_name:{desired_goods_num(int),selected_goods_num(int)}}）
 * @see this.getDesiredAndSelectedGoodsArraysFromProcessTab()
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.getDesiredAndSelectedGoodsNumListFromProcessTab = function(){
	let aryDesiredAndSelectedGoods = this.getDesiredAndSelectedGoodsArraysFromProcessTab();
	let aryDesiredGoodsNames = aryDesiredAndSelectedGoods['goods_names'];
	let aryDesiredorderNums = aryDesiredAndSelectedGoods['goods_nums'];
	let arySelectedGoodsIds = aryDesiredAndSelectedGoods['selected_goods_ids'];
	let arySelectedGoodsNames = aryDesiredAndSelectedGoods['selected_goods_names'];

	//選択された商品の商品名毎の数を計算
	let selectedNums = {};
	for(let i = 0; i < arySelectedGoodsNames.length; ++i){
		let cnt = selectedNums[arySelectedGoodsNames[i]];
		if(!Number.isInteger(cnt)) cnt = 0;
		selectedNums[arySelectedGoodsNames[i]] = cnt + 1;
	}
	
	//希望商品と選択商品の数をマージする
	let lists = [];
	for(let i = 0; i<aryDesiredGoodsNames.length; ++i){
		let selectedGoodsNum = selectedNums[aryDesiredGoodsNames[i]];
		if(!Number.isInteger(selectedGoodsNum)) selectedGoodsNum = 0;
		lists[aryDesiredGoodsNames[i]] = {desired_goods_num: parseInt(aryDesiredorderNums[i]), selected_goods_num: selectedGoodsNum};
	}
	//希望商品に存在しない選択商品をカウントマージする
	for(let selectedGoodsName in selectedNums){
		let selectedGoodsNum = selectedNums[selectedGoodsName];
		//希望商品の中に見つからない時だけ追加する
		if(aryDesiredGoodsNames.indexOf(selectedGoodsName) == -1){
			lists[selectedGoodsName] = {desired_goods_num: 0, selected_goods_num: selectedGoodsNum};
		}
	}
	return lists;
}

/**
 * 予約処理タブからレンタル期間を取得する。
 * @return {array} レンタル期間（[rantal_from, rental_to]）
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.getRentalTermFromProcessTab = function(){
	let objRentalTerm = this.findElementByDataId('reservation_tab_process.rental_term');
	let rentalFrom = objRentalTerm.attr('data-rental_from');
	let rentalTo = objRentalTerm.attr('data-rental_to');
	return [rentalFrom, rentalTo];
}


/**
 * 履歴タブの表示対象年月の値（yyyy-mm）を取得する。
 * @return {string} yyyy-mm
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.getTargetYearMonthFromHistoryTab = function(){
	let now = new Date();
	let targetYearMonth = this.findElementByDataId('reservation_tab_history.target_year_month').val();
	if(targetYearMonth == '') targetYearMonth = now.getFullYear() + '-' + ("00" + (now.getMonth()+1)).slice(-2);
	return targetYearMonth;
}


/**
 * モーダルダイアログを表示。
 * @return {string} yyyy-mm
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.writeModalDialog = function(templateName, title, json){
	//HTML作成
	let html = this.createHtmlFromTemplate(templateName, json);
	this.findElementByDataId('reservation_tab_modal.title').html(title);
	this.findElementByDataId('reservation_tab_modal.body').html(html);
}

/**
 * 注文を登録する。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.registOrder = function(){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	let order_id = this.findElementByDataId('reservation_tab_process.order_id').html();
	let status = this.findElementByDataId('reservation_tab_process.status').find('select').val();
	let aryDesiredAndSelectedGoods = this.getDesiredAndSelectedGoodsArraysFromProcessTab();
	let ary_goods_names = aryDesiredAndSelectedGoods['goods_names'];
	let ary_order_nums = aryDesiredAndSelectedGoods['goods_nums'];
	let ary_goods_ids = aryDesiredAndSelectedGoods['selected_goods_ids'];
	
	//ステータスが注文受付の場合は警告
	if(status == 're' && !confirm(this.getMsg('WARN.USER.RESGIST_RE', false))){
		alert(this.getMsg('OK.CANCEL', false));
		return;
	}
	
	//1000ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(1000);
	
	//
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders/' + order_id,
		method    : 'POST', 
		data      : {
						'rental_goods_ids': ary_goods_ids,
						'goods_names': ary_goods_names,
						'order_nums': ary_order_nums,
						'status': status
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (response) {
		alert(self.getMsg('OK.SUCCESS', false));
	}).fail( function(data){
		self.transactJsonErrByAlert(data);
	});
}



/**予約処理タブ：商品IDリンクを押したときの挙動。
 * 選択した貸出し予定の商品に追加する。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.onClickAddReservationGoods = function(event){
	let self = this;
	event.preventDefault();
	let a = event.target;
	this.processTab_addReservationGoods(a);
}
/**予約処理タブ：希望の商品の追加ボタンを押したときの挙動。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.onClickAddDesiredGoods = function(event){
	let self = this;
	event.preventDefault();
	
	//注文情報（商品）
	this.addDesiredGoods("", 0);
}
RGMP_RentalGoodsReservationEditCtrl.prototype.addDesiredGoods = function(goodsName, orderNum){
	let tbody = this.findElementByDataId('reservation_tab_process.middle_tbody');
	let index = tbody.find('tr').length;
	let goodsNames = this.data.reservationProcesssTab.goodsNames;
	
	//商品名select作成
	let objSelect = document.createElement("select");
	objSelect.style.width = '180px';
	for(let name in goodsNames){
		let objOption = document.createElement("option");
		objOption.setAttribute("value", name);
		if(name == goodsName) objOption.setAttribute("selected", "selected");
		objOption.append(document.createTextNode(name));
		objSelect.appendChild(objOption);
	}
	
	//注文情報（商品）
	let objTr = document.createElement("tr");
	let objTd = document.createElement("td");
	objTd.style.width = "200px";
	objTd.appendChild(objSelect);
	objTr.appendChild(objTd);
	//
	objTd = document.createElement("td");
	objTd.style.width = "80px";
	objTd.innerHTML = "<input type='text' name='order_nums[" + index + "]' value='" + orderNum + "' style='width:50px;'>";
	objTr.appendChild(objTd);
	
	//1行追加
	tbody.append(objTr);
}
/**予約処理タブ：希望の商品の削除ボタンを押したときの挙動。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.onClickDelDesiredGoods = function(event){
	let self = this;
	event.preventDefault();
	
	let tbody = this.findElementByDataId('reservation_tab_process.middle_tbody');
	let index = tbody.find('tr').length;
	
	//最後の1行の場合は何もしない
	if(index <= 1) return;
	//一番下の行を削除する
	tbody.find('tr:last-child').remove();
}

/**予約処理タブの選択した商品の合計数をポップアップ表示する。
 * @param {String} goodsName ポップアップ表示する商品名
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.processTab_writeReservationGoodsPopup = function(goodsName){
	//追加した商品のポップアップ--------
	let hashDesiredAndSelectedNums = this.getDesiredAndSelectedGoodsNumListFromProcessTab();
	let hashNums = hashDesiredAndSelectedNums[goodsName];
	if(typeof hashNums === 'undefined') return;
	let desiredGoodsNum = hashNums['desired_goods_num'];
	let selectedGoodsNum = hashNums['selected_goods_num']
	
	//HTML作成
	let json = {goods_name: goodsName, desired_goods_num: desiredGoodsNum, selected_goods_num: selectedGoodsNum};
	let html = this.createHtmlFromTemplate('tab_process_goods_num_popup', json);
	this.findElementByDataId('reservation_tab_process.middle_table_rental.popup').html(html);
}

/**予約処理タブの処理。
 * 選択した貸出し予定の商品に追加する。
 * @param {Element} objA 追加するAタグ要素
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.processTab_addReservationGoods = function(objA){
	let self = this;
	let tbody = this.findElementByDataId('reservation_tab_process.middle_tbody_rental');
	objA = jQuery(objA);
	
	//データの取得 
	let goods_id = objA.data("goods_id");
	let goods_name = objA.data("goods_name");
	let ary_rentalGoodsList= this.getRentalGoodsListFromProcessTab();
	let ary_rental_goods_ids = this.getRentalGoodsIdsFromProcessTab();
	
	//データ作成
 	let goods_masters = [{"goods_id": goods_id, "goods_name": goods_name}];
 	goods_masters = goods_masters.concat(ary_rentalGoodsList);
 	ary_rental_goods_ids.push(goods_id);
	
	//予約に商品を追加
	this.writeReservationProcess_rentalGoods(ary_rental_goods_ids);
	
	//スケジュールの再描画
	this.writeReservationProcess_schedule();//rentalTerm[0], rentalTerm[1]);
	
	//追加した商品のポップアップ--------
	this.processTab_writeReservationGoodsPopup(goods_name);
}

/**予約処理タブの処理。
 * 選択した貸出し予定から指定の商品を削除する。
 * @param {Element} objA 削除するAタグ要素
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.processTab_removeReservationGoods = function(objA){
	let self = this;
	let tbody = this.findElementByDataId('reservation_tab_process.middle_tbody_rental');
	objA = jQuery(objA);
	
	//データの取得
	let goods_id = objA.data("goods_id");
	let goods_name = objA.data("goods_name");
	
	//該当商品の行の削除
	let objTr = objA.parent();
	objTr = objTr.parent();
	objTr.remove();
	
	//スケジュールの再描画
	this.writeReservationProcess_schedule();//rentalTerm[0], rentalTerm[1]);
	
	//追加した商品のポップアップ--------
	this.processTab_writeReservationGoodsPopup(goods_name);
}

//
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationMain = function(json){
	let self = this;
	
	//
	let html = this.createHtmlFromTemplate('tab_main', json);
	this.findElementByDataId('reservation_tab_main.tbody').html(html);
	//ボタンのクリックイベントの設定
	this.prepareBtnOnclick();
	//処理ボタン
	this.findElementByDataId('reservation_tab_main.tbody').find('.rental-goods-manager-btn-edit').click(function(event){ 
		self.displayGoodsReservationProcess(event.target.getAttribute('data-order_id')); 
	});
	//印刷ボタン
	this.findElementByDataId('reservation_tab_main.tbody').find('.rental-goods-manager-btn-print').click(function(event){ 
		self.displayGoodsReservationPrint(event.target.getAttribute('data-order_id')); 
	});
	
}


RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationProcess = function(json){
	let tbody = this.findElementByDataId('reservation_tab_process.middle_tbody');
	//タグの中をクリア
	tbody.empty();
	
	//
	let data = json;
	let order_goods = data['order_goods'];
	
	//注文情報（予約者等）
	this.findElementByDataId('reservation_tab_process.order_id').html(data['order_id']);
	this.findElementByDataId('reservation_tab_process.user').html(data['user_name']);
	this.findElementByDataId('reservation_tab_process.remarks').html(data['remarks']);
	this.findElementByDataId('reservation_tab_process.rental_term').html(data['rental_from'] + this.getMsg('TEXT.TERM_TO') + data['rental_to'] );
	this.findElementByDataId('reservation_tab_process.rental_term').attr("data-rental_from", data['rental_from']);
	this.findElementByDataId('reservation_tab_process.rental_term').attr("data-rental_to", data['rental_to']);
	this.findElementByDataId('reservation_tab_process.send_info').html(this.getMsg('OBJ.USER_ZIP') + data['user_zip'] + "&nbsp;" + data['user_address']);
	//予約ステータス
	let selectStatus = document.createElement("select");
	for(let status in this.DEF_STATUS){
		let option = document.createElement("option");
		option.appendChild(document.createTextNode(this.DEF_STATUS[status]));
		option.setAttribute("value", status);
		if(data['status'] === status) option.setAttribute("selected", "selected");
		selectStatus.appendChild(option);
	}
	this.findElementByDataId('reservation_tab_process.status').html(selectStatus.outerHTML);
	
	//注文情報（商品）
	for(let goods of order_goods){
		this.addDesiredGoods(goods['goods_name'], goods['order_num']);
	}
	
}

/**
 * 予約管理のタブの選択された商品の数の一覧のHTML作成
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationProcess_goodsNumList = function(){
	let self = this;
	let json = {};
	//
	let hashDesiredAndSelectedGoodsNums = this.getDesiredAndSelectedGoodsNumListFromProcessTab();
	let list = [];
	for(let goodsName in hashDesiredAndSelectedGoodsNums){
		let nums = hashDesiredAndSelectedGoodsNums[goodsName];
		list.push({goods_name: goodsName, desired_goods_num: nums['desired_goods_num'], selected_goods_num: nums['selected_goods_num']});
	}
	json['list'] = list;
	
	//モーダル表示
	this.writeModalDialog('tab_process_goods_num_list', this.getMsg('RESERVATION_TAB_PROCESS.TITLE.MODAL.GOODS_NUM_LIST'), json);
}

/**
 * スケジュール表示部分の幅や位置を計算。作成。
 * @param {String} rentalFromStr - 貸出し予約開始日'yyyy-mm-dd'。この月を中心に3か月分を計算する。
 * @param {String} rentalToStr   - 貸出し予約終了日'yyyy-mm-dd'
 * @param {Array} targetStatuses - 表示対象のステータス(例['ou'])。undefinedのとき、'ou'
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.calcScheduleHtmlPos = function(rentalFromStr, rentalToStr, rentalGoodsList, targetStatuses){
	/*ここで作成するjsonの定義-----------
	let json = {
		frame:{
			nameWidth: 180,
			rentalTerm: {startPos: 252, width: 24},
			monthWidths: [
				{cellWidth: 174, strYearMonth:'2020/01'},
				{cellWidth: 186, strYearMonth:'2020/02'},
				{cellWidth: 180, strYearMonth:'2020/03'}
			]
		},
		list: [
			{goods_id: 1, goods_name: '2025E', goods_serialno:'11111111', goods_no: 'ggggg', order_goods:[{calc:{startPos:100, width:20, strStartDate:'01/1', strEndDate:'01/10'}}] },
			{goods_id: 2, goods_name: '2025E', goods_serialno:'22222222', goods_no: 'fffff', order_goods:[{calc:{startPos:200, width:20, strStartDate:'02/1', strEndDate:'02/10'}}] }
		]
	};
	*/
	
	//対象ステータス
	if(typeof(targetStatuses) === 'undefined') targetStatuses = ['ou'];
	
	//注文情報取得
	let order = this.data.reservationProcesssTab.order;
	//予約の商品IDの取得(HTML上の選択した商品IDの取得)
	//let rentalGoodsList = this.getRentalGoodsListFromProcessTab();
	let copyedGoodsMasters = Object.create(this.data.reservationProcesssTab.goodsMasters);

	//jsonデータをベースをコピーして作る
	let json = {order: order};
	
	//日付と位置に関する情報
	let rentalFrom = new Date(rentalFromStr); 
	let rentalTo = new Date(rentalToStr);
	//1つ前の月をベース年月として取得するために。ローカル時間分ずれるのでオフセットを加算する。
	let baseDate = new Date(rentalFrom.getFullYear(), rentalFrom.getMonth()-1, 1, 0,-rentalFrom.getTimezoneOffset());
	let unitDayWidth = 180 / 30; //1日の幅px

	//予約期間の位置の計算
	let startPos = (rentalFrom.getTime() - baseDate.getTime())/1000/60/60/24 * unitDayWidth;
	let width = (rentalTo.getTime() - rentalFrom.getTime())/1000/60/60/24  * unitDayWidth + unitDayWidth;
	//各月のセルの幅の計算
	let nameWidth = 180;
	let allWidth = nameWidth;
	let strYearMonths = []; //それぞれの年月
	let cellWidths = []; //それぞれの月の横幅
	for(let i=0; i<3; ++i ){
		//対象月の最後の日付を取得（ローカル時間で取得するためオフセットを加算）
		let monthLastDate = new Date(baseDate.getFullYear(), baseDate.getMonth()+i+1, 0, 0,-baseDate.getTimezoneOffset());
		cellWidths[i] = monthLastDate.getDate() * unitDayWidth;
		strYearMonths[i] = "" + monthLastDate.getFullYear() + "/" + ("00" + (monthLastDate.getMonth()+1)).slice(-2);
		allWidth += cellWidths[i];
	}
	//最後の月のセルの幅の計算
	cellWidths[3] = 800 - allWidth;
	
	//jsonに値を設定
	json['frame'] = {};
	let frame = json['frame'];
	frame['nameWidth'] = nameWidth;
	frame['rentalTerm'] = {startPos: startPos, width: width};
	frame['monthWidths'] = [];
	for(let i=0; i<cellWidths.length; ++i){
		frame['monthWidths'].push({cellWidth: cellWidths[i], strYearMonth: strYearMonths[i]});
	}
	json['list'] = [];
	
	//
	for(let line of copyedGoodsMasters){
		//予約されている場合は出力しない
		if(rentalGoodsList.some(data => data['goods_id'] == Number(line['goods_id']))) continue;
		
		//jsonにコピー設定
		let goods = line;
		json['list'].push(goods);
		
		//スケジュールバーの表示
		let terms = goods['order_goods'];
		for(let term of terms){
			//ステータスが対象以外の場合は表示しない（選択可能なので）
			if(targetStatuses.indexOf(term['status']) == -1) continue;
			let from = new Date(term['rental_from']);
			let to = new Date(term['rental_to']);
			if(from.getTime() < baseDate.getTime()) continue;
			let strStartDate = ("00" + (from.getMonth()+1)).slice(-2) + '/' + ("00" + from.getDate()).slice(-2);
			let strEndDate = ("00" + (to.getMonth()+1)).slice(-2) + '/' + ("00" + to.getDate()).slice(-2);
			let startPos = (from.getTime() - baseDate.getTime())/1000/60/60/24 * unitDayWidth;
			let width = (to.getTime() - from.getTime())/1000/60/60/24  * unitDayWidth + unitDayWidth;
			//jsonデータに設定
			term['calc'] = {startPos:startPos, width:width, strStartDate:strStartDate, strEndDate:strEndDate, 
				order_id: term['order_id'], rental_from: term['rental_from'], rental_to: term['rental_to'], 
				status: term['status'], strStatus: this.DEF_STATUS[term['status']]};
		}
	}
	return json;
}

/**
 * 予約管理のタブのスケジュール部分のHTML作成
 * 呼び出しの前にthis.data.reservationProcesssTab.orderを設定しておくこと。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationProcess_schedule = function(){
	let self = this;
	
	//
	let order = this.data.reservationProcesssTab.order;
	let rentalGoodsList = this.getRentalGoodsListFromProcessTab();
	let json = this.calcScheduleHtmlPos(order['rental_from'], order['rental_to'], rentalGoodsList);
	let html = this.createHtmlFromTemplate('tab_process_schedule', json);
	this.findElementByDataId('reservation_tab_process.schedule').html(html);
	//ボタンのクリックイベントの設定
	this.prepareBtnOnclick();
	//商品のaタグにクリックイベントを追加
	this.findElementByDataId('reservation_tab_process.schedule').find(".goods-reservation-schedule-data-item__name a").click(function(e){
		self.onClickAddReservationGoods(e);
	});
	
}
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationProcess_rentalGoods = function(rental_goods_ids){
	let self = this;
	let goodsMasters = this.data.reservationProcesssTab.goodsMasters;
	let order = this.data.reservationProcesssTab.order;
	let tbody = this.findElementByDataId('reservation_tab_process.middle_tbody_rental');
	let rentalGoodsIdsFromHtml = this.getRentalGoodsListFromProcessTab();
	//タグの中をクリア
	tbody.empty();
	
	//
	for(let id of rental_goods_ids){
		let goods = order['rental_goods'].find(function(goods){ return goods['goods_id'] == String(id); });
		if(typeof goods === 'undefined'){//注文情報にない場合は
			goods = goodsMasters.find(function(goods){ return goods['goods_id'] == String(id); });
		}
		if(typeof goods === 'undefined'){//マスタ情報にない場合は
			goods = rentalGoodsIdsFromHtml.find(function(goods){ return goods['goods_id'] == String(id); });
		}
		let objTr = document.createElement("tr");
		let objTd = document.createElement("td");
		let objA = document.createElement("a");
		//
		objTd.appendChild(document.createTextNode(id));
		objTd.style.width = "80px";
		objTr.appendChild(objTd);
		objTd = document.createElement("td");
		objTd.style.width = "200px";
		objTd.appendChild(document.createTextNode(goods['goods_name']));
		//削除リンク
		objA.appendChild(document.createTextNode("[" + this.getMsg('BTN.DEL') + "]"));
		objA.setAttribute("data-goods_id", goods['goods_id']);
		objA.setAttribute("data-id", "reservation_tab_process.rental_goods_info");
		objA.setAttribute("data-goods_name", goods['goods_name']);
		objA.addEventListener("click", event => {
				self.processTab_removeReservationGoods(objA);
			});
		objTd.appendChild(objA);
		objTr.appendChild(objTd);
		//trの追加
		tbody.append(objTr);
	}
	
}

/**
 * 印刷画面の出力
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationPrint = function(json){
	let order = json;
	let order_goods = order['order_goods'];
	//データ部の幅をタイトル部と一致させるために、タイトル部の幅を取得
	let thWidths = this.findElementByDataId('reservation_tab_print.table_rental').find('thead th')
		.map(function(i, el){
			return jQuery(this).outerWidth();
		}).get();
	
	//HTML作成
	let tempJson = {order: order};
	let html = this.createHtmlFromTemplate('tab_print_area', tempJson);
	this.findElementByDataId('reservation_tab_print.printarea').html(html);
}

/**
 * 履歴画面の出力
 * @param {string} targetYearMonth yyyy-mm、かundefined
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationHistory = function(targetYearMonth){
	let self = this;
	//対象年月
	if(typeof(targetYearMonth) === 'undefined') targetYearMonth = this.getTargetYearMonthFromHistoryTab();
	let date = targetYearMonth + '-01';
	//計算する
	let json = this.calcScheduleHtmlPos(date, date, [], ['re', 'ou', 'cl']);
	json['target_year_month'] = targetYearMonth;
	let html = this.createHtmlFromTemplate('tab_history_schedule', json);
	this.findElementByDataId('reservation_tab_history.schedule').html(html);
	
	//検索の値を設定
	this.findElementByDataId('reservation_tab_history.target_year_month').val(targetYearMonth);
	let baseDate = new Date(targetYearMonth);
	let prev = new Date(baseDate.getFullYear(), baseDate.getMonth(), 0, 0,-baseDate.getTimezoneOffset());
	let prevStr = prev.getFullYear() + '-' + ("00" + (prev.getMonth()+1)).slice(-2);
	let next = new Date(baseDate.getFullYear(), baseDate.getMonth()+2, 0, 0,-baseDate.getTimezoneOffset());
	let nextStr = next.getFullYear() + '-' + ("00" + (next.getMonth()+1)).slice(-2);
	this.findElementByDataId('reservation_tab_history.btn_prev_month').attr('data-target_year_month', prevStr);
	this.findElementByDataId('reservation_tab_history.btn_next_month').attr('data-target_year_month', nextStr);
	
	//ボタンのクリックイベントの設定
	this.prepareBtnOnclick();
	this.findElementByDataId('reservation_tab_history.schedule').find('.goods-reservation-schedule-data-item__bar')
	.click(function(event){
		let order_id = event.target.getAttribute('data-order_id');
		self.displayGoodsReservationHistory_order(order_id);
	});
	
}

/**
 * 履歴画面の注文情報出力
 * @param {array} json 注文情報
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.writeReservationHistory_order = function(json){
	let self = this;
	
	//ステータス文字追加
	json['strStatus'] = this.DEF_STATUS[json.status];
	
	
	//HTML作成
	/*
	let html = this.createHtmlFromTemplate('tab_history_order', json);
	document.getElementById('reservation_tab_modal').innerHTML = html;
	*/
	this.writeModalDialog('tab_history_order', this.getMsg('RESERVATION_TAB_HISTORY.TITLE.MODAL.ORDER'), json);
	
	return;
}

/**
 * メインタグを表示する。
 * @param {bool} isPrev 戻る動きをする
 * @param {string} page 表示する検索結果のページ番号を指定。undefの場合はページングのpageテキストのページを指定。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.displayGoodsReservationMain = function(isPrev, page){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	if(typeof(page) === 'undefined') page = this.findElementByDataId('reservation_tab_main.paging.page').val();
	page = parseInt(page);
	if(Number.isNaN(page) || page < 1) page = 1;
	
	//ページング情報取得
	let offset = (page-1) * this.pagingUnit;
	let limit = this.pagingUnit;
	
	//ステータスデータを配列として取得
	let statuses = this.findElements('input[data-id="reservation_tab_main.statuses"]:checked').map(function(){
		return jQuery(this).val();
	}).get();
	let userMail =  this.findElementByDataId('reservation_tab_main.user_mail').val();
	let orderId =  this.findElementByDataId('reservation_tab_main.order_id').val();

	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders',
		method    : 'GET', 
		data      : {
						'statuses': statuses,
						'pm_user_mail': userMail,
						'order_id': orderId,
						'offset': offset,
						'limit': limit
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (response) {
		self.writeReservationMain(response);
		self.writePaging('reservation_tab_main.paging', response);
		//
		if(isPrev){
			//タブの移動
			self.changeSliderTab(true, 'reservation_tab_main');
		}
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}


RGMP_RentalGoodsReservationEditCtrl.prototype.displayGoodsReservationProcess = function(order_id){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	let order;
	let order_goods;
	let goods_masters;
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	let allowedProcessingFlagId = this.setProcessingFlag(500);
	
	//商品名一覧取得
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/goods_masters/names',
		method    : 'GET', 
		data      : {
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).then(function(goodsNamesResult) {
		//商品名一覧を保存
		self.data.reservationProcesssTab.goodsNames = goodsNamesResult['list'];
		
		//注文情報取得
		return jQuery.ajax({
			url       : self.API_INFO.uriPrefix + '/orders/' + order_id,
			method    : 'GET', 
			data      : {
						},
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-WP-Nonce': self.xWpNonce
			}
		});
	}).then(function(response) {
		//予約処理の注文情報の表示
		self.writeReservationProcess(response);
		order = response;
		order_goods = order['order_goods'];
		//予約処理タブ情報に保存する
		self.data.reservationProcesssTab.order = order;
		
		//レンタル予定の商品を表示する
		self.writeReservationProcess_rentalGoods(order['rental_goods_ids']);
		
		//商品予定表を表示する
		self.displayGoodsReservationProcess_schedule(1, allowedProcessingFlagId);
	
		//タブの移動
		self.changeSliderTab(false, 'reservation_tab_process');
	}).fail(function(data){
		self.transactJsonErrByAlert(data);
	});
}

/**商品予定表の表示。
 * @param {string} page ページ番号。1～
 * @param {int} p_processingFlagId 指定すると処理フラグが立っていても、処理フラグが指定の処理中IDの場合は処理を続行する。
 *            この関数の呼び出し元で処理中フラグを立てた場合に使用。
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.displayGoodsReservationProcess_schedule = function(page, allowedProcessingFlagId){
	let self = this;
	if(this.isSetProcessingFlag() && allowedProcessingFlagId !== this.processingFlagId) return;
	if(typeof(page) === 'undefined') page = this.findElementByDataId('reservation_tab_process.paging.page').val();
	page = parseInt(page);
	if(Number.isNaN(page) || page < 1) page = 1;
	
	//注文情報
	let order = self.data.reservationProcesssTab.order;
	
	//ページング情報取得
	let offset = (page-1) * this.pagingUnit;
	let limit = this.pagingUnit;
	
	//検索情報
	let goodsName =  this.findElementByDataId('reservation_tab_process.bottom.goods_name').val();
	let goodsSerialno =  this.findElementByDataId('reservation_tab_process.bottom.goods_serialno').val();
	let goodsNo =  this.findElementByDataId('reservation_tab_process.bottom.goods_no').val();
	let rentalFrom = new Date(order['rental_from']); 
	let rentalTo = new Date(order['rental_to']);
	//1つ前の月をベース年月として取得するために。ローカル時間分ずれるのでオフセットを加算する。
	let baseDate = new Date(rentalFrom.getFullYear(), rentalFrom.getMonth()-1, 1, 0,-rentalFrom.getTimezoneOffset());
	let orderConditionStartDate = baseDate.getFullYear() + '-' + ("00" + (baseDate.getMonth()+1)).slice(-2) + '-01';
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//商品マスタ情報取得
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/goods_masters',
		method    : 'GET', 
		data      : {
						'rental_from': order['rental_from'], 
						'rental_to': order['rental_to'],
						'term_include': 0,
						'pm_goods_name': goodsName,
						'pm_goods_serialno': goodsSerialno,
						'pm_goods_no': goodsNo,
						'order_condition_start_date': orderConditionStartDate,
						'offset': offset,
						'limit': limit
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).then(function(res2){
		//商品マスタ
		let list2 = res2['list'];
		goods_masters = list2;
		
		//予約処理タブ情報に保存する
		self.data.reservationProcesssTab.goodsMasters = goods_masters;
		
		//商品予定表を表示する
		//let order = self.data.reservationProcesssTab.order;
		self.writeReservationProcess_schedule();//order['rental_from'], order['rental_to']);
		//ページング
		self.writePaging('reservation_tab_process.paging', res2);
		
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}

/**商品貸出注文のユーザに送る貸出し表の印刷。
 * @param {int} order_id 注文ID
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.displayGoodsReservationPrint = function(order_id){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	let order;
	let order_goods;
	let goods_masters;
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	let allowedProcessingFlagId = this.setProcessingFlag(500);
	
	//商品名一覧取得
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders/' + order_id,
		method    : 'GET', 
		data      : {
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).then(function(response) {
		//HTML表示
		self.writeReservationPrint(response);
		
		//タブの移動
		self.changeSliderTab(false, 'reservation_tab_print');
	}).fail(function(data){
		self.transactJsonErrByAlert(data);
	});
}


/**注文の履歴を表示する。
 * @param {bool} isPrev タブの移動が必要か？
 * @param {int} page ページ番号(1～)
 * @oaram {string} targetYearMonth 表示年月（YYYY-MM）
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.displayGoodsReservationHistory = function(isPrev, page, targetYearMonth){
	let self = this;
	if(typeof(page) === 'undefined') page = this.findElementByDataId('reservation_tab_history.paging.page').val();
	page = parseInt(page);
	if(Number.isNaN(page) || page < 1) page = 1;
	//対象年月
	if(typeof(targetYearMonth) === 'undefined') targetYearMonth = this.getTargetYearMonthFromHistoryTab();
	
	//ページング情報取得
	let offset = (page-1) * this.pagingUnit;
	let limit = this.pagingUnit;
	
	//検索情報
	let goodsName =  this.findElementByDataId('reservation_tab_history.goods_name').val();
	let goodsSerialno =  this.findElementByDataId('reservation_tab_history.goods_serialno').val();
	let goodsNo =  this.findElementByDataId('reservation_tab_history.goods_no').val();
	//1つ前の月をベース年月として取得するために。ローカル時間分ずれるのでオフセットを加算する。
	let baseDate =  new Date(targetYearMonth + '-01');
	let startDate = new Date(baseDate.getFullYear(), baseDate.getMonth()-1, 1, 0,-baseDate.getTimezoneOffset());
	let orderConditionStartDate = startDate.getFullYear() + '-' + ("00" + (startDate.getMonth()+1)).slice(-2) + '-01';
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//商品マスタ情報取得
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/goods_masters',
		method    : 'GET', 
		data      : {
						'pm_goods_name': goodsName,
						'pm_goods_serialno': goodsSerialno,
						'pm_goods_no': goodsNo,
						'order_condition_start_date': orderConditionStartDate,
						'offset': offset,
						'limit': limit
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).then(function(res2){
		//商品マスタ
		let list2 = res2['list'];
		goods_masters = list2;
		
		//予約処理タブ情報を利用するのでここに保存する
		self.data.reservationProcesssTab.goodsMasters = goods_masters;
		
		//注文履歴表を表示する
		self.writeReservationHistory(targetYearMonth);
		//ページング
		self.writePaging('reservation_tab_history.paging', res2);
		
		//
		if(isPrev){
			//タブの移動
			self.changeSliderTab(false, 'reservation_tab_history');
		}
		
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}
/**注文を表示する。
 * @oaram {string} order_id 注文ID
 */
RGMP_RentalGoodsReservationEditCtrl.prototype.displayGoodsReservationHistory_order = function(order_id){
	let self = this;
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//商品マスタ情報取得
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/orders/' + order_id,
		method    : 'GET', 
		data      : {},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).then(function(res2){
		//
		order = res2;
		
		//注文を表示する
		self.writeReservationHistory_order(order);
		
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
		//空の注文を表示する
		self.writeReservationHistory_order({});
	});
}

RGMP_RentalGoodsManagerCommon.inherit(RGMP_RentalGoodsReservationEditCtrl);

