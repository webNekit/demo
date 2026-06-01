<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SetupExamCommand extends Command
{
    protected $signature = 'exam:setup';

    protected $description = 'Автоматический генератор окружения для демоэкзамена';

    public function handle(): int
    {
        $variant = $this->ask('Выберите вариант экзамена [2, 3, 4]:');

        if (!in_array($variant, ['2', '3', '4'])) {
            $this->error('Неверный вариант. Выберите 2, 3 или 4.');
            return 1;
        }

        $this->info("Генерация варианта №{$variant}...");

        $this->createDirectories();
        $this->cleanupVariantFiles();
        $this->generateCommonFiles($variant);
        $this->{"generateVariant{$variant}"}();

        if ($this->confirm('Запустить миграции? (migrate:fresh --seed)', true)) {
            Artisan::call('migrate:fresh --seed');
            $this->info(Artisan::output());
        }

        $this->info("Вариант №{$variant} успешно развернут!");

        return 0;
    }

    private function createDirectories(): void
    {
        $filamentPath = app_path('Filament');
        if (File::isDirectory($filamentPath) && !is_writable($filamentPath)) {
            File::deleteDirectory($filamentPath);
        }

        $dirs = [
            app_path('Filament/Pages/Auth'),
            app_path('Filament/Resources'),
            database_path('migrations'),
        ];

        foreach ($dirs as $dir) {
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
    }

    private function cleanupVariantFiles(): void
    {
        $variantMigrations = glob(database_path('migrations/2025_*'));
        foreach ($variantMigrations as $file) {
            File::delete($file);
            $this->line("  <info>cleaned:</info> " . str_replace(base_path(), '', $file));
        }

        $resources = [
            'TestDriveRequestResource',
            'BookCardResource',
            'BookingResource',
            'ConferenceBookingResource',
            'TrainingApplicationResource',
            'BanquetBookingResource',
        ];

        foreach ($resources as $resource) {
            $path = app_path("Filament/Resources/{$resource}.php");
            if (File::exists($path)) {
                File::delete($path);
                $this->line("  <info>cleaned:</info> " . str_replace(base_path(), '', $path));
            }

            $dir = app_path("Filament/Resources/{$resource}");
            if (File::isDirectory($dir)) {
                File::deleteDirectory($dir);
                $this->line("  <info>cleaned:</info> " . str_replace(base_path(), '', $dir) . '/');
            }
        }

        $models = ['TestDriveRequest', 'BookCard', 'Booking', 'ConferenceBooking', 'TrainingApplication', 'BanquetBooking'];
        foreach ($models as $model) {
            $path = app_path("Models/{$model}.php");
            if (File::exists($path)) {
                File::delete($path);
                $this->line("  <info>cleaned:</info> " . str_replace(base_path(), '', $path));
            }
        }
    }

    private function generateCommonFiles(string $variant): void
    {
        $this->putFile(app_path('Filament/Pages/Auth/CustomLogin.php'), $this->getCustomLoginContent($variant));

        $registerContent = $variant === '3'
            ? $this->getCustomRegisterV3Content($variant)
            : $this->getCustomRegisterV2Content($variant);

        $this->putFile(app_path('Filament/Pages/Auth/CustomRegister.php'), $registerContent);
        $this->putFile(app_path('Providers/Filament/AdminPanelProvider.php'), $this->getAdminPanelProviderContent($variant));
        $this->putFile(base_path('routes/web.php'), $this->getRoutesWebContent());
    }

    // ─── Variant 2: Конференции.РФ ─────────────────────────────────────

    private function generateVariant2(): void
    {
        $this->putFile(
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            $this->getUserMigrationV2()
        );

        $this->putFile(app_path('Models/User.php'), $this->getUserModelV2());

        $this->putFile(
            database_path('migrations/2025_01_01_000001_create_conference_bookings_table.php'),
            $this->getConferenceBookingMigration()
        );

        $this->putFile(app_path('Models/ConferenceBooking.php'), $this->getConferenceBookingModel());

        $this->putFile(
            app_path('Filament/Resources/ConferenceBookingResource.php'),
            $this->getConferenceBookingResource()
        );

        $resourcePagesDir = app_path('Filament/Resources/ConferenceBookingResource/Pages');
        if (!File::isDirectory($resourcePagesDir)) {
            File::makeDirectory($resourcePagesDir, 0755, true);
        }

        $this->putFile(
            app_path('Filament/Resources/ConferenceBookingResource/Pages/ListConferenceBookings.php'),
            $this->getListConferenceBookingsPage()
        );

        $this->putFile(
            app_path('Filament/Resources/ConferenceBookingResource/Pages/CreateConferenceBooking.php'),
            $this->getCreateConferenceBookingPage()
        );

        $this->putFile(
            app_path('Filament/Resources/ConferenceBookingResource/Pages/EditConferenceBooking.php'),
            $this->getEditConferenceBookingPage()
        );

        $this->putFile(
            database_path('seeders/AdminSeeder.php'),
            $this->getAdminSeeder('2', 'admin@conference.ru')
        );

        $this->putFile(
            database_path('seeders/DatabaseSeeder.php'),
            $this->getDatabaseSeederWithAdmin()
        );
    }

    // ─── Variant 3: Пассажирам.РФ ──────────────────────────────────────

    private function generateVariant3(): void
    {
        $this->putFile(
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            $this->getUserMigrationV3()
        );

        $this->putFile(app_path('Models/User.php'), $this->getUserModelV3());

        $this->putFile(
            database_path('migrations/2025_01_01_000001_create_training_applications_table.php'),
            $this->getTrainingApplicationMigration()
        );

        $this->putFile(app_path('Models/TrainingApplication.php'), $this->getTrainingApplicationModel());

        $this->putFile(
            app_path('Filament/Resources/TrainingApplicationResource.php'),
            $this->getTrainingApplicationResource()
        );

        $resourcePagesDir = app_path('Filament/Resources/TrainingApplicationResource/Pages');
        if (!File::isDirectory($resourcePagesDir)) {
            File::makeDirectory($resourcePagesDir, 0755, true);
        }

        $this->putFile(
            app_path('Filament/Resources/TrainingApplicationResource/Pages/ListTrainingApplications.php'),
            $this->getListTrainingApplicationsPage()
        );

        $this->putFile(
            app_path('Filament/Resources/TrainingApplicationResource/Pages/CreateTrainingApplication.php'),
            $this->getCreateTrainingApplicationPage()
        );

        $this->putFile(
            app_path('Filament/Resources/TrainingApplicationResource/Pages/EditTrainingApplication.php'),
            $this->getEditTrainingApplicationPage()
        );

        $this->putFile(
            database_path('seeders/AdminSeeder.php'),
            $this->getAdminSeeder('3', 'admin@passenger.ru')
        );

        $this->putFile(
            database_path('seeders/DatabaseSeeder.php'),
            $this->getDatabaseSeederWithAdmin()
        );
    }

    private function generateVariant4(): void
    {
        $this->putFile(
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            $this->getUserMigrationV2()
        );

        $this->putFile(app_path('Models/User.php'), $this->getUserModelV2());

        $this->putFile(
            database_path('migrations/2025_01_01_000001_create_banquet_bookings_table.php'),
            $this->getBanquetBookingMigration()
        );

        $this->putFile(app_path('Models/BanquetBooking.php'), $this->getBanquetBookingModel());

        $this->putFile(
            app_path('Filament/Resources/BanquetBookingResource.php'),
            $this->getBanquetBookingResource()
        );

        $resourcePagesDir = app_path('Filament/Resources/BanquetBookingResource/Pages');
        if (!File::isDirectory($resourcePagesDir)) {
            File::makeDirectory($resourcePagesDir, 0755, true);
        }

        $this->putFile(
            app_path('Filament/Resources/BanquetBookingResource/Pages/ListBanquetBookings.php'),
            $this->getListBanquetBookingsPage()
        );

        $this->putFile(
            app_path('Filament/Resources/BanquetBookingResource/Pages/CreateBanquetBooking.php'),
            $this->getCreateBanquetBookingPage()
        );

        $this->putFile(
            app_path('Filament/Resources/BanquetBookingResource/Pages/EditBanquetBooking.php'),
            $this->getEditBanquetBookingPage()
        );

        $this->putFile(
            database_path('seeders/AdminSeeder.php'),
            $this->getAdminSeeder('4', 'admin@banquet.ru')
        );

        $this->putFile(
            database_path('seeders/DatabaseSeeder.php'),
            $this->getDatabaseSeederWithAdmin()
        );
    }

    private function putFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (File::exists($path) && !is_writable($path)) {
            chmod($path, 0664);
        }

        File::put($path, $content);
        $this->line("  <info>created:</info> " . str_replace(base_path(), '', $path));
    }

    // ─── Auth: Login ───────────────────────────────────────────────────

    private function getCustomLoginContent(string $variant): string
    {
        [$heading, $subheading] = match ($variant) {
            '2' => ['Конференции.РФ', 'Бронирование помещений для мероприятий'],
            '3' => ['Пассажирам.РФ', 'Запись на обучение вождению'],
            '4' => ['Банкетам.Нет', 'Бронирование банкетных залов'],
        };

        return <<<PHP
<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    public function getHeading(): string | Htmlable | null
    {
        return '{$heading}';
    }

    public function getSubheading(): string | Htmlable | null
    {
        \$parent = parent::getSubheading();

        if (\$parent) {
            return new HtmlString('{$subheading}' . ' · ' . \$parent);
        }

        return '{$subheading}';
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Логин')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->validationMessages([
                'required' => 'Поле логина обязательно для заполнения.',
            ]);
    }

    protected function getCredentialsFromFormData(array \$data): array
    {
        return [
            'login' => \$data['login'],
            'password' => \$data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => 'Неверный логин или пароль.',
        ]);
    }
}
PHP;
    }

    // ─── Auth: Register (Variant 2) ────────────────────────────────────

    private function getCustomRegisterV2Content(string $variant): string
    {
        $heading = match ($variant) {
            '2' => 'Регистрация — Конференции.РФ',
            '4' => 'Регистрация — Банкетам.Нет',
        };

        return <<<PHP
<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class CustomRegister extends BaseRegister
{
    public function getHeading(): string | Htmlable | null
    {
        return '{$heading}';
    }

    public function form(Schema \$schema): Schema
    {
        return \$schema
            ->components([
                TextInput::make('login')
                    ->label('Логин')
                    ->required()
                    ->unique(table: User::class)
                    ->rules(['regex:/^[a-zA-Z0-9]+\$/', 'min:6'])
                    ->validationMessages([
                        'required' => 'Поле логина обязательно для заполнения.',
                        'unique' => 'Такой логин уже существует.',
                        'regex' => 'Логин должен содержать только латинские буквы и цифры.',
                        'min' => 'Логин должен содержать не менее 6 символов.',
                    ]),
                TextInput::make('fio')
                    ->label('ФИО')
                    ->required()
                    ->rules(['regex:/^[\\p{Cyrillic}\\s]+\$/u'])
                    ->validationMessages([
                        'required' => 'Поле ФИО обязательно для заполнения.',
                        'regex' => 'ФИО должно содержать только кириллицу и пробелы.',
                    ]),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->mask('+7(999)-999-99-99')
                    ->required()
                    ->rules(['regex:/^\\+7\\(\\d{3}\\)-\\d{3}-\\d{2}-\\d{2}\$/'])
                    ->validationMessages([
                        'required' => 'Поле телефона обязательно для заполнения.',
                        'regex' => 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX.',
                    ]),
                TextInput::make('email')
                    ->label('Электронная почта')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: User::class)
                    ->validationMessages([
                        'required' => 'Поле электронной почты обязательно для заполнения.',
                        'email' => 'Введите корректный адрес электронной почты.',
                        'unique' => 'Такой адрес электронной почты уже используется.',
                    ]),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn (\$state) => Hash::make(\$state))
                    ->validationMessages([
                        'required' => 'Поле пароля обязательно для заполнения.',
                        'min' => 'Пароль должен содержать не менее 8 символов.',
                        'same' => 'Пароли не совпадают.',
                    ]),
                TextInput::make('passwordConfirmation')
                    ->label('Подтверждение пароля')
                    ->password()
                    ->required()
                    ->dehydrated(false)
                    ->validationMessages([
                        'required' => 'Подтверждение пароля обязательно.',
                    ]),
            ]);
    }

    public function getRegisterFormAction(): \\Filament\\Actions\\Action
    {
        return parent::getRegisterFormAction()
            ->label('Зарегистрироваться');
    }
}
PHP;
    }

    // ─── Auth: Register (Variant 3) ────────────────────────────────────

    private function getCustomRegisterV3Content(string $variant): string
    {
        return <<<PHP
<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class CustomRegister extends BaseRegister
{
    public function getHeading(): string | Htmlable | null
    {
        return 'Регистрация — Пассажирам.РФ';
    }

    public function form(Schema \$schema): Schema
    {
        return \$schema
            ->components([
                TextInput::make('login')
                    ->label('Логин')
                    ->required()
                    ->unique(table: User::class)
                    ->rules(['regex:/^[a-zA-Z0-9]+\$/', 'min:6'])
                    ->validationMessages([
                        'required' => 'Поле логина обязательно для заполнения.',
                        'unique' => 'Такой логин уже существует.',
                        'regex' => 'Логин должен содержать только латинские буквы и цифры.',
                        'min' => 'Логин должен содержать не менее 6 символов.',
                    ]),
                TextInput::make('fio')
                    ->label('ФИО')
                    ->required()
                    ->rules(['regex:/^[\\p{Cyrillic}\\s]+\$/u'])
                    ->validationMessages([
                        'required' => 'Поле ФИО обязательно для заполнения.',
                        'regex' => 'ФИО должно содержать только кириллицу и пробелы.',
                    ]),
                DatePicker::make('birth_date')
                    ->label('Дата рождения')
                    ->required()
                    ->validationMessages([
                        'required' => 'Поле даты рождения обязательно для заполнения.',
                    ]),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->mask('+7(999)-999-99-99')
                    ->required()
                    ->rules(['regex:/^\\+7\\(\\d{3}\\)-\\d{3}-\\d{2}-\\d{2}\$/'])
                    ->validationMessages([
                        'required' => 'Поле телефона обязательно для заполнения.',
                        'regex' => 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX.',
                    ]),
                TextInput::make('email')
                    ->label('Электронная почта')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: User::class)
                    ->validationMessages([
                        'required' => 'Поле электронной почты обязательно для заполнения.',
                        'email' => 'Введите корректный адрес электронной почты.',
                        'unique' => 'Такой адрес электронной почты уже используется.',
                    ]),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn (\$state) => Hash::make(\$state))
                    ->validationMessages([
                        'required' => 'Поле пароля обязательно для заполнения.',
                        'min' => 'Пароль должен содержать не менее 8 символов.',
                        'same' => 'Пароли не совпадают.',
                    ]),
                TextInput::make('passwordConfirmation')
                    ->label('Подтверждение пароля')
                    ->password()
                    ->required()
                    ->dehydrated(false)
                    ->validationMessages([
                        'required' => 'Подтверждение пароля обязательно.',
                    ]),
            ]);
    }

    public function getRegisterFormAction(): \\Filament\\Actions\\Action
    {
        return parent::getRegisterFormAction()
            ->label('Зарегистрироваться');
    }
}
PHP;
    }

    // ─── Admin Panel Provider ─────────────────────────────────────────

    private function getAdminPanelProviderContent(string $variant): string
    {
        $colors = match ($variant) {
            '2' => <<<'PHP'
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
PHP,
            '3' => <<<'PHP'
                'primary' => Color::Emerald,
                'gray' => Color::Zinc,
PHP,
            '4' => <<<'PHP'
                'primary' => Color::Rose,
                'gray' => Color::Stone,
PHP,
            default => <<<'PHP'
                'primary' => Color::Indigo,
PHP,
        };

        return <<<PHP
<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Auth\CustomRegister;
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
    public function panel(Panel \$panel): Panel
    {
        return \$panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->registration(CustomRegister::class)
            ->colors([
                {$colors}
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

    // ─── Routes ────────────────────────────────────────────────────────

    private function getRoutesWebContent(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }

    return redirect('/admin/login');
});
PHP;
    }

    // ─── User (Variant 2) ──────────────────────────────────────────────

    private function getUserMigrationV2(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('login')->unique();
            $table->string('password');
            $table->string('fio');
            $table->string('phone');
            $table->string('email')->unique();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
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

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
PHP;
    }

    private function getUserModelV2(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['login', 'password', 'fio', 'phone', 'email', 'is_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->fio;
    }
}
PHP;
    }

    // ─── User (Variant 3) ──────────────────────────────────────────────

    private function getUserMigrationV3(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('login')->unique();
            $table->string('password');
            $table->string('fio');
            $table->date('birth_date');
            $table->string('phone');
            $table->string('email')->unique();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
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

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
PHP;
    }

    private function getUserModelV3(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['login', 'password', 'fio', 'birth_date', 'phone', 'email', 'is_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_admin' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->fio;
    }
}
PHP;
    }

    // ─── Variant 2: Conference Booking ────────────────────────────────

    private function getConferenceBookingMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('room_type', ['auditorium', 'coworking', 'cinema']);
            $table->date('conference_date');
            $table->time('start_time');
            $table->enum('payment_type', ['cash', 'card']);
            $table->enum('status', ['new', 'assigned', 'completed'])->default('new');
            $table->text('review_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_bookings');
    }
};
PHP;
    }

    private function getConferenceBookingModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceBooking extends Model
{
    protected $fillable = [
        'user_id',
        'room_type',
        'conference_date',
        'start_time',
        'payment_type',
        'status',
        'review_text',
    ];

    protected function casts(): array
    {
        return [
            'conference_date' => 'date',
            'start_time' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function getConferenceBookingResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConferenceBookingResource\Pages;
use App\Models\ConferenceBooking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConferenceBookingResource extends Resource
{
    protected static ?string $model = ConferenceBooking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Заявки на бронирование';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Данные о конференции')
                    ->schema([
                        Select::make('room_type')
                            ->label('Тип помещения')
                            ->options([
                                'auditorium' => 'Аудитория',
                                'coworking' => 'Коворкинг',
                                'cinema' => 'Кинозал',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите тип помещения.',
                            ]),
                        DatePicker::make('conference_date')
                            ->label('Дата начала конференции')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->minDate(now()->format('Y-m-d'))
                            ->validationMessages([
                                'required' => 'Выберите дату конференции.',
                            ]),
                        Select::make('start_time')
                            ->label('Время начала')
                            ->options([
                                '09:00' => '09:00',
                                '10:00' => '10:00',
                                '11:00' => '11:00',
                                '12:00' => '12:00',
                                '13:00' => '13:00',
                                '14:00' => '14:00',
                                '15:00' => '15:00',
                                '16:00' => '16:00',
                                '17:00' => '17:00',
                                '18:00' => '18:00',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите время начала конференции.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Оплата')
                    ->schema([
                        Radio::make('payment_type')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Банковская карта',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите способ оплаты.',
                            ]),
                    ]),

                Section::make('Управление статусом')
                    ->schema([
                        Select::make('status')
                            ->label('Статус заявки')
                            ->options([
                                'new' => 'Новая',
                                'assigned' => 'Мероприятие назначено',
                                'completed' => 'Мероприятие завершено',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                    ]),

                Section::make('Отзыв')
                    ->schema([
                        Textarea::make('review_text')
                            ->label('Отзыв')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.fio')
                    ->label('ФИО')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Телефон')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('room_type')
                    ->label('Помещение')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'auditorium' => 'Аудитория',
                        'coworking' => 'Коворкинг',
                        'cinema' => 'Кинозал',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'auditorium' => 'info',
                        'coworking' => 'warning',
                        'cinema' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('conference_date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Время'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Оплата')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Банковская карта',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Новая',
                        'assigned' => 'Мероприятие назначено',
                        'completed' => 'Мероприятие завершено',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'assigned' => 'success',
                        'completed' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новая',
                        'assigned' => 'Мероприятие назначено',
                        'completed' => 'Мероприятие завершено',
                    ]),
                Tables\Filters\SelectFilter::make('room_type')
                    ->label('Помещение')
                    ->options([
                        'auditorium' => 'Аудитория',
                        'coworking' => 'Коворкинг',
                        'cinema' => 'Кинозал',
                    ]),
            ])
            ->actions([
                Action::make('review')
                    ->label('Оставить отзыв')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn ($record) => $record->status === 'completed' && !auth()->user()?->is_admin && !$record->review_text)
                    ->form([
                        Textarea::make('review_text')
                            ->label('Ваш отзыв')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'review_text' => $data['review_text'],
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Отзыв сохранен!')
                            ->send();
                    }),
                EditAction::make()
                    ->label('Редактировать')
                    ->visible(fn ($record) => auth()->user()?->is_admin),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()?->is_admin) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConferenceBookings::route('/'),
            'create' => Pages\CreateConferenceBooking::route('/create'),
            'edit' => Pages\EditConferenceBooking::route('/{record}/edit'),
        ];
    }
}
PHP;
    }

    private function getListConferenceBookingsPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\ConferenceBookingResource\Pages;

use App\Filament\Resources\ConferenceBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConferenceBookings extends ListRecords
{
    protected static string $resource = ConferenceBookingResource::class;

    public function getTitle(): string
    {
        return auth()->user()?->is_admin ? 'Управление заявками' : 'Мои заявки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Новая заявка'),
        ];
    }
}
PHP;
    }

    private function getCreateConferenceBookingPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\ConferenceBookingResource\Pages;

