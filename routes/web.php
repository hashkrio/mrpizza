<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\MyOrderController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/language/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'pt'])) {
        abort(404);
    }
    App::setLocale($locale);
    session(['locale' => $locale]);
    if (Auth::check()) {
        $user = Auth::user();
        $user->locale = $locale;
        $user->save();
    }

    return back();
})->name('language');

Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');

    return 'Application cache cleared!';
});

Route::get('/migrate', function () {
    Artisan::call('migrate');

    return 'Application migrated!';
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::post('/menu/preselect', [HomeController::class, 'menuGo'])->name('menu.preselect');
Route::get('/menu/items', [HomeController::class, 'menuItems'])->name('menu.items');
Route::get('/menu/item-detail/{id}', [HomeController::class, 'itemDetail'])->name('menu.detail');

Route::get('/cart', [CartController::class, 'cart'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/addons/sync', [CartController::class, 'syncAddons'])->name('cart.addons.sync');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/note', [CheckoutController::class, 'saveNote'])->name('checkout.note');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login/password', [LoginController::class, 'loginWithPassword'])->name('login.password');
    Route::post('/login/send-otp', [LoginController::class, 'sendOtp'])->name('login.sendOtp');
    Route::post('/login/verify-otp', [LoginController::class, 'verifyOtp'])->name('login.verifyOtp');

    Route::get('register', [RegisterController::class, 'register'])->name('register');
    Route::post('register/send-otp', [RegisterController::class, 'sendOtp'])->name('register.sendOtp');
    Route::post('register/verify-otp', [RegisterController::class, 'verifyOtp'])->name('register.verifyOtp');
    Route::post('/register/resend-otp', [RegisterController::class, 'resendOtp'])->name('register.resendOtp');

Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot-password');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // User Profile
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/checkout/place', [CheckoutController::class, 'checkout_place'])->name('checkout.place');

    Route::get('my-orders', [MyOrderController::class, 'index'])->name('my.orders');
    Route::get('my-orders/data', [MyOrderController::class, 'data'])->name('my.orders.data');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // Route::get('/dashboard', function () {
        //     return view('admin.dashboard');
        // })->name('admin.dashboard');

         Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');


        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::post('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');

        // Company Settings
        Route::get('/site-setting', [CompanySettingController::class, 'edit'])->name('admin.site.edit');
        Route::post('/site-setting', [CompanySettingController::class, 'update'])->name('admin.site.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/categories/data', [CategoryController::class, 'data'])->name('admin.categories.data');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        // Items
        Route::get('/items', [ItemController::class, 'index'])->name('admin.items.index');
        Route::get('/items/data', [ItemController::class, 'data'])->name('admin.items.data');
        Route::get('/items/create', [ItemController::class, 'create'])->name('admin.items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('admin.items.store');
        Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('admin.items.edit');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('admin.items.update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('admin.items.destroy');

        // Addons
        Route::get('/addons', [AddonController::class, 'index'])->name('admin.addons.index');
        Route::get('/addons/data', [AddonController::class, 'data'])->name('admin.addons.data');
        Route::get('/addons/create', [AddonController::class, 'create'])->name('admin.addons.create');
        Route::post('/addons', [AddonController::class, 'store'])->name('admin.addons.store');
        Route::get('/addons/{addon}/edit', [AddonController::class, 'edit'])->name('admin.addons.edit');
        Route::put('/addons/{addon}', [AddonController::class, 'update'])->name('admin.addons.update');
        Route::delete('/addons/{addon}', [AddonController::class, 'destroy'])->name('admin.addons.destroy');

        // Translations
        Route::get('translations', [TranslationController::class, 'index'])->name('admin.translations.index');
        Route::post('translations', [TranslationController::class, 'store'])->name('admin.translations.store');
        Route::put('translations', [TranslationController::class, 'update'])->name('admin.translations.update');
        Route::put('translations/rename', [TranslationController::class, 'rename'])->name('admin.translations.rename');
        Route::delete('translations', [TranslationController::class, 'destroy'])->name('admin.translations.destroy');

        // Users
        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/data', [UserController::class, 'data'])->name('admin.users.data');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('orders/data', [OrderController::class, 'data'])->name('admin.orders.data');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');


    });
