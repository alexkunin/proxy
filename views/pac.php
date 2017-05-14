<?php
/** App $this */
//header('Content-Type: application/x-ns-proxy-autoconfig', true);
header('Content-Type: text/javascript', true);
?><?= $this->generateProxyConfigurationScript() ?>
