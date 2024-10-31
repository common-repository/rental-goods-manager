<?php 
/*
RentalGoodsManager のDAOクラス

関数名のプレフィックス
find_  ...検索
count_ ...商品名などでグルーピングした数量を数える

予約status⇒
  no ... no order 何もしていない(初期状態)
  re ... reserved 予約中
  ou ... out 予約確定
  cl ... close 貸出後に機器が戻ってきた
  ca ... cancel 貸し出すことなく予約を破棄
  

*/


class RGMP_RentalGoodsManagerDao{
	//最後のエラー。成功してもクリアしないので、これで判断しないこと。
	public $last_error = '';

	public function obtain_user(string $loginid) : ?array {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rentalgoods_user';
		$rows = $wpdb->get_results( $wpdb->prepare("select * from $table_name where user_mail=%s", $loginid ), ARRAY_A);
		return $rows[0];
	}

	/**
	 * find検索結果形式に変換した結果を返す
	 * @param array $list 検索結果
	 * @param int $amount 検索総数
	 * @param int $offset 結果のオフセット。0～
	 * @param int $limit  結果の取得しようとした件数。検索結果が少ない場合、$listの件数より多くなる。
	 * @return array array('amount'=>$amount, 'offset'=>$offset, 'limit'=>$limit, 'list'=>$list)
	 */
	protected function _create_find_result(array $list, int $amount, int $offset, int $limit) : array{
		if($amount < $offset) $offset = $amount;
		return array('amount'=>$amount, 'offset'=>$offset, 'limit'=>$limit, 'list'=>$list);
	}

	/**
	 * 機器マスタの登録
	 * @param array $goods_info 配列{goods_id(NULLのとき新規登録), goods_name, goods_serialno, goods_no, hide}
	 */
	public function save_goods_master(array $goods_info) : bool {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rentalgoods_master';
		$data = array(
			'goods_name'=>$goods_info['goods_name'],
			'goods_serialno'=>$goods_info['goods_serialno'],
			'goods_no'=>$goods_info['goods_no'],
			'hide'=>$goods_info['hide'],
		);
		if(isset($goods_info['order_id'])) $data['order_id'] = $goods_info['order_id'];
		//DB保存
		if(isset($goods_info['goods_id'])){
			$result = $wpdb->update($table_name, $data, array('goods_id'=>$goods_info['goods_id']));
		}else{
			$result = $wpdb->insert($table_name, $data);
		}
		
		if($result === false){
			$this->last_error = $wpdb->last_error;
			return false;
		}
		return true;
	}
	
