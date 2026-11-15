<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE expense_notifications MODIFY channel ENUM('in_app', 'mail', 'whatsapp') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("DELETE FROM expense_notifications WHERE channel = 'in_app'");
            DB::statement("ALTER TABLE expense_notifications MODIFY channel ENUM('mail', 'whatsapp') NOT NULL");
        }
    }
};
