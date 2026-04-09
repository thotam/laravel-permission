<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Stub đơn giản hóa của trait Systems dùng trong môi trường test.
 * Giữ lại interface/method signature cần thiết để model compile được.
 * KHÔNG đăng ký global scope, KHÔNG gọi Creating listener — tránh tự động set
 * cột `system` và lọc query khi test.
 */
trait Systems
{
    /**
     * Bật/tắt tính năng system filtering.
     */
    protected bool $useSystem = true;

    /**
     * Boot trait — không đăng ký scope hay listener trong môi trường test.
     */
    public static function bootSystems(): void
    {
        // Stub: không đăng ký SystemsScope hay creating listener
    }

    /**
     * Kiểm tra tính năng system filtering có bật không.
     */
    public function isUseSystem(): bool
    {
        return $this->useSystem;
    }

    /**
     * Tắt system filtering.
     */
    public function stopUseSystem(): void
    {
        $this->useSystem = false;
    }

    /**
     * Bật system filtering.
     */
    public function startUseSystem(): void
    {
        $this->useSystem = true;
    }

    /**
     * Tên cột system.
     */
    public function getSystemsColumn(): string
    {
        return 'system';
    }

    /**
     * Tên cột system đầy đủ (qualified).
     */
    public function getQualifiedSystemsColumn(): string
    {
        return $this->qualifyColumn($this->getSystemsColumn());
    }

    /**
     * Scope lọc theo system — stub không làm gì, trả về query gốc.
     */
    public function scopeSystem(Builder $query, $system = null): Builder
    {
        // Stub: không áp dụng filter để tránh ảnh hưởng test
        return $query;
    }
}
