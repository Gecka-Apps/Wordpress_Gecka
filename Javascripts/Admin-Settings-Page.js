(function(c){var h="",f=null,g="";styleSelect={init:function(){c(".controls .select_wrapper").each(function(){c(this).prepend("<span>"+c(this).find("select option:selected").text()+"</span>")});c(".select_wrapper select").live("change",function(){c(this).prev("span").html(c(this).find("option:selected").text())});c(".select_wrapper select").bind(c.browser.msie?"click":"change",function(l){c(this).prev("span").html(c(this).find("option:selected").text())})}};function a(l){i(c(this).attr("href").replace(/^#/,""));l.preventDefault()}function j(){return location.hash.replace(/^#\!/,"")}function k(){var l=j();if(l==h){return}h=l;b(l)}function e(){f=setInterval(k,200)}function d(){if(f!=null){clearInterval(f)}f=null}function i(l){d();if(l==""){l=g}location.hash="!"+l;e()}function b(m){var n="div#"+m;var l="li."+m;c("#gk-container .menu li.current").removeClass("current");c("#gk-container .content > div").hide();c("#gk-container .content > "+n).fadeIn("fast");c("#gk-container .menu "+l).addClass("current");c("#gk-container .menu "+l).parents("li").addClass("current")}c(document).ready(function(){styleSelect.init();c("div.updated, div.error").not(".inline").insertBefore(c("div.wrap"));c("div.updated, div.error").addClass("below-h2");c("#gk-container .menu ul li a").click(a);c("#gk-container .content > div").hide();g=c("#gk-container .content > div:first").attr("id");i(j()?j():g)})})(jQuery);