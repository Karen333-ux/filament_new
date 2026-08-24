<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_id')->nullable()->unique()->after('id');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->string('avatar_url')->nullable()->after('telegram_username');
        });

        // Telegram hands us an id, a name and a photo — never an email, and
        // never a password. An account that only ever signs in through the
        // widget has nothing to put in either column, so neither can stay
        // required. Postgres allows repeated NULLs under a unique index, so
        // the existing unique constraint on email still holds for real ones.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_id']);
            $table->dropColumn(['telegram_id', 'telegram_username', 'avatar_url']);
        });

        // email and password are deliberately left nullable: rows created by
        // Telegram logins have no value to backfill, so tightening the columns
        // again here would fail on exactly the data this migration allowed.
    }
};
