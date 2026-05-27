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
        $variant = $this->ask('Выберите вариант экзамена для генерации [1, 2, 3]:');

        if (!in_array($variant, ['1', '2', '3'])) {
            $this->error('Неверный вариант. Выберите 1, 2 или 3.');
            return 1;
        }

        $this->info("Генерация варианта №{$variant}...");

        $this->createDirectories();

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

    private function generateCommonFiles(string $variant): void
    {
        // CustomLogin
        $this->putFile(app_path('Filament/Pages/Auth/CustomLogin.php'), $this->getCustomLoginContent());

        // CustomRegister
        $this->putFile(app_path('Filament/Pages/Auth/CustomRegister.php'), $this->getCustomRegisterContent($variant));

        // AdminPanelProvider
        $this->putFile(app_path('Providers/Filament/AdminPanelProvider.php'), $this->getAdminPanelProviderContent());

        // Routes
        $this->putFile(base_path('routes/web.php'), $this->getRoutesWebContent());
    }

    private function generateVariant1(): void
    {
        // User migration
        $this->putFile(
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            $this->getUserMigrationV1()
        );

        // User model
        $this->putFile(app_path('Models/User.php'), $this->getUserModelV1());

        // TestDriveRequest migration
        $this->putFile(
            database_path('migrations/2025_01_01_000001_create_test_drive_requests_table.php'),
            $this->getTestDriveRequestMigration()
        );

        // TestDriveRequest model
        $this->putFile(app_path('Models/TestDriveRequest.php'), $this->getTestDriveRequestModel());

        // TestDriveRequestResource
        $this->putFile(
            app_path('Filament/Resources/TestDriveRequestResource.php'),
            $this->getTestDriveRequestResource()
        );

        // Resource pages directory
        $resourcePagesDir = app_path('Filament/Resources/TestDriveRequestResource/Pages');
        if (!File::isDirectory($resourcePagesDir)) {
            File::makeDirectory($resourcePagesDir, 0755, true);
        }

        // List page
        $this->putFile(
            app_path('Filament/Resources/TestDriveRequestResource/Pages/ListTestDriveRequests.php'),
            $this->getListTestDriveRequestsPage()
        );

        // Create page
        $this->putFile(
            app_path('Filament/Resources/TestDriveRequestResource/Pages/CreateTestDriveRequest.php'),
            $this->getCreateTestDriveRequestPage()
        );

        // Edit page
        $this->putFile(
            app_path('Filament/Resources/TestDriveRequestResource/Pages/EditTestDriveRequest.php'),
            $this->getEditTestDriveRequestPage()
        );

        // Admin seeder
        $this->putFile(
            database_path('seeders/AdminSeeder.php'),
            $this->getAdminSeederV1()
        );

        // DatabaseSeeder
        $this->putFile(
            database_path('seeders/DatabaseSeeder.php'),
            $this->getDatabaseSeederWithAdmin()
        );
    }

    private function generateVariant2(): void
    {
        // User migration
        $this->putFile(
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            $this->getUserMigrationV2()
        );

        // User model
        $this->putFile(app_path('Models/User.php'), $this->getUserModelV2());

        // BookCard migration
        $this->putFile(
            database_path('migrations/2025_01_01_000001_create_book_cards_table.php'),
            $this->getBookCardMigration()
        );

        // BookCard model
        $this->putFile(app_path('Models/BookCard.php'), $this->getBookCardModel());

        // BookCardResource
        $this->putFile(
            app_path('Filament/Resources/BookCardResource.php'),
            $this->getBookCardResource()
        );

        // Resource pages directory
        $resourcePagesDir = app_path('Filament/Resources/BookCardResource/Pages');
        if (!File::isDirectory($resourcePagesDir)) {
            File::makeDirectory($resourcePagesDir, 0755, true);
        }

        // List page
        $this->putFile(
            app_path('Filament/Resources/BookCardResource/Pages/ListBookCards.php'),
            $this->getListBookCardsPage()
        );

        // Create page
        $this->putFile(
            app_path('Filament/Resources/BookCardResource/Pages/CreateBookCard.php'),
            $this->getCreateBookCardPage()
        );

        // Edit page
        $this->putFile(
            app_path('Filament/Resources/BookCardResource/Pages/EditBookCard.php'),
            $this->getEditBookCardPage()
        );

        // Admin seeder
        $this->putFile(
            database_path('seeders/AdminSeeder.php'),
            $this->getAdminSeederV2()
        );

        // DatabaseSeeder
        $this->putFile(
            database_path('seeders/DatabaseSeeder.php'),
            $this->getDatabaseSeederWithAdmin()
        );
    }

    private function generateVariant3(): void
    {
        // User migration
        $this->putFile(
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            $this->getUserMigrationV3()
        );

        // User model
        $this->putFile(app_path('Models/User.php'), $this->getUserModelV3());

        // Booking migration
        $this->putFile(
            database_path('migrations/2025_01_01_000001_create_bookings_table.php'),
            $this->getBookingMigration()
        );

        // Booking model
        $this->putFile(app_path('Models/Booking.php'), $this->getBookingModel());

        // BookingResource
        $this->putFile(
            app_path('Filament/Resources/BookingResource.php'),
            $this->getBookingResource()
        );

        // Resource pages directory
        $resourcePagesDir = app_path('Filament/Resources/BookingResource/Pages');
        if (!File::isDirectory($resourcePagesDir)) {
            File::makeDirectory($resourcePagesDir, 0755, true);
        }

        // List page
        $this->putFile(
            app_path('Filament/Resources/BookingResource/Pages/ListBookings.php'),
            $this->getListBookingsPage()
        );

        // Create page
        $this->putFile(
            app_path('Filament/Resources/BookingResource/Pages/CreateBooking.php'),
            $this->getCreateBookingPage()
        );

        // Edit page
        $this->putFile(
            app_path('Filament/Resources/BookingResource/Pages/EditBooking.php'),
            $this->getEditBookingPage()
        );

        // Admin seeder
        $this->putFile(
            database_path('seeders/AdminSeeder.php'),
            $this->getAdminSeederV3()
        );

        // DatabaseSeeder
        $this->putFile(
            database_path('seeders/DatabaseSeeder.php'),
            $this->getDatabaseSeederWithAdmin()
        );
    }

    private function putFile(string $path, string $content): void
    {
        File::put($path, $content);
        $this->line("  <info>created:</info> " . str_replace(base_path(), '', $path));
    }

    private function getCustomLoginContent(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
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

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'login' => $data['login'],
            'password' => $data['password'],
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

    private function getCustomRegisterContent(string $variant): string
    {
        if ($variant === '3') {
            return $this->getCustomRegisterV3Content();
        }

        return $this->getCustomRegisterV12Content();
    }

    private function getCustomRegisterV12Content(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class CustomRegister extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('login')
                    ->label('Логин')
                    ->required()
                    ->unique(table: User::class)
                    ->rules(['regex:/^[\p{Cyrillic}]+$/u', 'min:6'])
                    ->validationMessages([
                        'required' => 'Поле логина обязательно для заполнения.',
                        'unique' => 'Такой логин уже существует.',
                        'regex' => 'Логин должен содержать только кириллицу.',
                        'min' => 'Логин должен содержать не менее 6 символов.',
                    ]),
                TextInput::make('fio')
                    ->label('ФИО')
                    ->required()
                    ->rules(['regex:/^[\p{Cyrillic}\s]+$/u'])
                    ->validationMessages([
                        'required' => 'Поле ФИО обязательно для заполнения.',
                        'regex' => 'ФИО должно содержать только кириллицу и пробелы.',
                    ]),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->mask('+7(999)-999-99-99')
                    ->required()
                    ->rules(['regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'])
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
                    ->minLength(6)
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->validationMessages([
                        'required' => 'Поле пароля обязательно для заполнения.',
                        'min' => 'Пароль должен содержать не менее 6 символов.',
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

    public function getRegisterFormAction(): \Filament\Actions\Action
    {
        return parent::getRegisterFormAction()
            ->label('Зарегистрироваться');
    }
}
PHP;
    }

    private function getCustomRegisterV3Content(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class CustomRegister extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('login')
                    ->label('Логин')
                    ->required()
                    ->unique(table: User::class)
                    ->rules(['regex:/^[\p{Cyrillic}]+$/u', 'min:6'])
                    ->validationMessages([
                        'required' => 'Поле логина обязательно для заполнения.',
                        'unique' => 'Такой логин уже существует.',
                        'regex' => 'Логин должен содержать только кириллицу.',
                        'min' => 'Логин должен содержать не менее 6 символов.',
                    ]),
                TextInput::make('first_name')
                    ->label('Имя')
                    ->required()
                    ->rules(['regex:/^[\p{Cyrillic}\s]+$/u'])
                    ->validationMessages([
                        'required' => 'Поле имени обязательно для заполнения.',
                        'regex' => 'Имя должно содержать только кириллицу.',
                    ]),
                TextInput::make('last_name')
                    ->label('Фамилия')
                    ->required()
                    ->rules(['regex:/^[\p{Cyrillic}\s]+$/u'])
                    ->validationMessages([
                        'required' => 'Поле фамилии обязательно для заполнения.',
                        'regex' => 'Фамилия должна содержать только кириллицу.',
                    ]),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->mask('+7(999)-999-99-99')
                    ->required()
                    ->rules(['regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'])
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
                    ->minLength(6)
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->validationMessages([
                        'required' => 'Поле пароля обязательно для заполнения.',
                        'min' => 'Пароль должен содержать не менее 6 символов.',
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

    public function getRegisterFormAction(): \Filament\Actions\Action
    {
        return parent::getRegisterFormAction()
            ->label('Зарегистрироваться');
    }
}
PHP;
    }

    private function getAdminPanelProviderContent(): string
    {
        return <<<'PHP'
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
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->registration(CustomRegister::class)
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

    private function getUserMigrationV1(): string
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

    private function getUserModelV1(): string
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
            $table->string('first_name');
            $table->string('last_name');
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

    protected $fillable = ['login', 'password', 'first_name', 'last_name', 'phone', 'email', 'is_admin'];

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
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
PHP;
    }

    private function getTestDriveRequestMigration(): string
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
        Schema::create('test_drive_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('phone');
            $table->date('desired_date');
            $table->time('desired_time');
            $table->string('license_series');
            $table->string('license_number');
            $table->date('license_issue_date');
            $table->string('car_brand');
            $table->string('car_model');
            $table->enum('payment_type', ['cash', 'card']);
            $table->boolean('is_agreed')->default(false);
            $table->enum('status', ['в работе', 'одобрено', 'выполнено', 'отклонено'])->default('в работе');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_drive_requests');
    }
};
PHP;
    }

    private function getTestDriveRequestModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestDriveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'phone',
        'desired_date',
        'desired_time',
        'license_series',
        'license_number',
        'license_issue_date',
        'car_brand',
        'car_model',
        'payment_type',
        'is_agreed',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'desired_date' => 'date',
            'desired_time' => 'string',
            'license_issue_date' => 'date',
            'is_agreed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function getTestDriveRequestResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestDriveRequestResource\Pages;
use App\Models\TestDriveRequest;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TestDriveRequestResource extends Resource
{
    protected static ?string $model = TestDriveRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Заявки на тест-драйв';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Данные о заявке')
                    ->schema([
                        TextInput::make('address')
                            ->label('Адрес')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле адреса обязательно для заполнения.',
                            ]),
                        TextInput::make('phone')
                            ->label('Контактный телефон')
                            ->mask('+7(999)-999-99-99')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->rules(['regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'])
                            ->validationMessages([
                                'required' => 'Поле телефона обязательно для заполнения.',
                                'regex' => 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX.',
                            ]),
                        DatePicker::make('desired_date')
                            ->label('Желаемая дата')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->minDate(now()->format('Y-m-d'))
                            ->validationMessages([
                                'required' => 'Поле даты обязательно для заполнения.',
                            ]),
                        TimePicker::make('desired_time')
                            ->label('Желаемое время')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле времени обязательно для заполнения.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Водительское удостоверение')
                    ->schema([
                        TextInput::make('license_series')
                            ->label('Серия')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле серии ВУ обязательно для заполнения.',
                            ]),
                        TextInput::make('license_number')
                            ->label('Номер')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле номера ВУ обязательно для заполнения.',
                            ]),
                        DatePicker::make('license_issue_date')
                            ->label('Дата выдачи')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле даты выдачи ВУ обязательно для заполнения.',
                            ]),
                    ])
                    ->columns(3),

                Section::make('Автомобиль')
                    ->schema([
                        Select::make('car_brand')
                            ->label('Марка автомобиля')
                            ->options([
                                'Lada' => 'Lada',
                                'Toyota' => 'Toyota',
                                'Hyundai' => 'Hyundai',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->live()
                            ->validationMessages([
                                'required' => 'Выберите марку автомобиля.',
                            ]),
                        Select::make('car_model')
                            ->label('Модель автомобиля')
                            ->options(fn (Get $get) => match ($get('car_brand')) {
                                'Lada' => [
                                    'Vesta' => 'Vesta',
                                    'Granta' => 'Granta',
                                    'Niva' => 'Niva',
                                    'Largus' => 'Largus',
                                    'XRAY' => 'XRAY',
                                ],
                                'Toyota' => [
                                    'Camry' => 'Camry',
                                    'RAV4' => 'RAV4',
                                    'Corolla' => 'Corolla',
                                    'Land Cruiser' => 'Land Cruiser',
                                    'Yaris' => 'Yaris',
                                ],
                                'Hyundai' => [
                                    'Solaris' => 'Solaris',
                                    'Creta' => 'Creta',
                                    'Elantra' => 'Elantra',
                                    'Tucson' => 'Tucson',
                                    'Santa Fe' => 'Santa Fe',
                                ],
                                default => [],
                            })
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->hidden(fn (Get $get) => !$get('car_brand'))
                            ->validationMessages([
                                'required' => 'Выберите модель автомобиля.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Оплата')
                    ->schema([
                        Radio::make('payment_type')
                            ->label('Тип оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Банковская карта',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите тип оплаты.',
                            ]),
                    ]),

                Section::make('Согласие')
                    ->schema([
                        Checkbox::make('is_agreed')
                            ->label('Я ознакомлен с правилами предоставления услуги тест-драйва и все предоставленные данные верны')
                            ->rules(['accepted'])
                            ->live()
                            ->hidden(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'accepted' => 'Необходимо подтвердить согласие с правилами.',
                            ]),
                    ]),

                Section::make('Управление статусом')
                    ->schema([
                        Select::make('status')
                            ->label('Статус заявки')
                            ->options([
                                'в работе' => 'В работе',
                                'одобрено' => 'Одобрено',
                                'выполнено' => 'Выполнено',
                                'отклонено' => 'Отклонено',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                        Textarea::make('rejection_reason')
                            ->label('Причина отклонения')
                            ->required(fn (Get $get): bool => $get('status') === 'отклонено')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'edit' && auth()->user()?->is_admin && $get('status') === 'отклонено'),
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
                    ->label('ФИО клиента')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                Tables\Columns\TextColumn::make('car_brand')
                    ->label('Марка'),
                Tables\Columns\TextColumn::make('car_model')
                    ->label('Модель'),
                Tables\Columns\TextColumn::make('desired_date')
                    ->label('Дата')
                    ->date(),
                Tables\Columns\TextColumn::make('desired_time')
                    ->label('Время'),
                Tables\Columns\TextColumn::make('license_series')
                    ->label('Серия ВУ')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->label('Номер ВУ')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_issue_date')
                    ->label('Дата выдачи ВУ')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Тип оплаты')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Банковская карта',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'в работе' => 'info',
                        'одобрено' => 'success',
                        'выполнено' => 'primary',
                        'отклонено' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Причина отклонения')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'в работе' => 'В работе',
                        'одобрено' => 'Одобрено',
                        'выполнено' => 'Выполнено',
                        'отклонено' => 'Отклонено',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTestDriveRequests::route('/'),
            'create' => Pages\CreateTestDriveRequest::route('/create'),
            'edit' => Pages\EditTestDriveRequest::route('/{record}/edit'),
        ];
    }
}
PHP;
    }

    private function getListTestDriveRequestsPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\TestDriveRequestResource\Pages;

use App\Filament\Resources\TestDriveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestDriveRequests extends ListRecords
{
    protected static string $resource = TestDriveRequestResource::class;

    public function getTitle(): string
    {
        return 'Заявки на тест-драйв';
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

    private function getCreateTestDriveRequestPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\TestDriveRequestResource\Pages;

use App\Filament\Resources\TestDriveRequestResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTestDriveRequest extends CreateRecord
{
    protected static string $resource = TestDriveRequestResource::class;

    public function getTitle(): string
    {
        return 'Новая заявка на тест-драйв';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();

        return static::getModel()::create($data);
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Отправить заявку')
            ->disabled(fn (): bool => !$this->data['is_agreed']);
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
        return 'Заявка успешно создана!';
    }
}
PHP;
    }

    private function getEditTestDriveRequestPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\TestDriveRequestResource\Pages;

use App\Filament\Resources\TestDriveRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTestDriveRequest extends EditRecord
{
    protected static string $resource = TestDriveRequestResource::class;

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

    protected function afterSave(): void
    {
        if ($this->record->status === 'отклонено' && !$this->record->rejection_reason) {
            Notification::make()
                ->warning()
                ->title('Укажите причину отклонения')
                ->send();
        }
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

    // ─── Variant 2: BookCard ───────────────────────────────────────────

    private function getBookCardMigration(): string
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
        Schema::create('book_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('author');
            $table->string('title');
            $table->enum('type', ['share', 'wish']);
            $table->string('publisher')->nullable();
            $table->year('pub_year')->nullable();
            $table->enum('binding', ['hard', 'soft'])->nullable();
            $table->enum('condition', ['perfect', 'normal', 'attention', 'table_leg'])->nullable();
            $table->enum('status', ['pending', 'published', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_cards');
    }
};
PHP;
    }

    private function getBookCardModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'author',
        'title',
        'type',
        'publisher',
        'pub_year',
        'binding',
        'condition',
        'status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function getBookCardResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookCardResource\Pages;
use App\Models\BookCard;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookCardResource extends Resource
{
    protected static ?string $model = BookCard::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $modelLabel = 'карточка';

    protected static ?string $pluralModelLabel = 'карточки';

    public static function getNavigationItems(): array
    {
        if (auth()->user()?->is_admin) {
            return [
                NavigationItem::make('Панель модератора')
                    ->icon('heroicon-o-shield-check')
                    ->group('Модерация')
                    ->sort(1)
                    ->url(static::getNavigationUrl()),
            ];
        }

        return [
            NavigationItem::make('Мои книги и Архив')
                ->icon(static::getNavigationIcon())
                ->sort(1)
                ->url(static::getNavigationUrl()),
        ];
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('author')
                            ->label('Автор книги')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле автора обязательно для заполнения.',
                            ]),
                        TextInput::make('title')
                            ->label('Название книги')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Поле названия обязательно для заполнения.',
                            ]),
                        Radio::make('type')
                            ->label('Тип карточки')
                            ->options([
                                'share' => 'Готов поделиться',
                                'wish' => 'Хочу в свою библиотеку',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Выберите тип карточки.',
                            ]),
                    ]),

                Section::make('Детали книги (необязательно)')
                    ->schema([
                        TextInput::make('publisher')
                            ->label('Издательство')
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        TextInput::make('pub_year')
                            ->label('Год издания')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(now()->year)
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        Select::make('binding')
                            ->label('Переплет')
                            ->options([
                                'hard' => 'твердый',
                                'soft' => 'мягкий',
                            ])
                            ->placeholder('Не выбрано')
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        Select::make('condition')
                            ->label('Состояние книги')
                            ->options([
                                'perfect' => 'идеальное',
                                'normal' => 'нормальное',
                                'attention' => 'требует внимания',
                                'table_leg' => 'годится чтобы подпирать ножку стола',
                            ])
                            ->placeholder('Не выбрано')
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2),

                Section::make('Управление статусом')
                    ->schema([
                        Select::make('status')
                            ->label('Статус карточки')
                            ->options([
                                'pending' => 'На рассмотрении',
                                'published' => 'Опубликовано',
                                'rejected' => 'Отклонено',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                        Textarea::make('rejection_reason')
                            ->label('Причина отклонения')
                            ->required(fn (Get $get): bool => $get('status') === 'rejected')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'edit' && auth()->user()?->is_admin && $get('status') === 'rejected'),
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
                    ->label('Пользователь')
                    ->searchable(),
                Tables\Columns\TextColumn::make('author')
                    ->label('Автор')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'share' => 'Поделиться',
                        'wish' => 'В библиотеку',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'share' => 'success',
                        'wish' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'На рассмотрении',
                        'published' => 'Опубликовано',
                        'rejected' => 'Отклонено',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'published' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Причина отклонения')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Архив')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На рассмотрении',
                        'published' => 'Опубликовано',
                        'rejected' => 'Отклонено',
                    ]),
                Tables\Filters\TrashedFilter::make()
                    ->label('Архивные'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать')
                    ->visible(fn ($record) => !auth()->user()?->is_admin),
                \Filament\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->visible(fn ($record) => !auth()->user()?->is_admin),
                \Filament\Actions\Action::make('publish')
                    ->label('Опубликовать')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => auth()->user()?->is_admin && $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'published']);
                        Notification::make()
                            ->success()
                            ->title('Карточка опубликована')
                            ->send();
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => auth()->user()?->is_admin && $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Причина отклонения')
                            ->required()
                            ->validationMessages([
                                'required' => 'Укажите причину отклонения.',
                            ]),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->warning()
                            ->title('Карточка отклонена')
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()?->is_admin) {
            $query->where('user_id', auth()->id())
                ->withoutGlobalScopes([SoftDeletingScope::class]);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookCards::route('/'),
            'create' => Pages\CreateBookCard::route('/create'),
            'edit' => Pages\EditBookCard::route('/{record}/edit'),
        ];
    }
}
PHP;
    }

    private function getListBookCardsPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BookCardResource\Pages;

