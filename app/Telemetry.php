<?php

declare(strict_types=1);

namespace App;

/**
 * Minimal telemetry facade.
 *
 * All methods become no-ops when telemetry is disabled or when
 * required libraries are missing. Enable telemetry via
 * `TELEMETRY_ENABLED=true` and installing the necessary packages.
 */
class Telemetry
{
    private static ?bool $enabled = null;

    /**
     * Checks if telemetry is enabled and libraries are available.
     */
    public static function enabled(): bool
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }

        $envEnabled = filter_var($_ENV['TELEMETRY_ENABLED'] ?? false, FILTER_VALIDATE_BOOL);
        $hasLibrary = class_exists('OpenTelemetry\\API\\Globals')
            || class_exists('OpenTelemetry\\API\\Metrics\\MeterProvider')
            || class_exists('OpenTelemetry\\API\\Trace\\TracerProvider');

        self::$enabled = $envEnabled && $hasLibrary;
        return self::$enabled;
    }

    public static function incrementTelegramSent(): void
    {
        if (!self::enabled()) {
            return;
        }
        // integrate with metrics backend
    }

    public static function recordTelegramSendFailure(string $reason): void
    {
        if (!self::enabled()) {
            return;
        }
        // integrate with metrics backend
    }

    public static function setTelegramQueueSize(int $size): void
    {
        if (!self::enabled()) {
            return;
        }
        // gauge update
    }

    public static function setDlqSize(int $size): void
    {
        if (!self::enabled()) {
            return;
        }
        // gauge update
    }

    public static function setGptBreakerState(string $state): void
    {
        if (!self::enabled()) {
            return;
        }
        // gauge update
    }

    public static function observeGptResponseTime(float $seconds): void
    {
        if (!self::enabled()) {
            return;
        }
        // histogram/summary update
    }
}
