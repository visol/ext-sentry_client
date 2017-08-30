<?php
namespace Iresults\SentryClient\Log\Writer;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use Iresults\SentryClient\ClientProvider;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;

/**
 * Log writer that sends the log records to a sentry server
 */
class SentryWriter extends AbstractWriter
{
    protected $sentryLogLevelMap = [
        LogLevel::EMERGENCY => \Raven_Client::FATAL,
        LogLevel::ALERT => \Raven_Client::FATAL,
        LogLevel::CRITICAL => \Raven_Client::ERROR,
        LogLevel::ERROR => \Raven_Client::ERROR,
        LogLevel::WARNING => \Raven_Client::WARNING,
        LogLevel::NOTICE => \Raven_Client::WARNING,
        LogLevel::INFO => \Raven_Client::INFO,
        LogLevel::DEBUG => \Raven_Client::DEBUG,
    ];

    /**
     * Writes the log record
     *
     * @param LogRecord $record Log record
     * @return \TYPO3\CMS\Core\Log\Writer\WriterInterface $this
     * @throws \RuntimeException
     */
    public function writeLog(LogRecord $record)
    {
        $data = '';
        $recordData = $record->getData();
        if (!empty($recordData)) {
            // According to PSR3 the exception-key may hold an \Exception
            // Since json_encode() does not encode an exception, we run the _toString() here
            if (isset($recordData['exception']) && $recordData['exception'] instanceof \Exception) {
                $recordData['exception'] = (string)$recordData['exception'];
            }
            $data = '- ' . json_encode($recordData);
        }

        $options = [
            'extra' => [
                'request_id' => $record->getRequestId(),
                'time_micro' => $record->getCreated(),
                'component' => $record->getComponent(),
                'data' => $data,
            ],
            'level' => $this->sentryLogLevelMap[$record->getLevel()],
        ];

        ClientProvider::captureMessage($record->getMessage(), [], $options, true);

        return $this;
    }

}
