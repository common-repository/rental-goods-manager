
/**
 * オペレータ側の商品マスタを変更するHTMLをコントロールするクラス。
 * データをAPIから取得して動的にHTMLを変更する。
 * @param {String} id - ルート親HTMLを指定する。sectionタグのid属性で指定。
 */
function RGMP_RentalGoodsMasterCtrl(id, xWpNonce){
	//継承のために
	RGMP_RentalGoodsManagerCommon.call(this, id, xWpNonce);
	//
	this.xWpNonce = xWpNonce;
	this.handsontableData = [];
	//ページングの1ページの表示件数
	this.pagingUnit = 30;
	this.data={
	};
	//this.DEF_STATUS = this.getMsg('RESERVATION_STATUS.JS_ARRAY', false);

	//グリッドの取得
	let self = this;
	let grid = document.querySelector(id + ' [data-id="grid_goods_master"]');
	//this.displayGrid();
	self.g_rentalHoaadsontable = new Handsontable(grid, {
	    data: self.handsontableData,
	    colHeaders: [
	    	self.getMsg('OBJ.UPDATE'), 
	    	self.getMsg('OBJ.GOODS_ID'), 
	    	self.getMsg('OBJ.GOODS_NAME'), 
	    	self.getMsg('OBJ.GOODS_SERIALNO'), 
	    	self.getMsg('OBJ.GOODS_NO'), 
	    	self.getMsg('OBJ.RESERVATION_STATUS'), 
	    	self.getMsg('OBJ.RESERVATION_RENTAL_FROM'), 
	    	self.getMsg('OBJ.RESERVATION_RENTAL_TO'), 
	    	self.getMsg('OBJ.HIDE')
	    ],
	    columns: [
	        { type: 'checkbox', width: 50},
	        { readOnly: true, type: 'numeric' },
	        { type: 'text' , width: 210, className: "htLeft htMiddle"  },
	        { type: 'text' , width: 150, className: "htLeft htMiddle" },
	        { type: 'text',  width:  80, className: "htLeft htMiddle" },
	        { readOnly: true, type: 'text' , width: 100, className: "htLeft htMiddle" },
	        { readOnly: true, type: 'text' , width: 100, className: "htLeft htMiddle" },
	        { readOnly: true, type: 'text' , width: 100, className: "htLeft htMiddle" },
	        { type: 'checkbox' , width: 100}
	    ],
	    enterMoves: { row: 0, col: 1 },
	    autoWrapRow: false,//列の一番右にいるときに右カーソルを押したときの挙動
	    autoWrapCol: false,//行の一番下にいるときに下カーソルを押したときの挙動
	    outsideClickDeselects: true,
	    manualColumnResize: true,
	    fillHandle: false,
	    columnSorting: true
	});
	
	//
	this.prepareBtnOnclick();
}

/**
 * HTML内のすべてのボタンのonclickの呼び出し先functionを設定する。
 */
RGMP_RentalGoodsMasterCtrl.prototype.prepareBtnOnclick = function(){
	let self = this;
	//ページングのボタンやテキスト
	this.findElementByDataId('goods_master_sec.paging.btn_prev').click(function(event){ 
		let page = event.target.getAttribute('data-page');
		self.displayGrid(page); 
	});
	this.findElementByDataId('goods_master_sec.paging.btn_next').click(function(event){ 
		let page = event.target.getAttribute('data-page');
		self.displayGrid(page); 
	});
	this.findElementByDataId('goods_master_sec.paging.page').keypress(function(event){
		if( event.keyCode == 13 ){
			self.displayGrid(event.target.value);
		}
	});
	//検索キーのテキストボックス
	this.findElementByDataId('goods_master_sec.goods_name').keypress(function(event){
		if( event.keyCode == 13 ){
			self.displayGrid(1);
		}
	});
	this.findElementByDataId('goods_master_sec.goods_serialno').keypress(function(event){
		if( event.keyCode == 13 ){
			self.displayGrid(1);
		}
	});
	this.findElementByDataId('goods_master_sec.goods_no').keypress(function(event){
		if( event.keyCode == 13 ){
			self.displayGrid(1);
		}
	});
	//メインタブ内のボタン
	this.findElementByDataId("goods_master_sec.btn_all_check").click(function(event){ self.allCheck(event.target); });
	this.findElementByDataId("goods_master_sec.btn_find").click(function(){ self.displayGrid(); });
	this.findElementByDataId("goods_master_sec.btn_add_row").click(function(){ self.addRow(); });
	this.findElementByDataId("goods_master_sec.btn_save").click(function(){ self.save(); });
}


/**
 * データを取得して、テーブルを表示する。
 */
