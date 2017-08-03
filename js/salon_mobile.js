if (!window.slmSchedule)
	window.slmSchedule = {};


slmSchedule._events = {};
slmSchedule._days = {};
slmSchedule._daysStaff = {};

slmSchedule._width = [];



slmSchedule.config={
	days: []
	,days_detail:[]
	,on_business :[]
	,holidays :[]
//	,chkHolidays :[]
	,staff_holidays : {}
	,open_position : 0
	,close_width : 0

};

slmSchedule.setEventDetail = function(ev,detail) {
	this._events[ev]["item_cds"] = detail[0];
	this._events[ev]["remark"] = detail[1];
	this._events[ev]["p2"] = detail[2];
	this._events[ev]["name"] = detail[3];
	this._events[ev]["tel"] = detail[4];
	this._events[ev]["mail"] = detail[5];
	this._events[ev]["user_login"] = detail[6];
	this._events[ev]["coupon"] = detail[7];
	this._events[ev]["memo"] = detail[8];
}

slmSchedule.chkHoliday = function(yyyymmdd) {
	for	(var i=0,to=slmSchedule.config.on_business.length;i < to ; i++ ){
		//Dateには０時の値が入っているから＝での判定でよい
		if (yyyymmdd.getTime()== slmSchedule.config.on_business[i].getTime() ) return false;
	}
	var tmp_days = yyyymmdd.getDay();
	for	(var i=0,to=slmSchedule.config.days.length;i < to ; i++ ){
		if ( tmp_days == slmSchedule.config.days[i] ) return true;
	}
	if (slmSchedule.date.existHolidays(yyyymmdd) ) return true;
	return false;
}

slmSchedule.chkOnBusiness = function(yyyymmdd) {
	for	(var i=0,to=slmSchedule.config.on_business.length;i < to ; i++ ){
		//Dateには０時の値が入っているから＝での判定でよい
		if (yyyymmdd.getTime()== slmSchedule.config.on_business[i].getTime() ) return true;
	}
	return false;
}

slmSchedule.date = {
	toYYYYMMDD:function(yyyymmdd) {
		var y = yyyymmdd.getFullYear();
		var m = yyyymmdd.getMonth() + 1;
		var d = yyyymmdd.getDate();
		return y+('0' + m).slice(-2)+('0' + d).slice(-2);
	},
	existHolidays:function(yyyymmdd) {
		for	(var i=0,to=slmSchedule.config.holidays.length;i < to ; i++ ){
			if (yyyymmdd.getTime() === slmSchedule.config.holidays[i].getTime() ) return true;
		}
	},
	existFullHolidays:function(yyyymmdd) {
		for	(var i=0,to=slmSchedule.config.holidays.length;i < to ; i++ ){
			if (yyyymmdd.getTime() === slmSchedule.config.holidays[i].getTime() ) {
				var base = slmSchedule.config.holidays_detail[i][0];
				var width = slmSchedule.config.holidays_detail[i][1];
				if (base <= slmSchedule.config.open_position
				&& slmSchedule.config.close_width <= width ) return true;
			}
		}
	},
	getIndexHolidays:function(yyyymmdd) {
		for	(var i=0,to=slmSchedule.config.holidays.length;i < to ; i++ ){
			if (yyyymmdd.getTime() === slmSchedule.config.holidays[i].getTime() ) {
				return i;
			}
		}
		return -1;
	},
	getIndexOnBusiness:function(yyyymmdd) {
		for	(var i=0,to=slmSchedule.config.on_business.length;i < to ; i++ ){
			if (yyyymmdd.getTime() === slmSchedule.config.on_business[i].getTime() ) {
				return i;
			}
		}
		return -1;
	}
}


slmSchedule.chkFullOnBusiness = function(yyyymmdd) {
	for	(var i=0,to=slmSchedule.config.on_business.length;i < to ; i++ ){
		if (yyyymmdd.getTime() === slmSchedule.config.on_business[i].getTime() ) {
			var base = slmSchedule.config.on_business_detail[i][0];
			var width = slmSchedule.config.on_business_detail[i][1];
			if (base <= slmSchedule.config.open_position && slmSchedule.config.close_width <= width ) return true;
		}
	}
}


slmSchedule.chkFullHoliday = function(yyyymmdd) {
	//特別な休日で全休
	if (slmSchedule.date.existFullHolidays(yyyymmdd) ) return true;
	//特別な半休があったら定休日のチェックをせずにfalseでリターンする。
	if (slmSchedule.date.existHolidays(yyyymmdd) ) return false;
	var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (idx != -1 ) {
		var base = slmSchedule.config.days_detail[idx][0];
		var width = slmSchedule.config.days_detail[idx][1];
		if (base <= slmSchedule.config.open_position && slmSchedule.config.close_width <= width ) return true;
	}
	return false;
}

slmSchedule.getOnBusinessLeftAfter = function(yyyymmdd,li_first_width) {
	var idx = slmSchedule.date.getIndexOnBusiness(yyyymmdd) ;
	return	li_first_width
			+ slmSchedule._calcLeft(
				slmSchedule.config.on_business_detail[idx][0]
				+ slmSchedule.config.on_business_detail[idx][1]
			);

}

slmSchedule.getOnBusinessWidthBefore = function(yyyymmdd,li_first_width) {
	var idx = slmSchedule.date.getIndexOnBusiness(yyyymmdd) ;
	return slmSchedule._calcLeft(slmSchedule.config.on_business_detail[idx][0]);
}

