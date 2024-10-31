<?php 
/*
RGMP_RentalGoodsManager の初期化（テーブル作成など）をするクラス

*/


class RGMP_RentalGoodsManager_Init{
	const VERSION = '1.2';
	const OPTION_NAME = RGMP_RentalGoodsManager::PLUGIN_ID . '-db-version';
	
	static public function db_install(){
		$installed_ver = get_option( self::OPTION_NAME );
		if(!isset($installed_ver) || $installed_ver !== self::VERSION ){
			self::create_table();
			
			//バージョン管理のために
			update_option(self::OPTION_NAME, self::VERSION);
		}
	}
	
	static public function db_uninstall(){
		global $wpdb;
		$prefix = $wpdb->prefix . 'rentalgoods';
		$wpdb->query("DROP TABLE IF EXISTS {$prefix}_order;");
		$wpdb->query("DROP TABLE IF EXISTS {$prefix}_order_goods;");
		$wpdb->query("DROP TABLE IF EXISTS {$prefix}_master;");
		//
		delete_option(self::OPTION_NAME);
	}
	
	static public function create_table(){
		global $wpdb;
		$prefix = $wpdb->prefix . 'rentalgoods';
		$charset_collate = $wpdb->get_charset_collate();

		//レンタル注文
		$table_name = $prefix . '_order';
		$sql1 = "CREATE TABLE $table_name (
		  order_id mediumint(9) NOT NULL AUTO_INCREMENT,
		  application_date datetime(2) DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  rental_from date DEFAULT '0000-00-00' NOT NULL,
		  rental_to date DEFAULT '0000-00-00' NOT NULL,
		  status char(2) DEFAULT 'no' NOT NULL,
		  remarks text DEFAULT '' NOT NULL,
		  rental_goods_ids varchar(500) DEFAULT '' NOT NULL,
		  user_id bigint(20) unsigned NOT NULL,
		  user_department varchar(500) NOT NULL,
		  user_zip varchar(8) NOT NULL,
		  user_address varchar(500) NOT NULL,
		  PRIMARY KEY  (order_id)
		) $charset_collate;";

		//レンタル注文の内訳
		$table_name = $prefix . '_order_goods';
		$sql2 = "CREATE TABLE $table_name (
		  order_id mediumint(9) NOT NULL,
		  goods_name varchar(100) NOT NULL,
		  order_num mediumint(2) NOT NULL,
		  PRIMARY KEY  (order_id, goods_name)
		) $charset_collate;";
		
		//レンタル貸出し履歴
		$table_name = $prefix . '_order_history';
		$sql3 = "CREATE TABLE $table_name (
		  order_id mediumint(9) NOT NULL,
		  goods_id mediumint(9) NOT NULL,
		  rental_from date DEFAULT '0000-00-00' NOT NULL,
		  rental_to date DEFAULT '0000-00-00' NOT NULL
		) $charset_collate;";
		
		//商品定義
		$table_name = $prefix . '_master';
		$sql4 = "CREATE TABLE $table_name (
		  goods_id mediumint(9) NOT NULL AUTO_INCREMENT,
		  goods_name varchar(100) NOT NULL,
		  goods_serialno varchar(100) NOT NULL,
		  goods_no varchar(100) DEFAULT '-' NOT NULL,
		  hide boolean DEFAULT false NOT NULL,
		  PRIMARY KEY  (goods_id),
		  UNIQUE KEY goods_serialno (goods_serialno)
		) $charset_collate;";
		
		
		$table_name = $prefix . '_user';
		$sql5 = "CREATE TABLE $table_name (
		  user_id mediumint(9) NOT NULL AUTO_INCREMENT,
		  user_name varchar(200) NOT NULL,
		  user_mail varchar(200) NOT NULL,
		  user_pw varchar(300) NOT NULL,
		  tel varchar(20) NOT NULL,
		  user_repw_publish datetime(2) DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  PRIMARY KEY  (user_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql4 );
		dbDelta( $sql1 );
		dbDelta( $sql2 );
		//dbDelta( $sql3 );
		//dbDelta( $sql4 );
		//dbDelta( $sql5 );
		//
		//update_option(self::OPTION_NAME, self::VERSION);
	}

	
}


?>