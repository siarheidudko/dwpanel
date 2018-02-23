<?php
/*
Dudko Web Panel v2.5.0
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

$this_v = '2.5.0'; 
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
  <script src="js/ext/aes.js?v=3.1.2"></script> 																	
  <script src="js/ext/react.js?v=16.2.0"></script>
  <script src="js/ext/react-dom.js?v=16.2.0"></script>
  <script src="js/ext/EventEmitter.min.js?v=5.2.4"></script>													
  <script src="js/ext/firebase.js?v=4.2.0"></script> 			    													
  <script src="js/firebase-controller.js?v=<?php echo $this_v; ?>"></script> 															
  <script src="js/reloaderSSL.js?v=<?php echo $this_v; ?>"></script>																	
  <script src="js/ext/babel.min.js?v=6.26.0"></script> 															
  <script type="text/babel" src="js/dwpanel.js?v=<?php echo $this_v; ?>"></script> 												
</body>
</html>
