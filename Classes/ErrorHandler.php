<?php
/**
 * Created by PhpStorm.
 * User: cod
 * Date: 20.12.16
 * Time: 11:43
 */

namespace Iresults\SentryClient;


class ErrorHandler extends \TYPO3\CMS\Core\Error\ErrorHandler
{
    /**
     * Handles an error.
     * If the error is registered as exceptionalError it will by converted into an exception, to be handled
     * by the configured exceptionhandler. Additionally the error message is written to the configured logs.
     * If TYPO3_MODE is 'BE' the error message is also added to the flashMessageQueue, in FE the error message
     * is displayed in the admin panel (as TsLog message)
     *
     * @param int    $errorLevel   The error level - one of the E_* constants
     * @param string $errorMessage The error message
     * @param string $errorFile    Name of the file the error occurred in
     * @param int    $errorLine    Line number where the error occurred
     * @return bool
     * @throws \TYPO3\CMS\Core\Error\Exception with the data passed to this method if the error is registered as exceptionalError
     */
    public function handleError($errorLevel, $errorMessage, $errorFile, $errorLine)
    {
        // Do not handle error raised at early parse time
        // and ignore if a shared client already has been created
        if (class_exists('stdClass', false) && !ClientProvider::hasSharedClient()) {
            $this->sendErrorToSentry($errorLevel, $errorMessage, $errorFile, $errorLine);
        }

        return parent::handleError(
            $errorLevel,
            $errorMessage,
            $errorFile,
            $errorLine
        );
    }

    /**
     * @param int    $errorLevel
     * @param string $errorMessage
     * @param string $errorFile
     * @param int    $errorLine
     */
    private function sendErrorToSentry($errorLevel, $errorMessage, $errorFile, $errorLine)
    {
        if ($errorLevel & $this->exceptionalErrors) {
            $errorLevels = [
                E_WARNING           => 'Warning',
                E_NOTICE            => 'Notice',
                E_USER_ERROR        => 'User Error',
                E_USER_WARNING      => 'User Warning',
                E_USER_NOTICE       => 'User Notice',
                E_STRICT            => 'Runtime Notice',
                E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
                E_DEPRECATED        => 'Runtime Deprecation Notice',
            ];
            $message = sprintf(
                'PHP %s: %s in %s line %d',
                $errorLevels[$errorLevel],
                $errorMessage,
                $errorFile,
                $errorLine
            );
            $exception = new \ErrorException($message, $errorLevel);
            ClientProvider::captureException($exception);
        }
    }
}
