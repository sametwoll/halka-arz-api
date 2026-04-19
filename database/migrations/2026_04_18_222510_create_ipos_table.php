<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ipos', function (Blueprint $table) {
            $table->id();
            $table->string('company_name'); // Şirket Adı
            $table->string('stock_code')->unique(); // Hisse Kodu (Örn: REEDR)
            $table->decimal('price', 10, 2); // Hisse Fiyatı
            $table->bigInteger('total_lots'); // Toplam Dağıtılacak Lot
            $table->date('start_date')->nullable(); // Talep Toplama Başlangıç
            $table->date('end_date')->nullable(); // Talep Toplama Bitiş
            $table->boolean('is_participation_index')->default(false); // Katılım Endeksine Uygunluk
            $table->string('status')->default('upcoming'); // Durum: upcoming, active, trading
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipos');
    }
};
