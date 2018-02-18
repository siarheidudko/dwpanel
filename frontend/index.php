<?php 
$this_v = '2.1.1'; 
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dudko Web Panel</title>
  <link rel="stylesheet" href="css/AppDesctop.css?v=<?php echo $this_v; ?>">
  <?php 
	include(__DIR__ . '/detect.php');
	if(is_mobile()){echo '<link rel="stylesheet" href="css/AppMobile.css?v='.$this_v.'">';}
  ?>
  <meta charset=utf-8 />
</head>
<body>
  <div id="DwpanelBody" />
  <script src="js/ext/aes.js?v=<?php echo $this_v; ?>"></script> 																			<!-- крипто-модуль  -->
  <script src="js/ext/react.js?v=<?php echo $this_v; ?>"></script> 																			<!-- ядро  -->
  <script src="js/ext/react-dom.js?v=<?php echo $this_v; ?>"></script> 																		<!-- элементы  -->
  <script src="js/ext/EventEmitter.min.js?v=<?php echo $this_v; ?>"></script>															    <!-- глобальная система событий -->
  <script src="js/ext/firebase.js?v=<?php echo $this_v; ?>"></script> 			    														<!-- firebase  --> 
  <script src="js/firebase-controller.js?v=<?php echo $this_v; ?>"></script> 																<!-- модуль работы с firebase  -->
  <script src="js/reloaderSSL.js?v=<?php echo $this_v; ?>"></script>																			<!-- переадресация на https  -->
  <script src="js/ext/browser.min.js?v=<?php echo $this_v; ?>"></script> 																    <!-- парсер html  -->
  <script type="text/babel" src="js/dwpanel.js?v=<?php echo $this_v; ?>"></script> 															<!-- приложение  -->
</body>
</html>