use App\Filament\Resources\ConferenceBookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateConferenceBooking extends CreateRecord
{
    protected static string $resource = ConferenceBookingResource::class;

    public function getTitle(): string
    {
        return 'Новая заявка на бронирование';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'new';

        return static::getModel()::create($data);
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Отправить заявку');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Отправить заявку'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Заявка успешно отправлена!';
    }
}
PHP;
    }

    private function getEditConferenceBookingPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\ConferenceBookingResource\Pages;

use App\Filament\Resources\ConferenceBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConferenceBooking extends EditRecord
{
    protected static string $resource = ConferenceBookingResource::class;

    public function getTitle(): string
    {
        return 'Редактирование заявки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->visible(fn (): bool => auth()->user()?->is_admin),
        ];
    }

    protected function getFormActions(): array
    {
        if (!auth()->user()?->is_admin) {
            return [];
        }

        return [
            $this->getSaveFormAction()
                ->label('Сохранить изменения'),
            $this->getCancelFormAction()
                ->label('Отмена'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Заявка обновлена!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;
    }

    // ─── Variant 3: Training Application ───────────────────────────────

    private function getTrainingApplicationMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('transport_type', ['bus', 'electric_bus', 'tram']);
            $table->date('training_date');
            $table->time('start_time');
            $table->enum('payment_type', ['cash', 'card']);
            $table->enum('status', ['new', 'in_progress', 'completed'])->default('new');
            $table->text('review_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_applications');
    }
};
PHP;
    }

    private function getTrainingApplicationModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingApplication extends Model
{
    protected $fillable = [
        'user_id',
        'transport_type',
        'training_date',
        'start_time',
        'payment_type',
        'status',
        'review_text',
    ];

    protected function casts(): array
    {
        return [
            'training_date' => 'date',
            'start_time' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function getTrainingApplicationResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingApplicationResource\Pages;
use App\Models\TrainingApplication;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingApplicationResource extends Resource
{
    protected static ?string $model = TrainingApplication::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Заявки на обучение';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о заявителе')
                    ->description('Данные пользователя, подавшего заявку')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('user.fio')
                            ->label('ФИО')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('user.email')
                            ->label('Электронная почта')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('user.phone')
                            ->label('Телефон')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('user.birth_date')
                            ->label('Дата рождения')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ]),

                Section::make('Данные об обучении')
                    ->description('Выберите транспорт, дату и время обучения')
                    ->icon('heroicon-o-truck')
                    ->columns(2)
                    ->schema([
                        Radio::make('transport_type')
                            ->label('Тип транспорта')
                            ->options([
                                'bus' => 'Автобус',
                                'electric_bus' => 'Электробус',
                                'tram' => 'Трамвай',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите тип транспорта.',
                            ]),

                        DatePicker::make('training_date')
                            ->label('Дата обучения')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите дату обучения.',
                            ]),

                        Select::make('start_time')
                            ->label('Время начала')
                            ->options([
                                '09:00' => '09:00',
                                '10:00' => '10:00',
                                '11:00' => '11:00',
                                '12:00' => '12:00',
                                '13:00' => '13:00',
                                '14:00' => '14:00',
                                '15:00' => '15:00',
                                '16:00' => '16:00',
                                '17:00' => '17:00',
                                '18:00' => '18:00',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите время начала.',
                            ]),

                        Select::make('payment_type')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Банковская карта',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите способ оплаты.',
                            ]),
                    ]),

                Section::make('Управление статусом')
                    ->description('Изменение статуса обработки заявки')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Select::make('status')
                            ->label('Статус заявки')
                            ->options([
                                'new' => 'Новая',
                                'in_progress' => 'Идет обучение',
                                'completed' => 'Обучение завершено',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                    ]),

                Section::make('Отзыв клиента')
                    ->description('Отзыв, оставленный после завершения обучения')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('review_text')
                            ->label('Отзыв')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                    ]),

                Section::make('Информация о заявке')
                    ->description('Служебная информация')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Дата создания')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('updated_at')
                            ->label('Дата обновления')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.fio')
                    ->label('ФИО')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Телефон')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('transport_type')
                    ->label('Транспорт')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bus' => 'Автобус',
                        'electric_bus' => 'Электробус',
                        'tram' => 'Трамвай',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bus' => 'info',
                        'electric_bus' => 'success',
                        'tram' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('training_date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Время'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Оплата')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Банковская карта',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Новая',
                        'in_progress' => 'Идет обучение',
                        'completed' => 'Обучение завершено',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новая',
                        'in_progress' => 'Идет обучение',
                        'completed' => 'Обучение завершено',
                    ]),
                Tables\Filters\SelectFilter::make('transport_type')
                    ->label('Транспорт')
                    ->options([
                        'bus' => 'Автобус',
                        'electric_bus' => 'Электробус',
                        'tram' => 'Трамвай',
                    ]),
            ])
            ->actions([
                Action::make('review')
                    ->label('Оставить отзыв')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn ($record) => $record->status === 'completed' && !auth()->user()?->is_admin && !$record->review_text)
                    ->form([
                        Textarea::make('review_text')
                            ->label('Ваш отзыв')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'review_text' => $data['review_text'],
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Отзыв сохранен!')
                            ->send();
                    }),
                EditAction::make()
                    ->label('Редактировать')
                    ->visible(fn ($record) => auth()->user()?->is_admin),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()?->is_admin) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingApplications::route('/'),
            'create' => Pages\CreateTrainingApplication::route('/create'),
            'edit' => Pages\EditTrainingApplication::route('/{record}/edit'),
        ];
    }
}
PHP;
    }

    private function getListTrainingApplicationsPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\TrainingApplicationResource\Pages;

