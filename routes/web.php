<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AgreementSignatureController;
use App\Http\Controllers\Admin\AgreementTemplateController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\CoachCredentialController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\GuardianCredentialController;
use App\Http\Controllers\Admin\PillarController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\SquadController;
use App\Http\Controllers\Admin\SquadEnrolmentController;
use App\Http\Controllers\Admin\StrikeLevelController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentCredentialController;
use App\Http\Controllers\Admin\StudentGuardianController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\Coach\AttendanceController;
use App\Http\Controllers\Coach\CoachDashboardController;
use App\Http\Controllers\Coach\CoachSessionController;
use App\Http\Controllers\Coach\CoachSquadController;
use App\Http\Controllers\CoachPhotoController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Guardian\GuardianAttendanceController;
use App\Http\Controllers\Guardian\GuardianDashboardController;
use App\Http\Controllers\Guardian\GuardianScheduleController;
use App\Http\Controllers\Guardian\GuardianStudentController;
use App\Http\Controllers\Guardian\ProfileController as GuardianProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Public\PublicController;
use App\Http\Controllers\Student\StudentAttendanceController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentScheduleController;
use App\Http\Controllers\StudentPhotoController;
use Illuminate\Support\Facades\Route;

// --- Public -----------------------------------------------------------

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/framework', [PublicController::class, 'framework'])->name('framework');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

require __DIR__.'/auth.php';

// --- Authenticated dashboard redirect ----------------------------------

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'password.changed'])
    ->name('dashboard.redirect');

// --- Agreement gate (guardian) ------------------------------------------
//
// SPEC.md §7 lists this group as (auth, role:guardian, password.changed), and
// §8.3 requires the forced password change to be completed first: a binding
// agreement may not be signed while on an admin-issued password.

Route::middleware(['auth', 'role:guardian', 'password.changed'])->group(function () {
    Route::get('/agreement/{student}', [AgreementController::class, 'show'])->name('agreement.show');
    Route::post('/agreement/{student}', [AgreementController::class, 'sign'])->name('agreement.sign');
    Route::get('/agreement/{student}/download', [AgreementController::class, 'download'])->name('agreement.download');
});

// --- Guardian portal ------------------------------------------------------

Route::middleware(['auth', 'role:guardian', 'password.changed', 'agreement.signed'])
    ->prefix('portal')
    ->name('guardian.')
    ->group(function () {
        Route::get('/', [GuardianDashboardController::class, 'index'])->name('dashboard');
        Route::get('/children', [GuardianStudentController::class, 'index'])->name('children.index');
        Route::get('/children/{student}', [GuardianStudentController::class, 'show'])->name('children.show');
        Route::get('/schedule', [GuardianScheduleController::class, 'index'])->name('schedule');
        Route::get('/attendance/{student}', [GuardianAttendanceController::class, 'index'])->name('attendance');
        Route::get('/profile', [GuardianProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [GuardianProfileController::class, 'update'])->name('profile.update');
    });

// --- Student portal ---------------------------------------------------

Route::middleware(['auth', 'role:student', 'password.changed', 'agreement.signed'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule');
        Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance');
        Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    });

// --- Coach portal -------------------------------------------------------

Route::middleware(['auth', 'role:coach', 'password.changed'])
    ->prefix('coach')
    ->name('coach.')
    ->group(function () {
        Route::get('/', [CoachDashboardController::class, 'index'])->name('dashboard');
        Route::get('/squads', [CoachSquadController::class, 'index'])->name('squads.index');
        Route::get('/squads/{squad}', [CoachSquadController::class, 'show'])->name('squads.show');
        Route::get('/sessions', [CoachSessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/{session}', [CoachSessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/start', [CoachSessionController::class, 'start'])->name('sessions.start');
        Route::post('/sessions/{session}/complete', [CoachSessionController::class, 'complete'])->name('sessions.complete');
        Route::get('/sessions/{session}/attendance', [AttendanceController::class, 'edit'])->name('sessions.attendance.edit');
        Route::post('/sessions/{session}/attendance', [AttendanceController::class, 'store'])->name('sessions.attendance.store');
    });

// --- Admin portal -------------------------------------------------------

Route::middleware(['auth', 'role:admin', 'password.changed'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('students', StudentController::class);
        Route::post('students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.status');
        Route::post('students/{student}/guardians', [StudentGuardianController::class, 'store'])->name('students.guardians.store');
        Route::delete('students/{student}/guardians/{guardian}', [StudentGuardianController::class, 'destroy'])->name('students.guardians.destroy');
        Route::post('students/{student}/credentials', [StudentCredentialController::class, 'issue'])->name('students.credentials.issue');

        Route::resource('guardians', GuardianController::class);
        Route::post('guardians/{guardian}/credentials', [GuardianCredentialController::class, 'reset'])->name('guardians.credentials.reset');

        Route::resource('coaches', CoachController::class);
        Route::post('coaches/{coach}/credentials', [CoachCredentialController::class, 'reset'])->name('coaches.credentials.reset');

        Route::resource('squads', SquadController::class);
        Route::post('squads/{squad}/enrol', [SquadEnrolmentController::class, 'store'])->name('squads.enrol.store');
        Route::delete('squads/{squad}/enrol/{student}', [SquadEnrolmentController::class, 'destroy'])->name('squads.enrol.destroy');

        Route::resource('sessions', AdminSessionController::class);
        Route::post('sessions/generate', [AdminSessionController::class, 'generateFromSchedule'])->name('sessions.generate');

        Route::resource('agreement-templates', AgreementTemplateController::class);
        Route::post('agreement-templates/{agreement_template}/publish', [AgreementTemplateController::class, 'publish'])->name('agreement-templates.publish');
        Route::get('agreements', [AgreementSignatureController::class, 'index'])->name('agreements.index');

        Route::get('framework/pillars', [PillarController::class, 'index'])->name('framework.pillars.index');
        Route::get('framework/pillars/{pillar}/edit', [PillarController::class, 'edit'])->name('framework.pillars.edit');
        Route::patch('framework/pillars/{pillar}', [PillarController::class, 'update'])->name('framework.pillars.update');

        Route::get('framework/strike-levels', [StrikeLevelController::class, 'index'])->name('framework.strike-levels.index');
        Route::get('framework/strike-levels/{strike_level}/edit', [StrikeLevelController::class, 'edit'])->name('framework.strike-levels.edit');
        Route::patch('framework/strike-levels/{strike_level}', [StrikeLevelController::class, 'update'])->name('framework.strike-levels.update');

        Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');

        Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    });

// --- Private, signed, policy-checked student photo route ----------------

// `password.changed` precedes `signed` so a pending password change redirects
// rather than failing the signature check first (SPEC.md §8.1: ALL routes).
Route::get('/students/{student}/photo', StudentPhotoController::class)
    ->middleware(['auth', 'password.changed', 'signed'])
    ->name('students.photo');

// --- Public, signed coach photo route ------------------------------------

// Coach photos appear on the public homepage, so no auth — the signature
// alone stops URL enumeration. Not child data (contrast students.photo).
Route::get('/coaches/{coach}/photo', CoachPhotoController::class)
    ->middleware('signed')
    ->name('coaches.photo');
