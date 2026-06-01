<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CleanupExamCommand extends Command
{
    protected $signature = 'exam:cleanup';

    protected $description = 'Возвращает проект в исходное состояние, удаляя сгенерированные файлы демоэкзамена';

    public function handle(): int
    {
        if (!$this->confirm('Проект будет возвращён в исходное состояние. Все сгенерированные файлы будут удалены, база данных сброшена. Продолжить?')) {
            $this->info('Отменено.');
            return 0;
        }

        $this->info('Очистка проекта...');

        $this->removeGeneratedDirectories();
        $this->removeGeneratedFiles();
        $this->restoreOriginalFiles();

        if ($this->confirm('Запустить migrate:fresh для сброса базы данных?', true)) {
            Artisan::call('migrate:fresh');
            $this->info(Artisan::output());
        }

        $this->info('Проект возвращён в исходное состояние!');

        return 0;
    }

    private function removeGeneratedDirectories(): void
    {
        $paths = [
            app_path('Filament/Pages/Auth'),
            app_path('Filament/Resources'),
        ];

        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
                $this->line("  <info>removed:</info> " . str_replace(base_path(), '', $path));
            }
        }
    }

    private function removeGeneratedFiles(): void
    {
        $patterns = [
            database_path('migrations/2025_01_01_*.php'),
        ];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) as $file) {
                File::delete($file);
                $this->line("  <info>removed:</info> " . str_replace(base_path(), '', $file));
            }
        }

        $seederPath = database_path('seeders/AdminSeeder.php');
        if (File::exists($seederPath)) {
            File::delete($seederPath);
            $this->line("  <info>removed:</info> " . str_replace(base_path(), '', $seederPath));
        }
    }

    private function restoreOriginalFiles(): void
    {
        $this->putFile(app_path('Models/User.php'), $this->getOriginalUserModel());
        $this->putFile(app_path('Providers/Filament/AdminPanelProvider.php'), $this->getOriginalAdminPanelProvider());
        $this->putFile(database_path('migrations/0001_01_01_000000_create_users_table.php'), $this->getOriginalUsersMigration());
        $this->putFile(database_path('seeders/DatabaseSeeder.php'), $this->getOriginalDatabaseSeeder());
    }

    private function putFile(string $path, string $content): void
    {
        File::put($path, $content);
        $this->line("  <info>restored:</info> " . str_replace(base_path(), '', $path));
    }

    private function getOriginalUserModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
PHP;
    }

    private function getOriginalAdminPanelProvider(): string
    {
        return <<<'PHP'
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
PHP;
    }

    private function getOriginalUsersMigration(): string
    {
        return <<<'PHP'
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
PHP;
    }

    private function getOriginalDatabaseSeeder(): string
    {
        return <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
PHP;
    }
}
