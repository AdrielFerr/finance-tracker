<?php

namespace App\Providers;

use App\Imports\Csv\ColumnNames;
use App\Imports\Csv\CsvDocument;
use App\Imports\Csv\Profiles\BradescoProfile;
use App\Imports\Csv\Profiles\GenericProfile;
use App\Imports\Csv\Profiles\InterProfile;
use App\Imports\Csv\Profiles\NubankAccountProfile;
use App\Imports\Csv\Profiles\NubankInvoiceProfile;
use App\Imports\Readers\CsvStatementReader;
use App\Imports\Readers\OfxStatementReader;
use App\Imports\StatementReaderResolver;
use App\Notifications\Channels\InAppChannel;
use App\Notifications\Channels\MailChannel;
use App\Notifications\Channels\WhatsAppChannel;
use App\Repositories\DueExpenseRepository;
use App\Services\DueExpenseNotifier;
use App\Services\NotificationFeed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CsvStatementReader::class, function ($app) {
            return new CsvStatementReader(
                $app->make(CsvDocument::class),
                $app->make(ColumnNames::class),
                [
                    $app->make(NubankInvoiceProfile::class),
                    $app->make(NubankAccountProfile::class),
                    $app->make(InterProfile::class),
                    $app->make(BradescoProfile::class),
                    $app->make(GenericProfile::class),
                ]
            );
        });

        $this->app->singleton(StatementReaderResolver::class, function ($app) {
            return new StatementReaderResolver([
                $app->make(OfxStatementReader::class),
                $app->make(CsvStatementReader::class),
            ]);
        });

        $this->app->singleton(DueExpenseNotifier::class, function ($app) {
            return new DueExpenseNotifier(
                $app->make(DueExpenseRepository::class),
                [
                    $app->make(InAppChannel::class),
                    $app->make(MailChannel::class),
                    $app->make(WhatsAppChannel::class),
                ]
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            $feed = app(NotificationFeed::class);

            $view->with([
                'bellUnreadCount' => $user ? $feed->unreadCountFor($user) : 0,
                'bellNotifications' => $user ? $feed->recentFor($user) : collect(),
            ]);
        });
    }
}
