    <?php

    use App\Models\Task;
    use App\Models\User;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('deadline');
                $table->boolean('is_completed')->default(0);
                $table->unsignedTinyInteger('progress')->default(0);
                $table->enum('priority',Task::$priorities); 
                // $table->string('priority')
                // $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
                $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
                $table->timestamps(); 
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('tasks');
        }
    };
