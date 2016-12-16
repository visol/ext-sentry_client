<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        $confVars = $GLOBALS['TYPO3_CONF_VARS'];
        if (TYPO3_MODE === 'FE' && isset($confVars['EXT']['extConf']['sentry_client'])) {
            $configuration = @unserialize($confVars['EXT']['extConf']['sentry_client']);
            $dsn = (is_array($configuration) && isset($configuration['dsn'])) ? trim($configuration['dsn']) : '';

            if ($dsn === '') {
                return;
            }

            $productionOnly = isset($configuration['productionOnly']) && (bool)$configuration['productionOnly'] === true;
            if (!$productionOnly || \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction()) {
                $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client');
                require_once $extPath . 'Classes/ClientProvider.php';

                \Iresults\SentryClient\ClientProvider::createClient();
            }
        }
    }
);
