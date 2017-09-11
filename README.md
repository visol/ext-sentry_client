Sentry Client for TYPO3
=======================

This is a TYPO3 Extension for exception logging with sentry, see http://www.getsentry.com

It's based on https://github.com/getsentry/sentry-php


Installation
------------

1. Clone the repository    
    ```bash
    git clone https://github.com/iresults/sentry_client.git
    ```
    
2. Install the Sentry Library (e.g. with [CunddComposer](https://github.com/cundd/CunddComposer))

3. Register the handlers in the `Install Tool` or `typo3conf/AdditionalConfiguration.php`
    ```php
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = 'Iresults\\SentryClient\\DebugExceptionHandler';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = 'Iresults\\SentryClient\\ProductionExceptionHandler';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['errorHandler'] = 'Iresults\\SentryClient\\ErrorHandler';
    ```


Configuration
-------------

Set the dsn (e.g. `http://public_key:secret_key@your-sentry-server.com/project-id`) in the `Extension Manager`.

Alternatively, we can also define - and override - the DSN by setting
a global variable environment. This could be useful when using different Application Context.

```
SetEnv SENTRY_DSN http://public_key:secret_key@your-sentry-server.com/project-id
```

The same could be achieved via PHP configuration. Consider the following lines:

 ```php
if ((string)\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext() === 'Development/Foo') {
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Sentry']['dsn'] = 'http://public_key:secret_key@your-sentry-server.com/project-id';
}
```

To retrieve the DNS setting, the following priorities will be applied:

1. The global environment variable,
2. PHP configuration,
3. Finally, the value from the Extension Manager. 

Development
-----------

Development of this fork happens on https://github.com/iresults/sentry_client

The original version can be found on https://github.com/networkteam/sentry_client