//test SSL
var tenyears = new Date(new Date().getTime() + 10 * 365 * 24 * 60 * 60 * 1000);
document.cookie = "SSL=true; path=/; expires=" + tenyears.toUTCString() + ";secure";
if(getCookie("SSL") != "true"){
	this.location = "https://vpn.sergdudko.tk";
}