slmSchedule.getOnBusinessWidthAfter = function(yyyymmdd) {
	var idx = slmSchedule.date.getIndexOnBusiness(yyyymmdd) ;
	return	slmSchedule._calcAllWidth()
			- slmSchedule._calcLeft(
				slmSchedule.config.on_business_detail[idx][0]
				+ slmSchedule.config.on_business_detail[idx][1]
			);
}

slmSchedule.getHolidayLeft = function(yyyymmdd,li_first_width) {

	if (slmSchedule.date.existHolidays(yyyymmdd) ) {
		if (slmSchedule.date.existFullHolidays(yyyymmdd) ) {
			return li_first_width;
		}
		else {
			var idx = slmSchedule.date.getIndexHolidays(yyyymmdd) ;
			return li_first_width + slmSchedule._calcLeft(slmSchedule.config.holidays_detail[idx][0]);
		}
	}
	//ここに来る前に休日かのチェックはしている前提
	//休みの情報がはいっている。ここの数字は曜日の番号と等しい
	//0,3って場合は日曜日(0)と水曜日(3)はやすみで
	//それぞれ対応するIDXのdays_detailに休みの時間帯がはいっている
	var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (slmSchedule.config.days_detail[idx]) {

		return li_first_width + slmSchedule._calcLeft(slmSchedule.config.days_detail[idx][0]);
	}
	else {
		alert("E099 slmSchedule.getHolidayLeft is wrong.");
	}
}

slmSchedule.getHolidayWidth = function(yyyymmdd) {
	if (slmSchedule.date.existHolidays(yyyymmdd) ) {
		if (slmSchedule.date.existFullHolidays(yyyymmdd) ) {
			return slmSchedule._calcAllWidth();
		}
		else {
			var idx = slmSchedule.date.getIndexHolidays(yyyymmdd) ;
			return	 slmSchedule.calcWidthBase(
					 slmSchedule.config.holidays_detail[idx][0]
					,slmSchedule.config.holidays_detail[idx][1]);
		}
	}
	//ここに来る前に休日かのチェックはしている前提
	var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (slmSchedule.config.days_detail[idx]) {

		return	 slmSchedule.calcWidthBase(
				 slmSchedule.config.days_detail[idx][0]
				,slmSchedule.config.days_detail[idx][1]);
	}
	else {
		alert("E099 slmSchedule.getHolidayWidth is wrong.");
	}
}

//slmSchedule.getLeft = function(yyyymmdd,base,width) {
//	if (!slmSchedule.date.existHolidays(yyyymmdd) ) {
//		var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
//		if (slmSchedule.config.days_detail[idx]) {
////			return +slmSchedule.config.days_detail[idx][0] * width + base;
//			return base + slmSchedule._calcLeft(width);
//		}
//		else {
//			alert("E099 slmSchedule.getLeft is wrong.");
//		}
//	}
//	return slmSchedule.config.open_position + width + base;
//}

slmSchedule.calcLeftArray = function(base) {
	var calc = 0;
	for(var i = 0 ;i < base; i++ ) {
		calc += slmSchedule._width[i];
	}
	return calc;
}



//slmSchedule.getWidth = function(yyyymmdd,width) {
//	if (!slmSchedule.date.existHolidays(yyyymmdd) ) {
//		var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
//		if (slmSchedule.config.days_detail[idx]) {
//			return slmSchedule._calcWidth (idx);
////			return +slmSchedule.config.days_detail[idx][1] * width;
//		}
//		else {
//			alert("E099 slmSchedule.getWidth is wrong.");
//		}
//	}
//	return slmSchedule.config.close_width * width;
//}

slmSchedule.calcWidthBase = function(base,width) {
	var calc = 0;
	var max_cnt = +base + width;
//	if (base == 0 ) base =1;
//	for(var i = +base-1 ;i < max_cnt; i++ ) {
	for(var i = +base ;i < max_cnt; i++ ) {
		calc += slmSchedule._width[i];
	}
	return calc;
}


slmSchedule._calcLeft = function(max_cnt) {
	var calc = 0;
	for(var i=0;i < max_cnt; i++ ) {
		calc += slmSchedule._width[i];
	}
	return calc;
}


slmSchedule._calcWidth = function(idx) {
	var calc = 0;
	var i = +slmSchedule.config.days_detail[idx][0];
	var max_cnt = i + slmSchedule.config.days_detail[idx][1] ;
	if (i > 0 ) i--;
	for(i;i < max_cnt; i++ ) {
		calc += slmSchedule._width[i];
	}
	return calc;
}

slmSchedule._calcAllWidth = function() {
	var calc = 0;
	var max_cnt = slmSchedule._width.length ;
	for(i = 0;i < max_cnt; i++ ) {
		calc += slmSchedule._width[i];
	}
	return calc;
}


slmSchedule.clearWidthData = function() {
	slmSchedule._width.length = 0;
}

slmSchedule.setWidth = function(setWidth) {
	if (slmSchedule._width.length > 0 ) return;
	var tmp_array = setWidth.split(",");
	for(var i = 0,max_cnt = tmp_array.length;i < max_cnt  ; i++ ){
		var setWidth = tmp_array[i] /12 ;
		for(var j = 0 ; j < 12 ; j++ ) {
			slmSchedule._width.push(setWidth);
		}
	}
}