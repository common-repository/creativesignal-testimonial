/**
Plugin Name: Creative-Signal Testimonials
Plugin URI: http://creative-signal.com/wordpress/plugins/creativesignal-testimonials
Description: Display testimonials on your website in various ways and effects. Add a testimonial widget to your website. Display a testimonial anywhere by tag code. Categorize testimonials and display different categories on different places
Author: CreativeSignal Team
Version: 1.0.0
Author URI: http://creative-signal.com/
Email: creativesignalteam@gmail.com
@copyright  Copyright (C) 2012 creative-signal.com. All Rights Reserved.
@license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

(function($){ 
	$.CreativeSignalTestimonialAdmin = function(options){
		
		this.options  = $.extend({}, options);

		this.submitForm = function () {
			var self = this;
			var form = $('form[name="frm-creativesignal-testimonial"]');
			console.log($('select#category', form).val())
			if ($('input#title', form).val() == '' 
				|| $('textarea#desc', form).val() == '' 
				|| $('input#author_name', form).val() == '' 
				|| ($('input#new_category', form).val() == '' && $('select#category', form).val() == ''))
			{
				alert('All fields marked with an asterisk are mandatory');
			}
			else
			{
				form.submit();
			}
		};
		
		this.deleteTestimonial = function (id) {
			var self = this;
			if (confirm('Do you want to delete this testimonal?'))
			{
				var form = $('form[name="frm-creativesignal-testimonial"]');
				$('input#task', form).val('delete');
				$('input#cid', form).val(id);
			form.submit();
			}
		}
	}
})(jQuery);