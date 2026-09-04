<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->string('public_sftp_host', 253)->nullable()->after('daemonSFTP');
            $table->unsignedSmallInteger('public_sftp_port')->nullable()->after('public_sftp_host');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn(['public_sftp_host', 'public_sftp_port']);
        });
    }
};
