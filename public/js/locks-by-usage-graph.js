"use strict";

$(document).ready(function() {
	locks_by_usage.setDatepicker();

	$("#report_locksbyusage_percentage ul.drop li span").click(function(event) {
		locks_by_usage.current_usage_persent = $(event.currentTarget).data("usage-persent");
		locks_by_usage.doRequest();
	});

	$("#report_locksbyusage_groups ul.drop li span").click(function(event) {
		locks_by_usage.current_id_group = $(event.currentTarget).data("id-groups");
		$("#report_locksbyusage_percentage + div span:last-child").text( $(event.currentTarget).text() );
		locks_by_usage.doRequest();
	});
	$("#report_locksbyusage_groups ul.drop li:first-child span").click();
});

var locks_by_usage = {
	init: function() {
		google.load("visualization", "1", {packages: ["corechart", "table"]});
	},
	current_usage_persent: 10,
	current_id_group: 0,
	wrapper: {},
	fromDatepickerElement: {},
	toDatepickerElement: {},
	setDatepicker: function() {
		var self = this;
		self.fromDatepickerElement = $("#locksbyusage_from_datepicker");
		self.toDatepickerElement = $("#locksbyusage_to_datepicker");

		var options = {
			dateFormat: "yy-mm-dd",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 1
		};

		self.fromDatepickerElement.datepicker($.extend(options, {
			defaultDate: "-1m",
			onClose: function(selectedDate) {
				self.toDatepickerElement.datepicker("option", "minDate", selectedDate );
				self.doRequest(self.fromDatepickerElement, self.toDatepickerElement);
			}
		}));
		self.fromDatepickerElement.datepicker("setDate", self.fromDatepickerElement.datepicker("option", "defaultDate"));

		self.toDatepickerElement.datepicker($.extend(options, {
			defaultDate: 0,
			onClose: function(selectedDate) {
				self.fromDatepickerElement.datepicker("option", "maxDate", selectedDate );
				self.doRequest(self.fromDatepickerElement, self.toDatepickerElement);
			}
		}));
	},
	doRequest: function() {
		$("#report_locksbyusage_ajax_loading").show(0);

		$.get(
			"/catalog/report/graphdata?graph-data-ajax=1" +
			"&id-groups=" + this.current_id_group +
			"&usage-persent=" + this.current_usage_persent +
			"&from-date=" + encodeURIComponent(this.fromDatepickerElement.val()) + "&to-date=" + encodeURIComponent(this.toDatepickerElement.val()),
			function(data) {
				$( ".result" ).html( data );
				locks_by_usage.complete(data);
			}
		);
	},
	complete: function(data) {
		$("#report_locksbyusage_ajax_loading").hide(0);

		this.wrapper = $("#report_locksbyusage_groups + div").empty();
		delete data.id_groups;

		for (var device in data) {
			this.prepareRendering(data[device])
		}
	},
	prepareRendering: function(data) {
		// Chart
		var element = $("#locks_by_usage_template > div")
		.clone()
		.attr("id", data["title"])
		.appendTo(this.wrapper);

		for (var i = 0; i < data.graph_params.length; i++) {
			data.graph_params[i][1] = parseInt(data.graph_params[i][1]);
		}
		if ( ! data.graph_params[0][1] && ! data.graph_params[1][1] ) {
			element.children("div:first-child").text("Devices was not found");
			return;
		}
		this.drawChart(data.title, data.graph_params, element.children("div:first-child"));

		// Table
		this.drawTable(data.table_params, element.children("div:last-child"));
	},
	drawChart: function(title, details, element) {
		try {
			var data = new google.visualization.arrayToDataTable(details, true);
		} catch(err) {
			return;
		}

		var options = {
			title: title,
			height: 200,
			width: 400,
			is3D: true,
		};

		var chart = new google.visualization.PieChart(element.get(0));
		chart.draw(data, options);
	},
	drawTable: function(details, element) {
		details.unshift(['% Usage', 'Server Model',  'IP Address', 'Title']);

		try {
			var data = new google.visualization.arrayToDataTable(details);
		} catch(err) {
			return;
		}

		var options = {
			height: 200,
			width: 1200,
			cssClassNames: {
				tableCell : "locksbyusage_table_cell"
			}
		};

		var table = new google.visualization.Table(element.get(0));
		table.draw(data, options);
	}
}

locks_by_usage.init();