	/**
	 * 注文1つを保存する。エラー時はロールバック。
	 * 貸出し数が超える場合はロールバックし、エラーとなる。
	 * @param array $order_info 配列{...,array 'rental_goods_ids', 'order_goods'=>{'goods_name', 'order_num'}}
	 * @param array $result_goods_num 連想配列{'rentable_num'=>{'商品名'=>貸出し可能台数}, 'rentaling_num'=>{'商品ID'=>ダブルブッキング数]}。
	 *                        貸出数が不足の場合は貸出し可能台数が設定される（不足はマイナスになる）。
	 * @return bool 成功時：true、エラー時：false
	 */
	public function save_order(array $order_info, array &$result_goods_num=array(), int $buf_days=1) : bool {
		global $wpdb;
		$isErr = true;
		$result_goods_num = array('rentable_num'=>array(), 'rentaling_num'=>array());
		
		//ステータスに設定がない場合は予約を設定する
		if(!isset($order_info['status'])) $order_info['status'] = 're';
		
		//トランザクション開始
		$wpdb->query("START TRANSACTION"); 
		try{
			$table_order = $wpdb->prefix . 'rentalgoods_order';
			$table_order_goods = $wpdb->prefix . 'rentalgoods_order_goods';
			$data = array(
				'application_date'=>date_i18n('Y-m-d H-i-s.v'),
				'status'=>$order_info['status'],
				'remarks'=>$order_info['remarks'],
				'user_id'=>$order_info['user_id'],
				'user_department'=>$order_info['user_department'],
				'user_zip'=>$order_info['user_zip'],
				'user_address'=>$order_info['user_address'],
				'rental_from'=>$order_info['rental_from'],
				'rental_to'=>$order_info['rental_to'],
			);
			//既存データの変更の場合
			if(isset($order_info['order_id'])){
				$data['order_id'] = $order_info['order_id'];
				$data['application_date'] = $order_info['application_date'];
			}
			if($order_info['status'] === 're' || $order_info['status'] === 'no' ){
				$data['rental_goods_ids'] = '';
			}else if(isset($order_info['rental_goods_ids']) && is_array($order_info['rental_goods_ids'])){
				$data['rental_goods_ids'] = ',' . implode(',', $order_info['rental_goods_ids']) . ',';
			}
			
			//注文情報の保存
			$result = $wpdb->replace(
				$table_order, $data
			);
			if($result === false) return false;
			
			//登録されたレコードのID
			$order_id = $wpdb->insert_id;
			
			//内訳の削除
			$result = $wpdb->delete(
				$table_order_goods, array('order_id'=>$order_id)
			);
			//内訳の登録
			$list = $order_info['order_goods'];
			foreach($list as $item){
				//注文台数が0以下の場合は登録しない
				if($item['order_num'] < 1) continue;
				//
				$data = array(
					'order_id'=>$order_id,
					'goods_name'=>$item['goods_name'],
					'order_num'=>$item['order_num'],
				);
				$result = $wpdb->insert(
					$table_order_goods, $data
				);
				//
				if($result === false){
					return false;
				}
			}
			
			//数を確認(注文受付の時のみ)
			if($order_info['status'] === 're'){
				$list = $order_info['order_goods'];
				$rentable_goods_num = $this->count_rentable_goods($order_info['rental_from'], $order_info['rental_to'], $buf_days);
				foreach($list as $item){
					$name = $item['goods_name'];
					$num = $rentable_goods_num[$name];
					if($num < 0) $result_goods_num['rentable_num'][$name] = $num;
				}
				if(count($result_goods_num['rentable_num']) > 0) return false;
			}
			
			//選択した商品IDにダブルブッキングがないかを確認
			if($order_info['status'] === 'ou'){
				$rentaling_goods_num = $this->find_rentaling_goods_with_count($order_info['rental_from'], $order_info['rental_to'], $buf_days);
				$tmp_ary = array();
				foreach($rentaling_goods_num as $line){
					if(!in_array($line['goods_id'], $order_info['rental_goods_ids'])) continue;
					if($line['cnt'] > 1) $tmp_ary[$line['goods_id']] = $line['cnt'];
				}
				if(count($tmp_ary) > 0){
					$result_goods_num['rentaling_num'] = $tmp_ary;
					return false;
				}
			}
			
			//成功時
			$isErr = false;
			$wpdb->query('COMMIT');
			return true;
		}finally{
			if($isErr){
				$this->last_error = $wpdb->last_error;
				$wpdb->query('ROLLBACK');
			}
		}
	}
	
