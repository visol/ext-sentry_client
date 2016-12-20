<?php
namespace Iresults\SentryClient;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClientProvider
{
    /**
     * @var \Raven_Client
     */
    private static $sharedClient;

    /**
     * Returns the shared Raven client
     *
     * @return null|\Raven_Client
     */
    public static function getSharedClient()
    {
        if (!static::isEnabled()) {
            return null;
        }
        if (!static::$sharedClient) {
            static::$sharedClient = static::createClient();
        }

        return static::$sharedClient;
    }

    /**
     * Returns if a shared Raven client was already created
     *
     * @return bool
     */
    public static function hasSharedClient()
    {
        return null !== static::$sharedClient;
    }

    /**
     * Returns if the error handling is enabled
     *
     * @return bool
     */
    public static function isEnabled()
    {
        if (TYPO3_MODE !== 'FE') {
            return false;
        }

        $configuration = self::getConfiguration();

        if ('' === static::getDsn()) {
            return false;
        }

        $productionOnly = isset($configuration['productionOnly']) && (bool)$configuration['productionOnly'] === true;
        if (!$productionOnly || GeneralUtility::getApplicationContext()->isProduction()) {
            return true;
        }

        return false;
    }

    /**
     * @return null|\Raven_Client
     */
    private static function createClient()
    {
        if (static::loadLibrary()) {
            $client = new \Raven_Client(static::getDsn(), self::getClientOptions());
            $client->user_context(static::getUserContext());
            $client->tags_context(static::getTagsContext());

            return $client;
        } else {
            static::log('Could not load library', GeneralUtility::SYSLOG_SEVERITY_WARNING);
        }

        return null;
    }

    /**
     * Read the DSN from the configuration
     *
     * @return string
     */
    public static function getDsn()
    {
        if (isset($_SERVER['SENTRY_DSN']) && trim($_SERVER['SENTRY_DSN'])) {
            return trim($_SERVER['SENTRY_DSN']);
        }

        $configuration = self::getConfiguration();

        return (is_array($configuration) && isset($configuration['dsn'])) ? trim($configuration['dsn']) : '';
    }

    /**
     * @return int
     */
    private static function getErrorMask()
    {
        return intval($GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors']);
    }

    /**
     * @return array
     */
    private static function getUserContext()
    {
        if (TYPO3_MODE === 'BE') {
            return self::getBackendUserInformation();
        } elseif (TYPO3_MODE === 'FE') {
            return self::getFrontendUserInformation();
        }

        return [];
    }

    /**
     * @return array
     */
    private static function getTagsContext()
    {
        $applicationContext = GeneralUtility::getApplicationContext();
        $backendUser = static::getBackendUserInformation();

        $context = array(
            'typo3_version'            => TYPO3_version,
            'typo3_mode'               => TYPO3_MODE,
            'php_version'              => phpversion(),
            'application_context_name' => (string)$applicationContext,
            'application_context'      => $applicationContext->isProduction() === true ? 'Production' : 'Development',
            'backend_user'             => $backendUser ? ($backendUser['username'] . ' <' . $backendUser['email'] . '>') : 'none',
        );

        return $context;
    }

    /**
     * Load the Raven library
     */
    private static function loadLibrary()
    {
        $extensionBase = __DIR__ . '/../';
        $extensionBase = realpath($extensionBase) ?: $extensionBase;

        if (class_exists('Raven_Client')) {
            return true;
        }

        if (ExtensionManagementUtility::isLoaded('cundd_composer')) {
            \Cundd\CunddComposer\Autoloader::register();
        }
        if (file_exists($extensionBase . '/vendor/sentry/sentry/lib/Raven/Autoloader.php')) {
            require_once($extensionBase . '/vendor/sentry/sentry/lib/Raven/Autoloader.php');
        } elseif (file_exists($extensionBase . '/vendor/raven/lib/Raven')) {
            require_once($extensionBase . '/vendor/raven/lib/Raven/Autoloader.php');
        }

        return class_exists('Raven_Client');
    }

    /**
     * @return array
     */
    private static function getClientOptions()
    {
        $clientOptions = array(
            'error_types'                         => static::getErrorMask(),
            'install_default_breadcrumb_handlers' => false,
        );
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer'])) {
            $clientOptions['http_proxy'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer'];
        }

        return $clientOptions;
    }

    /**
     * @param string $message
     * @param int    $severity
     */
    private static function log($message, $severity)
    {
        GeneralUtility::sysLog($message, 'sentry_client', $severity);
    }

    /**
     * @return array
     */
    private static function getConfiguration()
    {
        $confVars = $GLOBALS['TYPO3_CONF_VARS'];

        if (isset($confVars['EXT'])
            && isset($confVars['EXT']['extConf'])
            && isset($confVars['EXT']['extConf']['sentry_client'])
        ) {
            return (array)@unserialize($confVars['EXT']['extConf']['sentry_client']);
        }

        return [];
    }

    /**
     * @return array
     */
    private static function getBackendUserInformation()
    {
        if (isset($GLOBALS['BE_USER']) && isset($GLOBALS['BE_USER']->user)) {
            $user = $GLOBALS['BE_USER']->user;

            return [
                'username' => isset($user['username']) ? $user['username'] : '',
                'email'    => isset($user['email']) ? $user['email'] : '',
            ];
        }

        return [];
    }

    /**
     * @return array
     */
    private static function getFrontendUserInformation()
    {
        if (isset($GLOBALS['TSFE']) && $GLOBALS['TSFE']->loginUser === true) {
            $user = $GLOBALS['TSFE']->fe_user->user;

            return [
                'username' => isset($user['username']) ? isset($user['username']) : '',
                'email'    => isset($user['email']) ? isset($user['email']) : '',
            ];
        }

        return [];
    }
}
