
/**指定のタグ内を印刷する。
 * @param {HTMLElement|String} selector - 印刷対象。CSSセレクタ(String)で指定するか、既に選択したHTMLElementを渡す。
 * @note タイミングによってCSSが適用されない問題を解消。CSS描画前にprint()が呼ばれていたので、iframe描画処理が終わった後に呼び出した。
 */
function printIframeArea(selector){
	let printArea = selector;
	if(!(printArea instanceof HTMLElement)){
		printArea = document.querySelector(selector);
	}
	
	//iframeを作って、HTMLを書き込んでいく
	iframe = document.createElement('IFRAME');
	iframe.style.position = "absolute";
	iframe.style.width = "0px";
	iframe.style.height = "0px";
	iframe.style.left = "-500px";
	iframe.style.top = "-500px";
	//
	document.body.appendChild(iframe);
	//jsやstyleの設定
	let doc=iframe.contentWindow.document;
	doc.open();
	let links=window.document.getElementsByTagName('link');
	for(var i=0;i<links.length;i++){
		if(links[i].rel.toLowerCase()=='stylesheet'){
			doc.write(links[i].outerHTML);
		}
	}
	let styles=window.document.getElementsByTagName('style');
	for(var i=0;i<styles.length;i++){
		doc.write(styles[i].outerHTML);
	}
	//bodyの描画
	doc.write("<body>" + printArea.outerHTML + "</body>");//<script>window.print();</script>");
	
	//iframeの描画（CSS適用）が終わる前にprint()を呼び出すと、スタイルが適用されないのでonloadでprint()呼び出し
	iframe.contentWindow.onload = function(){
		iframe.contentWindow.print();
		document.body.removeChild(iframe);
	}
	
	//close()するとonloadが呼ばれるらしい
	doc.close();
	
//alert(doc.head.outerHTML);
	//iframe.contentWindow.location.reload();
	//iframe.contentWindow.unload = function(){};
	//iframe.contentWindow.focus();
	//iframe.contentWindow.print();
	//print()が起動する前にiframeを削除するのもまずいのでコメントアウト
	//document.body.removeChild(iframe);
}

