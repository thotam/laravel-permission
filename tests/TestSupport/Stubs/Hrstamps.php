<?php

namespace App\Models\Traits;

/**
 * Stub đơn giản hóa của trait Hrstamps dùng trong môi trường test.
 * Giữ lại interface/method signature cần thiết để model compile được.
 * KHÔNG đăng ký global scope, KHÔNG kết nối listeners — tránh ảnh hưởng query test.
 */
trait Hrstamps
{
    /**
     * Bật/tắt tính năng hrstamping.
     */
    protected bool $hrstamping = true;

    /**
     * Boot trait — không đăng ký scope hay listener trong môi trường test.
     */
    public static function bootHrstamps(): void
    {
        // Stub: không đăng ký HrstampsScope hay listeners
    }

    /**
     * Kiểm tra model có dùng SoftDeletes không.
     */
    public static function usingSoftDeletes(): bool
    {
        static $usingSoftDeletes;

        if (is_null($usingSoftDeletes)) {
            $usingSoftDeletes = in_array(
                'Illuminate\\Database\\Eloquent\\SoftDeletes',
                \class_uses_recursive(get_called_class())
            );
        }

        return $usingSoftDeletes;
    }

    // --- Column getters ---

    /**
     * Tên cột created_by.
     */
    public function getCreatedByColumn(): string
    {
        return defined(static::class.'::CREATED_BY') ? constant(static::class.'::CREATED_BY') : 'created_by';
    }

    /**
     * Tên cột updated_by.
     */
    public function getUpdatedByColumn(): string
    {
        return defined(static::class.'::UPDATED_BY') ? constant(static::class.'::UPDATED_BY') : 'updated_by';
    }

    /**
     * Tên cột deleted_by.
     */
    public function getDeletedByColumn(): string
    {
        return defined(static::class.'::DELETED_BY') ? constant(static::class.'::DELETED_BY') : 'deleted_by';
    }

    // --- Toggle ---

    /**
     * Kiểm tra hrstamping có bật không.
     */
    public function isHrstamping(): bool
    {
        return $this->hrstamping;
    }

    /**
     * Tắt hrstamping.
     */
    public function stopHrstamping(): void
    {
        $this->hrstamping = false;
    }

    /**
     * Bật hrstamping.
     */
    public function startHrstamping(): void
    {
        $this->hrstamping = true;
    }
}
