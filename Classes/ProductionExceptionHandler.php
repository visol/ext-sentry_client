<?php
/**
 * Created by PhpStorm.
 * User: cod
 * Date: 16.12.16
 * Time: 14:15
 */

namespace Iresults\SentryClient;


class ProductionExceptionHandler extends \TYPO3\CMS\Core\Error\ProductionExceptionHandler
{
    /**
     * Displays the given exception
     *
     * @param \Exception|\Throwable $exception The exception(PHP 5.x) or throwable(PHP >= 7.0) object.
     *
     * @throws \Exception
     */
    public function handleException(\Throwable $exception)
    {
        ClientProvider::captureException($exception);

        parent::handleException($exception);
    }
}
