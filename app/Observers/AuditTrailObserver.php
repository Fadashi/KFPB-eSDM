<?php

namespace App\Observers;

use App\Helpers\AuditHelper;
use Illuminate\Database\Eloquent\Model;

class AuditTrailObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        AuditHelper::log(
            'tambah',
            class_basename($model),
            'Pembuatan ' . class_basename($model),
            null,
            $model->getAttributes()
        );
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // nilai sebelum dan sesudah
        $original = $model->getOriginal();
        $changes = $model->getChanges();
        AuditHelper::log(
            'ubah',
            class_basename($model),
            'Perubahan ' . class_basename($model),
            $original,
            $changes
        );
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        AuditHelper::log(
            'hapus',
            class_basename($model),
            'Penghapusan ' . class_basename($model),
            $model->getOriginal(),
            null
        );
    }
} 