use App\Filament\Resources\TrainingApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingApplications extends ListRecords
{
    protected static string $resource = TrainingApplicationResource::class;

    public function getTitle(): string
    {
        return auth()->user()?->is_admin ? 'Управление заявками' : 'Мои заявки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Новая заявка'),
        ];
    }
}
PHP;
    }

    private function getCreateTrainingApplicationPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\TrainingApplicationResource\Pages;

use App\Filament\Resources\TrainingApplicationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTrainingApplication extends CreateRecord
{
    protected static string $resource = TrainingApplicationResource::class;

    public function getTitle(): string
    {
        return 'Новая заявка на обучение';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'new';

        return static::getModel()::create($data);
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Отправить заявку');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Отправить заявку'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Заявка успешно отправлена!';
    }
}
PHP;
    }

    private function getEditTrainingApplicationPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\TrainingApplicationResource\Pages;

use App\Filament\Resources\TrainingApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingApplication extends EditRecord
{
    protected static string $resource = TrainingApplicationResource::class;

    public function getTitle(): string
    {
        return 'Редактирование заявки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->visible(fn (): bool => auth()->user()?->is_admin),
        ];
    }

    protected function getFormActions(): array
    {
        if (!auth()->user()?->is_admin) {
            return [];
        }

        return [
            $this->getSaveFormAction()
                ->label('Сохранить изменения'),
            $this->getCancelFormAction()
                ->label('Отмена'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Заявка обновлена!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;
    }

    // ─── Variant 4: BanquetBooking ─────────────────────────────────────

    private function getBanquetBookingMigration(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banquet_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('hall_type', ['hall', 'restaurant', 'summer_veranda', 'enclosed_veranda']);
            $table->date('banquet_date');
            $table->time('start_time');
            $table->enum('payment_type', ['cash', 'card']);
            $table->enum('status', ['new', 'assigned', 'completed'])->default('new');
            $table->text('review_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banquet_bookings');
    }
};
PHP;
    }

    private function getBanquetBookingModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BanquetBooking extends Model
{
    protected $fillable = [
        'user_id',
        'hall_type',
        'banquet_date',
        'start_time',
        'payment_type',
        'status',
        'review_text',
    ];

    protected function casts(): array
    {
        return [
            'banquet_date' => 'date',
            'start_time' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function getBanquetBookingResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BanquetBookingResource\Pages;
use App\Models\BanquetBooking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BanquetBookingResource extends Resource
{
    protected static ?string $model = BanquetBooking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Заявки на бронирование';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о заявителе')
                    ->description('Данные пользователя, подавшего заявку')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('user.fio')
                            ->label('ФИО')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('user.email')
                            ->label('Электронная почта')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('user.phone')
                            ->label('Телефон')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ]),

                Section::make('Данные о банкете')
                    ->description('Выберите зал, дату и время проведения')
                    ->icon('heroicon-o-building-office')
                    ->columns(2)
                    ->schema([
                        Select::make('hall_type')
                            ->label('Тип помещения')
                            ->options([
                                'hall' => 'Зал',
                                'restaurant' => 'Ресторан',
                                'summer_veranda' => 'Летняя веранда',
                                'enclosed_veranda' => 'Закрытая веранда',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите тип помещения.',
                            ]),

                        DatePicker::make('banquet_date')
                            ->label('Дата банкета')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите дату конференции.',
                            ]),

                        Select::make('start_time')
                            ->label('Время начала')
                            ->options([
                                '09:00' => '09:00',
                                '10:00' => '10:00',
                                '11:00' => '11:00',
                                '12:00' => '12:00',
                                '13:00' => '13:00',
                                '14:00' => '14:00',
                                '15:00' => '15:00',
                                '16:00' => '16:00',
                                '17:00' => '17:00',
                                '18:00' => '18:00',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите время начала.',
                            ]),

                        Select::make('payment_type')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Банковская карта',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите способ оплаты.',
                            ]),
                    ]),

                Section::make('Управление статусом')
                    ->description('Изменение статуса обработки заявки')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Select::make('status')
                            ->label('Статус заявки')
                            ->options([
                                'new' => 'Новая',
                                'assigned' => 'Мероприятие назначено',
                                'completed' => 'Мероприятие завершено',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                    ]),

                Section::make('Отзыв клиента')
                    ->description('Отзыв, оставленный после завершения мероприятия')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('review_text')
                            ->label('Отзыв')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                    ]),

                Section::make('Информация о заявке')
                    ->description('Служебная информация')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Дата создания')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('updated_at')
                            ->label('Дата обновления')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.fio')
                    ->label('ФИО')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Телефон')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('hall_type')
                    ->label('Помещение')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hall' => 'Зал',
                        'restaurant' => 'Ресторан',
                        'summer_veranda' => 'Летняя веранда',
                        'enclosed_veranda' => 'Закрытая веранда',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hall' => 'info',
                        'restaurant' => 'success',
                        'summer_veranda' => 'warning',
                        'enclosed_veranda' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('banquet_date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Время'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Оплата')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Банковская карта',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Новая',
                        'assigned' => 'Банкет назначен',
                        'completed' => 'Банкет завершен',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'assigned' => 'success',
                        'completed' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новая',
                        'assigned' => 'Банкет назначен',
                        'completed' => 'Банкет завершен',
                    ]),
                Tables\Filters\SelectFilter::make('hall_type')
                    ->label('Помещение')
                    ->options([
                        'hall' => 'Зал',
                        'restaurant' => 'Ресторан',
                        'summer_veranda' => 'Летняя веранда',
                        'enclosed_veranda' => 'Закрытая веранда',
                    ]),
            ])
            ->actions([
                Action::make('review')
                    ->label('Оставить отзыв')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn ($record) => $record->status === 'completed' && !auth()->user()?->is_admin && !$record->review_text)
                    ->form([
                        Textarea::make('review_text')
                            ->label('Ваш отзыв')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'review_text' => $data['review_text'],
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Отзыв сохранен!')
                            ->send();
                    }),
                EditAction::make()
                    ->label('Редактировать')
                    ->visible(fn ($record) => auth()->user()?->is_admin),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()?->is_admin) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanquetBookings::route('/'),
            'create' => Pages\CreateBanquetBooking::route('/create'),
            'edit' => Pages\EditBanquetBooking::route('/{record}/edit'),
        ];
    }
}
PHP;
    }

    private function getListBanquetBookingsPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BanquetBookingResource\Pages;

