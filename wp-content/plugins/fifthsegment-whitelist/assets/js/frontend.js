// jQuery(document).ready(function(){});
jQuery(function() {
	var barwrap = document.createElement("div");
	jQuery(barwrap).attr("id", "whitelistbarwrap");
	var bar = document.createElement("div"); 
	jQuery(bar).attr("class", "whitelistbar");
	// jQuery(bar).attr("class", "");
	var text = ' <span id="whitelistok"><a href="'+ whitelist_logout_link +'">Logout</a></span>';
	jQuery(bar).append(text);
	jQuery(barwrap).append(bar);
	jQuery("html").append(barwrap);   

  setTimeout(function() {
    return jQuery(".whitelistbar").animate({
      height: "toggle"
    }, "slow")
  }, 150);
  
});