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
	$.fn.creativeSignalTestimonial = function(options) {
		var options = $.extend(options, options);
		return this.each(function(){
			var $this = $(this);
			$.fn.creativeSignalTestimonial.scrollDown($this);	
		});
	};
	
	$.fn.creativeSignalTestimonial.scrollDown = function(el) {
		var settings = { 
				direction: "down", 
				step: 40, 
				scroll: true, 
				onEdge: function (edge) { 
					if (edge.y == "bottom")
					{
						setTimeout(function(){
							$.fn.creativeSignalTestimonial.scrollUp(el);
						}, 3000);
					}
				} 
			};
		el.autoscroll(settings);
	};
	
	$.fn.creativeSignalTestimonial.scrollUp = function(el){
		var settings = { 
				direction: "up", 
				step: 40, 
				scroll: true,    
				onEdge: function (edge) { 
					if (edge.y == "top")
					{
						setTimeout(function(){
							$.fn.creativeSignalTestimonial.scrollDown(el);
						}, 3000);
					}
				} 
			};
		el.autoscroll(settings);
	};	
})(jQuery);