use App\Filament\Resources\BanquetBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBanquetBookings extends ListRecords
{
    protected static string $resource = BanquetBookingResource::class;

    public function getTitle(): string
    {
        return auth()->user()?->is_admin ? 'Управление заявками' : 'Мои заявки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Новая заявка'),
        ];
    }
}
PHP;
    }

    private function getCreateBanquetBookingPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BanquetBookingResource\Pages;

use App\Filament\Resources\BanquetBookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBanquetBooking extends CreateRecord
{
    protected static string $resource = BanquetBookingResource::class;

    public function getTitle(): string
    {
        return 'Новая заявка на бронирование';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'new';

        return static::getModel()::create($data);
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Отправить заявку');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Отправить заявку'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Заявка успешно отправлена!';
    }
}
PHP;
    }

    private function getEditBanquetBookingPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BanquetBookingResource\Pages;

use App\Filament\Resources\BanquetBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanquetBooking extends EditRecord
{
    protected static string $resource = BanquetBookingResource::class;

    public function getTitle(): string
    {
        return 'Редактирование заявки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->visible(fn (): bool => auth()->user()?->is_admin),
        ];
    }

    protected function getFormActions(): array
    {
        if (!auth()->user()?->is_admin) {
            return [];
        }

        return [
            $this->getSaveFormAction()
                ->label('Сохранить изменения'),
            $this->getCancelFormAction()
                ->label('Отмена'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Заявка обновлена!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;
    }

    // ─── Seeders ───────────────────────────────────────────────────────

    private function getAdminSeeder(string $variant, string $email): string
    {
        $email = preg_replace('/[^a-z@.]/', '', $email);
        $birthDateField = $variant === '3' ? "'birth_date' => '1990-01-01',\n" : '';

        return <<<PHP
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'login' => 'Admin26',
            'password' => bcrypt('Demo20'),
            'fio' => 'Администратор Системы',
            {$birthDateField}            'phone' => '+7(999)-999-99-99',
            'email' => '{$email}',
            'is_admin' => true,
        ]);
    }
}
PHP;
    }

    private function getDatabaseSeederWithAdmin(): string
    {
        return <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
PHP;
    }
}
