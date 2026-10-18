<?php

namespace App\Support;

final class SpanishLabel
{
    public static function companyStatus(?string $status): string
    {
        return match ($status) {
            'active' => 'Activa',
            'inactive' => 'Inactiva',
            'blocked' => 'Bloqueada',
            default => self::fallback($status),
        };
    }

    public static function subscriptionStatus(?string $status): string
    {
        return match ($status) {
            'active' => 'Activa',
            'suspended' => 'Suspendida',
            'cancelled' => 'Cancelada',
            'expired' => 'Vencida',
            default => self::fallback($status),
        };
    }

    public static function paymentStatus(?string $status): string
    {
        return match ($status) {
            'paid' => 'Pagado',
            'pending' => 'Pendiente',
            'overdue' => 'Vencido',
            'void' => 'Anulado',
            'waived' => 'Exonerado',
            default => self::fallback($status),
        };
    }

    public static function provisioningStatus(?string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'running' => 'En proceso',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            default => self::fallback($status),
        };
    }

    public static function billingPeriod(?string $period): string
    {
        return match ($period) {
            'monthly' => 'Mensual',
            'quarterly' => 'Trimestral',
            'annual' => 'Anual',
            'one_time' => 'Único',
            default => self::fallback($period),
        };
    }

    public static function service(?string $service): string
    {
        return match ($service) {
            'pos' => 'POS',
            'ecommerce' => 'E-commerce',
            'restaurant' => 'Restaurante',
            'optic' => 'Óptica',
            default => self::fallback($service),
        };
    }

    private static function fallback(?string $value): string
    {
        return $value ? ucfirst(str_replace('_', ' ', $value)) : '—';
    }
}
