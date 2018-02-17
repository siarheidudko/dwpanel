<!DOCTYPE html>
<html>
<head>
  <title>Dudko Web Panel</title>
  <link rel="stylesheet" href="css/AppDesctop.css">
  <?php 
	include(__DIR__ . '/detect.php');
	if(is_mobile()){echo '<link rel="stylesheet" href="css/AppMobile.css">';}
  ?>
  <meta charset=utf-8 />
</head>
<body>
  <div id="DwpanelBody" />
  <script src="js/ext/aes.js"></script> 																			<!-- крипто-модуль  -->
  <script src="js/ext/react.js"></script> 																			<!-- ядро  -->
  <script src="js/ext/react-dom.js"></script> 																		<!-- элементы  -->
  <script src="js/ext/EventEmitter.min.js"></script>															    <!-- глобальная система событий -->
  <script src="js/ext/firebase.js"></script> 			    														<!-- firebase  --> 
  <script src="js/firebase-controller.js"></script> 																<!-- модуль работы с firebase  -->
  <script src="js/reloaderSSL.js"></script>																			<!-- переадресация на https  -->
  <script src="js/ext/browser.min.js"></script> 																    <!-- парсер html  -->
  <script type="text/babel" src="js/dwpanel.js"></script> 															<!-- приложение  -->
</body>
</html>
