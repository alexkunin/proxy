<?php
/** App $this */
?>
<!doctype html>
<html>
    <head>
        <title><?= $this->getDockerNetworkName() ?></title>
    </head>
    <body>
        <h1><?= $this->getDockerNetworkName()?></h1>

        <h2>SOCKS Proxy</h2>

        <p>
            SOCKS5 proxy is running here: <samp><?= $this->getSocksHost() ?></samp>.
        </p>

        <p>
            Proxy autoconfiguration script can be found here:
            <a href="<?= htmlspecialchars($this->generateProxyConfigurationUrl()) ?>"><?= htmlspecialchars($this->generateProxyConfigurationUrl()) ?></a>.
        </p>
        <pre><?= $this->generateProxyConfigurationScript() ?></pre>

        <p>
            Command line for <samp>curl</samp>:
        </p>

        <pre>curl -x socks5h://<?= $this->getSocksHost() ?> http://&lt;service&gt;.<?= $this->getDockerNetworkName() ?></pre>

        <h2>DNS</h2>

        <p>
            DNS is running here: <samp><?= $this->getDnsHost() ?></samp>.
        </p>
    </body>
</html>