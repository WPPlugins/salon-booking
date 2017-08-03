<?php 
class Salon_Country {
	static function echoZipTable() {
		echo <<<EOT
			prefucture = [
				null,       '北海道',   '青森県',   '岩手県',   '宮城県',
				'秋田県',   '山形県',   '福島県',   '茨城県',   '栃木県',
				'群馬県',   '埼玉県',   '千葉県',   '東京都',   '神奈川県',
				'新潟県',   '富山県',   '石川県',   '福井県',   '山梨県',
				'長野県',   '岐阜県',   '静岡県',   '愛知県',   '三重県',
				'滋賀県',   '京都府',   '大阪府',   '兵庫県',   '奈良県',
				'和歌山県', '鳥取県',   '島根県',   '岡山県',   '広島県',
				'山口県',   '徳島県',   '香川県',   '愛媛県',   '高知県',
				'福岡県',   '佐賀県',   '長崎県',   '熊本県',   '大分県',
				'宮崎県',   '鹿児島県', '沖縄県'
			];
EOT;
	}

	static function echoZipFunc($target,$set) {
	
	//以下の論理はAjaxZip2を参考に
		
		$target_src = SL_PLUGIN_URL;
		echo <<<EOT
	
			\$j("#{$target}").keyup(function(e) {
				var val = \$j("#{$target}").val();
				if( val.match(/^(\d{3})\-(\d{4})$/) || val.match(/^(\d{3})(\d{4})$/) ){
					var tmp_zip3 = 	RegExp.$1;
					var nzip = tmp_zip3+RegExp.$2;			
					if (  ! \$j("#{$set}").val() ) {
	
	
						 \$j.ajax({
								type: "post",
								url:  "{$target_src}/data/zip-"+tmp_zip3+".json", 
								dataType : "json",
								success: function(data) {
									var array = data[nzip];
									var opera = (nzip-0+0xff000000)+"";
									if ( ! array && data[opera] ) array = data[opera];
									if ( array ) \$j("#address").val(prefucture[array[0]]+array[1]+array[2]);
								},
								error:  function(XMLHttpRequest, textStatus){
									if (XMLHttpRequest.status != '404' ) {
										alert (textStatus);
									}
								}
						 });			
					}
				}
				
			});
	
EOT;
	}
}