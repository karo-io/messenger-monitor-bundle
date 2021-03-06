<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\FailedMessage;

use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRejecter;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class FailedMessageRejecterTest extends TestCase
{
    public function testRejectFailedMessage(): void
    {
        $failureReceiverProvider = $this->createMock(FailureReceiverProvider::class);
        $failedMessageRejecter = new FailedMessageRejecter($failureReceiverProvider);

        $failureReceiverProvider->expects($this->once())
            ->method('getFailureReceiver')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())
            ->method('find')
            ->with('id')
            ->willReturn($envelope = new Envelope(new TestableMessage()));

        $failureReceiver->expects($this->once())->method('reject')->with($envelope);

        $failedMessageRejecter->rejectFailedMessage('id');
    }

    public function testExceptionIfNoMessageForId(): void
    {
        $this->expectException(\RuntimeException::class);

        $failureReceiverProvider = $this->createMock(FailureReceiverProvider::class);
        $failedMessageRejecter = new FailedMessageRejecter($failureReceiverProvider);

        $failureReceiverProvider->expects($this->once())
            ->method('getFailureReceiver')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())->method('find')->with('id')->willReturn(null);

        $failedMessageRejecter->rejectFailedMessage('id');
    }
}