RGMP_RentalGoodsMasterCtrl.prototype.displayGrid = function(page){
	let self = this;
	if(this.isSetProcessingFlag()) return;
	if(typeof(page) === 'undefined') page = this.findElementByDataId('goods_master_sec.paging.page').val();
	page = parseInt(page);
	if(Number.isNaN(page) || page < 1) page = 1;
	
	//ページング情報取得
	let offset = (page-1) * this.pagingUnit;
	let limit = this.pagingUnit;
	//検索情報取得
	let goods_name = this.findElementByDataId('goods_master_sec.goods_name').val();
	let goods_serialno = this.findElementByDataId('goods_master_sec.goods_serialno').val();
	let goods_no = this.findElementByDataId('goods_master_sec.goods_no').val();
	
	//500ミリ秒間は処理中にして他の処理を受け付けない
	this.setProcessingFlag(500);
	
	//
	this.handsontableData.length = 0;
	let defStatus = this.DEF_STATUS;
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/goods_masters',
		method    : 'GET', 
		data      : {
						'offset': offset,
						'limit': limit,
						'pm_goods_name': goods_name,
						'pm_goods_serialno': goods_serialno,
						'pm_goods_no': goods_no
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (json) {
		self.writePaging('goods_master_sec.paging', json);
		let list = json['list'];
		for(let goods of list){
			let order_goods = goods['order_goods'];
			let order = order_goods[0];
			let line = [false, goods['goods_id'], goods['goods_name'], goods['goods_serialno'], goods['goods_no']
				 ,'', '', '' , goods['hide']=='1'];
			if(typeof order !== 'undefined'){
				line[5] = defStatus[order['status']];
				line[6] = order['rental_from'];
				line[7] = order['rental_to'];
			}
			self.handsontableData.push(line);
		}
		//テーブルを更新
		//self.g_rentalHoaadsontable.clear();
		//self.g_rentalHoaadsontable.loadData(self.handsontableData);
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}

/**行追加ボタン処理
 */
RGMP_RentalGoodsMasterCtrl.prototype.addRow = function(){
    this.g_rentalHoaadsontable.alter('insert_row', this.g_rentalHoaadsontable.countRows());
    let col = 0;
    this.g_rentalHoaadsontable.setDataAtCell(this.g_rentalHoaadsontable.countRows() - 1, 0, true);
    this.g_rentalHoaadsontable.selectCell(this.g_rentalHoaadsontable.countRows() - 1, col);
}

// 行削除ボタン処理
RGMP_RentalGoodsMasterCtrl.prototype.delRow = function(){
    // 画面反映
    let col = this.g_rentalHoaadsontable.propToCol(COL_SELECT);
    for (let i = this.g_rentalHoaadsontable.countRows() - 1; i >= 0; i--)
        if (this.g_rentalHoaadsontable.getDataAtCell(i, col) === true)
            this.g_rentalHoaadsontable.alter('remove_row', i);

    // 全行無くなったら1行追加
    if (this.g_rentalHoaadsontable.countRows() === 0) {
        this.addRow();
    }

    // 全選択/全削除を解除
    this.findElementByDataId('goods_master_sec.btn_all_check').prop("checked", false);
}

// 全選択／全削除チェック処理
RGMP_RentalGoodsMasterCtrl.prototype.allCheck = function(objCheckbox) {
	for (var i = 0; i < this.g_rentalHoaadsontable.countRows() ; i++) {
		this.g_rentalHoaadsontable.setDataAtCell(i, 0, objCheckbox.checked)
	}
}

/** 保存ボタン処理
 */
RGMP_RentalGoodsMasterCtrl.prototype.save = function(){
	let self = this;
	let data = this.g_rentalHoaadsontable.getSourceData();
	let goods_ids = [];
	let goods_names = [];
	let goods_serialnos = [];
	let goods_nos = [];
	let hides = [];

	//保存する値を取得
	for(let line of data){
		//console.log(line);
		if(line[0]){
			goods_ids.push(!line[1] ? null : line[1]);
			goods_names.push(line[2]);
			goods_serialnos.push(line[3]);
			goods_nos.push(line[4]);
			hides.push(line[8] === true ? true : false);
		}
	}
	if(goods_ids.length == 0){
		alert(this.getMsg('HTML.ERR.NO_CHECK'));
		return;
	}
	
	//保存する
	jQuery.ajax({
		url       : this.API_INFO.uriPrefix + '/goods_masters',
		method    : 'POST', 
		data      : {
						'goods_ids[]' : goods_ids,
						'goods_names[]' : goods_names,
						'goods_serialnos[]' : goods_serialnos,
						'goods_nos[]' : goods_nos,
						'hides[]' : hides
					},
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-WP-Nonce': self.xWpNonce
		}
	}).done(function (json) {
		alert(self.getMsg('OK.SUCCESS'));
		self.displayGrid();
	}).fail( (data) => {
		self.transactJsonErrByAlert(data);
	});
}

RGMP_RentalGoodsManagerCommon.inherit(RGMP_RentalGoodsMasterCtrl);


