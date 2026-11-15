<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_by_email')->default(true);
            $table->boolean('notify_by_whatsapp')->default(false);
            $table->string('whatsapp_number', 20)->nullable();
            $table->unsignedSmallInteger('notify_days_before')->default(3);
            $table->boolean('notify_on_due_date')->default(true);
            $table->boolean('notify_when_overdue')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_by_email',
                'notify_by_whatsapp',
                'whatsapp_number',
                'notify_days_before',
                'notify_on_due_date',
                'notify_when_overdue',
            ]);
        });
    }
};
