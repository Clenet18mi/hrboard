<?php

use App\Enums\AccountStateType;
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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone')->nullable();
            $table->date('birthDate')->nullable();
            $table->date('hireDate');
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('jobTitle');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->enum('contractType', array_column(\App\Enums\ContractType::cases(), 'value'));
            $table->float('grossSalary')->nullable();
            $table->enum('status', array_column(\App\Enums\AccountStateType::cases(), 'value'))->default(AccountStateType::ACTIVE);
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
