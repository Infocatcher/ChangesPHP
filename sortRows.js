// (c) Infocatcher 2009, 2012
// version 0.1.4 - 2012-11-14

// Usage:
// new RowsSorter("tableId");
// new RowsSorter("tableId").highlight();
// new RowsSorter("tableId").sortRows(columnNumber, reverseFlag, alreadySorted);
//   reverseFlag and alreadySorted is optional

//=== Fixes for IE 5.0
if(typeof Array.prototype.push == "undefined") {
	Array.prototype.push = function() {
		for(var i = 0, len = arguments.length; i < len; i++)
			this[this.length] = arguments[i];
		return this.length;
	};
}
if(typeof Array.prototype.shift == "undefined") {
	Array.prototype.shift = function() {
		var ret = this[0];
		var len = this.length - 1;
		for(var i = 0; i < len; i++)
			this[i] = this[i + 1];
		this.length = len;
		return ret;
	};
}
//=== End of fixes

function RowsSorter(table, titleRow, rowsContainer) {
	this.table = this.$(table);
	var tRow = this.$(titleRow);
	if(!tRow) {
		tRow = this.table.getElementsByTagName("thead")[0];
		tRow = tRow && tRow.getElementsByTagName("tr")[0] || this.table.getElementsByTagName("tr")[0];
	}
	this.titleRow = tRow;
	this.titleTag = tRow.getElementsByTagName("th").length ? "th" : "td";
	this.rowsContainer = this.$(rowsContainer) || this.table.getElementsByTagName("tbody")[0];
	this.addSortControls();
	this.init();
	return table.rowsSorter = this;
}
RowsSorter.prototype = {
	//== Settings begin:
	strings: {
		sortTitle: "Click to sort",
		dirUp: "\u25b2",
		dirDown: "\u25bc"
	},
	classes: {
		sortLink: "rowsSorterLink",
		sortDir: "rowsSorterDir",
		noDir: "rowsSorterNoDir",
		rowsEven: "rowsSorterEven",
		rowsOdd: "rowsSorterOdd"
	},
	//== Settings end
	_lastColumn: -1,
	$: function(s) {
		return typeof s == "string" ? document.getElementById(s) : s;
	},
	addSortControls: function() {
		var tds = this.titleRow.getElementsByTagName(this.titleTag);
		var aProto = document.createElement("a");
		aProto.className = this.classes.sortLink;
		aProto.title = this.strings.sortTitle;
		aProto.href = "javascript: void(0);";
		var dirMarker = document.createElement("span");
		dirMarker.className = this.classes.sortDir + " " + this.classes.noDir;
		dirMarker.innerHTML = this.strings.dirUp;
		var _this = this;
		var f = function(e) {
			_this.sortRows(this.parentNode);
			return false;
		};
		for(var i = 0, len = tds.length; i < len; i++) {
			var td = tds[i];
			var a = aProto.cloneNode(true);
			var html = td.innerHTML;
			if(/^(&nbsp;|\s)*$/.test(html))
				html = "";
			a.innerHTML = html;
			var d = a.appendChild(dirMarker.cloneNode(true));
			td.innerHTML = "";
			td.appendChild(a);
			td.__rowsSorterColumn = i;
			td.__rowsSorterReverse = false;
			td.__rowsSorterDirMarker = d;
			a.onclick = f;
		}
	},
	init: function() {
		this.rowsEvenRe = this.classRegExp(this.classes.rowsEven);
		this.rowsOddRe = this.classRegExp(this.classes.rowsOdd);
		//this.highlight();
	},
	classRegExp: function(c) {
		return new RegExp("(^|\\s)" + c + "(\\s|$)");
	},
	sortRows: function(tar, reverseFlag, alreadySorted) {
		tar = typeof tar == "number"
			? this.titleRow.getElementsByTagName(this.titleTag)[tar]
			: tar;
		var cId = tar.__rowsSorterColumn;
		reverseFlag = typeof reverseFlag == "boolean"
			? reverseFlag
			: cId == this._lastColumn ? tar.__rowsSorterReverse : false;
		this._lastColumn = cId;

		var dirMarker = tar.__rowsSorterDirMarker;
		dirMarker.innerHTML = reverseFlag ? this.strings.dirDown : this.strings.dirUp;
		dirMarker.className = this.classes.sortDir;
		var tds = this.titleRow.getElementsByTagName(this.titleTag), td;
		var i, len;
		for(i = 0, len = tds.length; i < len; i++) {
			td = tds[i];
			if(td == tar)
				continue;
			td.__rowsSorterDirMarker.innerHTML = this.strings.dirUp;
			td.__rowsSorterDirMarker.className = this.classes.sortDir + " " + this.classes.noDir;
		}

		tar.__rowsSorterReverse = !reverseFlag;
		if(alreadySorted)
			return;

		var rows = this.table.getElementsByTagName("tr");
		var row, cell, cText;
		var arr = [], map = {}, tmp;
		len = rows.length;
		for(i = 0; i < len; i++) {
			row = rows[i];
			if(row == this.titleRow)
				continue;
			cell = row.getElementsByTagName("td")[cId];
			cText = cell.textContent || cell.innerText;

			tmp = map[cText] || [];
			tmp.push(row); // text <=> row
			map[cText] = tmp;

			arr.push(cText);
		}
		arr.sort(this.compare);
		if(reverseFlag)
			arr.reverse();
		for(i = 0; i < len - 1; i++)
			this.rowsContainer.appendChild(map[arr[i]].shift());
		this.highlight();
	},
	compare: function(a, b) {
		var re = /^([\d\s]+|[+-][\d\s:d]+)$/;
		if(re.test(a) && re.test(b)) {
			var an = Number(a.replace(/[^\d-]+/g, ""));
			var bn = Number(b.replace(/[^\d-]+/g, ""));
			return an == bn ? 0 : an < bn ? -1 : 1;
		}
		re = /^\d+(\.\d+)?/;
		if(re.test(a) && re.test(b)) {
			var an = parseFloat(a), bn = parseFloat(b);
			if(an < bn)
				return -1;
			if(an != bn)
				return 1;
		}
		else {
			re = /(^[a-z]+:\/{2,})www\./;
		}
		a = a.replace(re, "");
		b = b.replace(re, "");
		return a == b ? 0 : a < b ? -1 : 1;
	},
	highlight: function() {
		var rows = this.table.getElementsByTagName("tr"), row;
		for(var i = 0, len = rows.length; i < len; i++) {
			row = rows[i];
			if(row == this.titleRow)
				continue;
			var c = row.className
				.replace(this.rowsEvenRe, " ")
				.replace(this.rowsOddRe, " ")
				.replace(/\s+/, " ")
			c += " " + (i%2 ? this.classes.rowsEven : this.classes.rowsOdd);
			row.className = c.replace(/^\s+/, "");
		}
	}
};