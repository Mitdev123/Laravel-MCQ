
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_attempts')) {
            Schema::create('exam_attempts', function (Blueprint $table) {
                $table->id();

                $table->foreignId('exam_id')
                    ->constrained()->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained()->cascadeOnDelete();

                $table->timestamp('started_at');
                $table->timestamp('expires_at');
                $table->timestamp('submitted_at')->nullable();

                $table->decimal('score', 10, 2)->default(0);
                $table->decimal('total_marks', 10, 2)->default(0);

                $table->unsignedInteger('correct_answers')->default(0);
                $table->unsignedInteger('wrong_answers')->default(0);
                $table->unsignedInteger('unanswered')->default(0);

                $table->enum('status', [
                    'in_progress',
                    'submitted',
                    'expired'
                ])->default('in_progress');

                $table->timestamps();
                $table->index(['user_id', 'exam_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
