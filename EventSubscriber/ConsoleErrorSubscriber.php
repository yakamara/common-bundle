<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsoleErrorSubscriber implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        if (!getenv('SYMFONY_CONSOLE_LOG_EXCEPTION')) {
            return;
        }

        $command = $event->getCommand();
        $error = $event->getError();

        $message = sprintf(
            '%s: %s at %s line %s while running console command `%s`',
            get_class($error),
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
            $command->getName()
        );

        $this->logger->critical($message, ['error' => $error]);
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::ERROR => 'onConsoleError',
        ];
    }
}
