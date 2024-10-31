<?php 
/*
RentalGoodsManager message difinition(English)
note) Don't write private information, because this text send to browser.

*/


RGMP_RentalGoodsManager::obj()->setMessages(array(
	//MANAGER PLUGIN NAME
	'MNG.TITLE.MAIN_MENU'         =>'RentalGoodsMng',
	'MNG.TITLE.SUB_MENU_SETTINGS' =>'Settings',
	'MNG.TITLE.SUB_MENU_MASTER'   =>'GoodsMaster',
	'MNG.TITLE.SUB_MENU_RESERVATION'=>'Order',
	//
	'BTN.BACK' => 'back',
	'BTN.SAVE' => 'save',
	'BTN.FIND' => 'find',
	'BTN.NEW_ORER'  => 'new order',
	'BTN.CANCEL'    => 'cancel',
	'BTN.EDIT'      => 'edit',
	'BTN.PRINT'     => 'print',
	'BTN.HISTORY'   => 'history',
	'BTN.ADD'       => 'add',
	'BTN.DEL'       => 'del',
	'BTN.ORDER_REGISTER'  => 'register',
	'BTN.PAGE_PREV' => '<prev',
	'BTN.PAGE_NEXT' => 'next>',
	'BTN.PREV_MONTH' => '<prev_month',
	'BTN.NEXT_MONTH' => 'next_month>',
	'BTN.ALL_CHECK'  => 'select_all/deselect_all',
	
	//Date until
	'TEXT.TERM_TO' => ' to ',
	//placeholder for input tag (no need to set)
	'TEXT.NO_NEED_TO_SET'  => 'no need to set',
	//
	'RESERVATION_STATUS.re' => 'accepted',
	'RESERVATION_STATUS.ou' => 'comfirmed',
	'RESERVATION_STATUS.cl' => 'closed',
	'RESERVATION_STATUS.ca' => 'canceled',
	'RESERVATION_STATUS.JS_ARRAY' => '{re: "accepted", ou:"comfirmed", cl:"closed", ca:"canceled"}',
	//
	'RESERVATION_USER_TAB_LIST.TITLE'       => 'Order Main',
	'RESERVATION_USER_TAB_PROCESS.TITLE'    => 'Order Process',
	//
	'RESERVATION_TAB_MAIN.TITLE'             => 'Order Info',
	'RESERVATION_TAB_PROCESS.TITLE'          => 'Order Edit',
	'RESERVATION_TAB_PROCESS.TITLE.DESIRED_GOODS'  =>'Desired Goods',
	'RESERVATION_TAB_PROCESS.TITLE.SELECTED_GOODS' =>'Selected Goods',
	'RESERVATION_TAB_PROCESS.TITLE.SELECTING_GOODS'=>'Selecting Goods',
	'RESERVATION_TAB_PROCESS.TITLE.MODAL.GOODS_NUM_LIST'=>'Desired And Selected Goods Num',
	'RESERVATION_TAB_PRINT.TITLE'            => 'Print Bill for rental items',
	'RESERVATION_TAB_PRINT.PRINTAREA.TITLE'  => 'Bill for rental items',
	'RESERVATION_TAB_PRINT.PRINTAREA.TITLE.RESERVATION_INFO'  => 'Reservation Info',
	'RESERVATION_TAB_PRINT.PRINTAREA.TITLE.SELECTED_GOODS'    => 'Goods for sending',
	'RESERVATION_TAB_PRINT.PRINTAREA.SELECTEDGOODS_DESCRIPTION' => "Our equipment rented are as follows. \nPlease confirm when you recieve. When return, please send everything including the equipment box. \n"
					. 'If the equipment is missing or damaged, we may ask you to purchase a replacement equipment.',
	'RESERVATION_TAB_HISTORY.TITLE'        => 'Order History',
	'RESERVATION_TAB_HISTORY.TITLE.MODAL.ORDER'  => 'Order Detail',
	
	
	//For admin
	'OBJ.ADMIN.OPERATOR_ROLE'  =>'OperatorRole',
	'OBJ.ADMIN.USER_ROLE'      =>'UserRole',
	'OBJ.ADMIN.DEFAULT_LOCALE' =>'DefaultLocale',
	'OBJ.ADMIN.RENTAL_BUF_DAYS'=>'RentalTermBufferDays',
	'OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'=>'MaxOrderNum',
	//For user
	'OBJ.RESERVATION_ID'  => 'OrderID',
	'OBJ.RESERVATION_USER'=> 'User',
	'OBJ.RESERVATION_USER_ID'=> 'UserID',
	'OBJ.RESERVATION_DATE'=> 'OrderDate',
	'OBJ.RESERVATION_ORDER_GOODS'=> 'GoodsList',
	'OBJ.RESERVATION_SELECTED_GOODS_NUM' => 'SelectedNum',
	'OBJ.RESERVATION_RENTAL_GOODS_IDS'=> 'SelectedGoodsID',
	'OBJ.RESERVATION_TERM'=> 'RentalTerm',
	'OBJ.RESERVATION_RENTAL_FROM'=>'RentalFrom',
	'OBJ.RESERVATION_RENTAL_TO'  =>'RentalTo',
	'OBJ.RESERVATION_ORDER_NUM'  =>'OrderedNum',
	'OBJ.RESERVATION_SEND_INFO'=>'SendTo',
	'OBJ.RESERVATION_STATUS'=> 'Status',
	'OBJ.MAIL'            => 'Mail',
	'OBJ.USER_ZIP'        => 'Zip',
	'OBJ.USER_ADDRESS'    => 'Address',
	'OBJ.USER_DEPARTMENT' => 'Department',
	'OBJ.REMARKS'         => 'Remarks',
	'OBJ.GOODS_ID'        => 'GoodsID',
	'OBJ.GOODS_NAME'      => 'GoodsName',
	'OBJ.GOODS_SERIALNO'  => 'SerialNo',
	'OBJ.GOODS_NO'        => 'GoodsNo',
	'OBJ.HIDE'            => 'Hide',
	'OBJ.PROCESS'         => 'Process',
	'OBJ.UPDATE'          => 'Update',
	'OBJ.ORDER_CONDITION_START_DATE' => 'OrderStartDate',
	'OBJ.ORDER_CONDITION_MONTHS' => 'OrderMonths',
	
	//Process
	'OK.SUCCESS'          => 'Processing is successful.',
	'OK.CANCEL'           => 'Processing canceled.',
	'WARN.USER.RESGIST_RE'  => "\"Selected Goods\" will be deleted, if Status is \"accepted\".\nAre you sure you want to execute the process?",
	//Errors
	'ERR.ERR_OCCURED'       => 'Error has occured.',
	'ERR.UNEXPECTED'        => 'Unexpected Error.',
	'ERR.INVALID_PARAMS'    => 'Invalid params',
	'ERR.NOT_FOUND'         => 'Resource is not found.',
	'ERR.UNAUTHORIZED'      => 'Unauthoried.',
	'ERR.ACCESS_ERROR'      => 'You do not have permission.',
	'ERR.DB_ERROR'          => 'DB error.',
	'ERR.DB_DUPLICATED'     => 'This value is already registered.({0})',
	'ERR.OVER_ORDER_MAX_NUM'    => 'You over max order num.',
	'ERR.OVER_ORDER_GOODS_NUM'    => 'Not enough stock.(OrderNum:{0}/ShortNum:{1})',
	'ERR.OVER_RENTALED_GOODS_NUM' => 'There are no stocked in specified term.(GoodsName:{0}/ShortNum:{1})',
	'ERR.DUPLICATE_RENTALED' => 'The Goods is rentating in the term.(GoodsID:{0})',
	'ERR.OBJ_ADMIN_DEFAULT_LANG' => '{field} must be alphanum,-,_,.(period).',
	'ERR.OBJ_USER_ZIP' => '{field} must be number withoout hyphen.',
	//Messages on HTML
	'HTML.ERR.NO_CHECK'     => 'Check the checkbox, which you want to update.',
	
	//Function Description-------
	//For Admin
	'HELP.OBJ.ADMIN.OPERATOR_ROLE'    => 'Capabilities required to perform management operations such as editing reservations,goods masters. This is created automatically and assigned to the administrator role. Set this capabilities to other role, if you want.',
	'HELP.OBJ.ADMIN.USER_ROLE'        => 'Authority required for user operations such as goods reservation.ex.:publish_posts, edit_pages, edit_post',
	'HELP.OBJ.ADMIN.DEFAULT_LOCALE'   => 'Language code such as ja,en. Use this value when message file of language code sent from browser can not be found.',
	'HELP.OBJ.ADMIN.RENTAL_BUF_DAYS'  => 'Reserve days before and after the rental period. Others cannot make a rental reservation even on the spare day. If the rental period is set to, for example, two days for five days rental, a total of nine days cannot be lent to another person.',
	'HELP.OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'  => 'Max number of orders per user(Operator has no restrict.). It is judged by the number of orders whose status is "accepted" and "comfirmed", so it is possible to order if the order becomes "canceled" or "closed".',
	//For Other
	'HELP.RESERVATION_USER_TAB_LIST.TITLE'  => 'You can sure your orders.If you want to order, click "new order" button.',
	//商品予約管理：注文情報のヘルプ
	'HELP.RESERVATION_MANAGER_CTRL.MAIN.TITLE' => 'You can edit orders, status, goods. First, find orders with clicking "find" button.',
	//予約管理：の希望商品のヘルプ
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.STATUS'         => 'Change the status, Case deciding rental goods, "comfirmed", Case returned rental goods, "close", Case canceling order, "cancel".',
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.DESIRED_GOODS'  => 'Goods details user ordered. You can edit goods name and number.',
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.SELECTED_GOODS' => 'Goods for sending. If you decide sending goods, select goods below list ,and change the status "comfirm", and "save" button.',
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.SELECTING_GOODS'=> 'Clicking goods link, it is selected for sending goods. Blue bar means in rental.',
	//注文履歴：ヘルプ
	'HELP.RESERVATION_MANAGER_CTRL.HISTORY.TITLE' => 'You can check the rentaling history for a certain goods. It will also be useful for checking past rentaled people when parts are lost or failures are discovered. The displayed orders are "comfirmed" and "closed". Press a bar in the table to see order details.',
	
));

?>