<?php
/*インクルード用（オペレータ用画面）

この画面を呼び出すときは、事前に以下の変数にインスタンスを設定しておくこと。
*/


$msg = array(RGMP_RentalGoodsManager::obj(), 'getMessage');

//このノードのルートのid
if(empty($rental_goods_content_id)){
	$rental_goods_content_id = 'goods_master_sec';
}




?>





<style>


/*  --------------------   */
.handsontable th,
.handsontable td {
    padding: 2px 5px 2px 5px;
    font-size: 12px;
    text-align: center;
}

.handsontable th:last-child {
    padding-left: 8px;
    text-align: left;
}

.handsontable td:first-child {
    background: #EEE;
}
#grid {
  height: 300px;
  overflow: hidden;
}
.goods_master_sec a{
	display: inline-block;
	transition: .3s;
	-webkit-transform: scale(1);
	transform: scale(1);
	vertical-align: text-top;
	text-decoration: none;
	border-bottom: 1px dotted #000;
	height: 1.5em;
	padding: 0px;
	margin: 0px 0px 0px 3px;
	cursor: pointer;
}
.goods_master_sec a:hover {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}



</style>



<section id="<?= $rental_goods_content_id; ?>" class="goods_master_sec">
	<div class="container">
		<div class="row">
			<span class="pr-4"></span>
			<label><input type="checkbox" data-id="goods_master_sec.btn_all_check" ><?= $msg('BTN.ALL_CHECK'); ?></label>
			<?= $msg('OBJ.GOODS_NAME'); ?>:<input type="text" data-id="goods_master_sec.goods_name" style="width:100px;" value="">
			<?= $msg('OBJ.GOODS_SERIALNO'); ?>:<input type="text" data-id="goods_master_sec.goods_serialno" style="width:150px;" value="">
			<?= $msg('OBJ.GOODS_NO'); ?>:<input type="text" data-id="goods_master_sec.goods_no" style="width:100px;" value="">
			<br>
			<a data-id="goods_master_sec.paging.btn_prev" data-page="1"><?= $msg('BTN.PAGE_PREV'); ?></a>
			<input type="text" data-id="goods_master_sec.paging.page" name="paging.page" value="1" style="width:50px;">
			/ <span data-id="goods_master_sec.paging.max_page"></span>
			<a data-id="goods_master_sec.paging.btn_next" data-page="1"><?= $msg('BTN.PAGE_NEXT'); ?></a>
			<button type="button" class="rental-goods-manager-btn-square-soft" data-id="goods_master_sec.btn_find"><?= $msg('BTN.FIND'); ?></button>
			
			
			<button type="button" class="rental-goods-manager-btn-square-soft" data-id="goods_master_sec.btn_add_row"><?= $msg('BTN.ADD'); ?></button>
			<button type="button" class="rental-goods-manager-btn-square-soft" data-id="goods_master_sec.btn_save"><?= $msg('BTN.SAVE'); ?></button>
		</div>
		<div>
			<div data-id="grid_goods_master" id="test123"></div>
		</div>
	</div>
</section><!-- /商品マスタ -->

<script>
//商品マスタの処理オブジェクト
new RGMP_RentalGoodsMasterCtrl("#<?= $rental_goods_content_id; ?>", "<?php echo wp_create_nonce( 'wp_rest' ); ?>");

</script>






