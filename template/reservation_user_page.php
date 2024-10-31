<?php
/*インクルード用（オペレータ用画面）

この画面を呼び出すときは、事前に以下の変数にインスタンスを設定しておくこと。
*/


$msg = array(RGMP_RentalGoodsManager::obj(), 'getMessage');


//このノードのルートのid
if(empty($rental_goods_content_id)){
	$rental_goods_content_id = 'goods_resv_user';
}
?>


<section id="<?= $rental_goods_content_id; ?>" class="reservation-user-sec">

<div class="reservation-user-tab-container">

<div data-id="reservation_user_tab_list" class="reservation-user-tab slider-tab slider-tab--active">
	<h3><?= $msg('RESERVATION_USER_TAB_LIST.TITLE'); ?>
		<div class="rental-goods-manager-help">
			<span class="rental-goods-manager-help__link">
				<i class="icon-question-circle"></i>
			</span>
			<span class="rental-goods-manager-help__balloon">
				<?= $msg('HELP.RESERVATION_USER_TAB_LIST.TITLE'); ?>
			</span>
		</div>
	</h3>
	<div class="row">
		<a data-id="reservation_user_tab_list.paging.btn_prev" data-page="1"><?= $msg('BTN.PAGE_PREV'); ?></a>
		<input type="text" data-id="reservation_user_tab_list.paging.page" name="paging.page" value="1" style="width:50px;">
		/ <span data-id="reservation_user_tab_list.paging.max_page"></span>
		<a data-id="reservation_user_tab_list.paging.btn_next" data-page="1"><?= $msg('BTN.PAGE_NEXT'); ?></a>
		<button type="button" class="rental-goods-manager-btn-square-so-pop" data-id="reservation_user_tab_list.btn_find"><?= $msg('BTN.FIND'); ?></button>
		<button type="button" class="rental-goods-manager-btn-square-so-pop" data-id="reservation_user_tab_list.btn_new"><?= $msg('BTN.NEW_ORER'); ?></button>
	</div>

	<table data-id="reservation_user_tab_list.table">
	<thead>
		<tr>
			<th style="width:80px"><?= $msg('OBJ.RESERVATION_ID'); ?></th>
			<th style="width:150px"><?= $msg('OBJ.RESERVATION_USER'); ?></th>
			<th style="width:100px"><?= $msg('OBJ.RESERVATION_RENTAL_FROM'); ?></th>
			<th style="width:100px"><?= $msg('OBJ.RESERVATION_RENTAL_TO'); ?></th>
			<th style="width:190px"><?= $msg('OBJ.RESERVATION_DATE'); ?></th>
			<th style="width:95px"><?= $msg('OBJ.RESERVATION_STATUS'); ?></th>
			<th style="width:80px"><?= $msg('OBJ.PROCESS'); ?></th>
		</tr>
	</thead>
	<tbody data-id="reservation_user_tab_list.tbody" style="overflow-y: scroll; max-height:300px; display: block;">
	<script id="<?= $rental_goods_content_id; ?>_reservation_user_tab_list_table" type="text/x-handlebars-template">
	{{#each list}}
		<tr><td style='width:80px'>{{order_id}}</td>
		<td style='width:150px'>{{user_name}}</td>
		<td style='width:100px'>{{rental_from}}</td><td style='width:100px'>{{rental_to}}</td>
		<td style='width:190px'>{{application_date}}</td><td style='width:95px'>{{lookup @root.DEF_STATUS status}}</td>
		<td style='width:80px'>
			{{eval "$evalVars.cancelButtonDisabled = (this.status == 're' ? '':'disabled')" false}} 
			<button type='button' class='rental-goods-manager-btn-cancel rental-goods-manager-btn-square-so-pop' data-orderId='{{order_id}}' {{@evalVars.cancelButtonDisabled}}><?= $msg('BTN.CANCEL'); ?></button>
			</td>
		</tr>
	{{/each}}
	</script>
	</tbody>
	</table>
</div><!-- /reservation_user_tab_list -->


<div data-id="reservation_user_tab_process" class="reservation-user-tab reservation-user-tab--scroll slider-tab" onload="javascript:alert('a');">
	<h3><?= $msg('RESERVATION_USER_TAB_PROCESS.TITLE'); ?></h3>
	<button type="button" class="rental-goods-manager-btn-square-so-pop" data-id="reservation_user_tab_process.btn_back"><?= $msg('BTN.BACK'); ?></button>
	<br>
	<div data-id="reservation_user_tab_process.message" class="reservation-user-err"></div>
	<span data-id="reservation_user_tab_process.err.order_id" class="reservation-user-err"></span>
	<form action="./" method="POST" class="">
		<input type="hidden" data-id="reservation_user_tab_process.is_canel" name="is_canel" value="">
		
		<p> 
		  <label for="title"><?= $msg('OBJ.RESERVATION_ID'); ?>:</label>
		  <input type="text" readonly data-id="reservation_user_tab_process.order_id" placeholder="<?= $msg('TEXT.NO_NEED_TO_SET'); ?>" readonly="readonly" value="{{order_id}}"/>
		</p>
		<p> 
		  <label for="title"><?= $msg('OBJ.RESERVATION_RENTAL_FROM'); ?>:</label>
		  <input id="rental_from" type="text" data-id="reservation_user_tab_process.rental_from" name="rental_from" placeholder="Select Date.." readonly="readonly" value="">
		  <span data-id="reservation_user_tab_process.err.rental_from" class="reservation-user-err"></span>
		</p>
		<p> 
		  <label for="title"><?= $msg('OBJ.RESERVATION_RENTAL_TO'); ?>:</label>
		  <input id="rental_to" type="text" data-id="reservation_user_tab_process.rental_to" name="rental_from" placeholder="Select Date.." readonly="readonly" value="">
		  <span data-id="reservation_user_tab_process.err.rental_to" class="reservation-user-err"></span>
		</p>
<script>
	const config = {
		minDate: "today"
	}
	flatpickr('#rental_from', config);
	flatpickr('#rental_to', config);
</script>
		<p> 
		  <label for="title"><?= $msg('OBJ.USER_DEPARTMENT'); ?>:</label>
		  <input type="text" data-id="reservation_user_tab_process.user_department" style="width: 250px;" value=""/>
		  <span data-id="reservation_user_tab_process.err.user_department" class="reservation-user-err"></span>
		</p>
		<p> 
		  <label for="title"><?= $msg('OBJ.REMARKS'); ?>:</label>
		  <textarea type="text" data-id="reservation_user_tab_process.remarks" name="remarks" ></textarea>
		  <span data-id="reservation_user_tab_process.err.reamrks" class="reservation-user-err"></span>
		</p>
		<p> 
		  <label for="title"><?= $msg('OBJ.USER_ZIP'); ?>:</label>
		  <input type="text" data-id="reservation_user_tab_process.user_zip" name="user_zip" value=""/>
		  <span data-id="reservation_user_tab_process.err.user_zip" class="reservation-user-err"></span>
		</p>
		<p> 
		  <label for="title"><?= $msg('OBJ.USER_ADDRESS'); ?>:</label>
		  <input type="text" data-id="reservation_user_tab_process.user_address" style="width: 350px;" value=""/>
		  <span data-id="reservation_user_tab_process.err.user_address" class="reservation-user-err"></span>
		</p>
		<div data-id="reservation_user_tab_process.order_goods">
			<div>
				<?= $msg('OBJ.RESERVATION_ORDER_GOODS'); ?>
				<button type="button" data-id="reservation_user_tab_process.btn_add_order_goods" class="rental-goods-manager-btn-square-soft"><?= $msg('BTN.ADD'); ?></button>
				<button type="button" data-id="reservation_user_tab_process.btn_del_order_goods" class="rental-goods-manager-btn-square-soft"><?= $msg('BTN.DEL'); ?></button>
			</div>
			<span data-id="reservation_user_tab_process.err.goods_names" class="reservation-user-err"></span><br>
			<span data-id="reservation_user_tab_process.err.order_nums.*" class="reservation-user-err"></span>
			<span data-id="reservation_user_tab_process.err.goods_names.*" class="reservation-user-err"></span>
		</div>
		
		<p><input type='submit' data-id="reservation_user_tab_process.btn_regist" class='button button-primary button-large' value=''></p>
	</form>
</div><!-- /reservation_user_tab_process -->
</div><!-- /reservation-user-sec-container -->
</section>

<script>
//商品予約の処理オブジェクト
new RGMP_GoodsResvUserCtrl("#<?= $rental_goods_content_id; ?>", "<?php echo wp_create_nonce( 'wp_rest' ); ?>").displayGoodsReservationList(false);
</script>


