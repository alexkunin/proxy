<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

class App
{
    /**
     * @var array
     */
    private $SERVER;

    /**
     * @var string|null
     */
    private $cachedDockerNetworkName = null;

    /**
     * App constructor.
     * @param array $SERVER
     */
    public function __construct(array $SERVER)
    {
        $this->SERVER = $SERVER;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    private function getServerValue($key, $default = '')
    {
        return array_key_exists($key, $this->SERVER) ? (string)$this->SERVER[$key] : $default;
    }

    /**
     * @param string $key
     * @return string
     * @throws Exception
     */
    private function getRequiredServerValue($key)
    {
        if (!array_key_exists($key, $this->SERVER)) {
            throw new Exception('Required value $_SERVER[' . $key . '] is missing');
        }

        return (string)$_SERVER[$key];
    }

    /**
     * @return string
     */
    private function getServerProtocol()
    {
        return $this->getServerValue('SERVER_PROTOCOL', 'HTTP/1.1');
    }

    /**
     * @return string
     */
    private function getScheme()
    {
        return strtolower($this->getServerValue('HTTPS', 'off')) !== 'off' ? 'https' : 'http';
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getHttpHost()
    {
        return $this->getRequiredServerValue('HTTP_HOST');
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getRequestUri()
    {
        return $this->getRequiredServerValue('REQUEST_URI');
    }

    /**
     * @return string
     */
    private function getDockerNetworkName()
    {
        if ($this->cachedDockerNetworkName === null) {
            $hostname = trim(`hostname`);
            $ip = trim(`dig +short a $hostname`);
            $arpa = join('.', array_reverse(explode('.', $ip))) . '.in-addr.arpa';
            $fqdn = trim(`dig +short ptr $arpa`);
            $this->cachedDockerNetworkName = join('.', array_slice(explode('.', trim($fqdn, '.')), 1));
        }

        return $this->cachedDockerNetworkName;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getSocksHost()
    {
        return preg_replace('/:.*$/', '', $this->getHttpHost()) . ':1080';
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getDnsHost()
    {
        return preg_replace('/:.*$/', '', $this->getHttpHost()) . ':53';
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateProxyConfigurationUrl()
    {
        return $this->getScheme() . '://' . $this->getHttpHost() . '/' . $this->getDockerNetworkName() . '.' . md5($this->generateProxyConfigurationScript()) . '.pac';
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateProxyConfigurationScript()
    {
        $network = $this->getDockerNetworkName();
        $socksHost = $this->getSocksHost();

        return join(
            PHP_EOL,
            [
                'function FindProxyForURL(url, host) {',
                '    if (shExpMatch(host, "*.' . $network . '")) {',
                '        return "SOCKS ' . $socksHost . '";',
                '    } else {',
                '        return "DIRECT";',
                '    }',
                '}',
            ]
        );
    }

    /**
     * @param int $status
     */
    private function outputHttpStatus($status)
    {
        static $errors = [
            400 => 'Bad Request',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ];

        $serverProtocol = $this->getServerProtocol();

        $error = array_key_exists($status, $errors) ? $errors[$status] : 'Unknown';
        header("$serverProtocol $status $error", true, $status);
        echo '<h1>', $status, ' ', $error, '</h1>', PHP_EOL;
    }

    /**
     *
     */
    public function run()
    {
        try {
            global $serverProtocol;

            $serverProtocol = $this->getServerProtocol();

            if (preg_match('/^\\/$/', $this->getRequestUri())) {
                require 'views/main.php';
            } elseif (preg_match('/^\\/.+\\.pac$/', $this->getRequestUri())) {
                require 'views/pac.php';
            } else {
                $this->outputHttpStatus(404);
            }
        } catch (Exception $e) {
            $this->outputHttpStatus(500);
            echo '<p>', get_class($e), ': ', $e->getMessage(), '</p>', PHP_EOL;
        }
    }
}

$app = new App($_SERVER);
$app->run();