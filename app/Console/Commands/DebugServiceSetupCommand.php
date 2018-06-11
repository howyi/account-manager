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

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function handle()
    {
        $repository = app(EntityManager::class)->getRepository(AuthenticateService::class);
        $qb = $repository
            ->createQueryBuilder('s')
            ->values(
               [
                    'service_id' => '?',
                    'service_name' => '?',
                    'service_type' => '?',
                    'client_id' => '?',
                    'client_secret' => '?',
                    'redirect_url' => '?',
                ]
            )
            ->setParameter(0, env('DEBUG_SERVICE_ID'))
            ->setParameter(1, env('DEBUG_SERVICE_NAME'))
            ->setParameter(2, env('DEBUG_SERVICE_TYPE'))
            ->setParameter(3, env('DEBUG_SERVICE_CLIENT_ID'))
            ->setParameter(4, env('DEBUG_SERVICE_CLIENT_SECRET'))
            ->setParameter(5, env('DEBUG_SERVICE_REDIRECT_URL'));

        dump($qb->getResult());
    }
}
