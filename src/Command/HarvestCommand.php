<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Service\OaiPmhService;
use App\Service\ElasticSearchService;


class HarvestCommand extends Command
{
    protected static $defaultName = 'app:harvest';

    private $oaiPmhService;
    private $elasticSearchService;

    public function __construct(OaiPmhService $oaiPmhService, ElasticSearchService $elasticSearchService)
    {
        $this->oaiPmhService = $oaiPmhService;
        $this->elasticSearchService = $elasticSearchService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Harvest OAI-PMH soruce')
            ->addArgument('repository', InputArgument::OPTIONAL, 'Repository to harvest from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $input->getArgument('repository');

        $result = [];

        if ($repository) {
            $this->elasticSearchService->createIndex();
            
            $io->note(sprintf('Start harvesting from: %s ...', $repository));

            $result = $this->oaiPmhService->harvest($repository);
            $io->note(sprintf('Got: %s resources, importing to ElasticSearch', count($result)));

            $progressBar = new ProgressBar($output, count($result));
            $progressBar->start();
            
            foreach($result as $index => $record){
                $this->elasticSearchService->index($record);
                $progressBar->advance();
            }

            $progressBar->finish();
        }

        $io->success(sprintf('Harvesting done. resources: %s', count($result)));        
    }
}