use App\Filament\Resources\BookCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookCards extends ListRecords
{
    protected static string $resource = BookCardResource::class;

    public function getTitle(): string
    {
        return auth()->user()?->is_admin ? 'Панель модератора' : 'Мои книги и Архив';
    }

    public function getTabs(): array
    {
        if (auth()->user()?->is_admin) {
            return [];
        }

        return [
            'active' => Tab::make('Мои активные карточки')
                ->query(fn (Builder $query) => $query->whereIn('status', ['pending', 'published'])->whereNull('deleted_at')),
            'archive' => Tab::make('Архивные карточки')
                ->query(fn (Builder $query) => $query->where(function (Builder $q) {
                    $q->where('status', 'rejected')->orWhereNotNull('deleted_at');
                })),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Добавить книгу'),
        ];
    }
}
PHP;
    }

    private function getCreateBookCardPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BookCardResource\Pages;

use App\Filament\Resources\BookCardResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBookCard extends CreateRecord
{
    protected static string $resource = BookCardResource::class;

    public function getTitle(): string
    {
        return 'Создание карточки';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';

        return static::getModel()::create($data);
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Отправить');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Отправить'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Карточка успешно отправлена на рассмотрение администратору!';
    }
}
PHP;
    }

    private function getEditBookCardPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BookCardResource\Pages;

