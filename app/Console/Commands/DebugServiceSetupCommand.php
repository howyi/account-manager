<?php

namespace App\Console\Commands;

use App\Models\AuthenticateService;
use App\Utils\Slack;
use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;

class DebugServiceSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:service:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert one service from env';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $pdo = app(EntityManager::class)->getConnection()->getWrappedConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO authenticate_services (service_id, service_name, service_type, client_id, client_secret, redirect_url, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW());'
        );
        $stmt->execute([
            env('DEBUG_SERVICE_ID'),
            env('DEBUG_SERVICE_NAME'),
            env('DEBUG_SERVICE_TYPE'),
            env('DEBUG_SERVICE_CLIENT_ID'),
            env('DEBUG_SERVICE_CLIENT_SECRET'),
            env('DEBUG_SERVICE_REDIRECT_URL'),
        ]);
    }
}
