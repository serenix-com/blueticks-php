<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\ScheduledMessage;
use PHPUnit\Framework\TestCase;

final class ScheduledMessageTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'sched_507f1f77bcf86cd799439011',
            'to' => '+15551234567',
            'text' => 'Reminder: pay invoice',
            'media_url' => null,
            'media_caption' => null,
            'media_filename' => null,
            'media_mime_type' => null,
            'send_at' => '2026-05-15T09:00:00Z',
            'status' => 'scheduled',
            'is_recurring' => true,
            'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=MO',
            'created_at' => '2026-04-23T10:00:00Z',
            'updated_at' => '2026-04-24T11:00:00Z',
        ];
    }

    public function testHappyPath(): void
    {
        $sm = ScheduledMessage::fromArray(self::fixture());
        self::assertSame('sched_507f1f77bcf86cd799439011', $sm->id);
        self::assertSame('+15551234567', $sm->to);
        self::assertSame('Reminder: pay invoice', $sm->text);
        self::assertSame('2026-05-15T09:00:00Z', $sm->send_at);
        self::assertSame('scheduled', $sm->status);
        self::assertTrue($sm->is_recurring);
        self::assertSame('FREQ=WEEKLY;BYDAY=MO', $sm->recurrence_rule);
        self::assertSame('2026-04-23T10:00:00Z', $sm->created_at);
        self::assertSame('2026-04-24T11:00:00Z', $sm->updated_at);
    }

    public function testMissingRequiredThrows(): void
    {
        $raw = self::fixture();
        unset($raw['id']);
        $this->expectException(ValidationError::class);
        ScheduledMessage::fromArray($raw);
    }

    public function testWrongTypeIsRecurringThrows(): void
    {
        $raw = self::fixture();
        $raw['is_recurring'] = 'yes';
        $this->expectException(ValidationError::class);
        ScheduledMessage::fromArray($raw);
    }
}
