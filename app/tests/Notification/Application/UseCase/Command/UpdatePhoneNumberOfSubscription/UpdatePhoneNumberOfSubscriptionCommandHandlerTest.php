<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\UpdatePhoneNumberOfSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Application\UseCase\Command\UpdatePhoneNumberOfSubscription\UpdatePhoneNumberOfSubscriptionCommand;
use App\Notification\Application\UseCase\Command\UpdatePhoneNumberOfSubscription\UpdatePhoneNumberOfSubscriptionCommandHandler;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Repository\PhoneRepositoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Notification\Domain\Service\PhoneNumberOrganizer;
use App\Shared\Infrastructure\Exception\AppException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UpdatePhoneNumberOfSubscriptionCommandHandlerTest extends TestCase
{
    private UpdatePhoneNumberOfSubscriptionCommandHandler $handler;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionAccessControl $accessControl;
    private PhoneNumberOrganizer $numberOrganizer;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->accessControl = $this->createMock(SubscriptionAccessControl::class);

        $phoneRepository = $this->createMock(PhoneRepositoryInterface::class);
        $this->numberOrganizer = new PhoneNumberOrganizer($phoneRepository);

        $this->handler = new UpdatePhoneNumberOfSubscriptionCommandHandler(
            $this->subscriptionRepository,
            $this->numberOrganizer,
            $this->accessControl
        );
    }

    public function testSuccessfullyUpdatesPhoneNumbers(): void
    {
        $subscriptionId = 'sub-123';
        $ownerId = 'user-456';
        $phoneNumbers = ['79111111111', '79222222222'];
        $command = new UpdatePhoneNumberOfSubscriptionCommand($subscriptionId, $ownerId, ...$phoneNumbers);

        $subscription = $this->createMock(Subscription::class);
        $subscription->phoneNumbers = new ArrayCollection();

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with($subscriptionId)
            ->willReturn($subscription);

        $this->accessControl->expects($this->once())
            ->method('canUpdateSubscription')
            ->with($subscription, $ownerId)
            ->willReturn(true);

        $subscription->expects($this->exactly(2))
            ->method('addPhoneNumber')
            ->with($this->callback(function ($phoneNumber) {
                return $phoneNumber instanceof \App\Notification\Domain\Aggregate\PhoneNumber;
            }));

        $this->subscriptionRepository->expects($this->once())
            ->method('save')
            ->with($subscription);

        ($this->handler)($command);
    }

    public function testThrowsWhenSubscriptionNotFound(): void
    {
        $command = new UpdatePhoneNumberOfSubscriptionCommand('non-existent-sub', 'user-456', ...['79111111111']);

        $this->subscriptionRepository->method('findById')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription not found');

        ($this->handler)($command);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $subscriptionId = 'sub-123';
        $unauthorizedUser = 'user-789';
        $command = new UpdatePhoneNumberOfSubscriptionCommand($subscriptionId, $unauthorizedUser, ...['79222222222']);

        $subscription = $this->createMock(Subscription::class);
        $this->subscriptionRepository->method('findById')->willReturn($subscription);
        $this->accessControl->method('canUpdateSubscription')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('You are not allowed to do this action.');
        $this->expectExceptionCode(403);

        ($this->handler)($command);
    }

    public function testClearsExistingPhoneNumbers(): void
    {
        $command = new UpdatePhoneNumberOfSubscriptionCommand(
            'sub-123',
            'user-456',
            '791111111111');

        $subscription = $this->createMock(Subscription::class);
        $subscription->phoneNumbers = new ArrayCollection([
            $this->createMockPhoneNumber('79222222222'),
        ]);

        $this->subscriptionRepository->method('findById')->willReturn($subscription);
        $this->accessControl->method('canUpdateSubscription')->willReturn(true);

        // Verify the phoneNumbers collection is cleared before adding new ones
        $subscription->expects($this->once())
            ->method('addPhoneNumber')
            ->willReturnCallback(function () use ($subscription) {
                $this->assertCount(0, $subscription->phoneNumbers);
            });

        ($this->handler)($command);
    }

    private function createMockPhoneNumber(string $number): \App\Notification\Domain\Aggregate\PhoneNumber
    {
        $phoneNumber = $this->createMock(\App\Notification\Domain\Aggregate\PhoneNumber::class);
        $phoneNumber->method('getPhone')
            ->willReturn(new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber($number));
        return $phoneNumber;
    }
}
