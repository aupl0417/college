<?php
try{
$app = 'apicollege';
	system("/usr/lib/node_modules/apidoc/bin/apidoc -v -i  /home/web/app/$app/model/ -o /home/web/apidoc/$app -t /usr/lib/node_modules/apidoc/template_$app");
}catch(Exception $e){
	echo $e->getMessage();
}
?>
