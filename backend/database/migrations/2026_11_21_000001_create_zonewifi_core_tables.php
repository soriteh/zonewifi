<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('companies', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->string('email')->nullable();
            $t->string('phone',40)->nullable(); $t->string('country',2)->default('GN'); $t->char('currency',3)->default('GNF');
            $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->nullable()->constrained()->nullOnDelete(); $t->string('name');
            $t->string('email')->unique(); $t->timestamp('email_verified_at')->nullable(); $t->string('password');
            $t->string('role',40)->default('operator'); $t->boolean('is_active')->default(true); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('routers', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained()->cascadeOnDelete(); $t->string('name'); $t->string('host');
            $t->unsignedSmallInteger('api_port')->default(8728); $t->string('username'); $t->text('password_encrypted');
            $t->string('routeros_version')->nullable(); $t->string('status',24)->default('pending'); $t->timestamp('last_seen_at')->nullable();
            $t->timestamps(); $t->unique(['company_id','name']);
        });
        Schema::create('wifi_plans', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained()->cascadeOnDelete(); $t->string('name');
            $t->unsignedInteger('duration_minutes'); $t->unsignedBigInteger('price'); $t->string('currency',3)->default('GNF');
            $t->string('router_profile')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained()->cascadeOnDelete(); $t->string('name');
            $t->string('phone',40)->nullable(); $t->string('email')->nullable(); $t->string('external_reference')->nullable();
            $t->timestamps(); $t->index(['company_id','phone']);
        });
        Schema::create('wifi_tickets', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('wifi_plan_id')->constrained()->restrictOnDelete(); $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('username')->unique(); $t->text('password_encrypted'); $t->string('status',24)->default('created');
            $t->timestamp('activated_at')->nullable(); $t->timestamp('expires_at')->nullable(); $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('wifi_ticket_id')->nullable()->constrained()->nullOnDelete(); $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete(); $t->unsignedBigInteger('amount');
            $t->string('currency',3)->default('GNF'); $t->string('payment_method',24)->default('cash'); $t->string('status',24)->default('paid');
            $t->timestamp('sold_at'); $t->timestamps(); $t->index(['company_id','sold_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('sales'); Schema::dropIfExists('wifi_tickets'); Schema::dropIfExists('customers');
        Schema::dropIfExists('wifi_plans'); Schema::dropIfExists('routers'); Schema::dropIfExists('users'); Schema::dropIfExists('companies');
    }
};