	/** 商品マスタ＋予約情報を取得する
	 * @see self::_create_find_result() 
	 * @param string $goods_name 商品名(部分一致)
	 * @param array  $ary_term  レンタル可能期間の商品のみ抽出{include:bool, renatal_from:string, renatal_to:string}。
	 *                          includeがfalseのとき指定期間にレンタル不可の商品のみ抽出。
	 * @param array  $ary_order_condition 抽出された商品を含む注文を取得。その条件をrental_fromの期間で指定{'start_date','months'}。
	 *                          この項目を指定しない場合、今月の1日から3か月内の注文を抽出する。
	 * @return array[] find形式で返す。商品情報([{goods_id:string,goods_name:string,...,order_goods:[{order_id:string,rental_from:string,rental_to:string,status:string}]},..])
	 */
	public function find_goods(int $offset=0, int $limit=30, 
		?int $goods_id = NULL, ?string $goods_name = NULL, ?string $serialno = NULL,
		?string $goods_no, ?array $statuses=NULL, ?array $ary_term=NULL, ?array $ary_order_condition=NULL
	) : array{
		global $wpdb;
		$table_order= $wpdb->prefix . 'rentalgoods_order';
		$table_master = $wpdb->prefix . 'rentalgoods_master';
		$order_condition_start_date = date_i18n('Y-m-01');
		$order_condition_months = 3;
		$where = '';
		$where_ary = array();
		if(!is_null($goods_id)){
			$where .= 'and mas.goods_id=%d ';
			$where_ary[] = $goods_id;
		}
		if(!is_null($goods_name)){
			$where .= 'and mas.goods_name like %s ';
			$where_ary[] = '%'.$goods_name.'%';
		}
		if(!is_null($serialno)){
			$where .= 'and mas.goods_serialno like %s ';
			$where_ary[] = '%'.$serialno.'%';
		}
		if(!is_null($goods_no)){
			$where .= 'and mas.goods_no like %s ';
			$where_ary[] = '%'.$goods_no.'%';
		}
		if(!is_null($statuses)){
			$csv = '';
			foreach($statuses as $val){
				$csv .= ',%s';
				$where_ary[] = $val;
			}
			$csv = substr($csv, 1);
			$where .= 'and ord.status in(' . $csv . ')';
		}
		if(!is_null($ary_term) && is_array($ary_term)){
			$buf_days = $ary_term['buf_days'];
			if($ary_term['include'] === false) $strNot = 'not';
			$where .= "and $strNot exists(select ord2.order_id from $table_order ord2 
					where ord2.rental_goods_ids like concat('%,', mas.goods_id, ',%')
					and ord2.status='ou'
					and !(DATE_ADD(ord2.rental_to, INTERVAL $buf_days DAY) < %s 
						or %s < DATE_ADD(ord2.rental_from, INTERVAL -$buf_days DAY) )
 				) ";
 			//値の設定
 			$where_ary[] = $ary_term['renatal_from'];
 			$where_ary[] = $ary_term['renatal_to'];
		}
		if(!is_null($ary_order_condition) && is_array($ary_order_condition)){
			$order_condition_start_date = $ary_order_condition['start_date'];
			$order_condition_months = (int)$ary_order_condition['months'];
		}
		if(strlen($where) != 0){
			$where = 'where ' . substr($where, 3);
		}
		//注文情報の抽出条件
		array_unshift($where_ary, $order_condition_start_date, $order_condition_start_date, $order_condition_months);
		
		//すべてのレコード数をカウントするSQL実行
		$left_where = $wpdb->prepare(
			" left outer join $table_order ord on ord.rental_goods_ids like concat('%,', mas.goods_id, ',%') "
			. " and ord.rental_from between %s and DATE_ADD(%s, INTERVAL %d MONTH) "
			. $where , $where_ary);
		$amount = $wpdb->get_results("select count(distinct mas.goods_id) as cnt from $table_master mas $left_where", ARRAY_A)[0];
		$amount = isset($amount) ? $amount['cnt'] : 0;
		
		//レコードを取得★★
		$rows = $wpdb->get_results("select mas.*, ord.order_id, ord.rental_from, ord.rental_to, ord.status "
			." from (select mas1.* from $table_master mas1 where mas1.goods_id in(select goods_id from $table_master mas $left_where) order by mas1.goods_id limit $offset, $limit) mas "
			." $left_where order by mas.goods_id, ord.rental_from ", ARRAY_A);
		
