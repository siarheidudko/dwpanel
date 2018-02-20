/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 20017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

//test SSL
var tenyears = new Date(new Date().getTime() + 10 * 365 * 24 * 60 * 60 * 1000);
document.cookie = "SSL=true; path=/; expires=" + tenyears.toUTCString() + ";secure";
if(getCookie("SSL") != "true"){
	this.location = "https://"+this.location.hostname;
}