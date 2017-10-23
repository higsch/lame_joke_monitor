// Absolutely useless nonsense viewing script by MS
var previousJoke = '';
jQuery(document).ready(function() {
	getJoke();
	// update every 10 min
	setInterval(getJoke, 10 * 60 * 1000);
});

// Assign joke html
function getJoke() {
	jQuery.get("fetch_mails.php", function(joke) {
		if (previousJoke !== joke) {
			jQuery("#lame_joke").css('opacity', '0');
		  	jQuery("#lame_joke").html(joke).animate({opacity: 1}, 500);
			previousJoke = joke;
			//alert(joke);
		}
	});
}