		//詰めなおし
		$ret = $this->reshape_result($rows, 'goods_id', array('order_id', 'rental_from', 'rental_to', 'status'), 'order_goods');
		return $this->_create_find_result($ret, $amount, $offset, $limit);
	}


	/** 注文情報を取得する
	 * @param string $pm_goods_name 商品名(部分一致)
	 * @return array find形式で返す。注文情報([{'order_id','rental_from','rental_to','rental_goods_ids'=>[goods_id],'order_goods'=>{'goods_name','order_num'}},..])
	 * @see self::_create_find_result() 
	 */
	public function find_order(int $offset=0, int $limit=30,?bool $order_by_asc=true,
		?string $pm_goods_name = NULL, ?string $pm_serialno = NULL, ?string $pm_user_mail=NULL,
		?array $statuses=NULL, ?int $order_id=NULL, ?int $user_id=NULL
	) : array{
		global $wpdb;
		$table_order= $wpdb->prefix . 'rentalgoods_order';
		$table_order_goods= $wpdb->prefix . 'rentalgoods_order_goods';
		$table_users = $wpdb->prefix . 'users';
		//
		$where = '';
		$where_ary = array();

		if(!is_null($pm_goods_name)){
			$where .= "and ord.order_id in(select order_id from $table_order_goods ordg "
				    . " where ordg.order_id=ord.order_id and goods_name like %s) ";
			$where_ary[] = '%'.$pm_goods_name.'%';
		}
		if(!is_null($pm_serialno)){
			$where .= 'and goods_serialno like %s ';
			$where_ary[] = '%'.$pm_serialno.'%';
		}
		if(!is_null($user_id) & !empty($user_id)){
			$where .= 'and user_id = %d ';
			$where_ary[] = $user_id;
		}
		if(!is_null($pm_user_mail) & !empty($pm_user_mail)){
			$where .= "and exists(select user_email from $table_users where user_email like %s )";
			$where_ary[] = '%'.$pm_user_mail.'%';
		}
		if(!is_null($statuses)){
			$csv = '';
			foreach($statuses as $val){
				$csv .= ',%s';
				$where_ary[] = $val;
			}
			$csv = substr($csv, 1);
			$where .= 'and status in(' . $csv . ')';
		}
		if(!is_null($order_id)){
			$where .= 'and ord.order_id = %d ';
			$where_ary[] = $order_id;
		}
		if(strlen($where) != 0){
			$where = 'where ' . substr($where, 3);
		}
		
		//すべてのレコード数をカウントするSQL実行
		$left_where = $wpdb->prepare(
			" left outer join $table_users u on ord.user_id=u.id left outer join $table_order_goods ordg on ord.order_id=ordg.order_id "
			. $where , $where_ary);
		$amount = $wpdb->get_results("select count(distinct ord.order_id) as cnt from $table_order ord $left_where", ARRAY_A)[0];
		$amount = isset($amount) ? $amount['cnt'] : 0;
		
		//ソート順
		if(!$order_by_asc) $order_desc = 'DESC';
		
		//レコードを取得★★
		$rows = $wpdb->get_results("select ord.order_id as order_id, u.user_login, u.display_name as user_name, ord.*, ordg.* "
			." from (select ord1.* from $table_order ord1 where ord1.order_id in(select ord.order_id from $table_order ord $left_where) 
				order by ord1.order_id $order_desc limit $offset,$limit) ord $left_where order by ord.order_id $order_desc, ordg.goods_name"
			, ARRAY_A);
		
		//詰め直し
		$ret = $this->reshape_result($rows, 'order_id', array('goods_name', 'order_num'), 'order_goods');
		$ret = $this->reshape_orders_rental_goods_ids_str2array($ret);
		
		return $this->_create_find_result($ret, $amount, $offset, $limit);
	}
	
	/**
	 * 指定の商品マスタ情報をすべて取得する
	 * @param array $rental_goods_ids 配列で取得したい商品IDを指定する
	 * @retrn array 商品マスタ情報を配列で返す
	 */
	public function list_goods_simple(array $rental_goods_ids):array {
		global $wpdb;
		$table_master = $wpdb->prefix . 'rentalgoods_master';
		//
		$str_rental_goods_ids = ',' . implode(",", $rental_goods_ids) . ',';
		//
		$sql = $wpdb->prepare("select mas.* 
			 from $table_master mas 
			 where %s like concat('%,', mas.goods_id, ',%')
			 order by mas.goods_id"
			, $str_rental_goods_ids);
		$rows = $wpdb->get_results($sql, ARRAY_A);
		return $rows;
	}
	
	/**
	 * 注文情報の商品IDを配列に変換して返す
	 * @param array $orders  対象の注文情報
	 * @retrn array $orders[rental_goods_ids]をCSVから配列型に変換したものを返す
	 */
	protected function reshape_orders_rental_goods_ids_str2array(array $orders) : array{
		foreach($orders as &$order){
			$csv = $order['rental_goods_ids'];
			if(is_string($csv)){
				$csv = (substr($csv, 0,1)===',' ? substr($csv, 1, -1) : $csv);
				if(strlen($csv) !== 0){
					$ary = explode(',', $csv);
				}else{
					$ary = array();
				}
				$order['rental_goods_ids'] = $ary;
			}
		}
		return $orders;
	}
	
	/**
	 * joinでひとまとめに取得したSQLの結果をキーでまとめる（$keyでgroup by するようなイメージ）
	 * @param array $rows          SQLの結果
	 * @param string $key         集約する項目名。$rows[*][$key]が同じ値のものをひとつにまとめる
	 * @param array $gather_keys  1つに配列化する項目の名前。指定した項目はすべて$ret[*][$gather_name]に連想配列で設定される
	 * @param string $gather_name 1つにした配列を代入する項目名。
	 * @retrn array まとめた結果を返す([{$key, ..., $gather_name=>{$gather_keysの項目値}}])
	 */
	protected function reshape_result(array $rows, string $key, array $gather_keys, string $gather_name) : array {
		if(!isset($key)) die('不正な引数：$key');
		$ret = array();
		if(count($rows) == 0) return $ret;
		$cur_id = NULL;
		
		//$orderはreturnする配列の1行を表す
		foreach($rows as $line){
			if($cur_id != $line[$key]){
				if(isset($order)) $ret[] = $order;
				$cur_id = $line[$key];
				$order = $line;
				foreach($gather_keys as $condens_key){
					unset($order[$condens_key]);
				}
				$order[$gather_name] = array();
			}
			//1つに配列化するカラムの処理
			$condens_ary = array();
			foreach($gather_keys as $condens_key){
				if(!is_null($line[$condens_key])){
					$condens_ary[$condens_key] = $line[$condens_key];
				}
			}
			if(count($condens_ary) != 0) $order[$gather_name][] = $condens_ary;
		}
		
		//最後の1つを設定する
		$ret[] = $order;
		
		return $ret;
	}

	/**
	 * 商品名(goods_name)でグルーピングして、それぞれの数を取得する
	 * @return array find形式で返す。配列{goods_name=>数}
	 * @see self::_create_find_result() 
	 */
	public function find_goods_name_with_count() : array{
		global $wpdb;
		$table_master= $wpdb->prefix . 'rentalgoods_master';
		//SQL実行
		$sql = $wpdb->prepare("select goods_name, count(*) as cnt from $table_master"
			." where hide=false group by goods_name", array());
		$rows = $wpdb->get_results($sql, ARRAY_A);
		$ret = array();
		foreach($rows as $value){
			$ret[$value['goods_name']] = $value['cnt'];
		}
		
		return $this->_create_find_result($ret, count($ret), 0, count($ret));
	}
	
	/**指定の期間に貸出し可能な機器の数
	 * @param string $from 指定の期間（開始日）
	 * @param string $to   指定の期間（終了日）
	 * @param int    $buf_days  各商品の貸出期間にバッファを設けて検索する。
	 *               1を指定すると商品の予約期間を開始・終了日を1日延ばして、数を計算する（つまり2日延びる）。
	 * @return array {@internal 配列{goods_name=>数}
	 */
	public function count_rentable_goods(string $from, string $to, int $buf_days) : array {
		global $wpdb;
		$table_order= $wpdb->prefix . 'rentalgoods_order';
		$table_order_goods= $wpdb->prefix . 'rentalgoods_order_goods';
		$table_user = $wpdb->prefix . 'rentalgoods_user';
		$table_master = $wpdb->prefix . 'rentalgoods_master';
		
		$sql = $wpdb->prepare("select goods_name, sum(cnt) as cnt
				from
				(select ordg.goods_name, -sum(order_num) as cnt from $table_order ord 
					left outer join $table_order_goods ordg on ord.order_id=ordg.order_id
					where !(DATE_ADD(rental_to, INTERVAL $buf_days DAY) < %s 
							or %s < DATE_ADD(rental_from, INTERVAL -$buf_days DAY) ) 
					and status ='re'
					group by ordg.goods_name
				union all
				select mas.goods_name, -count(mas.goods_id) as cnt
					from $table_master mas
 					where exists(select order_id from $table_order ord2 where ord2.rental_goods_ids like concat('%,', mas.goods_id, ',%')
							and ord2.status='ou'
							and !(DATE_ADD(rental_to, INTERVAL $buf_days DAY) < %s 
								or %s < DATE_ADD(rental_from, INTERVAL -$buf_days DAY) )
 						)
 					group by mas.goods_name
				union all
				select goods_name, count(*) as cnt from $table_master
					where hide=false group by goods_name
				) tmp
				group by goods_name " , 
				$from, $to, $from, $to
			);
		
		$rows = $wpdb->get_results($sql, ARRAY_A);
		
		//入れ替え
		$ret = array();
		foreach($rows as $value){
			$ret[$value['goods_name']] = $value['cnt'];
		}
		return $ret;
	}


	/**指定の期間に貸出された各商品(status='ou')を検索する。貸し出された数も結果に含める。
	 * ダブルブッキングしていないか？をチェックするための情報が取得できる。
	 * @param string $from 指定の期間（開始日）
	 * @param string $to   指定の期間（終了日）
	 * @param int    $buf_days  各商品の貸出期間にバッファを設けて検索する。
	 *               1を指定すると商品の予約期間を開始・終了日を1日延ばして、数を計算する（つまり2日延びる）。
	 * @return array {@internal 配列[{goods_id,goods_name,hide,cnt}]。期間中貸し出された商品だけ取得する。
	 */
	public function find_rentaling_goods_with_count(string $from, string $to, int $buf_days) : array {
		global $wpdb;
		$table_order= $wpdb->prefix . 'rentalgoods_order';
		$table_order_goods= $wpdb->prefix . 'rentalgoods_order_goods';
		$table_user = $wpdb->prefix . 'rentalgoods_user';
		$table_master = $wpdb->prefix . 'rentalgoods_master';
		
		$sql = $wpdb->prepare("select mas.goods_id, mas.goods_name, mas.hide, count(mas.goods_id) as cnt
				from $table_master mas
				left outer join $table_order ord on ord.rental_goods_ids like concat('%,', mas.goods_id, ',%')
				where ord.status='ou'
					and !(DATE_ADD(ord.rental_to, INTERVAL $buf_days DAY) < %s 
						or %s < DATE_ADD(ord.rental_from, INTERVAL -$buf_days DAY) )
					
				group by mas.goods_id, mas.goods_name, mas.hide " , 
				$from, $to
			);
		
		$rows = $wpdb->get_results($sql, ARRAY_A);

		return $rows;
	}

}


?>