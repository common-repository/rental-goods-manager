<?php
/*インクルード用（オペレータ用画面）

この画面を呼び出すときは、事前に以下の変数にインスタンスを設定しておくこと。
*/


$msg = array(RGMP_RentalGoodsManager::obj(), 'getMessage');

//このノードのルートのid
if(empty($rental_goods_content_id)){
	$rental_goods_content_id = 'goods_resv_manager';
}


//カスタムのhbsファイルの出力
$config_js = '';
$config_js .= ',' . RGMP_RentalGoodsManager::obj()->write_script_html_template($rental_goods_content_id, 'tab_print_area');
$config_js = substr($config_js, 1);
?>

<section id="<?= $rental_goods_content_id; ?>" style="overflow-x: scroll;">
<div class="reservation-container">

	<div data-id="reservation_tab_main" class="reservation-tab slider-tab slider-tab--active">
		<h3><?= $msg('RESERVATION_TAB_MAIN.TITLE'); ?>
			<div class="rental-goods-manager-help">
				<span class="rental-goods-manager-help__link">
					<i class="icon-question-circle"></i>
				</span>
				<span class="rental-goods-manager-help__balloon">
					<?= $msg('HELP.RESERVATION_MANAGER_CTRL.MAIN.TITLE'); ?>
				</span>
			</div>
		</h3>
		<div class="row">
			<?= $msg('OBJ.RESERVATION_ID'); ?>:<input type="text" data-id="reservation_tab_main.order_id" style="width: 80px;" value="">
			<?= $msg('OBJ.MAIL'); ?>:<input type="text" data-id="reservation_tab_main.user_mail" value="">
			<?= $msg('OBJ.RESERVATION_STATUS'); ?>:
				<label><input type="checkbox" data-id="reservation_tab_main.statuses" name="statuses" value="re"><?= $msg('RESERVATION_STATUS.re'); ?></label>
				<label><input type="checkbox" data-id="reservation_tab_main.statuses" name="statuses" value="ou"><?= $msg('RESERVATION_STATUS.ou'); ?></label>
				<label><input type="checkbox" data-id="reservation_tab_main.statuses" name="statuses" value="cl"><?= $msg('RESERVATION_STATUS.cl'); ?></label>
				<label><input type="checkbox" data-id="reservation_tab_main.statuses" name="statuses" value="ca"><?= $msg('RESERVATION_STATUS.ca'); ?></label>
				<br>
				<a data-id="reservation_tab_main.paging.btn_prev" data-page="1"><?= $msg('BTN.PAGE_PREV'); ?></a>
				<input type="text" data-id="reservation_tab_main.paging.page" name="paging.page" value="1" style="width:50px;">
				/ <span data-id="reservation_tab_main.paging.max_page"></span>
				<a data-id="reservation_tab_main.paging.btn_next" data-page="1"><?= $msg('BTN.PAGE_NEXT'); ?></a>
			<button type="button" class="rental-goods-manager-btn-square-soft" data-id="reservation_tab_main.btn_find"><?= $msg('BTN.FIND'); ?></button>
			&nbsp;&nbsp;&nbsp;
			<button type="button" class="rental-goods-manager-btn-square-so-pop" data-id="reservation_tab_main.btn_history"><?= $msg('BTN.HISTORY'); ?></button>
		</div>
	
		<table data-id="reservation_tab_main.table">
		<thead>
			<tr><th style="width:80px"><?= $msg('OBJ.RESERVATION_ID'); ?></th>
			<th style="width:150px"><?= $msg('OBJ.RESERVATION_USER'); ?></th>
			<th style="width:100px"><?= $msg('OBJ.RESERVATION_RENTAL_FROM'); ?></th>
			<th style="width:100px"><?= $msg('OBJ.RESERVATION_RENTAL_TO'); ?></th>
			<th style="width:210px"><?= $msg('OBJ.RESERVATION_DATE'); ?></th>
			<th style="width:90px"><?= $msg('OBJ.RESERVATION_STATUS'); ?></th>
			<th style="width:120px"><?= $msg('OBJ.PROCESS'); ?></th></tr>
		</thead>
		<tbody data-id="reservation_tab_main.tbody" style="overflow-y: scroll; max-height:300px; display: block;">
		<script id="<?= $rental_goods_content_id; ?>_reservation_tab_main_tbody" type="text/x-handlebars-template">
		{{#each list}}
			<tr>
			<td style='width:80px'>{{order_id}}</td><td style='width:150px'>{{user_name}}</td> 
			<td style='width:100px'>{{rental_from}}</td><td style='width:100px'>{{rental_to}}</td> 
			<td style='width:210px'>{{application_date}}</td><td style='width:90px'>{{lookup @root.DEF_STATUS status}}</td>
			<td style='width:120px'>
				<button type='button' class='rental-goods-manager-btn-edit rental-goods-manager-btn-square-so-pop' data-order_id='{{order_id}}'><?= $msg('BTN.EDIT'); ?></button>
				<button type='button' class='rental-goods-manager-btn-print rental-goods-manager-btn-square-so-pop' data-order_id='{{order_id}}'><?= $msg('BTN.PRINT'); ?></button>
			</td>
			</tr>
		{{/each}}
		</script>
		</tbody>
		</table>
	</div>
	
	
	<div data-id="reservation_tab_process" class="reservation-tab slider-tab" >
		<h3><?= $msg('RESERVATION_TAB_PROCESS.TITLE'); ?></h3>
		<div>
			<button type="button" data-id="reservation_tab_process.btn_back" class="rental-goods-manager-btn-square-so-pop"><?= $msg('BTN.BACK'); ?></button>
			<button type="button" data-id="reservation_tab_process.btn_regist" class="rental-goods-manager-btn-square-so-pop"><?= $msg('BTN.SAVE'); ?></button>
		</div>
		<table>
			<tr><th><?= $msg('OBJ.RESERVATION_ID'); ?></th><td data-id="reservation_tab_process.order_id" style="min-width: 50px;"></td>
				<th><?= $msg('OBJ.RESERVATION_USER'); ?></th><td data-id="reservation_tab_process.user"></td>
				<th><?= $msg('OBJ.RESERVATION_TERM'); ?></th><td data-id="reservation_tab_process.rental_term"></td>
				<th><?= $msg('OBJ.RESERVATION_STATUS'); ?>
					<div class="rental-goods-manager-help">
						<span class="rental-goods-manager-help__link">
							<i class="icon-question-circle"></i>
						</span>
						<span class="rental-goods-manager-help__balloon">
							<?= $msg('HELP.RESERVATION_MANAGER_CTRL.PROCESS.STATUS'); ?>
						</span>
					</div>
				</th><td data-id="reservation_tab_process.status"></td>
				<th rowspan="2"><?= $msg('OBJ.REMARKS'); ?></th><td rowspan="2" data-id="reservation_tab_process.remarks" style="width: 150px;overflow: scroll;"></td>
			</tr>
			<tr>
				<th><?= $msg('OBJ.RESERVATION_SEND_INFO'); ?></th><td colspan="7" data-id="reservation_tab_process.send_info"></td>
			</tr>
		</table>
		<br>
		<div class="reservation-tab-process-middle">
			<div>
				<?= $msg('RESERVATION_TAB_PROCESS.TITLE.DESIRED_GOODS'); ?>
				<div class="rental-goods-manager-help">
					<span class="rental-goods-manager-help__link">
						<i class="icon-question-circle"></i>
					</span>
					<span class="rental-goods-manager-help__balloon">
						<?= $msg('HELP.RESERVATION_MANAGER_CTRL.PROCESS.DESIRED_GOODS'); ?>
					</span>
				</div>
				<button type="button" data-id="reservation_tab_process.btn_add" class="rental-goods-manager-btn-square-soft"><?= $msg('BTN.ADD'); ?></button>
				<button type="button" data-id="reservation_tab_process.btn_del" class="rental-goods-manager-btn-square-soft"><?= $msg('BTN.DEL'); ?></button>
				<br>
				<table data-id="reservation_tab_process.middle_table">
				<thead>
					<tr><th style="width:200px"><?= $msg('OBJ.GOODS_NAME'); ?></th><th style="width:80px"><?= $msg('OBJ.RESERVATION_ORDER_NUM'); ?></th></tr>
				</thead>
				<tbody data-id="reservation_tab_process.middle_tbody" style="overflow-y: scroll; max-height:120px; display: block;">
				</tbody>
				</table>
			</div>
			
			<!-- 選択された商品の数を一覧で表示するモーダル -->
			<script id="<?= $rental_goods_content_id; ?>_reservation_tab_process_goods_num_list" type="text/x-handlebars-template">
				<table style="width: 520px;">
				<tbody>
					<tr><th ><?= $msg('OBJ.GOODS_NAME');?></th><th style="width: 50px;"><?= $msg('OBJ.RESERVATION_ORDER_NUM');?></th><th style="width: 50px;"><?= $msg('OBJ.RESERVATION_SELECTED_GOODS_NUM');?></th></tr>
					{{#each list}}
						{{eval "$evalVars.strStyle = (this.desired_goods_num == this.selected_goods_num ? '':'background-color: red;')" false}}
						<tr><td>{{goods_name}}</td><td>{{desired_goods_num}}</td><td style="{{@evalVars.strStyle}}">{{selected_goods_num}}</td></tr>
					{{/each}}
				</tbody>
				</table>
			</script>
			
			<div style="min-height: 150px;">
				<?= $msg('RESERVATION_TAB_PROCESS.TITLE.SELECTED_GOODS'); ?>
				<div class="rental-goods-manager-help">
					<span class="rental-goods-manager-help__link">
						<i class="icon-question-circle"></i>
					</span>
					<span class="rental-goods-manager-help__balloon">
						<?= $msg('HELP.RESERVATION_MANAGER_CTRL.PROCESS.SELECTED_GOODS'); ?>
					</span>
				</div>
				<label for="reservation_tab_modal_switch" data-id="reservation_tab_process.btn_goods_num_list"><i class="icon-bars"></i></label>
				<br>				
				<div data-id="reservation_tab_process.middle_table_rental.popup" class="rental-goods-manager-popup-container">
					<!-- 選択された商品の数を表示するポップアップ -->
					<script id="<?= $rental_goods_content_id; ?>_reservation_tab_process_goods_num_popup" type="text/x-handlebars-template">
						<div class="rental-goods-manager-popup-content-bound-animation" style="width: 300px;">
							<div class="rental-goods-manager-table">
								<ul class="rental-goods-manager-table--header">
									<li><?= $msg('OBJ.GOODS_NAME');?></li>
									<li style="width: 85px;"><?= $msg('OBJ.RESERVATION_ORDER_NUM');?></li>
									<li style="width: 85px;"><?= $msg('OBJ.RESERVATION_SELECTED_GOODS_NUM');?></li>
								</ul>
								<ul>
									<li>{{goods_name}}</li>
									<li>{{desired_goods_num}}</li>
									{{eval "$evalVars.strStyle = (this.desired_goods_num == this.selected_goods_num ? '':'background-color: red;')" false}}
									<li style="font-weight: bold; {{@evalVars.strStyle}}">{{selected_goods_num}}</li>
								</ul>
							</div>
						</div>
					</script>
				</div>
				<table data-id="reservation_tab_process.middle_table_rental">
				<thead>
					<tr><th style="width:80px"><?= $msg('OBJ.GOODS_ID'); ?></th><th style="width:200px"><?= $msg('OBJ.GOODS_NAME'); ?>
					</th></tr>
				</thead>
				<tbody data-id="reservation_tab_process.middle_tbody_rental" style="overflow-y: scroll; max-height:140px; display: block;">
					<tr><td>1</td><td>M1065-LW</td></tr>
				</tbody>
				</table>
			</div>
		</div>
		<div class="reservation-tab-process-bottom">
			<div>
				<?= $msg('RESERVATION_TAB_PROCESS.TITLE.SELECTING_GOODS'); ?>
				<div class="rental-goods-manager-help">
					<span class="rental-goods-manager-help__link">
						<i class="icon-question-circle"></i>
					</span>
					<span class="rental-goods-manager-help__balloon">
						<?= $msg('HELP.RESERVATION_MANAGER_CTRL.PROCESS.SELECTING_GOODS'); ?>
					</span>
				</div>
			</div>
			<a data-id="reservation_tab_process.paging.btn_prev" data-page="1"><?= $msg('BTN.PAGE_PREV'); ?></a>
			<input type="text" data-id="reservation_tab_process.paging.page" name="paging.page" value="1" style="width:50px;">
			/ <span data-id="reservation_tab_process.paging.max_page"></span>
			<a data-id="reservation_tab_process.paging.btn_next" data-page="1"><?= $msg('BTN.PAGE_NEXT'); ?></a>
			<?= $msg('OBJ.GOODS_NAME'); ?>:<input data-id="reservation_tab_process.bottom.goods_name" style="width: 120px;">
			<?= $msg('OBJ.GOODS_SERIALNO'); ?>:<input data-id="reservation_tab_process.bottom.goods_serialno" style="width: 120px;">
			<?= $msg('OBJ.GOODS_NO'); ?>:<input data-id="reservation_tab_process.bottom.goods_no" style="width: 120px;">
			<button class="rental-goods-manager-btn-square-soft" data-id="reservation_tab_process.bottom.btn_find"><?= $msg('BTN.FIND'); ?></button>
			<div data-id="reservation_tab_process.schedule" class="goods-reservation-schedule">
			<script id="<?= $rental_goods_content_id; ?>_reservation_tab_process_schedule" type="text/x-handlebars-template">
				<div class='goods-reservation-schedule-title'>
					<div class='goods-reservation-schedule-title__name' style='width:{{frame.nameWidth}}px;'><?= $msg('OBJ.GOODS_NAME'); ?></div>
					<div class='goods-reservation-schedule-title--relative'>
						<div class='goods-reservation-schedule-rental-term' style='left:{{frame.rentalTerm.startPos}}px; width:{{frame.rentalTerm.width}}px;'></div>
						<div class='goods-reservation-schedule-title--absolute'>
						{{#each frame.monthWidths}}
							<div class='goods-reservation-schedule-title__date' style='width:{{cellWidth}}px;'>{{strYearMonth}}</div>
						{{/each}}
						</div>
					</div>
				</div>
				
				<div class="goods-reservation-schedule-data">
				{{#each list}}
				<div class='goods-reservation-schedule-data-item'>
					<div class='goods-reservation-schedule-data-item__name'>
						
						<a data-goods_id='{{goods_id}}' data-goods_name='{{goods_name}}' ><i class='icon-add'></i>[{{goods_id}}] {{goods_name}}</a>
					</div>
					<span class='goods-reservation-schedule-data-item__balloon'> 
						<?= $msg('OBJ.GOODS_SERIALNO'); ?>: {{goods_serialno}}<br>
						<?= $msg('OBJ.GOODS_NO'); ?>: {{goods_no}}<br>
					</span>
					<div class='goods-reservation-schedule-data-item--relative'>
					{{#each order_goods}}
						<span class='goods-reservation-schedule-data-item__bar' style='left: {{calc.startPos}}px; width: {{calc.width}}px;'></span>
						<span class='goods-reservation-schedule-data-item__bar-text' style='left: {{calc.startPos}}px; width: 100px;'>{{calc.strStartDate}} - {{calc.strEndDate}}</span>
					{{/each}}
					</div>
				</div>
				{{/each}}
				</div><!-- /.goods-reservation-schedule-data -->
				
			</script>
			</div><!-- /.goods-reservation-schedule -->
		</div>
	</div><!-- /data-id="reservation_tab_process" -->
	
	
	
	
	<div data-id="reservation_tab_print" class="reservation-tab slider-tab" >
		<h3><?= $msg('RESERVATION_TAB_PRINT.TITLE'); ?>
			<button type="button" data-id="reservation_tab_print.btn_back" class="rental-goods-manager-btn-square-so-pop"><?= $msg('BTN.BACK'); ?></button>
			<button class="rental-goods-manager-btn-square-so-pop" data-id="reservation_tab_print.btn_print">
			<?= $msg('BTN.PRINT'); ?></button>
		</h3>
		<div data-id="reservation_tab_print.printarea" class="goods-reservation-print">
		<!-- 印刷エリア -->
		<script id="<?= $rental_goods_content_id; ?>_reservation_tab_print_area" type="text/x-handlebars-template">
			<div class="goods-reservation-print__title"><?= $msg('RESERVATION_TAB_PRINT.PRINTAREA.TITLE'); ?></div>
			<div class="goods-reservation-print__subtitle"><?= $msg('RESERVATION_TAB_PRINT.PRINTAREA.TITLE.RESERVATION_INFO'); ?></div>
			<table>
				<tr><th><?= $msg('OBJ.RESERVATION_ID'); ?></th><td data-id="reservation_tab_print.order_id">{{order.order_id}}</td></tr>
				<tr><th><?= $msg('OBJ.RESERVATION_USER'); ?></th><td data-id="reservation_tab_print.user">{{order.user_name}}</td></tr>
				<tr><th><?= $msg('OBJ.RESERVATION_TERM'); ?></th><td data-id="reservation_tab_print.rental_term">{{order.rental_from}} <?= $msg('TEXT.TERM_TO'); ?> {{order.rental_to}} </td></tr>
				<tr><th><?= $msg('OBJ.RESERVATION_SEND_INFO'); ?></th><td data-id="reservation_tab_print.send_info"><?= $msg('OBJ.USER_ZIP'); ?>&nbsp;{{order.user_zip}}&nbsp;{{order.user_address}}</td></tr>
				<tr><th><?= $msg('OBJ.REMARKS'); ?></th><td data-id="reservation_tab_print.remarks">{{order.remarks}}</td></tr>
			</table>
			<br>
			<div class="goods-reservation-print__subtitle"><?= $msg('RESERVATION_TAB_PRINT.PRINTAREA.TITLE.SELECTED_GOODS'); ?></div>
			<div class="goods-reservation-print__description">
				<?= $msg('RESERVATION_TAB_PRINT.PRINTAREA.SELECTEDGOODS_DESCRIPTION'); ?>
			</div>
			<table data-id="reservation_tab_print.table_rental">
				<tr><th style="width:120px"><?= $msg('OBJ.GOODS_NAME'); ?></th><th style="width:200px"><?= $msg('OBJ.GOODS_SERIALNO'); ?></th><th style="width:150px"><?= $msg('OBJ.GOODS_NO'); ?></th></tr>
				{{#each order.rental_goods}}
					<tr><td>{{goods_name}}</td><td>{{goods_serialno}}</td><td>{{goods_no}}</td></tr>
				{{/each}}
			</table>
		</script>
		</div>
	</div><!-- /data-id="reservation_tab_print" -->
	
	
	
	<div data-id="reservation_tab_history" class="reservation-tab slider-tab" >
		<h3><?= $msg('RESERVATION_TAB_HISTORY.TITLE'); ?>
			<div class="rental-goods-manager-help">
				<span class="rental-goods-manager-help__link">
					<i class="icon-question-circle"></i>
				</span>
				<span class="rental-goods-manager-help__balloon">
					<?= $msg('HELP.RESERVATION_MANAGER_CTRL.HISTORY.TITLE'); ?>
				</span>
			</div>
		</h3>
		<div>
			<button type="button" data-id="reservation_tab_history.btn_back" class="rental-goods-manager-btn-square-so-pop"><?= $msg('BTN.BACK'); ?></button>
		</div>
		<br>
		<div >
			<?= $msg('OBJ.GOODS_NAME'); ?>:<input data-id="reservation_tab_history.goods_name" style="width: 120px;">
			<?= $msg('OBJ.GOODS_SERIALNO'); ?>:<input data-id="reservation_tab_history.goods_serialno" style="width: 120px;">
			<?= $msg('OBJ.GOODS_NO'); ?>:<input data-id="reservation_tab_history.goods_no" style="width: 120px;">
			<br>
			<a data-id="reservation_tab_history.paging.btn_prev" data-page="1"><?= $msg('BTN.PAGE_PREV'); ?></a>
			<input type="text" data-id="reservation_tab_history.paging.page" name="paging.page" value="1" style="width:50px;">
			/ <span data-id="reservation_tab_history.paging.max_page"></span>
			<a data-id="reservation_tab_history.paging.btn_next" data-page="1"><?= $msg('BTN.PAGE_NEXT'); ?></a>
			&nbsp;&nbsp;
			<a data-id="reservation_tab_history.btn_prev_month" ><?= $msg('BTN.PREV_MONTH'); ?></a>
			<input data-id="reservation_tab_history.target_year_month" style="width: 80px;" readonly>
			<a data-id="reservation_tab_history.btn_next_month"><?= $msg('BTN.NEXT_MONTH'); ?></a>
			<button class="rental-goods-manager-btn-square-soft" data-id="reservation_tab_history.btn_find"><?= $msg('BTN.FIND'); ?></button>
<script>
	const config = {
		dateFormat: 'Y-m'
	}
	flatpickr('input[data-id="reservation_tab_history.target_year_month"]', config);
</script>
			
			
			<!-- 履歴の注文情報の詳細モーダル表示 -->
			<script id="<?= $rental_goods_content_id; ?>_reservation_tab_history_order" type="text/x-handlebars-template">
				<table>
					<tr><th><?= $msg('OBJ.RESERVATION_ID'); ?></th><td>{{order_id}}</td></tr>
					<tr><th><?= $msg('OBJ.RESERVATION_STATUS'); ?></th><td>{{strStatus}}</td></tr>
					<tr><th><?= $msg('OBJ.RESERVATION_USER'); ?></th><td>{{user_name}}</td></tr>
					<tr><th><?= $msg('OBJ.RESERVATION_RENTAL_FROM'); ?></th><td>{{rental_from}}</td></tr>
					<tr><th><?= $msg('OBJ.RESERVATION_RENTAL_TO'); ?></th><td>{{rental_to}}</td></tr>
					<tr><th><?= $msg('OBJ.USER_DEPARTMENT'); ?></th><td>{{user_department}}</td></tr>
					<tr><th><?= $msg('OBJ.REMARKS'); ?></th><td>{{remarks}}</td></tr>
					<tr><th><?= $msg('OBJ.USER_ZIP'); ?></th><td>{{user_zip}}</td></tr>
					<tr><th><?= $msg('OBJ.USER_ADDRESS'); ?></th><td>{{user_address}}</td></tr>
					<tr><th><?= $msg('OBJ.RESERVATION_ORDER_GOODS'); ?></th><td>
						<table>
						<tbody>
							<tr><th><?= $msg('OBJ.GOODS_ID'); ?></th>
								<th><?= $msg('OBJ.GOODS_NAME'); ?></th>
								<th><?= $msg('OBJ.GOODS_SERIALNO'); ?></th>
								<th><?= $msg('OBJ.GOODS_NO'); ?></th>
							</tr>
						{{#each rental_goods}}
							<tr><td>{{goods_id}}</td>
								<td>{{goods_name}}</td>
								<td>{{goods_serialno}}</td>
								<td>{{goods_no}}</td>
							</tr>
						{{/each}}
						</tbody>
						</table>
					</td></tr>
				</table>
			</script>
			
			<div data-id="reservation_tab_history.schedule" class="goods-reservation-schedule">
			<script id="<?= $rental_goods_content_id; ?>_reservation_tab_history_schedule" type="text/x-handlebars-template">
				<div class='goods-reservation-schedule-title'>
					<div class='goods-reservation-schedule-title__name' style='width:{{frame.nameWidth}}px;'><?= $msg('OBJ.GOODS_NAME'); ?></div>
					<div class='goods-reservation-schedule-title--relative'>
						<div class='goods-reservation-schedule-title--absolute'>
						{{#each frame.monthWidths}}
							<div class='goods-reservation-schedule-title__date' style='width:{{cellWidth}}px;'>{{strYearMonth}}</div>
						{{/each}}
						</div>
					</div>
				</div>
				
				<div class="goods-reservation-schedule-data">
				{{#each list}}
				<div class='goods-reservation-schedule-data-item'>
					<div class='goods-reservation-schedule-data-item__name'>
						<span data-goods_id='{{goods_id}}' data-goods_name='{{goods_name}}' >[{{goods_id}}] {{goods_name}}</span><br>
						<?= $msg('OBJ.GOODS_SERIALNO'); ?>: {{goods_serialno}}
					</div>
					<span class='goods-reservation-schedule-data-item__balloon'> 
						<?= $msg('OBJ.GOODS_ID'); ?>: {{goods_id}}<br>
						<?= $msg('OBJ.GOODS_SERIALNO'); ?>: {{goods_serialno}}<br>
						<?= $msg('OBJ.GOODS_NO'); ?>: {{goods_no}}<br>
					</span>
					<div class='goods-reservation-schedule-data-item--relative'>
					{{#each order_goods}}
						<label for="reservation_tab_modal_switch" data-order_id="{{order_id}}" class='goods-reservation-schedule-data-item__bar goods-reservation-schedule-data-item--bar-{{status}}' style='left: {{calc.startPos}}px; width: {{calc.width}}px;'>
						</label>
						<span class='goods-reservation-schedule-data-item__bar-text' style='left: {{calc.startPos}}px; width: 100px;'>{{calc.strStartDate}} - {{calc.strEndDate}}</span>
					{{/each}}
					</div>
				</div>
				{{/each}}
				</div><!-- /.goods-reservation-schedule-data -->
				
			</script>
			</div><!-- /.goods-reservation-schedule -->
			
		</div>
	</div><!-- /data-id="reservation_tab_history" -->
	
	
</div>


	<!-- 共通のモーダルダイアローグ -->
	<input type="checkbox" id="reservation_tab_modal_switch" class="goods-reservation-modal-switch">
	<section data-id="reservation_tab_modal" class="goods-reservation-modal-overlay">
		<div class="goods-reservation-modal-content">
			<header>
				<label for="reservation_tab_modal_switch"><i class="icon-window-close"></i></label>
				&nbsp;&nbsp;
				<span data-id="reservation_tab_modal.title"></span>
			</header>
			<div data-id="reservation_tab_modal.body" class="goods-reservation-modal-content--body">
			</div>
		</div>
	</section><!-- /reservation_tab_modal -->
</section>
<br>


<script>

//商品予約の処理オブジェクト
new RGMP_RentalGoodsReservationEditCtrl("#<?= $rental_goods_content_id; ?>", "<?php echo wp_create_nonce( 'wp_rest' ); ?>",
	{<?= $config_js; ?>}
)
.displayGoodsReservationMain(false, 1);
</script>
