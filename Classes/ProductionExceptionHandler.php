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
     * @TODO #72293 This will change to \Throwable only if we are >= PHP7.0 only
     *
     * @throws \Exception
     */
    public function handleException($exception)
    {
        $client = ClientProvider::createClient();
        if ($client) {
            $client->captureException($exception, [
                'c' => __CLASS__
            ]);
        }
    }


}