use App\Filament\Resources\BookCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookCard extends EditRecord
{
    protected static string $resource = BookCardResource::class;

    public function getTitle(): string
    {
        return 'Редактирование карточки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->visible(fn (): bool => !auth()->user()?->is_admin),
            Actions\ForceDeleteAction::make()
                ->label('Удалить навсегда')
                ->visible(fn (): bool => !auth()->user()?->is_admin),
            Actions\RestoreAction::make()
                ->label('Восстановить')
                ->visible(fn (): bool => !auth()->user()?->is_admin),
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
        return 'Карточка обновлена!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;
    }

    // ─── Variant 3: Booking ────────────────────────────────────────────

    private function getBookingMigration(): string
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->integer('guests_count');
            $table->string('contact_phone');
            $table->enum('status', ['new', 'completed', 'canceled'])->default('new');
            $table->text('review_text')->nullable();
            $table->unsignedTinyInteger('review_rating')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
PHP;
    }

    private function getBookingModel(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'booking_date',
        'booking_time',
        'guests_count',
        'contact_phone',
        'status',
        'review_text',
        'review_rating',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'booking_time' => 'string',
            'guests_count' => 'integer',
            'review_rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;
    }

    private function getBookingResource(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Бронирования';

    protected static ?string $modelLabel = 'бронирование';

    protected static ?string $pluralModelLabel = 'бронирования';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Детали бронирования')
                    ->schema([
                        DatePicker::make('booking_date')
                            ->label('Дата бронирования')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->minDate(now()->format('Y-m-d'))
                            ->validationMessages([
                                'required' => 'Выберите дату бронирования.',
                            ]),
                        TimePicker::make('booking_time')
                            ->label('Время бронирования')
                            ->format('H:i')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->validationMessages([
                                'required' => 'Укажите время бронирования.',
                            ]),
                        TextInput::make('guests_count')
                            ->label('Количество гостей')
                            ->numeric()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->minValue(1)
                            ->maxValue(10)
                            ->validationMessages([
                                'required' => 'Укажите количество гостей.',
                                'min' => 'Минимальное количество гостей — 1.',
                                'max' => 'Максимальное количество гостей — 10.',
                            ]),
                        TextInput::make('contact_phone')
                            ->label('Контактный телефон')
                            ->mask('+7(999)-999-99-99')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->rules(['regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'])
                            ->validationMessages([
                                'required' => 'Укажите контактный телефон.',
                                'regex' => 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Управление статусом')
                    ->schema([
                        Select::make('status')
                            ->label('Статус бронирования')
                            ->options([
                                'new' => 'Новое',
                                'completed' => 'Посещение состоялось',
                                'canceled' => 'Отменено',
                            ])
                            ->required()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                        Textarea::make('review_text')
                            ->label('Отзыв клиента')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit' && auth()->user()?->is_admin),
                        TextInput::make('review_rating')
                            ->label('Оценка клиента')
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
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->user->first_name . ' ' . $record->user->last_name),
                Tables\Columns\TextColumn::make('booking_date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_time')
                    ->label('Время'),
                Tables\Columns\TextColumn::make('guests_count')
                    ->label('Гости'),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Телефон'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Новое',
                        'completed' => 'Посещение состоялось',
                        'canceled' => 'Отменено',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('review_rating')
                    ->label('Оценка')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('review_text')
                    ->label('Отзыв')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новое',
                        'completed' => 'Посещение состоялось',
                        'canceled' => 'Отменено',
                    ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('review')
                    ->label('Оставить отзыв')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn ($record) => $record->status === 'completed' && !auth()->user()?->is_admin)
                    ->form([
                        Textarea::make('review_text')
                            ->label('Отзыв')
                            ->required()
                            ->maxLength(1000),
                        Select::make('review_rating')
                            ->label('Оценка')
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'review_text' => $data['review_text'],
                            'review_rating' => $data['review_rating'],
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Отзыв сохранен!')
                            ->send();
                    }),
                \Filament\Actions\EditAction::make()
                    ->label('Редактировать')
                    ->visible(fn ($record) => !auth()->user()?->is_admin),
                \Filament\Actions\Action::make('complete')
                    ->label('Подтвердить посещение')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => auth()->user()?->is_admin && $record->status === 'new')
                    ->action(function ($record) {
                        $record->update(['status' => 'completed']);
                        Notification::make()
                            ->success()
                            ->title('Посещение подтверждено')
                            ->send();
                    }),
                \Filament\Actions\Action::make('cancel')
                    ->label('Отменить бронирование')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => auth()->user()?->is_admin && $record->status === 'new')
                    ->action(function ($record) {
                        $record->update(['status' => 'canceled']);
                        Notification::make()
                            ->warning()
                            ->title('Бронирование отменено')
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
PHP;
    }

    private function getListBookingsPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return auth()->user()?->is_admin ? 'Управление бронированиями' : 'Мои бронирования';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Забронировать столик'),
        ];
    }
}
PHP;
    }

    private function getCreateBookingPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return 'Забронировать столик';
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
            ->label('Забронировать');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Забронировать'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Столик успешно забронирован!';
    }
}
PHP;
    }

    private function getEditBookingPage(): string
    {
        return <<<'PHP'
<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return 'Редактирование бронирования';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->visible(fn (): bool => !auth()->user()?->is_admin),
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
        return 'Бронирование обновлено!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;
    }

    // ─── Seeders ───────────────────────────────────────────────────────

    private function getAdminSeederV1(): string
    {
        return <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'login' => 'avto2024',
            'password' => bcrypt('poehali'),
            'fio' => 'Администратор Системы',
            'phone' => '+7(999)-999-99-99',
            'email' => 'admin@auto.ru',
            'is_admin' => true,
        ]);
    }
}
PHP;
    }

    private function getAdminSeederV2(): string
    {
        return <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'login' => 'admin',
            'password' => bcrypt('bookworm'),
            'fio' => 'Администратор Системы',
            'phone' => '+7(999)-999-99-99',
            'email' => 'admin@book.ru',
            'is_admin' => true,
        ]);
    }
}
PHP;
    }

    private function getAdminSeederV3(): string
    {
        return <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'login' => 'admin',
            'password' => bcrypt('restaurant'),
            'first_name' => 'Администратор',
            'last_name' => 'Системы',
            'phone' => '+7(999)-999-99-99',
            'email' => 'admin@rest.ru',
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
