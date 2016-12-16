<?php
namespace Iresults\SentryClient;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClientProvider
{
    /**
     * @param null $dsn
     * @return null|\Raven_Client
     */
    public static function createClient($dsn = null)
    {
        if (static::loadLibrary()) {
            if (!$dsn) {
                $dsn = static::getDsn();
            }
            $client = new \Raven_Client($dsn);
            $client->user_context(static::getUserContext());
            $client->tags_context(static::getTagsContext());

            $errorHandler = new \Raven_ErrorHandler($client, true, static::getErrorMask());
            $errorHandler->registerExceptionHandler();
            $errorHandler->registerShutdownFunction();

            return $client;
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
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client'])) {
            $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);
            if ($configuration && isset($configuration['dsn'])) {
                return trim($configuration['dsn']);
            }
        }

        return '';
    }

    /**
     * @return int
     */
    private static function getErrorMask()
    {
        return intval($GLOBALS['TYPO3_CONF_VARS']['SYS']['errorHandlerErrors']);
    }

    /**
     * @return array
     */
    private static function getUserContext()
    {
        $userContext = array();
        switch (TYPO3_MODE) {
            case 'FE':
                if (isset($GLOBALS['TSFE']) && $GLOBALS['TSFE']->loginUser === true) {
                    $user = $GLOBALS['TSFE']->fe_user->user;
                    if (isset($user['username'])) {
                        $userContext['username'] = $user['username'];
                    }
                    if (isset($user['email'])) {
                        $userContext['email'] = $user['email'];
                    }
                }
                break;
            case 'BE':
                if (isset($GLOBALS['BE_USER']) && isset($GLOBALS['BE_USER']->user)) {
                    $user = $GLOBALS['BE_USER']->user;
                    if (isset($user['username'])) {
                        $userContext['username'] = $user['username'];
                    }

                    if (isset($user['email'])) {
                        $userContext['email'] = $user['email'];
                    }
                }
                break;
        }

        return $userContext;
    }

    /**
     * @return array
     */
    private static function getTagsContext(): array
    {
        $applicationContext = GeneralUtility::getApplicationContext();

        $context = array(
            'typo3_version'            => TYPO3_version,
            'typo3_mode'               => TYPO3_MODE,
            'php_version'              => phpversion(),
            'application_context_name' => (string)$applicationContext,
            'application_context'      => $applicationContext->isProduction() === true ? 'Production' : 'Development',
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
        if (file_exists($extensionBase . '/vendor/raven/lib/Raven')) {
            require_once($extensionBase . '/vendor/raven/lib/Raven/Autoloader.php');
        } elseif (file_exists($extensionBase . '/vendor/sentry/sentry/lib/Raven/Autoloader.php')) {
            require_once($extensionBase . '/vendor/sentry/sentry/lib/Raven/Autoloader.php');
        }

        return class_exists('Raven_Client');
    }
}
